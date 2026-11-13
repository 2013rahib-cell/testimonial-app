<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TestimonialStatus;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardAnalyticsService
{
    /**
     * Get summary metrics for the authenticated user.
     *
     * @return array{
     *     totalTestimonials: int,
     *     uniqueCustomers: int,
     *     approvedCount: int,
     *     activeSpacesCount: int,
     *     averageRating: float
     * }
     */
    public function getSummaryMetrics(User $user): array
    {
        $totalTestimonials = $user->testimonials()->count();

        // Calculate unique customers based on submitter email or name
        $uniqueCustomers = $user->testimonials()
            ->whereNotNull('submitter_email')
            ->where('submitter_email', '<>', '')
            ->distinct()
            ->count('submitter_email');

        if ($uniqueCustomers === 0 && $totalTestimonials > 0) {
            $uniqueCustomers = $user->testimonials()
                ->whereNotNull('submitter_name')
                ->where('submitter_name', '<>', '')
                ->distinct()
                ->count('submitter_name');
        }

        $approvedCount = $user->testimonials()
            ->where('status', TestimonialStatus::Approved)
            ->count();

        $activeSpacesCount = $user->spaces()
            ->where('is_active', true)
            ->count();

        $averageRating = (float) ($user->testimonials()
            ->whereNotNull('rating')
            ->avg('rating') ?? 0.0);

        return [
            'totalTestimonials' => $totalTestimonials,
            'uniqueCustomers' => $uniqueCustomers,
            'approvedCount' => $approvedCount,
            'activeSpacesCount' => $activeSpacesCount,
            'averageRating' => round($averageRating, 1),
        ];
    }

    /**
     * Get time series data for testimonials based on day or week density.
     *
     * @return array{
     *     labels: list<string>,
     *     data: list<int>,
     *     max: int,
     *     total: int,
     *     points: list<array{label: string, count: int, x: float, y: float}>,
     *     svgPath: string,
     *     areaPath: string
     * }
     */
    public function getTimeSeriesData(User $user, string $density = 'day'): array
    {
        $density = in_array($density, ['day', 'week'], true) ? $density : 'day';

        return $density === 'week'
            ? $this->getWeeklyTimeSeries($user)
            : $this->getDailyTimeSeries($user);
    }

    /**
     * Daily time series over the past 14 days.
     *
     * @return array{
     *     labels: list<string>,
     *     data: list<int>,
     *     max: int,
     *     total: int,
     *     points: list<array{label: string, count: int, x: float, y: float}>,
     *     svgPath: string,
     *     areaPath: string
     * }
     */
    protected function getDailyTimeSeries(User $user): array
    {
        $daysCount = 14;
        $startDate = Carbon::today()->subDays($daysCount - 1)->startOfDay();

        $testimonials = $user->testimonials()
            ->where('testimonials.created_at', '>=', $startDate)
            ->get(['testimonials.created_at']);

        $buckets = [];
        for ($i = 0; $i < $daysCount; $i++) {
            $date = $startDate->copy()->addDays($i);
            $key = $date->format('Y-m-d');
            $buckets[$key] = [
                'label' => $date->format('M j'),
                'count' => 0,
            ];
        }

        foreach ($testimonials as $testimonial) {
            $key = $testimonial->created_at?->format('Y-m-d');
            if ($key && isset($buckets[$key])) {
                $buckets[$key]['count']++;
            }
        }

        return $this->formatChartData(array_values($buckets));
    }

    /**
     * Weekly time series over the past 8 weeks.
     *
     * @return array{
     *     labels: list<string>,
     *     data: list<int>,
     *     max: int,
     *     total: int,
     *     points: list<array{label: string, count: int, x: float, y: float}>,
     *     svgPath: string,
     *     areaPath: string
     * }
     */
    protected function getWeeklyTimeSeries(User $user): array
    {
        $weeksCount = 8;
        $startOfWeek = Carbon::today()->subWeeks($weeksCount - 1)->startOfWeek();

        $testimonials = $user->testimonials()
            ->where('testimonials.created_at', '>=', $startOfWeek)
            ->get(['testimonials.created_at']);

        $buckets = [];
        for ($i = 0; $i < $weeksCount; $i++) {
            $weekStart = $startOfWeek->copy()->addWeeks($i);
            $key = $weekStart->format('o-W');
            $buckets[$key] = [
                'label' => $weekStart->format('M j'),
                'count' => 0,
            ];
        }

        foreach ($testimonials as $testimonial) {
            $key = $testimonial->created_at?->format('o-W');
            if ($key && isset($buckets[$key])) {
                $buckets[$key]['count']++;
            }
        }

        return $this->formatChartData(array_values($buckets));
    }

    /**
     * Compute coordinates and SVG curve for rendering.
     *
     * @param  list<array{label: string, count: int}>  $series
     * @return array{
     *     labels: list<string>,
     *     data: list<int>,
     *     max: int,
     *     total: int,
     *     points: list<array{label: string, count: int, x: float, y: float}>,
     *     svgPath: string,
     *     areaPath: string
     * }
     */
    protected function formatChartData(array $series): array
    {
        $counts = array_column($series, 'count');
        $labels = array_column($series, 'label');
        $maxCount = max($counts ?: [0]);
        $total = array_sum($counts);
        $yMax = max($maxCount, 5); // Minimum scale height of 5

        $chartWidth = 600.0;
        $chartHeight = 160.0;
        $padding = 20.0;
        $effectiveWidth = $chartWidth - ($padding * 2);
        $effectiveHeight = $chartHeight - ($padding * 2);

        $pointCount = count($series);
        $stepX = $pointCount > 1 ? $effectiveWidth / ($pointCount - 1) : $effectiveWidth;

        $points = [];
        foreach ($series as $idx => $item) {
            $x = round($padding + ($idx * $stepX), 2);
            $y = round($chartHeight - $padding - (($item['count'] / $yMax) * $effectiveHeight), 2);
            $points[] = [
                'label' => $item['label'],
                'count' => $item['count'],
                'x' => $x,
                'y' => $y,
            ];
        }

        // Build SVG line and area path
        $svgPath = '';
        $areaPath = '';

        if (! empty($points)) {
            $first = $points[0];
            $svgPath = "M {$first['x']} {$first['y']}";
            for ($i = 1; $i < count($points); $i++) {
                $svgPath .= " L {$points[$i]['x']} {$points[$i]['y']}";
            }

            $last = end($points);
            $bottomY = $chartHeight - $padding;
            $areaPath = "M {$first['x']} {$bottomY} L {$first['x']} {$first['y']}";
            for ($i = 1; $i < count($points); $i++) {
                $areaPath .= " L {$points[$i]['x']} {$points[$i]['y']}";
            }
            $areaPath .= " L {$last['x']} {$bottomY} Z";
        }

        return [
            'labels' => $labels,
            'data' => $counts,
            'max' => $maxCount,
            'total' => $total,
            'points' => $points,
            'svgPath' => $svgPath,
            'areaPath' => $areaPath,
        ];
    }
}

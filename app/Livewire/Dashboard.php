<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\DashboardAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Time-series chart density: 'day' or 'week'.
     */
    #[Url(as: 'density')]
    public string $density = 'day';

    /**
     * Whether the Free plan upgrade banner was dismissed for this session.
     */
    public bool $bannerDismissed = false;

    /**
     * Set the density filter for the time-series graph.
     */
    public function setDensity(string $density): void
    {
        if (in_array($density, ['day', 'week'], true)) {
            $this->density = $density;
        }
    }

    /**
     * Dismiss the upgrade banner.
     */
    public function dismissBanner(): void
    {
        $this->bannerDismissed = true;
    }

    /**
     * Current authenticated user.
     */
    #[Computed]
    public function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    /**
     * Summary counters and metrics.
     *
     * @return array{
     *     totalTestimonials: int,
     *     uniqueCustomers: int,
     *     approvedCount: int,
     *     activeSpacesCount: int,
     *     averageRating: float
     * }
     */
    #[Computed]
    public function metrics(): array
    {
        return app(DashboardAnalyticsService::class)->getSummaryMetrics($this->user);
    }

    /**
     * Time series data based on selected density.
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
    #[Computed]
    public function timeSeries(): array
    {
        return app(DashboardAnalyticsService::class)->getTimeSeriesData($this->user, $this->density);
    }

    /**
     * Spaces owned by user.
     */
    #[Computed]
    public function spacesCount(): int
    {
        return $this->user->spaces()->count();
    }

    /**
     * Render the Livewire component.
     */
    public function render(): View
    {
        return view('livewire.dashboard', [
            'user' => $this->user,
            'metrics' => $this->metrics,
            'timeSeries' => $this->timeSeries,
            'spacesCount' => $this->spacesCount,
            'maxSpaces' => $this->user->maxSpaces(),
            'maxPerSpace' => $this->user->maxTestimonialsPerSpace(),
            'isPro' => $this->user->isProPlan(),
        ]);
    }
}

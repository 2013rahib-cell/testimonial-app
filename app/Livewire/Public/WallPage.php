<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Space;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Standalone, shareable Wall of Love page: /wall/{slug}.
 * Shows only approved, non-hidden testimonials of an active space.
 *
 * @property-read Collection<int, Testimonial> $testimonials
 * @property-read float|null $averageRating
 */
#[Layout('layouts.public')]
class WallPage extends Component
{
    #[Locked]
    public Space $space;

    public function mount(string $slug): void
    {
        $this->space = Space::query()->active()->where('slug', $slug)->firstOrFail();
    }

    /**
     * @return Collection<int, Testimonial>
     */
    #[Computed]
    public function testimonials(): Collection
    {
        return $this->space->testimonials()
            ->approved()
            ->visible()
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function averageRating(): ?float
    {
        $avg = $this->testimonials->whereNotNull('rating')->avg('rating');

        return $avg === null ? null : round((float) $avg, 1);
    }

    public function render(): View
    {
        $isDark = $this->space->theme->value !== 'light';

        return view('livewire.public.wall-page', [
            'testimonials' => $this->testimonials,
            'averageRating' => $this->averageRating,
            'isDark' => $isDark,
            'theme' => $this->space->theme,
        ])->title(($this->space->title ?: $this->space->name).' – Wall of Love');
    }
}

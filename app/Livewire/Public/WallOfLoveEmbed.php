<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Space;
use App\Models\Testimonial;
use App\Support\CssColor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Livewire #[Computed] properties:
 *
 * @property-read Collection<int, Testimonial> $testimonials
 */
#[Layout('layouts.public')]
class WallOfLoveEmbed extends Component
{
    public Space $space;

    #[Url(as: 'layout')]
    public string $layout = 'masonry'; // 'masonry' | 'carousel'

    #[Url(as: 'theme')]
    public string $theme = 'light'; // 'light' | 'dark'

    #[Url(as: 'bg')]
    public string $bg = '#ffffff';

    #[Url(as: 'animations')]
    public string $animations = '1';

    public function mount(string $slug): void
    {
        $this->space = Space::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        // Sanitize layout & theme
        if (! in_array($this->layout, ['masonry', 'carousel'], true)) {
            $this->layout = 'masonry';
        }

        if (! in_array($this->theme, ['light', 'dark'], true)) {
            $this->theme = 'light';
        }

        // Prevent CSS injection via ?bg= (value is rendered inside a style attribute).
        $this->bg = CssColor::sanitize($this->bg, $this->theme === 'dark' ? '#09090b' : '#ffffff');
    }

    /**
     * Display only approved (Wall of Love hearted) and non-hidden testimonials.
     *
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

    public function render(): View
    {
        return view('livewire.public.wall-of-love-embed', [
            'space' => $this->space,
            // Re-sanitize on every render: public properties can be updated client-side.
            'bg' => CssColor::sanitize($this->bg, $this->theme === 'dark' ? '#09090b' : '#ffffff'),
            'testimonials' => $this->testimonials,
            'isDark' => $this->theme === 'dark',
            'isCarousel' => $this->layout === 'carousel',
            'hasAnimations' => $this->animations === '1' || $this->animations === 'true',
        ]);
    }
}

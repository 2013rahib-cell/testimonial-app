<?php

declare(strict_types=1);

namespace App\Livewire\Embed;

use App\Models\Space;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Generator extends Component
{
    #[Url(as: 'space')]
    public ?int $selectedSpaceId = null;

    #[Url(as: 'layout')]
    public string $layout = 'masonry'; // 'masonry' | 'carousel'

    #[Url(as: 'theme')]
    public string $theme = 'light'; // 'light' | 'dark'

    #[Url(as: 'bg')]
    public string $backgroundColor = '#ffffff';

    #[Url(as: 'animations')]
    public bool $animations = true;

    public function mount(?int $space = null): void
    {
        if ($space !== null) {
            $this->selectedSpaceId = $space;
        }

        if ($this->selectedSpaceId === null) {
            $firstSpace = $this->user->spaces()->first();
            $this->selectedSpaceId = $firstSpace?->id;
        }

        // Sanitize layout & theme
        if (! in_array($this->layout, ['masonry', 'carousel'], true)) {
            $this->layout = 'masonry';
        }

        if (! in_array($this->theme, ['light', 'dark'], true)) {
            $this->theme = 'light';
        }
    }

    #[Computed]
    public function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    /**
     * Spaces belonging to the authenticated user.
     *
     * @return Collection<int, Space>
     */
    #[Computed]
    public function spaces(): Collection
    {
        return $this->user->spaces()->orderBy('name')->get();
    }

    /**
     * Currently selected space.
     */
    #[Computed]
    public function selectedSpace(): ?Space
    {
        if (! $this->selectedSpaceId) {
            return $this->spaces->first();
        }

        return $this->spaces->firstWhere('id', $this->selectedSpaceId) ?? $this->spaces->first();
    }

    /**
     * Approved and non-hidden testimonials for preview.
     *
     * @return Collection<int, Testimonial>
     */
    #[Computed]
    public function testimonials(): Collection
    {
        if (! $this->selectedSpace) {
            return new Collection();
        }

        return $this->selectedSpace->testimonials()
            ->approved()
            ->visible()
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Total count of eligible testimonials.
     */
    #[Computed]
    public function approvedCount(): int
    {
        return $this->testimonials->count();
    }

    /**
     * Public embed URL for the currently configured widget.
     */
    #[Computed]
    public function embedUrl(): string
    {
        if (! $this->selectedSpace) {
            return '';
        }

        return route('wall-of-love.embed', [
            'slug' => $this->selectedSpace->slug,
            'layout' => $this->layout,
            'theme' => $this->theme,
            'bg' => $this->backgroundColor,
            'animations' => $this->animations ? '1' : '0',
        ]);
    }

    /**
     * Copyable iframe snippet.
     */
    #[Computed]
    public function iframeSnippet(): string
    {
        if (! $this->selectedSpace) {
            return '<!-- Select a space to generate embed code -->';
        }

        return sprintf(
            '<iframe src="%s" width="100%%" height="600" frameborder="0" style="border:0;" scrolling="auto"></iframe>',
            $this->embedUrl
        );
    }

    /**
     * Copyable JavaScript embed snippet.
     */
    #[Computed]
    public function scriptSnippet(): string
    {
        if (! $this->selectedSpace) {
            return '<!-- Select a space to generate embed code -->';
        }

        $scriptUrl = url('/embed.js');

        return sprintf(
            '<div id="wall-of-love" data-space="%s" data-layout="%s" data-theme="%s" data-bg="%s" data-animations="%s"></div>' . "\n" .
            '<script src="%s" defer></script>',
            e($this->selectedSpace->slug),
            e($this->layout),
            e($this->theme),
            e($this->backgroundColor),
            $this->animations ? '1' : '0',
            $scriptUrl
        );
    }

    public function selectSpace(int $spaceId): void
    {
        $this->selectedSpaceId = $spaceId;
        unset($this->selectedSpace, $this->testimonials, $this->approvedCount, $this->embedUrl, $this->iframeSnippet, $this->scriptSnippet);
    }

    public function setLayout(string $layout): void
    {
        if (in_array($layout, ['masonry', 'carousel'], true)) {
            $this->layout = $layout;
        }
    }

    public function setTheme(string $theme): void
    {
        if (in_array($theme, ['light', 'dark'], true)) {
            $this->theme = $theme;
            // Set matching background color if still default
            if ($theme === 'dark' && ($this->backgroundColor === '#ffffff' || $this->backgroundColor === '#f8fafc')) {
                $this->backgroundColor = '#09090b';
            } elseif ($theme === 'light' && ($this->backgroundColor === '#09090b' || $this->backgroundColor === '#18181b')) {
                $this->backgroundColor = '#ffffff';
            }
        }
    }

    public function setBackgroundColor(string $color): void
    {
        $this->backgroundColor = $color;
    }

    public function toggleAnimations(): void
    {
        $this->animations = ! $this->animations;
    }

    public function render(): View
    {
        return view('livewire.embed.generator', [
            'spaces' => $this->spaces,
            'selectedSpace' => $this->selectedSpace,
            'testimonials' => $this->testimonials,
            'approvedCount' => $this->approvedCount,
            'embedUrl' => $this->embedUrl,
            'iframeSnippet' => $this->iframeSnippet,
            'scriptSnippet' => $this->scriptSnippet,
            'isDark' => $this->theme === 'dark',
            'isCarousel' => $this->layout === 'carousel',
            'hasAnimations' => $this->animations,
        ]);
    }
}

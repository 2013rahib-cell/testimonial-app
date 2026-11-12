<?php

declare(strict_types=1);

namespace App\Livewire\Spaces;

use App\Actions\UpdateSpaceAction;
use App\Enums\SpaceTheme;
use App\Models\Space;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Edit extends Component
{
    public Space $space;

    public string $name = '';

    public string $slug = '';

    public string $title = '';

    public string $subtitle = '';

    public string $ask = '';

    public string $theme = 'light';

    public bool $collectCompany = true;

    public bool $requireCompany = false;

    public bool $collectSocialLinks = true;

    public bool $requireSocialLinks = false;

    public bool $collectAvatar = true;

    public bool $requireAvatar = false;

    public bool $enableRatings = true;

    public string $websiteUrl = '';

    public string $customCta = '';

    public bool $isActive = true;

    public function mount(Space $space): void
    {
        // Enforce strict tenant isolation
        if ($space->user_id !== Auth::id()) {
            abort(403, 'Unauthorized space access.');
        }

        $this->space = $space;
        $this->name = $space->name;
        $this->slug = $space->slug;
        $this->title = $space->title ?? '';
        $this->subtitle = $space->subtitle ?? '';
        $this->ask = $space->ask ?? '';
        $this->theme = $space->theme->value ?? 'light';
        $this->collectCompany = (bool) $space->collect_company;
        $this->requireCompany = (bool) $space->require_company;
        $this->collectSocialLinks = (bool) $space->collect_social_links;
        $this->requireSocialLinks = (bool) $space->require_social_links;
        $this->collectAvatar = (bool) $space->collect_avatar;
        $this->requireAvatar = (bool) $space->require_avatar;
        $this->enableRatings = (bool) $space->enable_ratings;
        $this->websiteUrl = $space->website_url ?? '';
        $this->customCta = $space->custom_cta ?? 'Send Testimonial';
        $this->isActive = (bool) $space->is_active;
    }

    public function updatedCollectCompany(bool $value): void
    {
        if (! $value) {
            $this->requireCompany = false;
        }
    }

    public function updatedCollectSocialLinks(bool $value): void
    {
        if (! $value) {
            $this->requireSocialLinks = false;
        }
    }

    public function updatedCollectAvatar(bool $value): void
    {
        if (! $value) {
            $this->requireAvatar = false;
        }
    }

    /**
     * @return list<SpaceTheme>
     */
    #[Computed]
    public function themes(): array
    {
        return SpaceTheme::cases();
    }

    #[Computed]
    public function publicUrl(): string
    {
        return route('spaces.submit', ['slug' => $this->space->slug]);
    }

    /**
     * Update space.
     */
    public function save(UpdateSpaceAction $action): void
    {
        // Re-verify tenant isolation
        if ($this->space->user_id !== Auth::id()) {
            abort(403, 'Unauthorized space access.');
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['required', 'string', 'min:2', 'max:255', Rule::unique('spaces', 'slug')->ignore($this->space->id)],
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:1000'],
            'ask' => ['nullable', 'string', 'max:1000'],
            'theme' => ['required', 'string', 'in:light,dark,indigo,emerald,sunset'],
            'collectCompany' => ['boolean'],
            'requireCompany' => ['boolean'],
            'collectSocialLinks' => ['boolean'],
            'requireSocialLinks' => ['boolean'],
            'collectAvatar' => ['boolean'],
            'requireAvatar' => ['boolean'],
            'enableRatings' => ['boolean'],
            'websiteUrl' => ['nullable', 'url', 'max:255'],
            'customCta' => ['nullable', 'string', 'max:100'],
            'isActive' => ['boolean'],
        ]);

        $this->space = $action->execute($this->space, [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'ask' => $validated['ask'] ?? null,
            'theme' => $validated['theme'],
            'collect_company' => $this->collectCompany,
            'require_company' => $this->requireCompany && $this->collectCompany,
            'collect_social_links' => $this->collectSocialLinks,
            'require_social_links' => $this->requireSocialLinks && $this->collectSocialLinks,
            'collect_avatar' => $this->collectAvatar,
            'require_avatar' => $this->requireAvatar && $this->collectAvatar,
            'enable_ratings' => $this->enableRatings,
            'website_url' => ! empty($validated['websiteUrl']) ? $validated['websiteUrl'] : null,
            'custom_cta' => ! empty($validated['customCta']) ? $validated['customCta'] : 'Send Testimonial',
            'is_active' => $this->isActive,
        ]);

        session()->flash('status', 'Space updated successfully!');
    }

    public function render(): View
    {
        return view('livewire.spaces.edit');
    }
}

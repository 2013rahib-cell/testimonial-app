<?php

declare(strict_types=1);

namespace App\Livewire\Spaces;

use App\Actions\CreateSpaceAction;
use App\Enums\SpaceTheme;
use App\Exceptions\PlanLimitExceededException;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Livewire #[Computed] properties:
 *
 * @property-read User $user
 * @property-read int $currentSpacesCount
 * @property-read int $maxSpacesAllowed
 * @property-read bool $isAtLimit
 */
#[Layout('layouts.app')]
class Create extends Component
{
    /**
     * Space Internal Name.
     */
    public string $name = '';

    /**
     * Unique URL Slug.
     */
    public string $slug = '';

    /**
     * Public page title / header.
     */
    public string $title = '';

    /**
     * Public page subtitle / description.
     */
    public string $subtitle = 'We would love to hear your honest thoughts and feedback about our service.';

    /**
     * Ask prompt / questions asked to submitters.
     */
    public string $ask = 'What is the biggest benefit or value you have received from using our product?';

    /**
     * Predefined visual theme.
     */
    public string $theme = 'light';

    /**
     * Form toggles.
     */
    public bool $collectCompany = true;

    public bool $requireCompany = false;

    public bool $collectSocialLinks = true;

    public bool $requireSocialLinks = false;

    public bool $collectAvatar = true;

    public bool $requireAvatar = false;

    public bool $enableRatings = true;

    /**
     * Optional website & custom CTA.
     */
    public string $websiteUrl = '';

    public string $customCta = 'Send Testimonial';

    /**
     * Track whether user has manually edited the slug.
     */
    public bool $manuallyEditedSlug = false;

    /**
     * Automatically suggest title and slug when name changes.
     */
    /**
     * Name before the current update – used to tell an auto-filled slug from a typed one.
     */
    private string $previousName = '';

    public function updatingName(string $value): void
    {
        $this->previousName = $this->name;
    }

    public function updatedName(string $value): void
    {
        // Only auto-fill while the slug is empty or still the auto-generated one.
        // (A typed slug can arrive in the same request as a debounced name update.)
        $slugIsAuto = $this->slug === '' || $this->slug === Str::slug($this->previousName);

        if (! $this->manuallyEditedSlug && $slugIsAuto) {
            $this->slug = Str::slug($value);
        }

        if (blank($this->title) || $this->title === 'Would you share a quick review for '.Str::before($this->title, '?')) {
            $this->title = 'Would you share a quick review for '.trim($value).'?';
        }
    }

    public function updatedSlug(): void
    {
        $this->manuallyEditedSlug = true;
    }

    /**
     * If collectCompany is turned off, requireCompany must be false.
     */
    public function updatedCollectCompany(bool $value): void
    {
        if (! $value) {
            $this->requireCompany = false;
        }
    }

    /**
     * If collectSocialLinks is turned off, requireSocialLinks must be false.
     */
    public function updatedCollectSocialLinks(bool $value): void
    {
        if (! $value) {
            $this->requireSocialLinks = false;
        }
    }

    /**
     * If collectAvatar is turned off, requireAvatar must be false.
     */
    public function updatedCollectAvatar(bool $value): void
    {
        if (! $value) {
            $this->requireAvatar = false;
        }
    }

    #[Computed]
    public function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    #[Computed]
    public function currentSpacesCount(): int
    {
        return $this->user->spaces()->count();
    }

    #[Computed]
    public function maxSpacesAllowed(): int
    {
        return $this->user->maxSpaces();
    }

    #[Computed]
    public function isAtLimit(): bool
    {
        return $this->currentSpacesCount >= $this->maxSpacesAllowed;
    }

    /**
     * Available predefined visual themes.
     *
     * @return list<SpaceTheme>
     */
    #[Computed]
    public function themes(): array
    {
        return SpaceTheme::cases();
    }

    /**
     * Save and create the new space.
     */
    public function save(CreateSpaceAction $action): mixed
    {
        if ($this->isAtLimit) {
            $this->addError(
                'limit',
                $this->user->isProPlan()
                    ? "You have reached the Pro plan limit of {$this->maxSpacesAllowed} spaces. Delete an unused space to create a new one."
                    : "You have reached your limit of {$this->maxSpacesAllowed} spaces on the Free plan. Please upgrade to Pro to create more spaces."
            );

            return null;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['nullable', 'string', 'min:2', 'max:255', 'unique:spaces,slug'],
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
        ]);

        try {
            $space = $action->execute($this->user, [
                'name' => $validated['name'],
                'slug' => $validated['slug'] ?? null,
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
            ]);

            session()->flash('status', 'Space created successfully!');

            return $this->redirectRoute('spaces.success', ['space' => $space->slug]);
        } catch (PlanLimitExceededException $e) {
            $this->addError('limit', $e->getMessage());

            return null;
        }
    }

    public function render(): View
    {
        return view('livewire.spaces.create');
    }
}

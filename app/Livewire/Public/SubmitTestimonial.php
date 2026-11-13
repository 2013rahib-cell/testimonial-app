<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Enums\TestimonialStatus;
use App\Enums\TestimonialType;
use App\Models\Space;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.public')]
class SubmitTestimonial extends Component
{
    use WithFileUploads;

    /** Max submissions per visitor (IP) per space within the decay window. */
    public const MAX_ATTEMPTS = 10;

    public const DECAY_SECONDS = 3600;

    #[Locked]
    public Space $space;

    public string $name = '';

    public string $email = '';

    public int $rating = 5;

    public string $content = '';

    public string $company = '';

    public string $role = '';

    public string $socialLinks = '';

    public mixed $avatar = null;

    /** Social-sharing consent must be given explicitly (never pre-checked). */
    public bool $consent = false;

    public bool $submitted = false;

    public function mount(string $slug): void
    {
        $this->space = Space::where('slug', $slug)->firstOrFail();
    }

    public function setRating(int $rating): void
    {
        if ($rating >= 1 && $rating <= 5) {
            $this->rating = $rating;
        }
    }

    public function submit(): void
    {
        if (! $this->space->is_active) {
            $this->addError('form', 'This space is not currently accepting submissions.');

            return;
        }

        if ($this->space->testimonials()->count() >= $this->space->user->maxTestimonialsPerSpace()) {
            $this->addError('form', 'This space has reached its maximum testimonial collection limit.');

            return;
        }

        // Normalise "linkedin.com/in/jane" → "https://linkedin.com/in/jane" before validating.
        $this->socialLinks = $this->normaliseUrl($this->socialLinks);

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'content' => ['required', 'string', 'min:10', 'max:2000'],
            'consent' => ['accepted'],
        ];

        if ($this->space->enable_ratings) {
            $rules['rating'] = ['required', 'integer', 'between:1,5'];
        }

        if ($this->space->collect_company) {
            $rules['company'] = $this->space->require_company
                ? ['required', 'string', 'max:255']
                : ['nullable', 'string', 'max:255'];
            $rules['role'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->space->collect_social_links) {
            $rules['socialLinks'] = $this->space->require_social_links
                ? ['required', 'url:http,https', 'max:255']
                : ['nullable', 'url:http,https', 'max:255'];
        }

        if ($this->space->collect_avatar) {
            $rules['avatar'] = $this->space->require_avatar
                ? ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048']
                : ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'];
        }

        $this->validate($rules, [
            'consent.accepted' => 'Please confirm we may share your testimonial.',
            'socialLinks.url' => 'Please enter a valid link, e.g. https://linkedin.com/in/yourname.',
        ], [
            'socialLinks' => 'social link',
            'content' => 'testimonial',
        ]);

        // Throttle after validation so visitors still see field errors; only valid submissions count.
        $throttleKey = 'testimonial-submit:'.$this->space->id.':'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);
            $this->addError('form', "Too many submissions. Please try again in {$minutes} minute(s).");

            return;
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

        // Only persist an upload when this space actually collects avatars.
        $avatarPath = null;
        if ($this->space->collect_avatar && $this->avatar) {
            $avatarPath = $this->avatar->store('avatars', 'public');
        }

        $this->space->testimonials()->create([
            'submitter_name' => trim($this->name),
            'submitter_email' => trim($this->email),
            'submitter_company' => $this->space->collect_company && $this->company !== '' ? trim($this->company) : null,
            'submitter_role' => $this->space->collect_company && $this->role !== '' ? trim($this->role) : null,
            'submitter_social_url' => $this->space->collect_social_links && $this->socialLinks !== '' ? $this->socialLinks : null,
            'submitter_avatar_path' => $avatarPath,
            'content' => trim($this->content),
            'rating' => $this->space->enable_ratings ? $this->rating : null,
            'type' => TestimonialType::Text,
            'video_url' => null,
            'status' => TestimonialStatus::Pending,
            'is_featured' => false,
        ]);

        $this->submitted = true;
    }

    /**
     * Reset the form so the visitor can leave another testimonial.
     */
    public function submitAnother(): void
    {
        $this->reset(['name', 'email', 'rating', 'content', 'company', 'role', 'socialLinks', 'avatar', 'consent', 'submitted']);
        $this->resetValidation();
    }

    private function normaliseUrl(string $value): string
    {
        $value = trim($value);

        if ($value === '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $value) === 1) {
            return $value;
        }

        return 'https://'.Str::after($value, '//');
    }

    public function render(): View
    {
        return view('livewire.public.submit-testimonial', [
            'theme' => $this->space->theme,
        ]);
    }
}

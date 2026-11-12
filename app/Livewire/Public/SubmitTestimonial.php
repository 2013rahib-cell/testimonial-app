<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Enums\TestimonialStatus;
use App\Enums\TestimonialType;
use App\Models\Space;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.public')]
class SubmitTestimonial extends Component
{
    use WithFileUploads;

    public Space $space;

    public string $name = '';

    public string $email = '';

    public int $rating = 5;

    public string $content = '';

    public string $company = '';

    public string $role = '';

    public string $socialLinks = '';

    public mixed $avatar = null;

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

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'content' => ['required', 'string', 'min:10', 'max:2000'],
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
                ? ['required', 'string', 'max:255']
                : ['nullable', 'string', 'max:255'];
        }

        if ($this->space->collect_avatar) {
            $rules['avatar'] = $this->space->require_avatar
                ? ['required', 'image', 'max:2048']
                : ['nullable', 'image', 'max:2048'];
        }

        $this->validate($rules);

        $avatarPath = null;
        if ($this->avatar) {
            $avatarPath = $this->avatar->store('avatars', 'public');
        }

        $this->space->testimonials()->create([
            'submitter_name' => trim($this->name),
            'submitter_email' => trim($this->email),
            'submitter_company' => $this->space->collect_company && ! empty($this->company) ? trim($this->company) : null,
            'submitter_role' => $this->space->collect_company && ! empty($this->role) ? trim($this->role) : null,
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

    public function render(): View
    {
        return view('livewire.public.submit-testimonial', [
            'theme' => $this->space->theme,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Spaces;

use App\Enums\TestimonialStatus;
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
class Inbox extends Component
{
    public Space $space;

    /** @var string The testimonial content being edited */
    public string $editContent = '';

    /** @var int|null The ID of the testimonial currently being edited */
    public ?int $editingId = null;

    /** @var string Filter: all | pending | approved | featured | hidden */
    #[Url(as: 'filter')]
    public string $filter = 'all';

    public function mount(Space $space): void
    {
        /** @var User $user */
        $user = Auth::user();

        // Authorize: only the space owner may access the inbox
        if ($space->user_id !== $user->id) {
            abort(403);
        }

        $this->space = $space;
    }

    #[Computed]
    public function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    /**
     * Load testimonials for the space, ordered by: featured first, then by created_at desc.
     *
     * @return Collection<int, Testimonial>
     */
    #[Computed]
    public function testimonials(): Collection
    {
        $query = $this->space->testimonials()
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at');

        return match ($this->filter) {
            'pending'  => $query->where('status', TestimonialStatus::Pending)->get(),
            'approved' => $query->where('status', TestimonialStatus::Approved)->get(),
            'featured' => $query->where('is_featured', true)->get(),
            'hidden'   => $query->where('is_hidden', true)->get(),
            default    => $query->get(),
        };
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->space->testimonials()->count();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return $this->space->testimonials()->where('status', TestimonialStatus::Pending)->count();
    }

    #[Computed]
    public function approvedCount(): int
    {
        return $this->space->testimonials()->where('status', TestimonialStatus::Approved)->count();
    }

    #[Computed]
    public function featuredCount(): int
    {
        return $this->space->testimonials()->where('is_featured', true)->count();
    }

    #[Computed]
    public function hiddenCount(): int
    {
        return $this->space->testimonials()->where('is_hidden', true)->count();
    }

    #[Computed]
    public function collectionCap(): int
    {
        return $this->user->maxTestimonialsPerSpace();
    }

    #[Computed]
    public function isAtCap(): bool
    {
        return $this->totalCount >= $this->collectionCap;
    }

    /**
     * Toggle is_featured (Favorite) for a testimonial.
     * Featured testimonials are pinned to the top.
     */
    public function toggleFavorite(int $testimonialId): void
    {
        $testimonial = $this->space->testimonials()->findOrFail($testimonialId);
        $testimonial->update(['is_featured' => ! $testimonial->is_featured]);
        unset($this->testimonials, $this->featuredCount);
    }

    /**
     * Toggle approved status (Wall of Love) for a testimonial.
     * Approved testimonials are eligible for public embedding.
     */
    public function toggleApproval(int $testimonialId): void
    {
        $testimonial = $this->space->testimonials()->findOrFail($testimonialId);

        $newStatus = $testimonial->status === TestimonialStatus::Approved
            ? TestimonialStatus::Pending
            : TestimonialStatus::Approved;

        $testimonial->update(['status' => $newStatus]);
        unset($this->testimonials, $this->approvedCount, $this->pendingCount);
    }

    /**
     * Open the inline editor for a testimonial.
     */
    public function startEdit(int $testimonialId): void
    {
        $testimonial = $this->space->testimonials()->findOrFail($testimonialId);
        $this->editingId = $testimonialId;
        $this->editContent = $testimonial->content;
    }

    /**
     * Cancel the inline editor.
     */
    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editContent = '';
    }

    /**
     * Save the edited testimonial content.
     */
    public function saveEdit(): void
    {
        $this->validate([
            'editContent' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $testimonial = $this->space->testimonials()->findOrFail($this->editingId);
        $testimonial->update(['content' => trim($this->editContent)]);

        $this->editingId = null;
        $this->editContent = '';
        unset($this->testimonials);

        session()->flash('status', 'Testimonial updated successfully.');
    }

    /**
     * Delete a testimonial permanently.
     */
    public function deleteTestimonial(int $testimonialId): void
    {
        $testimonial = $this->space->testimonials()->findOrFail($testimonialId);
        $testimonial->delete();
        unset($this->testimonials, $this->totalCount, $this->pendingCount, $this->approvedCount, $this->featuredCount, $this->hiddenCount);

        session()->flash('status', 'Testimonial deleted.');
    }

    /**
     * Toggle hidden state for a testimonial (hide from public view).
     */
    public function toggleHidden(int $testimonialId): void
    {
        $testimonial = $this->space->testimonials()->findOrFail($testimonialId);
        $testimonial->update(['is_hidden' => ! $testimonial->is_hidden]);
        unset($this->testimonials, $this->hiddenCount);
    }

    public function setFilter(string $filter): void
    {
        $allowed = ['all', 'pending', 'approved', 'featured', 'hidden'];
        if (in_array($filter, $allowed, strict: true)) {
            $this->filter = $filter;
            unset($this->testimonials);
        }
    }

    public function render(): View
    {
        return view('livewire.spaces.inbox', [
            'testimonials'  => $this->testimonials,
            'totalCount'    => $this->totalCount,
            'pendingCount'  => $this->pendingCount,
            'approvedCount' => $this->approvedCount,
            'featuredCount' => $this->featuredCount,
            'hiddenCount'   => $this->hiddenCount,
            'collectionCap' => $this->collectionCap,
            'isAtCap'       => $this->isAtCap,
        ]);
    }
}

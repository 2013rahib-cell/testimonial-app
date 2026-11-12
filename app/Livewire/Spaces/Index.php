<?php

declare(strict_types=1);

namespace App\Livewire\Spaces;

use App\Models\Space;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    #[Computed]
    public function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    /**
     * Spaces belonging strictly to this user.
     *
     * @return Collection<int, Space>
     */
    #[Computed]
    public function spaces(): Collection
    {
        return $this->user->spaces()
            ->withCount('testimonials')
            ->latest()
            ->get();
    }

    #[Computed]
    public function spacesCount(): int
    {
        return $this->spaces->count();
    }

    #[Computed]
    public function maxSpaces(): int
    {
        return $this->user->maxSpaces();
    }

    #[Computed]
    public function isAtLimit(): bool
    {
        return $this->spacesCount >= $this->maxSpaces;
    }

    /**
     * Delete a space owned by the user.
     */
    public function deleteSpace(int $spaceId): void
    {
        $space = $this->user->spaces()->findOrFail($spaceId);

        $space->delete();

        session()->flash('status', 'Space deleted successfully.');
    }

    /**
     * Toggle active status of a space.
     */
    public function toggleActive(int $spaceId): void
    {
        $space = $this->user->spaces()->findOrFail($spaceId);

        $space->update([
            'is_active' => ! $space->is_active,
        ]);

        session()->flash('status', 'Space status updated.');
    }

    public function render(): View
    {
        return view('livewire.spaces.index', [
            'spaces' => $this->spaces,
            'spacesCount' => $this->spacesCount,
            'maxSpaces' => $this->maxSpaces,
            'isAtLimit' => $this->isAtLimit,
            'isPro' => $this->user->isProPlan(),
        ]);
    }
}

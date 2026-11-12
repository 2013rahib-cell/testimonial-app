<?php

declare(strict_types=1);

namespace App\Livewire\Spaces;

use App\Models\Space;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Success extends Component
{
    public Space $space;

    public function mount(Space $space): void
    {
        // Enforce tenant isolation
        if ($space->user_id !== Auth::id()) {
            abort(403, 'Unauthorized space access.');
        }

        $this->space = $space;
    }

    #[Computed]
    public function publicUrl(): string
    {
        return route('spaces.submit', ['slug' => $this->space->slug]);
    }

    public function render(): View
    {
        return view('livewire.spaces.success', [
            'publicUrl' => $this->publicUrl,
        ]);
    }
}

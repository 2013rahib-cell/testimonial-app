<?php

use App\Http\Controllers\BillingCheckoutController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Billing;
use App\Livewire\Settings\MockCheckout;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Security;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', Profile::class)->name('profile.edit');
    Route::livewire('settings/billing', Billing::class)->name('settings.billing');
    Route::livewire('settings/billing/demo-checkout', MockCheckout::class)->name('billing.demo-checkout');
    Route::post('settings/billing/checkout', [BillingCheckoutController::class, 'checkout'])->name('billing.checkout');
    Route::post('settings/billing/portal', [BillingCheckoutController::class, 'portal'])->name('billing.portal');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::livewire('settings/security', Security::class)
        /* @chisel-password-confirmation */
        ->middleware([
            'password.confirm',
        ])
        /* @end-chisel-password-confirmation */
        ->name('security.edit');
});

/* @chisel-passkeys */
Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('security.edit'),
        'manage' => route('security.edit'),
    ]);
})->name('well-known.passkeys');
/* @end-chisel-passkeys */

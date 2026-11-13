<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MockPaymentGateway;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingCheckoutController extends Controller
{
    /**
     * Redirect the user to Stripe Checkout for the $9.99/mo Pro upgrade.
     */
    public function checkout(Request $request, StripeService $stripeService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (MockPaymentGateway::isEnabled()) {
            return redirect()->route('billing.demo-checkout');
        }

        if (! StripeService::isConfigured()) {
            return redirect()->route('settings.billing')->with('error', 'Payments are not configured yet.');
        }

        $session = $stripeService->createProCheckoutSession(
            user: $user,
            successUrl: route('settings.billing', ['checkout' => 'success']),
            cancelUrl: route('settings.billing', ['checkout' => 'cancelled']),
        );

        if ($session->url) {
            return redirect()->away($session->url);
        }

        return redirect()->route('settings.billing')->with('error', 'Unable to initiate checkout session.');
    }

    /**
     * Redirect the user to the Stripe Customer Portal.
     */
    public function portal(Request $request, StripeService $stripeService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! StripeService::isConfigured()) {
            return redirect()->route('settings.billing')->with('error', 'Payments are not configured yet.');
        }

        $session = $stripeService->createBillingPortalSession(
            user: $user,
            returnUrl: route('settings.billing'),
        );

        if ($session->url) {
            return redirect()->away($session->url);
        }

        return redirect()->route('settings.billing')->with('error', 'Unable to access billing portal.');
    }
}

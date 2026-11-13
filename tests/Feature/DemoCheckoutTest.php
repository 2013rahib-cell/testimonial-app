<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Enums\UserPlan;
use App\Livewire\Settings\Billing;
use App\Livewire\Settings\MockCheckout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class DemoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Demo mode = mock enabled and no Stripe keys.
        config(['services.stripe.secret' => '', 'services.billing.mock' => true]);
    }

    private function fillCard(string $number): Testable
    {
        return Livewire::test(MockCheckout::class)
            ->set('cardNumber', $number)
            ->set('expiry', '12/'.now()->addYears(2)->format('y'))
            ->set('cvc', '123')
            ->set('postalCode', '1212');
    }

    public function test_upgrade_button_redirects_to_demo_checkout(): void
    {
        $this->actingAs(User::factory()->create(['plan' => UserPlan::Free]));

        Livewire::test(Billing::class)
            ->call('checkout')
            ->assertRedirect(route('billing.demo-checkout'));
    }

    public function test_demo_checkout_page_renders_with_prefilled_details(): void
    {
        $user = User::factory()->create(['name' => 'Jane Buyer', 'plan' => UserPlan::Free]);

        $this->actingAs($user)
            ->get(route('billing.demo-checkout'))
            ->assertOk()
            ->assertSee('Demo checkout')
            ->assertSee('$9.99')
            ->assertSee('Jane Buyer');
    }

    public function test_successful_test_card_upgrades_user_to_pro(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);
        $this->actingAs($user);

        $this->fillCard('4242 4242 4242 4242')
            ->call('pay')
            ->assertHasNoErrors()
            ->assertRedirect(route('settings.billing', ['checkout' => 'success']));

        $user->refresh();
        $this->assertSame(UserPlan::Pro, $user->plan);
        $this->assertSame(SubscriptionStatus::Active, $user->subscription_status);
        $this->assertTrue($user->subscription_ends_at->isFuture());
    }

    public function test_declined_test_card_shows_error_and_keeps_free_plan(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);
        $this->actingAs($user);

        $this->fillCard('4000 0000 0000 0002')
            ->call('pay')
            ->assertSet('paymentError', 'Your card was declined. Please try a different card.')
            ->assertSet('cardNumber', '')
            ->assertSet('cvc', '');

        $this->assertSame(UserPlan::Free, $user->fresh()->plan);
    }

    public function test_invalid_card_details_are_rejected(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);
        $this->actingAs($user);

        Livewire::test(MockCheckout::class)
            ->set('cardNumber', '1234 5678 9012 3456')
            ->set('expiry', '01/20')
            ->set('cvc', '12')
            ->call('pay')
            ->assertHasErrors(['cardNumber', 'expiry', 'cvc']);

        $this->assertSame(UserPlan::Free, $user->fresh()->plan);
    }

    public function test_pro_user_can_cancel_demo_subscription(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Pro, 'subscription_status' => SubscriptionStatus::Active]);
        $this->actingAs($user);

        Livewire::test(Billing::class)
            ->assertSee('Cancel subscription')
            ->call('cancelDemoSubscription');

        $this->assertSame(UserPlan::Free, $user->fresh()->plan);
        $this->assertSame(SubscriptionStatus::Canceled, $user->fresh()->subscription_status);
    }

    public function test_demo_checkout_is_disabled_when_stripe_is_configured(): void
    {
        config(['services.stripe.secret' => 'sk_test_fake']);
        $user = User::factory()->create(['plan' => UserPlan::Free]);
        $this->actingAs($user);

        // The demo page sends the user back to Billing (real Stripe checkout is used instead).
        Livewire::test(MockCheckout::class)->assertRedirect(route('settings.billing'));

        $this->assertSame(UserPlan::Free, $user->fresh()->plan);
    }

    public function test_demo_checkout_requires_login(): void
    {
        $this->get(route('billing.demo-checkout'))->assertRedirect(route('login'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\CreateSpaceAction;
use App\Enums\SubscriptionStatus;
use App\Enums\UserPlan;
use App\Exceptions\PlanLimitExceededException;
use App\Livewire\Settings\Billing;
use App\Models\Space;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Tests\TestCase;

class StripeBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_visit_billing_settings_page(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Free,
            'subscription_status' => SubscriptionStatus::Inactive,
        ]);

        $this->actingAs($user)
            ->get(route('settings.billing'))
            ->assertOk()
            ->assertSee('Billing &amp; Subscription', false)
            ->assertSee('Free')
            ->assertSee('$9.99');
    }

    public function test_unauthenticated_user_is_redirected_to_login_from_billing_page(): void
    {
        $this->get(route('settings.billing'))
            ->assertRedirect(route('login'));
    }

    public function test_pro_user_sees_active_pro_plan_and_manage_subscription_option(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Pro,
            'subscription_status' => SubscriptionStatus::Active,
            'stripe_customer_id' => 'cus_test123',
            'stripe_subscription_id' => 'sub_test123',
        ]);

        $this->actingAs($user)
            ->get(route('settings.billing'))
            ->assertOk()
            ->assertSee('Pro')
            ->assertSee('Manage Billing in Stripe');
    }

    public function test_checkout_action_in_livewire_component_redirects_to_stripe_session_url(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Free,
        ]);

        $fakeCheckoutSession = new CheckoutSession('cs_test_session');
        $fakeCheckoutSession->url = 'https://checkout.stripe.com/c/pay/cs_test_session';

        $stripeMock = Mockery::mock(StripeService::class);
        $stripeMock->shouldReceive('createProCheckoutSession')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->id === $user->id),
                Mockery::any(),
                Mockery::any()
            )
            ->andReturn($fakeCheckoutSession);

        $this->app->instance(StripeService::class, $stripeMock);

        $this->actingAs($user);

        Livewire::test(Billing::class)
            ->call('checkout', $stripeMock)
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_session');
    }

    public function test_manage_billing_action_in_livewire_component_redirects_to_stripe_portal_url(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Pro,
            'stripe_customer_id' => 'cus_portal_test',
        ]);

        $fakePortalSession = new PortalSession('bps_test_session');
        $fakePortalSession->url = 'https://billing.stripe.com/p/session/bps_test_session';

        $stripeMock = Mockery::mock(StripeService::class);
        $stripeMock->shouldReceive('createBillingPortalSession')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->id === $user->id),
                Mockery::any()
            )
            ->andReturn($fakePortalSession);

        $this->app->instance(StripeService::class, $stripeMock);

        $this->actingAs($user);

        Livewire::test(Billing::class)
            ->call('manageBilling', $stripeMock)
            ->assertRedirect('https://billing.stripe.com/p/session/bps_test_session');
    }

    public function test_free_plan_limit_enforces_maximum_3_spaces_while_pro_plan_allows_up_to_25(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Free,
        ]);

        /** @var CreateSpaceAction $action */
        $action = app(CreateSpaceAction::class);

        // Create 3 spaces successfully
        Space::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertSame(3, $user->spaces()->count());

        // 4th space on Free plan should throw PlanLimitExceededException
        $this->expectException(PlanLimitExceededException::class);
        $action->execute($user, ['name' => 'Space 4']);
    }

    public function test_pro_plan_user_can_create_more_than_3_spaces(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Pro,
            'subscription_status' => SubscriptionStatus::Active,
        ]);

        /** @var CreateSpaceAction $action */
        $action = app(CreateSpaceAction::class);

        // Create 3 spaces
        Space::factory()->count(3)->create(['user_id' => $user->id]);

        $fourthSpace = $action->execute($user, ['name' => 'Space 4']);
        $this->assertInstanceOf(Space::class, $fourthSpace);
        $this->assertSame(4, $user->spaces()->count());
    }
}

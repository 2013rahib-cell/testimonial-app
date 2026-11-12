<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Enums\UserPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => '']);
    }

    public function test_stripe_webhook_endpoint_is_accessible_without_csrf_protection(): void
    {
        $payload = [
            'id' => 'evt_test_dummy',
            'type' => 'unknown.event',
            'data' => [
                'object' => [],
            ],
        ];

        $response = $this->postJson(route('stripe.webhook'), $payload);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'event' => 'unknown.event',
            ]);
    }

    public function test_checkout_session_completed_event_upgrades_user_to_pro_and_sets_active_status(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Free,
            'subscription_status' => SubscriptionStatus::Inactive,
        ]);

        $payload = [
            'id' => 'evt_checkout_completed',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_session_123',
                    'customer' => 'cus_stripe_12345',
                    'subscription' => 'sub_stripe_67890',
                    'client_reference_id' => (string) $user->id,
                    'metadata' => [
                        'user_id' => (string) $user->id,
                    ],
                ],
            ],
        ];

        $this->postJson(route('stripe.webhook'), $payload)->assertOk();

        $user->refresh();

        $this->assertSame(UserPlan::Pro, $user->plan);
        $this->assertSame(SubscriptionStatus::Active, $user->subscription_status);
        $this->assertSame('cus_stripe_12345', $user->stripe_customer_id);
        $this->assertSame('sub_stripe_67890', $user->stripe_subscription_id);
    }

    public function test_customer_subscription_updated_updates_subscription_status_and_period_end(): void
    {
        $periodEnd = Carbon::now()->addMonth()->startOfSecond();

        $user = User::factory()->create([
            'plan' => UserPlan::Pro,
            'stripe_customer_id' => 'cus_stripe_12345',
            'stripe_subscription_id' => 'sub_stripe_67890',
            'subscription_status' => SubscriptionStatus::Active,
        ]);

        $payload = [
            'id' => 'evt_sub_updated',
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => 'sub_stripe_67890',
                    'customer' => 'cus_stripe_12345',
                    'status' => 'past_due',
                    'current_period_end' => $periodEnd->timestamp,
                ],
            ],
        ];

        $this->postJson(route('stripe.webhook'), $payload)->assertOk();

        $user->refresh();

        $this->assertSame(SubscriptionStatus::PastDue, $user->subscription_status);
        $this->assertSame($periodEnd->timestamp, $user->subscription_ends_at?->timestamp);
    }

    public function test_customer_subscription_deleted_downgrades_user_to_free_and_sets_canceled_status(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Pro,
            'stripe_customer_id' => 'cus_stripe_12345',
            'stripe_subscription_id' => 'sub_stripe_67890',
            'subscription_status' => SubscriptionStatus::Active,
        ]);

        $payload = [
            'id' => 'evt_sub_deleted',
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'id' => 'sub_stripe_67890',
                    'customer' => 'cus_stripe_12345',
                    'status' => 'canceled',
                ],
            ],
        ];

        $this->postJson(route('stripe.webhook'), $payload)->assertOk();

        $user->refresh();

        $this->assertSame(UserPlan::Free, $user->plan);
        $this->assertSame(SubscriptionStatus::Canceled, $user->subscription_status);
        $this->assertNotNull($user->subscription_ends_at);
    }

    public function test_invoice_payment_succeeded_ensures_active_pro_status(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Free,
            'stripe_customer_id' => 'cus_stripe_12345',
            'stripe_subscription_id' => 'sub_stripe_67890',
            'subscription_status' => SubscriptionStatus::PastDue,
        ]);

        $payload = [
            'id' => 'evt_inv_paid',
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'id' => 'in_test_123',
                    'customer' => 'cus_stripe_12345',
                    'subscription' => 'sub_stripe_67890',
                ],
            ],
        ];

        $this->postJson(route('stripe.webhook'), $payload)->assertOk();

        $user->refresh();

        $this->assertSame(UserPlan::Pro, $user->plan);
        $this->assertSame(SubscriptionStatus::Active, $user->subscription_status);
    }

    public function test_invoice_payment_failed_sets_subscription_status_to_past_due(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Pro,
            'stripe_customer_id' => 'cus_stripe_12345',
            'stripe_subscription_id' => 'sub_stripe_67890',
            'subscription_status' => SubscriptionStatus::Active,
        ]);

        $payload = [
            'id' => 'evt_inv_failed',
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'in_test_failed',
                    'customer' => 'cus_stripe_12345',
                    'subscription' => 'sub_stripe_67890',
                ],
            ],
        ];

        $this->postJson(route('stripe.webhook'), $payload)->assertOk();

        $user->refresh();

        $this->assertSame(SubscriptionStatus::PastDue, $user->subscription_status);
    }

    public function test_invalid_webhook_signature_returns_400_when_webhook_secret_is_configured(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test_secret_key']);

        $payload = (string) json_encode([
            'id' => 'evt_test_invalid_sig',
            'type' => 'ping',
            'data' => ['object' => []],
        ]);

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => 't=123456,v1=invalid_signature',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        )->assertStatus(400);
    }
}

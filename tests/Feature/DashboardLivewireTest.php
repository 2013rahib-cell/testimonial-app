<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TestimonialStatus;
use App\Enums\UserPlan;
use App\Livewire\Dashboard;
use App\Models\Space;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_plan_user_sees_upgrade_banner(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Free,
        ]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSee('Free Plan')
            ->assertSee('Unlock Unlimited Growth with Testimonial Pro')
            ->assertSee('$9.99/mo');
    }

    public function test_pro_plan_user_does_not_see_upgrade_banner(): void
    {
        $user = User::factory()->pro()->create();

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertDontSee('Unlock Unlimited Growth with Testimonial Pro');
    }

    public function test_free_user_can_dismiss_upgrade_banner(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Free,
        ]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSee('Unlock Unlimited Growth with Testimonial Pro')
            ->call('dismissBanner')
            ->assertSet('bannerDismissed', true)
            ->assertDontSee('Unlock Unlimited Growth with Testimonial Pro');
    }

    public function test_summary_counters_reflect_correct_counts(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);

        // Create 3 testimonials with 2 unique email addresses
        Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_email' => 'client1@example.com',
            'status' => TestimonialStatus::Approved,
        ]);
        Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_email' => 'client1@example.com',
            'status' => TestimonialStatus::Approved,
        ]);
        Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_email' => 'client2@example.com',
            'status' => TestimonialStatus::Approved,
        ]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSeeHtml('data-test="total-testimonials-count"')
            ->assertSeeHtml('data-test="unique-customers-count"');
    }

    public function test_can_toggle_day_and_week_density_filters(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSet('density', 'day')
            ->call('setDensity', 'week')
            ->assertSet('density', 'week')
            ->call('setDensity', 'day')
            ->assertSet('density', 'day');
    }
}

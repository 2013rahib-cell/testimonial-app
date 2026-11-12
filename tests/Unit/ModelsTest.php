<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\SubscriptionStatus;
use App\Enums\TestimonialStatus;
use App\Enums\TestimonialType;
use App\Enums\UserPlan;
use App\Models\Space;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_relationships_and_plan_helpers(): void
    {
        $user = User::factory()->create([
            'plan' => UserPlan::Free,
            'subscription_status' => SubscriptionStatus::Inactive,
        ]);

        $this->assertFalse($user->isProPlan());
        $this->assertSame(3, $user->maxSpaces());
        $this->assertSame(100, $user->maxTestimonialsPerSpace());

        $space = Space::factory()->create(['user_id' => $user->id]);
        $testimonial = Testimonial::factory()->create(['space_id' => $space->id]);

        $this->assertCount(1, $user->spaces);
        $this->assertCount(1, $user->testimonials);
        $this->assertTrue($user->spaces->first()->is($space));
        $this->assertTrue($user->testimonials->first()->is($testimonial));

        // Pro user
        $proUser = User::factory()->pro()->create();
        $this->assertTrue($proUser->isProPlan());
        $this->assertSame(25, $proUser->maxSpaces());
        $this->assertSame(1000, $proUser->maxTestimonialsPerSpace());
    }

    public function test_space_scopes_and_relationships(): void
    {
        $user = User::factory()->create();
        $activeSpace = Space::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        $inactiveSpace = Space::factory()->inactive()->create(['user_id' => $user->id]);

        $this->assertTrue($activeSpace->user->is($user));
        $this->assertCount(1, Space::active()->where('user_id', $user->id)->get());
    }

    public function test_testimonial_scopes_and_relationships(): void
    {
        $space = Space::factory()->create();

        $approved = Testimonial::factory()->approved()->featured()->create(['space_id' => $space->id]);
        $pending = Testimonial::factory()->pending()->create(['space_id' => $space->id]);

        $this->assertTrue($approved->space->is($space));
        $this->assertCount(1, Testimonial::approved()->where('space_id', $space->id)->get());
        $this->assertCount(1, Testimonial::featured()->where('space_id', $space->id)->get());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SpaceTheme;
use App\Enums\TestimonialStatus;
use App\Livewire\Public\SubmitTestimonial;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerFacingSpaceSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_access_space_by_slug_without_authentication(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'title' => 'Testimonial Showcase',
            'subtitle' => 'We love hearing from you',
            'ask' => 'What did you like best?',
            'custom_cta' => 'Send Testimonial',
            'theme' => SpaceTheme::Light,
            'enable_ratings' => true,
            'collect_company' => true,
            'require_company' => false,
            'collect_social_links' => true,
            'require_social_links' => false,
            'collect_avatar' => false,
            'require_avatar' => false,
            'is_active' => true,
        ]);

        $response = $this->get('/space/test-product');
        $response->assertOk();
        $response->assertSee('Testimonial Showcase');
        $response->assertSee('We love hearing from you');
        $response->assertSee('What did you like best?');
        $response->assertSee('Your Rating');
        $response->assertSee('Your Testimonial / Review');
        $response->assertSee('Your Full Name');
        $response->assertSee('Your Email Address');
        $response->assertSee('Company / Organization');
        $response->assertSee('Your Role / Title');
        $response->assertSee('I give permission to use this testimonial across social media channels and marketing materials.');
    }

    public function test_customer_submits_testimonial_and_sees_success_screen_with_gif(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'title' => 'Testimonial Showcase',
            'subtitle' => 'We love hearing from you',
            'ask' => 'What did you like best?',
            'custom_cta' => 'Send Testimonial',
            'enable_ratings' => true,
            'collect_company' => true,
            'is_active' => true,
        ]);

        Livewire::test(SubmitTestimonial::class, ['slug' => 'test-product'])
            ->set('name', 'Alice Tester')
            ->set('email', 'alice@tester.com')
            ->set('rating', 5)
            ->set('company', 'Acme Inc.')
            ->set('role', 'QA Lead')
            ->set('content', 'This product is exceptionally well crafted and easy to use!')
            ->set('consent', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true)
            ->assertSee('Thank You!')
            ->assertSee('Your testimonial for')
            ->assertSee('Celebration GIF')
            ->assertSee('data-testid="confirmation-gif"', false);

        $this->assertDatabaseHas('testimonials', [
            'space_id' => $space->id,
            'submitter_name' => 'Alice Tester',
            'submitter_email' => 'alice@tester.com',
            'submitter_company' => 'Acme Inc.',
            'submitter_role' => 'QA Lead',
            'content' => 'This product is exceptionally well crafted and easy to use!',
            'rating' => 5,
            'status' => TestimonialStatus::Pending->value,
        ]);
    }

    public function test_submission_fails_when_consent_is_not_given(): void
    {
        $user = User::factory()->create();
        Space::factory()->create([
            'user_id' => $user->id,
            'slug' => 'test-product',
            'is_active' => true,
        ]);

        Livewire::test(SubmitTestimonial::class, ['slug' => 'test-product'])
            ->set('name', 'Alice Tester')
            ->set('email', 'alice@tester.com')
            ->set('rating', 5)
            ->set('content', 'Great experience overall!')
            ->set('consent', false)
            ->call('submit')
            ->assertHasErrors(['consent'])
            ->assertSet('submitted', false);
    }
}

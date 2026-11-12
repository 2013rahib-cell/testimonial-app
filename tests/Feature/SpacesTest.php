<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SpaceTheme;
use App\Enums\TestimonialStatus;
use App\Enums\UserPlan;
use App\Livewire\Public\SubmitTestimonial;
use App\Livewire\Spaces\Create as SpaceCreate;
use App\Livewire\Spaces\Edit as SpaceEdit;
use App\Livewire\Spaces\Index as SpaceIndex;
use App\Livewire\Spaces\Success as SpaceSuccess;
use App\Models\Space;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SpacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_spaces_routes(): void
    {
        $this->get(route('spaces.index'))->assertRedirect(route('login'));
        $this->get(route('spaces.create'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_spaces_index_and_only_sees_their_own_spaces(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $space1 = Space::factory()->create(['user_id' => $user1->id, 'name' => 'User 1 Space']);
        $space2 = Space::factory()->create(['user_id' => $user2->id, 'name' => 'User 2 Secret Space']);

        $this->actingAs($user1);

        Livewire::test(SpaceIndex::class)
            ->assertSee('User 1 Space')
            ->assertDontSee('User 2 Secret Space');
    }

    public function test_user_can_create_space_with_all_fields_and_themes(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);
        $this->actingAs($user);

        Livewire::test(SpaceCreate::class)
            ->set('name', 'Acme App')
            ->set('slug', 'acme-app')
            ->set('title', 'Tell us about Acme App')
            ->set('subtitle', 'Share your honest journey.')
            ->set('ask', 'How did Acme improve your conversion rate?')
            ->set('theme', 'sunset')
            ->set('collectCompany', true)
            ->set('requireCompany', true)
            ->set('collectSocialLinks', true)
            ->set('requireSocialLinks', false)
            ->set('collectAvatar', true)
            ->set('requireAvatar', false)
            ->set('enableRatings', true)
            ->set('customCta', 'Submit Review')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('spaces.success', 'acme-app'));

        $this->assertDatabaseHas('spaces', [
            'user_id' => $user->id,
            'name' => 'Acme App',
            'slug' => 'acme-app',
            'title' => 'Tell us about Acme App',
            'theme' => SpaceTheme::Sunset->value,
            'require_company' => true,
            'enable_ratings' => true,
        ]);
    }

    public function test_free_tier_user_cannot_exceed_3_spaces_limit(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);

        // Create 3 existing spaces (Free limit)
        Space::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(SpaceCreate::class)
            ->set('name', 'Fourth Space')
            ->set('title', 'Header for Fourth Space')
            ->call('save')
            ->assertHasErrors(['limit']);

        $this->assertSame(3, $user->spaces()->count());
    }

    public function test_pro_user_can_create_more_than_3_spaces(): void
    {
        $user = User::factory()->pro()->create();

        // Create 3 existing spaces
        Space::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(SpaceCreate::class)
            ->set('name', 'Fourth Space Pro')
            ->set('slug', 'fourth-space-pro')
            ->set('title', 'Review Fourth Space')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('spaces.success', 'fourth-space-pro'));

        $this->assertSame(4, $user->spaces()->count());
    }

    public function test_success_page_displays_unique_shareable_public_url(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create([
            'user_id' => $user->id,
            'slug' => 'awesome-product',
        ]);

        $this->actingAs($user);

        $expectedUrl = route('spaces.submit', 'awesome-product');

        Livewire::test(SpaceSuccess::class, ['space' => $space])
            ->assertSee($expectedUrl)
            ->assertSee($space->name);
    }

    public function test_user_cannot_access_success_page_of_another_users_space(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $space = Space::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);

        Livewire::test(SpaceSuccess::class, ['space' => $space])
            ->assertStatus(403);
    }

    public function test_user_cannot_edit_another_users_space(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $space = Space::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);

        Livewire::test(SpaceEdit::class, ['space' => $space])
            ->assertStatus(403);
    }

    public function test_user_can_update_their_own_space_settings(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Space Name',
            'theme' => SpaceTheme::Light,
            'enable_ratings' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(SpaceEdit::class, ['space' => $space])
            ->set('name', 'Updated Space Name')
            ->set('theme', 'indigo')
            ->set('enableRatings', false)
            ->call('save')
            ->assertHasNoErrors();

        $space->refresh();
        $this->assertSame('Updated Space Name', $space->name);
        $this->assertSame(SpaceTheme::Indigo, $space->theme);
        $this->assertFalse($space->enable_ratings);
    }

    public function test_user_can_delete_their_own_space(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(SpaceIndex::class)
            ->call('deleteSpace', $space->id);

        $this->assertDatabaseMissing('spaces', ['id' => $space->id]);
    }

    public function test_public_user_can_submit_testimonial_to_active_space(): void
    {
        $space = Space::factory()->create([
            'slug' => 'test-community',
            'is_active' => true,
            'enable_ratings' => true,
            'collect_company' => true,
            'require_company' => false,
        ]);

        Livewire::test(SubmitTestimonial::class, ['slug' => 'test-community'])
            ->set('name', 'Jane Submitter')
            ->set('email', 'jane@community.org')
            ->set('rating', 5)
            ->set('content', 'This service completely exceeded our team expectations!')
            ->set('company', 'Acme Inc.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('testimonials', [
            'space_id' => $space->id,
            'submitter_name' => 'Jane Submitter',
            'submitter_email' => 'jane@community.org',
            'rating' => 5,
            'status' => TestimonialStatus::Pending->value,
        ]);
    }

    public function test_public_submission_requires_mandatory_fields(): void
    {
        $space = Space::factory()->create([
            'slug' => 'validation-space',
            'collect_company' => true,
            'require_company' => true,
        ]);

        Livewire::test(SubmitTestimonial::class, ['slug' => 'validation-space'])
            ->set('name', '')
            ->set('email', '')
            ->set('content', '')
            ->set('company', '')
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'content', 'company']);
    }

    public function test_user_cannot_mass_assign_plan_or_billing_fields(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);

        $user->fill([
            'plan' => UserPlan::Pro->value,
            'stripe_customer_id' => 'cus_evil_override',
        ]);
        $user->save();

        $user->refresh();
        $this->assertSame(UserPlan::Free, $user->plan);
        $this->assertNull($user->stripe_customer_id);
    }

    public function test_space_id_is_not_mass_assignable_on_testimonial(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);

        $testimonial = new Testimonial();
        $testimonial->fill([
            'space_id' => $space->id,
            'submitter_name' => 'John Doe',
            'submitter_email' => 'john@example.com',
            'content' => 'Great product!',
        ]);

        $this->assertNull($testimonial->space_id);
    }

    public function test_user_cannot_delete_another_users_space(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);

        Livewire::test(SpaceIndex::class)
            ->call('deleteSpace', $space->id)
            ->assertStatus(404);

        $this->assertDatabaseHas('spaces', ['id' => $space->id]);
    }

    public function test_cannot_submit_testimonial_when_space_has_reached_plan_limit(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);
        $space = Space::factory()->create([
            'user_id' => $user->id,
            'slug' => 'quota-full-space',
            'is_active' => true,
        ]);

        Testimonial::factory()->count(100)->create(['space_id' => $space->id]);

        Livewire::test(SubmitTestimonial::class, ['slug' => 'quota-full-space'])
            ->set('name', 'Excess Submitter')
            ->set('email', 'excess@example.com')
            ->set('content', 'This should be blocked by plan limit.')
            ->call('submit')
            ->assertHasErrors(['form']);

        $this->assertSame(100, $space->testimonials()->count());
    }
}

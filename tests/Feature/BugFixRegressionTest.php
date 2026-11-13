<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TestimonialStatus;
use App\Enums\UserPlan;
use App\Livewire\Dashboard;
use App\Livewire\Embed\Generator;
use App\Livewire\Public\SubmitTestimonial;
use App\Livewire\Settings\Billing;
use App\Livewire\Spaces\Create;
use App\Livewire\Spaces\Edit as SpaceEdit;
use App\Models\Space;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\CssColor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression tests for bugs fixed during the 2026-11-13 audit.
 */
class BugFixRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function validSubmission(string $slug): Testable
    {
        return Livewire::test(SubmitTestimonial::class, ['slug' => $slug])
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('content', 'An absolutely wonderful product, highly recommended!')
            ->set('consent', true);
    }

    public function test_consent_checkbox_is_not_pre_checked(): void
    {
        Space::factory()->create(['slug' => 'consent-space']);

        Livewire::test(SubmitTestimonial::class, ['slug' => 'consent-space'])
            ->assertSet('consent', false);
    }

    public function test_social_link_is_normalised_and_stored(): void
    {
        $space = Space::factory()->create(['slug' => 'social-space', 'collect_social_links' => true]);

        $this->validSubmission('social-space')
            ->set('socialLinks', 'linkedin.com/in/janedoe')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('testimonials', [
            'space_id' => $space->id,
            'submitter_social_url' => 'https://linkedin.com/in/janedoe',
        ]);
    }

    public function test_non_http_social_link_is_rejected(): void
    {
        Space::factory()->create(['slug' => 'social-bad', 'collect_social_links' => true]);

        $this->validSubmission('social-bad')
            ->set('socialLinks', 'javascript:alert(1)')
            ->call('submit')
            ->assertHasErrors(['socialLinks']);
    }

    public function test_avatar_is_not_stored_when_space_does_not_collect_avatars(): void
    {
        Storage::fake('public');
        $space = Space::factory()->create(['slug' => 'no-avatar', 'collect_avatar' => false, 'require_avatar' => false]);

        $this->validSubmission('no-avatar')
            ->set('avatar', UploadedFile::fake()->image('me.png'))
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertNull($space->testimonials()->first()?->submitter_avatar_path);
        $this->assertEmpty(Storage::disk('public')->allFiles('avatars'));
    }

    public function test_submit_another_resets_the_form(): void
    {
        Space::factory()->create(['slug' => 'again-space']);

        $this->validSubmission('again-space')
            ->call('submit')
            ->assertSet('submitted', true)
            ->call('submitAnother')
            ->assertSet('submitted', false)
            ->assertSet('name', '')
            ->assertSet('content', '')
            ->assertSet('consent', false);
    }

    public function test_public_submissions_are_rate_limited(): void
    {
        $space = Space::factory()->create(['slug' => 'throttle-space']);

        for ($i = 0; $i < SubmitTestimonial::MAX_ATTEMPTS; $i++) {
            $this->validSubmission('throttle-space')->call('submit')->assertHasNoErrors();
        }

        $this->validSubmission('throttle-space')->call('submit')->assertHasErrors(['form']);
        $this->assertSame(SubmitTestimonial::MAX_ATTEMPTS, $space->testimonials()->count());
    }

    public function test_embed_rejects_css_injection_in_bg_parameter(): void
    {
        $space = Space::factory()->create(['slug' => 'css-space', 'is_active' => true]);

        $this->get('/embed/wall-of-love/css-space?bg='.urlencode('red;background-image:url(//evil.test/x)'))
            ->assertOk()
            ->assertDontSee('evil.test', false)
            ->assertSee('background-color: #ffffff;', false);
    }

    public function test_embed_accepts_valid_hex_bg(): void
    {
        Space::factory()->create(['slug' => 'hex-space', 'is_active' => true]);

        $this->get('/embed/wall-of-love/hex-space?bg='.urlencode('#1e1b4b'))
            ->assertOk()
            ->assertSee('background-color: #1e1b4b;', false);
    }

    public function test_embed_only_shows_approved_visible_testimonials(): void
    {
        $space = Space::factory()->create(['slug' => 'wall-space', 'is_active' => true]);
        Testimonial::factory()->create(['space_id' => $space->id, 'content' => 'APPROVED-VISIBLE', 'status' => TestimonialStatus::Approved, 'is_hidden' => false]);
        Testimonial::factory()->create(['space_id' => $space->id, 'content' => 'APPROVED-HIDDEN', 'status' => TestimonialStatus::Approved, 'is_hidden' => true]);
        Testimonial::factory()->create(['space_id' => $space->id, 'content' => 'PENDING-ONE', 'status' => TestimonialStatus::Pending, 'is_hidden' => false]);

        $this->get('/embed/wall-of-love/wall-space')
            ->assertOk()
            ->assertSee('APPROVED-VISIBLE')
            ->assertDontSee('APPROVED-HIDDEN')
            ->assertDontSee('PENDING-ONE')
            ->assertSee('wol-resize', false);
    }

    public function test_generator_ignores_invalid_background_colour(): void
    {
        $user = User::factory()->create();
        Space::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Generator::class)
            ->call('setBackgroundColor', 'red;position:fixed')
            ->assertSet('backgroundColor', '#ffffff')
            ->set('backgroundColor', 'not-a-colour')
            ->assertSet('backgroundColor', '#ffffff')
            ->call('setBackgroundColor', '#09090B')
            ->assertSet('backgroundColor', '#09090b');
    }

    public function test_css_color_helper(): void
    {
        $this->assertSame('#abc', CssColor::sanitize('#ABC'));
        $this->assertSame('transparent', CssColor::sanitize('transparent'));
        $this->assertSame('#ffffff', CssColor::sanitize('red; x:y'));
        $this->assertSame('#000000', CssColor::sanitize('#12345', '#000000'));
        $this->assertFalse(CssColor::isValid('url(x)'));
    }

    public function test_unsigned_webhook_is_rejected_outside_local_and_testing(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);
        config(['services.stripe.webhook_secret' => '']);
        $this->app['env'] = 'production';

        $this->postJson('/stripe/webhook', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['client_reference_id' => (string) $user->id]],
        ])->assertStatus(500);

        $this->assertSame(UserPlan::Free, $user->fresh()->plan);
    }

    public function test_editing_slug_redirects_to_new_edit_url(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id, 'slug' => 'old-slug', 'title' => 'Title']);

        Livewire::actingAs($user)
            ->test(SpaceEdit::class, ['space' => $space])
            ->set('slug', 'Brand New Slug')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('spaces.edit', ['space' => 'brand-new-slug']));

        $this->assertSame('brand-new-slug', $space->fresh()->slug);
    }

    public function test_other_users_cannot_open_edit_page(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)->get(route('spaces.edit', $space->slug))->assertForbidden();
        $this->actingAs($intruder)->get(route('spaces.inbox', $space->slug))->assertForbidden();
        $this->actingAs($intruder)->get(route('spaces.success', $space->slug))->assertForbidden();
    }

    public function test_billing_shows_friendly_message_when_stripe_not_configured(): void
    {
        config(['services.stripe.secret' => '', 'services.billing.mock' => false]);
        $user = User::factory()->create(['plan' => UserPlan::Free]);

        Livewire::actingAs($user)
            ->test(Billing::class)
            ->call('checkout')
            ->assertSet('errorMessage', fn (?string $m): bool => is_string($m) && str_contains($m, 'not configured'));
    }

    public function test_public_wall_page_lists_only_approved_visible_testimonials(): void
    {
        $space = Space::factory()->create(['slug' => 'public-wall', 'is_active' => true]);
        Testimonial::factory()->create(['space_id' => $space->id, 'content' => 'SHOWN-ON-WALL', 'status' => TestimonialStatus::Approved, 'is_hidden' => false]);
        Testimonial::factory()->create(['space_id' => $space->id, 'content' => 'PENDING-NOT-SHOWN', 'status' => TestimonialStatus::Pending]);

        $this->get(route('wall.show', 'public-wall'))
            ->assertOk()
            ->assertSee('SHOWN-ON-WALL')
            ->assertDontSee('PENDING-NOT-SHOWN')
            ->assertSee('Leave a testimonial');
    }

    public function test_public_wall_page_404s_for_inactive_space(): void
    {
        Space::factory()->create(['slug' => 'closed-wall', 'is_active' => false]);

        $this->get(route('wall.show', 'closed-wall'))->assertNotFound()->assertSee('Page not found');
    }

    public function test_database_seeder_is_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(1, User::where('email', 'demo@example.com')->count());
        $this->assertSame(1, Space::where('slug', 'saas-product-alpha')->count());
    }

    public function test_dismissed_upgrade_banner_stays_hidden_after_reload(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);

        Livewire::actingAs($user)->test(Dashboard::class)->call('dismissBanner');

        Livewire::actingAs($user)->test(Dashboard::class)->assertSet('bannerDismissed', true);
    }

    public function test_typed_slug_is_not_overwritten_by_name_autofill(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('name', 'Acme')
            ->assertSet('slug', 'acme')
            ->set('slug', 'my-custom-slug')
            ->set('name', 'Acme Corp')
            ->assertSet('slug', 'my-custom-slug');
    }

    public function test_landing_page_describes_the_product(): void
    {
        $this->get('/')->assertOk()->assertSee('Wall of Love');
    }
}

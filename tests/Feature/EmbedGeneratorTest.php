<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TestimonialStatus;
use App\Livewire\Embed\Generator;
use App\Models\Space;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmbedGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_embed_generator(): void
    {
        $this->get(route('embed.generator'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_embed_generator(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('embed.generator'))
            ->assertOk()
            ->assertSee('Wall of Love Embed Generator')
            ->assertSee($space->name);
    }

    public function test_shows_empty_state_when_user_has_no_spaces(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('embed.generator'))
            ->assertOk()
            ->assertSee('No spaces created yet');
    }

    public function test_preview_only_displays_approved_and_visible_testimonials(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);

        // Approved and visible - should show
        $approvedTestimonial = Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_name' => 'Alice Approved',
            'content' => 'I love this product completely!',
            'status' => TestimonialStatus::Approved,
            'is_hidden' => false,
        ]);

        // Pending - should NOT show
        $pendingTestimonial = Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_name' => 'Bob Pending',
            'content' => 'Awaiting review here.',
            'status' => TestimonialStatus::Pending,
            'is_hidden' => false,
        ]);

        // Rejected - should NOT show
        $rejectedTestimonial = Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_name' => 'Charlie Rejected',
            'content' => 'Terrible experience.',
            'status' => TestimonialStatus::Rejected,
            'is_hidden' => false,
        ]);

        // Hidden approved - should NOT show
        $hiddenTestimonial = Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_name' => 'Dana Hidden',
            'content' => 'Secret review.',
            'status' => TestimonialStatus::Approved,
            'is_hidden' => true,
        ]);

        Livewire::actingAs($user)
            ->test(Generator::class, ['space' => $space->id])
            ->assertSee('Alice Approved')
            ->assertSee('I love this product completely!')
            ->assertDontSee('Bob Pending')
            ->assertDontSee('Charlie Rejected')
            ->assertDontSee('Dana Hidden');
    }

    public function test_can_toggle_layout_theme_background_and_animations(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Generator::class, ['space' => $space->id])
            ->assertSet('layout', 'masonry')
            ->assertSet('theme', 'light')
            ->call('setLayout', 'carousel')
            ->assertSet('layout', 'carousel')
            ->call('setTheme', 'dark')
            ->assertSet('theme', 'dark')
            ->assertSet('backgroundColor', '#09090b')
            ->call('setBackgroundColor', '#18181b')
            ->assertSet('backgroundColor', '#18181b')
            ->call('toggleAnimations')
            ->assertSet('animations', false);
    }

    public function test_generates_copyable_snippets_with_current_options(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->create([
            'user_id' => $user->id,
            'slug' => 'test-space-snippets',
        ]);

        $component = Livewire::actingAs($user)
            ->test(Generator::class, ['space' => $space->id])
            ->call('setLayout', 'carousel')
            ->call('setTheme', 'dark')
            ->call('setBackgroundColor', '#18181b');

        $iframeSnippet = $component->get('iframeSnippet');
        $scriptSnippet = $component->get('scriptSnippet');

        $this->assertStringContainsString('iframe', $iframeSnippet);
        $this->assertStringContainsString('layout=carousel', $iframeSnippet);
        $this->assertStringContainsString('theme=dark', $iframeSnippet);
        $this->assertStringContainsString('test-space-snippets', $iframeSnippet);

        $this->assertStringContainsString('data-space="test-space-snippets"', $scriptSnippet);
        $this->assertStringContainsString('data-layout="carousel"', $scriptSnippet);
        $this->assertStringContainsString('data-theme="dark"', $scriptSnippet);
        $this->assertStringContainsString('embed.js', $scriptSnippet);
    }
}

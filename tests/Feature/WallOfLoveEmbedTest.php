<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TestimonialStatus;
use App\Models\Space;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WallOfLoveEmbedTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_embed_endpoint_renders_without_authentication(): void
    {
        $space = Space::factory()->create(['slug' => 'acme-corp']);

        $response = $this->get(route('wall-of-love.embed', 'acme-corp'));

        $response->assertOk();
    }

    public function test_embed_returns_404_for_nonexistent_or_inactive_space(): void
    {
        $this->get(route('wall-of-love.embed', 'does-not-exist'))
            ->assertNotFound();

        $inactiveSpace = Space::factory()->create([
            'slug' => 'inactive-space',
            'is_active' => false,
        ]);

        $this->get(route('wall-of-love.embed', 'inactive-space'))
            ->assertNotFound();
    }

    public function test_only_approved_and_visible_testimonials_are_rendered(): void
    {
        $space = Space::factory()->create(['slug' => 'wall-test-space']);

        Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_name' => 'Visible Approved User',
            'content' => 'Outstanding service and quality!',
            'status' => TestimonialStatus::Approved,
            'is_hidden' => false,
        ]);

        Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_name' => 'Pending Review User',
            'content' => 'Still waiting for approval.',
            'status' => TestimonialStatus::Pending,
            'is_hidden' => false,
        ]);

        Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_name' => 'Rejected Review User',
            'content' => 'Rejected content here.',
            'status' => TestimonialStatus::Rejected,
            'is_hidden' => false,
        ]);

        Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_name' => 'Hidden Approved User',
            'content' => 'Approved but marked hidden.',
            'status' => TestimonialStatus::Approved,
            'is_hidden' => true,
        ]);

        $response = $this->get(route('wall-of-love.embed', 'wall-test-space'));

        $response->assertOk()
            ->assertSee('Visible Approved User')
            ->assertSee('Outstanding service and quality!')
            ->assertDontSee('Pending Review User')
            ->assertDontSee('Rejected Review User')
            ->assertDontSee('Hidden Approved User');
    }

    public function test_embed_supports_carousel_and_dark_mode_parameters(): void
    {
        $space = Space::factory()->create(['slug' => 'carousel-space']);

        Testimonial::factory()->create([
            'space_id' => $space->id,
            'submitter_name' => 'John Carousel',
            'content' => 'Carousel testimonial card.',
            'status' => TestimonialStatus::Approved,
            'is_hidden' => false,
        ]);

        $response = $this->get(route('wall-of-love.embed', [
            'slug' => 'carousel-space',
            'layout' => 'carousel',
            'theme' => 'dark',
            'bg' => '#121212',
            'animations' => '1',
        ]));

        $response->assertOk()
            ->assertSee('John Carousel')
            ->assertSee('#121212')
            ->assertSee('bg-zinc-900'); // dark mode card class
    }
}

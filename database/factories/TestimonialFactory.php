<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TestimonialStatus;
use App\Enums\TestimonialType;
use App\Models\Space;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'space_id' => Space::factory(),
            'submitter_name' => fake()->name(),
            'submitter_email' => fake()->safeEmail(),
            'submitter_avatar_path' => null,
            'submitter_company' => fake()->company(),
            'submitter_role' => fake()->jobTitle(),
            'content' => fake()->paragraph(),
            'rating' => fake()->numberBetween(1, 5),
            'type' => TestimonialType::Text,
            'video_url' => null,
            'status' => TestimonialStatus::Approved,
            'is_featured' => false,
            'is_hidden' => false,
        ];
    }

    /**
     * Indicate that the testimonial is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestimonialStatus::Pending,
        ]);
    }

    /**
     * Indicate that the testimonial is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestimonialStatus::Approved,
        ]);
    }

    /**
     * Indicate that the testimonial is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestimonialStatus::Rejected,
        ]);
    }

    /**
     * Indicate that the testimonial is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the testimonial is a video testimonial.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TestimonialType::Video,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }
}

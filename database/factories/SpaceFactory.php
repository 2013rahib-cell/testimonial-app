<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SpaceTheme;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company().' Space';

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'title' => 'Would you share a quick review for '.$name.'?',
            'subtitle' => 'We appreciate your support and would love to hear your feedback.',
            'ask' => 'How has our product helped you or your team accomplish your goals?',
            'description' => fake()->sentence(),
            'website_url' => fake()->url(),
            'logo_path' => null,
            'custom_cta' => 'Share your experience with us!',
            'theme' => SpaceTheme::Light,
            'collect_company' => true,
            'require_company' => false,
            'collect_social_links' => true,
            'require_social_links' => false,
            'collect_avatar' => true,
            'require_avatar' => false,
            'enable_ratings' => true,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the space is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

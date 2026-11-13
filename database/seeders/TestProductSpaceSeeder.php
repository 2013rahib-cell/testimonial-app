<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SpaceTheme;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestProductSpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Demo/E2E data only – never attach the "test-product" space to a real customer.
        if (app()->environment('production')) {
            $this->command?->warn('TestProductSpaceSeeder skipped in production.');

            return;
        }

        $user = User::where('email', 'demo@example.com')->first()
            ?? User::factory()->create([
                'name' => 'Demo User',
                'email' => 'demo@example.com',
            ]);

        Space::updateOrCreate(
            ['slug' => 'test-product'],
            [
                'user_id' => $user->id,
                'name' => 'Test Product',
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
            ]
        );
    }
}

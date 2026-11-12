<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TestimonialStatus;
use App\Enums\UserPlan;
use App\Models\Space;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'plan' => UserPlan::Free,
        ]);

        $space1 = Space::factory()->create([
            'user_id' => $user->id,
            'name' => 'SaaS Product Alpha',
            'slug' => 'saas-product-alpha',
            'website_url' => 'https://alpha.example.com',
            'custom_cta' => 'Share your thoughts with the world!',
        ]);

        $space2 = Space::factory()->create([
            'user_id' => $user->id,
            'name' => 'E-Commerce Storefront',
            'slug' => 'ecommerce-storefront',
            'website_url' => 'https://shop.example.com',
            'custom_cta' => 'How was your shopping experience?',
        ]);

        $now = Carbon::now();
        $sampleSubmitters = [
            ['name' => 'Sarah Connor', 'email' => 'sarah@skynet.dev', 'company' => 'Resistance Inc.', 'role' => 'CTO', 'rating' => 5],
            ['name' => 'John Doe', 'email' => 'john@acme.com', 'company' => 'Acme Corp', 'role' => 'Product Lead', 'rating' => 5],
            ['name' => 'Alice Johnson', 'email' => 'alice@startup.io', 'company' => 'Startup IO', 'role' => 'Founder', 'rating' => 4],
            ['name' => 'Bob Smith', 'email' => 'bob@designco.com', 'company' => 'DesignCo', 'role' => 'Lead Designer', 'rating' => 5],
            ['name' => 'Elena Rostova', 'email' => 'elena@vanguard.tech', 'company' => 'Vanguard Tech', 'role' => 'VP of Engineering', 'rating' => 4],
            ['name' => 'Michael Scott', 'email' => 'michael@dunder.com', 'company' => 'Dunder Mifflin', 'role' => 'Regional Manager', 'rating' => 5],
            ['name' => 'Dwight Schrute', 'email' => 'dwight@dunder.com', 'company' => 'Dunder Mifflin', 'role' => 'Assistant to the RM', 'rating' => 4],
            ['name' => 'Pam Beesly', 'email' => 'pam@dunder.com', 'company' => 'Dunder Mifflin', 'role' => 'Office Admin', 'rating' => 5],
            ['name' => 'Jim Halpert', 'email' => 'jim@dunder.com', 'company' => 'Athlead', 'role' => 'Marketing Director', 'rating' => 5],
            ['name' => 'Bruce Wayne', 'email' => 'bruce@wayne.enterprises', 'company' => 'Wayne Enterprises', 'role' => 'CEO', 'rating' => 5],
            ['name' => 'Clark Kent', 'email' => 'clark@dailyplanet.com', 'company' => 'Daily Planet', 'role' => 'Journalist', 'rating' => 4],
            ['name' => 'Diana Prince', 'email' => 'diana@themiscira.org', 'company' => 'Themyscira Embassy', 'role' => 'Ambassador', 'rating' => 5],
        ];

        foreach ($sampleSubmitters as $index => $sub) {
            $space = ($index % 2 === 0) ? $space1 : $space2;
            $daysAgo = ($index * 2) % 28;
            $createdAt = (clone $now)->subDays($daysAgo)->subHours($index * 3);

            Testimonial::factory()->create([
                'space_id' => $space->id,
                'submitter_name' => $sub['name'],
                'submitter_email' => $sub['email'],
                'submitter_company' => $sub['company'],
                'submitter_role' => $sub['role'],
                'rating' => $sub['rating'],
                'status' => TestimonialStatus::Approved,
                'is_featured' => $index % 3 === 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}

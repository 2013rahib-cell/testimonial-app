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
        // Idempotent: safe to run `php artisan db:seed` more than once.
        $user = User::where('email', 'demo@example.com')->first()
            ?? User::factory()->create([
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'plan' => UserPlan::Free,
            ]);

        $space1 = $this->demoSpace($user, 'saas-product-alpha', [
            'name' => 'SaaS Product Alpha',
            'website_url' => 'https://alpha.example.com',
            'custom_cta' => 'Share your thoughts with the world!',
        ]);

        $space2 = $this->demoSpace($user, 'ecommerce-storefront', [
            'name' => 'E-Commerce Storefront',
            'website_url' => 'https://shop.example.com',
            'custom_cta' => 'How was your shopping experience?',
        ]);

        $testProductSpace = Space::updateOrCreate(
            ['slug' => 'test-product'],
            [
                'user_id' => $user->id,
                'name' => 'Test Product',
                'title' => 'Testimonial Showcase',
                'subtitle' => 'We love hearing from you',
                'ask' => 'What did you like best?',
                'custom_cta' => 'Send Testimonial',
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

        if ($space1->testimonials()->exists() || $space2->testimonials()->exists()) {
            return; // sample testimonials already seeded
        }

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

        $quotes = [
            'We replaced three tools with this. Collecting reviews now takes minutes instead of days.',
            'Setup took five minutes and our landing page conversion went up 18% after adding the Wall of Love.',
            'Customers actually enjoy leaving feedback now. The form is simple and looks great on mobile.',
            'The embed matched our brand perfectly. Light and dark themes saved our designer a full day.',
            'Curating testimonials from one inbox is a game changer for our marketing team.',
            'Best purchase this quarter. Our sales team links the wall in every proposal now.',
            'Approving and pinning the best reviews is incredibly fast. Exactly what we needed.',
            'Clean, fast and reliable. The carousel widget fits right into our product pages.',
            'Our agency uses a separate space for every client. Keeping things organised has never been easier.',
            'The social proof widget paid for itself in the first week.',
            'Support answered within minutes and the product just works. Highly recommended.',
            'Finally a testimonial tool that does not feel bloated. Simple, focused and beautiful.',
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
                'content' => $quotes[$index % count($quotes)],
                'status' => TestimonialStatus::Approved,
                'is_featured' => $index % 3 === 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    /**
     * Find a demo space by slug (owned by the demo user) or create it from the factory.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function demoSpace(User $user, string $slug, array $attributes): Space
    {
        $space = $user->spaces()->where('slug', $slug)->first();

        if ($space === null) {
            $space = Space::factory()->create([...$attributes, 'user_id' => $user->id, 'slug' => $slug]);
        }

        return $space;
    }
}

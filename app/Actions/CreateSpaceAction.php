<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SpaceTheme;
use App\Exceptions\PlanLimitExceededException;
use App\Models\Space;
use App\Models\User;
use Illuminate\Support\Str;

class CreateSpaceAction
{
    /**
     * Create a new space for the user enforcing plan limits.
     *
     * @param array{
     *     name: string,
     *     slug?: string|null,
     *     title?: string|null,
     *     subtitle?: string|null,
     *     ask?: string|null,
     *     description?: string|null,
     *     website_url?: string|null,
     *     logo_path?: string|null,
     *     custom_cta?: string|null,
     *     theme?: string|SpaceTheme,
     *     collect_company?: bool,
     *     require_company?: bool,
     *     collect_social_links?: bool,
     *     require_social_links?: bool,
     *     collect_avatar?: bool,
     *     require_avatar?: bool,
     *     enable_ratings?: bool,
     *     is_active?: bool,
     * } $data
     * @throws PlanLimitExceededException
     */
    public function execute(User $user, array $data): Space
    {
        if ($user->spaces()->count() >= $user->maxSpaces()) {
            throw new PlanLimitExceededException(
                "Upgrade to Pro to create more than {$user->maxSpaces()} spaces."
            );
        }

        $slug = $this->generateUniqueSlug($data['slug'] ?? null, $data['name']);

        $theme = $data['theme'] ?? SpaceTheme::Light;
        if ($theme instanceof SpaceTheme) {
            $theme = $theme->value;
        }

        $spaceData = [
            'name' => trim($data['name']),
            'slug' => $slug,
            'title' => ! empty($data['title']) ? trim((string) $data['title']) : 'Would you share a quick review for '.trim($data['name']).'?',
            'subtitle' => ! empty($data['subtitle']) ? trim((string) $data['subtitle']) : 'We would love to hear your honest feedback.',
            'ask' => ! empty($data['ask']) ? trim((string) $data['ask']) : 'What did you like most about our service?',
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            'website_url' => isset($data['website_url']) ? trim((string) $data['website_url']) : null,
            'logo_path' => $data['logo_path'] ?? null,
            'custom_cta' => ! empty($data['custom_cta']) ? trim((string) $data['custom_cta']) : 'Send testimonial',
            'theme' => $theme,
            'collect_company' => (bool) ($data['collect_company'] ?? true),
            'require_company' => (bool) ($data['require_company'] ?? false),
            'collect_social_links' => (bool) ($data['collect_social_links'] ?? true),
            'require_social_links' => (bool) ($data['require_social_links'] ?? false),
            'collect_avatar' => (bool) ($data['collect_avatar'] ?? true),
            'require_avatar' => (bool) ($data['require_avatar'] ?? false),
            'enable_ratings' => (bool) ($data['enable_ratings'] ?? true),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        return $user->spaces()->create($spaceData);
    }

    /**
     * Generate a unique slug for a space.
     */
    private function generateUniqueSlug(?string $preferredSlug, string $name): string
    {
        $base = ! empty($preferredSlug) ? Str::slug($preferredSlug) : Str::slug($name);

        if (empty($base)) {
            $base = 'space-'.Str::lower(Str::random(6));
        }

        $slug = $base;
        $counter = 1;

        while (Space::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

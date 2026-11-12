<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SpaceTheme;
use App\Models\Space;
use Illuminate\Support\Str;

class UpdateSpaceAction
{
    /**
     * Update an existing space.
     *
     * @param array{
     *     name?: string,
     *     slug?: string,
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
     */
    public function execute(Space $space, array $data): Space
    {
        if (isset($data['slug']) && $data['slug'] !== $space->slug) {
            $data['slug'] = $this->generateUniqueSlug($data['slug'], $space->id);
        }

        if (isset($data['theme']) && $data['theme'] instanceof SpaceTheme) {
            $data['theme'] = $data['theme']->value;
        }

        $allowedFields = [
            'name',
            'slug',
            'title',
            'subtitle',
            'ask',
            'description',
            'website_url',
            'logo_path',
            'custom_cta',
            'theme',
            'collect_company',
            'require_company',
            'collect_social_links',
            'require_social_links',
            'collect_avatar',
            'require_avatar',
            'enable_ratings',
            'is_active',
        ];

        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        $space->update($filteredData);

        return $space->fresh();
    }

    /**
     * Ensure slug is unique excluding current space id.
     */
    private function generateUniqueSlug(string $preferredSlug, int $ignoreId): string
    {
        $base = Str::slug($preferredSlug);
        if (empty($base)) {
            $base = 'space-'.Str::lower(Str::random(6));
        }

        $slug = $base;
        $counter = 1;

        while (Space::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

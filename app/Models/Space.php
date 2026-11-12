<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SpaceTheme;
use Database\Factories\SpaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $title
 * @property string|null $subtitle
 * @property string|null $ask
 * @property string|null $description
 * @property string|null $website_url
 * @property string|null $logo_path
 * @property string|null $custom_cta
 * @property SpaceTheme $theme
 * @property bool $collect_company
 * @property bool $require_company
 * @property bool $collect_social_links
 * @property bool $require_social_links
 * @property bool $collect_avatar
 * @property bool $require_avatar
 * @property bool $enable_ratings
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
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
])]
class Space extends Model
{
    /** @use HasFactory<SpaceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'theme' => SpaceTheme::class,
            'collect_company' => 'boolean',
            'require_company' => 'boolean',
            'collect_social_links' => 'boolean',
            'require_social_links' => 'boolean',
            'collect_avatar' => 'boolean',
            'require_avatar' => 'boolean',
            'enable_ratings' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * A space belongs to a user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A space has many testimonials.
     *
     * @return HasMany<Testimonial, $this>
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * Scope a query to only include active spaces.
     *
     * @param Builder<$this> $query
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Public shareable URL for collecting testimonials.
     */
    public function publicUrl(): string
    {
        return route('spaces.submit', ['slug' => $this->slug]);
    }
}

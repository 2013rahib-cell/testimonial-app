<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TestimonialStatus;
use App\Enums\TestimonialType;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $space_id
 * @property string $submitter_name
 * @property string|null $submitter_email
 * @property string|null $submitter_avatar_path
 * @property string|null $submitter_company
 * @property string|null $submitter_role
 * @property string $content
 * @property int|null $rating
 * @property TestimonialType $type
 * @property string|null $video_url
 * @property TestimonialStatus $status
 * @property bool $is_featured
 * @property bool $is_hidden
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Space $space
 */
#[Fillable([
    'submitter_name',
    'submitter_email',
    'submitter_avatar_path',
    'submitter_company',
    'submitter_role',
    'content',
    'rating',
    'type',
    'video_url',
    'status',
    'is_featured',
    'is_hidden',
])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'submitter_name',
        'submitter_email',
        'submitter_avatar_path',
        'submitter_company',
        'submitter_role',
        'content',
        'rating',
        'type',
        'video_url',
        'status',
        'is_featured',
        'is_hidden',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'space_id' => 'integer',
            'rating' => 'integer',
            'type' => TestimonialType::class,
            'status' => TestimonialStatus::class,
            'is_featured' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }

    /**
     * A testimonial belongs to a space.
     *
     * @return BelongsTo<Space, $this>
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Scope a query to only include approved testimonials.
     *
     * @param Builder<$this> $query
     * @return Builder<$this>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', TestimonialStatus::Approved);
    }

    /**
     * Scope a query to only include featured testimonials.
     *
     * @param Builder<$this> $query
     * @return Builder<$this>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to exclude hidden testimonials.
     *
     * @param Builder<$this> $query
     * @return Builder<$this>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }
}

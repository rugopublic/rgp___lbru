<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Casts\CleanHtmlInput;
use App\Enums\Course\CourseDifficulty;
use App\Enums\Material\PublishingStatus;
use App\Models\Polymorphic\Seo;
use App\Models\Course\Teacher;
use App\Observers\CourseObserver;

#[ObservedBy([CourseObserver::class])]
class Course extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cover',
        'title',
        'category_id', 'difficulty',
        'summary',
        'pitch',
        'objective', 'skills',
        'audience', 'features',
        'prerequisites', 'preferables',
        'price',
        'publish_status', 'publish_date'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'difficulty'        => CourseDifficulty::class,
            'pitch'             => CleanHtmlInput::class,
            'objective'         => CleanHtmlInput::class,
            'skills'            => CleanHtmlInput::class,
            'price'             => 'decimal:2',
            'publish_status'    => PublishingStatus::class,
            'publish_date'      => 'datetime'
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return string An attribute that will be the route key.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Get the category associated with the course.
     * By default, the lookup refers to the `category_id` attribute of the course.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class);
    }

    /**
     * Get the course's files directory.
     *
     * @return Attribute<string, never>
     */
    protected function folder(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes)
                => "courses/{$attributes['public_id']}"
        );
    }

    /**
     * Get the course modules ordered by their sort order.
     */
    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)
            ->orderBy('sort_order');
    }

    /**
     * Get all course lessons through the course modules.
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(
            CourseLesson::class,
            CourseModule::class,
            'course_id',
            'module_id',
            'id',
            'id'
        );
    }

    /**
     * Get the SEO data associated with the course.
     */
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    /**
     * Get the teachers associated with the course.
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'course_teacher')
            ->using(\App\Models\Course\CourseTeacher::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /**
     * Handles media folder for the course.
     * @return string
     */
    public function mediaFolder(): string
    {
        return "courses/{$this->public_id}";
    }

    /**
     * Returns the cover url accessor value.
     * @return ?string
     */
    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover) return null;
        $path = str_contains($this->cover, '/') ? $this->cover : $this->mediaFolder().'/'.$this->cover;
        return \Storage::disk('public')->url($path);
    }
}

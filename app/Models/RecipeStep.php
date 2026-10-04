<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ContentMediaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ContentMediaObserver::class])]
class RecipeStep extends Model
{
    protected $fillable = ['recipe_id', 'step_number', 'description', 'image', 'duration_seconds'];

    /**
     * Handles casts for the recipe step.
     * @return array
     */
    protected function casts(): array
    {
        return ['duration_seconds' => 'integer', 'step_number' => 'integer'];
    }

    /**
     * Returns the recipe relationship.
     * @return BelongsTo
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Handles media folder for the recipe step.
     * @return string
     */
    public function mediaFolder(): string
    {
        return 'recipes/'.($this->recipe?->public_id ?? 'unknown').'/steps/'.$this->getKey();
    }

    /**
     * Handles media disk for the recipe step.
     *
     * @param string $field
     * @return string
     */
    public function mediaDisk(string $field): string
    {
        return 'public';
    }

    /**
     * Handles media disks for the recipe step.
     * @return array
     */
    public function mediaDisks(): array
    {
        return ['public'];
    }

    /**
     * Handles image url for the recipe step.
     * @return ?string
     */
    public function imageUrl(): ?string
    {
        return $this->image ? \Storage::disk('public')->url($this->image) : null;
    }
}

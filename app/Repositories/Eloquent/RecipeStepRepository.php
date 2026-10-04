<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Repositories\Contracts\RecipeStepRepositoryInterface;

final class RecipeStepRepository implements RecipeStepRepositoryInterface
{

    /**
     * Queries for recipe and returns the matching persistence model.
     *
     * @param Recipe $recipe
     * @param int $id
     * @return ?RecipeStep
     */
    public function findForRecipe(Recipe $recipe, int $id): ?RecipeStep
    {
        return $recipe->steps()->whereKey($id)->first();
    }

    /**
     * Persists a new record through the repository model.
     *
     * @param Recipe $recipe
     * @param array $attributes
     * @return RecipeStep
     */
    public function create(Recipe $recipe, array $attributes): RecipeStep
    {
        return $recipe->steps()->create($attributes);
    }

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param RecipeStep $step
     * @param array $attributes
     * @return bool
     */
    public function update(RecipeStep $step, array $attributes): bool
    {
        return $step->fill($attributes)->save();
    }

    /**
     * Queries or persists delete missing for the recipe step repository repository.
     *
     * @param Recipe $recipe
     * @param array $keepIds
     * @return int
     */
    public function deleteMissing(Recipe $recipe, array $keepIds): int
    {
        $query = RecipeStep::query()->where('recipe_id', $recipe->id);
        if ($keepIds !== []) {
            $query->whereNotIn('id', $keepIds);
        }
        return $query->get()->each->delete()->count();
    }
}

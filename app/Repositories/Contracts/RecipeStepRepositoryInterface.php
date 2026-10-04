<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Recipe;
use App\Models\RecipeStep;

interface RecipeStepRepositoryInterface
{

    /**
     * Queries for recipe and returns the matching persistence model.
     *
     * @param Recipe $recipe
     * @param int $id
     * @return ?RecipeStep
     */
    public function findForRecipe(Recipe $recipe, int $id): ?RecipeStep;

    /**
     * Persists a new record through the repository model.
     *
     * @param Recipe $recipe
     * @param array $attributes
     * @return RecipeStep
     */
    public function create(Recipe $recipe, array $attributes): RecipeStep;

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param RecipeStep $step
     * @param array $attributes
     * @return bool
     */
    public function update(RecipeStep $step, array $attributes): bool;

    /**
     * Queries or persists delete missing for the recipe step repository interface repository.
     *
     * @param Recipe $recipe
     * @param array $keepIds
     * @return int
     */
    public function deleteMissing(Recipe $recipe, array $keepIds): int;
}

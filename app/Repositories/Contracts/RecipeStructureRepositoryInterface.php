<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Recipe;
use App\Models\RecipeIngredientGroup;
use App\Models\RecipeIngredient;

interface RecipeStructureRepositoryInterface
{

    /**
     * Queries or persists persist for the recipe structure repository interface repository.
     *
     * @param Recipe $recipe
     * @param array $groups
     * @return void
     */
    public function persist(Recipe $recipe, array $groups): void;

    /**
     * Queries or persists create group for the recipe structure repository interface repository.
     *
     * @param Recipe $recipe
     * @param array $attributes
     * @return RecipeIngredientGroup
     */
    public function createGroup(Recipe $recipe, array $attributes): RecipeIngredientGroup;

    /**
     * Queries or persists create ingredient for the recipe structure repository interface repository.
     *
     * @param Recipe $recipe
     * @param int $groupId
     * @param array $attributes
     * @return RecipeIngredient
     */
    public function createIngredient(Recipe $recipe, int $groupId, array $attributes): RecipeIngredient;
}

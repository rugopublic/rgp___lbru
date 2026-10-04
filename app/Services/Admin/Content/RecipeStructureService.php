<?php

declare(strict_types=1);

namespace App\Services\Admin\Content;

use App\Models\Recipe;
use App\Models\RecipeIngredientGroup;
use App\Models\RecipeIngredient;
use App\Repositories\Contracts\RecipeStructureRepositoryInterface;

final class RecipeStructureService
{

    /**
     * Initializes the recipe structure service instance and wires its required dependencies.
     *
     * @param RecipeStructureRepositoryInterface $repository
     * @return void
     */
    public function __construct(private readonly RecipeStructureRepositoryInterface $repository) {}

    /**
     * Synchronizes related data as part of the domain operation.
     *
     * @param Recipe $recipe
     * @param array $groups
     * @return void
     */
    public function sync(Recipe $recipe, array $groups): void
    {
        $this->repository->persist($recipe, $groups);
    }

    /**
     * Orchestrates the create group operation for the recipe structure service domain service.
     *
     * @param Recipe $recipe
     * @param array $attributes
     * @return RecipeIngredientGroup
     */
    public function createGroup(Recipe $recipe, array $attributes): RecipeIngredientGroup
    {
        return $this->repository->createGroup($recipe, $attributes);
    }

    /**
     * Orchestrates the create ingredient operation for the recipe structure service domain service.
     *
     * @param Recipe $recipe
     * @param int $groupId
     * @param array $attributes
     * @return RecipeIngredient
     */
    public function createIngredient(Recipe $recipe, int $groupId, array $attributes): RecipeIngredient
    {
        return $this->repository->createIngredient($recipe, $groupId, $attributes);
    }
}

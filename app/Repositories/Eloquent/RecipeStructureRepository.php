<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeIngredientGroup;
use App\Repositories\Contracts\RecipeStructureRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RecipeStructureRepository extends Repository implements RecipeStructureRepositoryInterface
{

    /**
     * Queries or persists create group for the recipe structure repository repository.
     *
     * @param Recipe $recipe
     * @param array $attributes
     * @return RecipeIngredientGroup
     */
    public function createGroup(Recipe $recipe, array $attributes): RecipeIngredientGroup
    {
        return $recipe->ingredientGroups()->create([
            'title' => trim((string) ($attributes['title'] ?? 'Новая группа')),
            'sort_order' => (int) ($attributes['sort_order'] ?? $recipe->ingredientGroups()->count()),
        ]);
    }

    /**
     * Queries or persists create ingredient for the recipe structure repository repository.
     *
     * @param Recipe $recipe
     * @param int $groupId
     * @param array $attributes
     * @return RecipeIngredient
     */
    public function createIngredient(Recipe $recipe, int $groupId, array $attributes): RecipeIngredient
    {
        $group = $recipe->ingredientGroups()->whereKey($groupId)->firstOrFail();
        return $group->ingredients()->create([
            'recipe_id' => $recipe->id,
            'reference_item_id' => (int) $attributes['reference_item_id'],
            'quantity' => $attributes['quantity'],
            'unit' => trim((string) $attributes['unit']),
            'sort_order' => (int) ($attributes['sort_order'] ?? $group->ingredients()->count()),
        ]);
    }

    /**
     * Queries or persists persist for the recipe structure repository repository.
     *
     * @param Recipe $recipe
     * @param array $groups
     * @return void
     */
    public function persist(Recipe $recipe, array $groups): void
    {
        DB::transaction(function () use ($recipe, $groups): void {
            $existingGroups = $recipe->ingredientGroups()->with('ingredients')->get()->keyBy('id');
            $keptGroups = [];
            $keptIngredients = [];
            $seenGroupIds = [];
            $seenIngredientIds = [];

            foreach (array_values($groups) as $groupIndex => $groupData) {
                $groupId = (int) ($groupData['id'] ?? 0);
                $group = $groupId > 0 ? $existingGroups->get($groupId) : null;
                if ($groupId > 0 && in_array($groupId, $seenGroupIds, true)) {
                    throw ValidationException::withMessages(['structure' => 'Одна группа ингредиентов не может присутствовать в структуре дважды.']);
                }
                if ($groupId > 0 && !$group) {
                    throw ValidationException::withMessages(['structure' => 'Обнаружена группа ингредиентов чужого рецепта.']);
                }
                $group ??= new RecipeIngredientGroup(['recipe_id' => $recipe->id]);
                $group->recipe_id = $recipe->id;
                $group->title = trim((string) ($groupData['title'] ?? ''));
                $group->sort_order = $groupIndex;
                $group->save();
                $keptGroups[] = $group->id;
                $seenGroupIds[] = (int) $group->id;

                $existingIngredients = $group->exists
                    ? $group->ingredients()->get()->keyBy('id')
                    : collect();
                foreach (array_values($groupData['items'] ?? []) as $itemIndex => $itemData) {
                    $itemId = (int) ($itemData['id'] ?? 0);
                    $item = $itemId > 0 ? $existingIngredients->get($itemId) : null;
                    if ($itemId > 0 && !$item) {
                        $item = RecipeIngredient::query()->whereKey($itemId)->where('recipe_id', $recipe->id)->first();
                    }
                    if ($itemId > 0 && in_array($itemId, $seenIngredientIds, true)) {
                        throw ValidationException::withMessages(['structure' => 'Один ингредиент не может присутствовать в структуре дважды.']);
                    }
                    if ($itemId > 0 && !$item) {
                        throw ValidationException::withMessages(['structure' => 'Обнаружен ингредиент чужого рецепта.']);
                    }
                    $item ??= new RecipeIngredient();
                    $item->recipe_id = $recipe->id;
                    $item->group_id = $group->id;
                    $item->reference_item_id = (int) $itemData['reference_item_id'];
                    $item->quantity = $itemData['quantity'];
                    $item->unit = trim((string) $itemData['unit']);
                    $item->sort_order = $itemIndex;
                    $item->save();
                    $keptIngredients[] = $item->id;
                    $seenIngredientIds[] = (int) $item->id;
                }
            }

            $recipe->ingredientGroups()->whereNotIn('id', $keptGroups ?: [0])->delete();
            $recipe->ingredients()->whereNotIn('id', $keptIngredients ?: [0])->delete();
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\Recipe\RecipeGroupStoreRequest;
use App\Http\Requests\Admin\Form\Recipe\RecipeIngredientStoreRequest;
use App\Http\Requests\Admin\Form\Recipe\StoreRequest;
use App\Http\Requests\Admin\Form\Recipe\UpdateRequest;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\RecipeCategoryRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Admin\Content\RecipeService;
use App\Services\Admin\Content\RecipeStructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RecipeController extends Controller
{

    /**
     * Initializes the recipe controller instance and wires its required dependencies.
     *
     * @param RecipeRepositoryInterface $recipes
     * @param RecipeCategoryRepositoryInterface $categories
     * @param ProductRepositoryInterface $products
     * @param ReferenceRepositoryInterface $references
     * @param UserRepositoryInterface $users
     * @param RecipeService $service
     * @param RecipeStructureService $structureService
     * @return void
     */
    public function __construct(
        private readonly RecipeRepositoryInterface $recipes,
        private readonly RecipeCategoryRepositoryInterface $categories,
        private readonly ProductRepositoryInterface $products,
        private readonly ReferenceRepositoryInterface $references,
        private readonly UserRepositoryInterface $users,
        private readonly RecipeService $service,
        private readonly RecipeStructureService $structureService,
    ) {
    }

    /**
     * Builds the listing response and loads selector/filter data.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $recipes = $this->recipes->paginateForAdmin(
            $request->only(['search', 'status', 'deleted'])
        );
        $categories = $this->categories->options();

        return view('admin.recipes.index', compact('recipes', 'categories'));
    }

    /**
     * Prepares the create flow for the requested resource.
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        return redirect()
            ->route('admin.recipes.index')
            ->with('open_create_modal', 'recipeCreateModal');
    }

    /**
     * Accepts validated input and delegates creation to the domain service or repository.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $item = $this->service->create([
            'title' => $request->validated('title'),
            'author_id' => $request->validated('author_id') ?: $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.recipes.edit', $item),
        ]);
    }

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $recipe
     * @return View
     */
    public function edit(string $recipe): View
    {
        $recipe = $this->recipes->findForEdit($recipe);
        $categories = $this->categories->options();
        $products = $this->products->options();
        $references = $this->references->options();
        $authors = $this->users->paginateForAdmin([], 100)
            ->getCollection()
            ->map(fn ($user) => [
                'value' => (string) $user->id,
                'label' => $user->account?->name ?: $user->email,
            ]);

        return view('admin.recipes.edit', compact(
            'recipe',
            'categories',
            'products',
            'references',
            'authors'
        ));
    }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param UpdateRequest $request
     * @param string $recipe
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, string $recipe): JsonResponse
    {
        $model = $this->recipes->findForEdit($recipe);
        $this->service->update($model, $request->user(), $request->safe()->all());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.recipes.edit', $model),
        ]);
    }

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $recipe
     * @return JsonResponse
     */
    public function destroy(string $recipe): JsonResponse
    {
        $model = $this->recipes->findForEdit($recipe);
        $this->recipes->delete($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.recipes.index'),
        ]);
    }

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $recipe
     * @return JsonResponse
     */
    public function restore(string $recipe): JsonResponse
    {
        $model = $this->recipes->findForEdit($recipe);
        $this->recipes->restore($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.recipes.edit', $model),
        ]);
    }

    /**
     * Handles the store group HTTP action for the recipe controller resource.
     *
     * @param RecipeGroupStoreRequest $request
     * @param string $recipe
     * @return JsonResponse
     */
    public function storeGroup(RecipeGroupStoreRequest $request, string $recipe): JsonResponse
    {
        $model = $this->recipes->findForEdit($recipe);
        $this->structureService->createGroup($model, $request->validated());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.recipes.edit', $model),
        ]);
    }

    /**
     * Handles the store ingredient HTTP action for the recipe controller resource.
     *
     * @param RecipeIngredientStoreRequest $request
     * @param string $recipe
     * @return JsonResponse
     */
    public function storeIngredient(RecipeIngredientStoreRequest $request, string $recipe): JsonResponse
    {
        $model = $this->recipes->findForEdit($recipe);
        $data = $request->validated();
        $groupId = (int) $data['group_id'];
        unset($data['group_id']);

        $this->structureService->createIngredient($model, $groupId, $data);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.recipes.edit', $model),
        ]);
    }

    /**
     * Returns selector options for the requested resource type.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function options(Request $request): JsonResponse
    {
        $items = $this->recipes
            ->paginateForAdmin(['search' => $request->query('q')], 20)
            ->getCollection()
            ->map(fn ($recipe) => [
                'value' => (string) $recipe->id,
                'label' => $recipe->title,
            ])
            ->values();

        return response()->json($items);
    }
}

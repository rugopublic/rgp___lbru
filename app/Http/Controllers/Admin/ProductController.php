<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\Product\StoreRequest;
use App\Http\Requests\Admin\Form\Product\UpdateRequest;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Services\Admin\Content\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductController extends Controller
{

    /**
     * Initializes the product controller instance and wires its required dependencies.
     *
     * @param ProductRepositoryInterface $products
     * @param ProductCategoryRepositoryInterface $categories
     * @param ArticleRepositoryInterface $articles
     * @param RecipeRepositoryInterface $recipes
     * @param ReferenceRepositoryInterface $references
     * @param ProductService $service
     * @return void
     */
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductCategoryRepositoryInterface $categories,
        private readonly ArticleRepositoryInterface $articles,
        private readonly RecipeRepositoryInterface $recipes,
        private readonly ReferenceRepositoryInterface $references,
        private readonly ProductService $service,
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
        $products = $this->products->paginateForAdmin(
            $request->only(['search', 'visible', 'deleted'])
        );
        $categories = $this->categories->options();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Prepares the create flow for the requested resource.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(): \Illuminate\Http\RedirectResponse
    {
        return redirect()
            ->route('admin.products.index')
            ->with('open_create_modal', 'productCreateModal');
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
            'price' => 0,
            'is_visible' => false,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.products.edit', $item),
        ]);
    }

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $product
     * @return View
     */
    public function edit(string $product): View
    {
        $product = $this->products->findForEdit($product);
        $categories = $this->categories->options(null, (string) $product->category_id);
        $articles = $this->articles->paginateForAdmin([], 100)
            ->getCollection()
            ->map(fn ($article) => [
                'value' => (string) $article->id,
                'label' => $article->title,
            ]);
        $recipes = $this->recipes->paginateForAdmin([], 100)
            ->getCollection()
            ->map(fn ($recipe) => [
                'value' => (string) $recipe->id,
                'label' => $recipe->title,
            ]);
        $references = $this->references->options();

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'articles',
            'recipes',
            'references'
        ));
    }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param UpdateRequest $request
     * @param string $product
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, string $product): JsonResponse
    {
        $model = $this->products->findForEdit($product);
        $this->service->update($model, $request->user(), $request->safe()->all());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.products.edit', $model),
        ]);
    }

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $product
     * @return JsonResponse
     */
    public function destroy(string $product): JsonResponse
    {
        $model = $this->products->findForEdit($product);
        $this->products->delete($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.products.index'),
        ]);
    }

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $product
     * @return JsonResponse
     */
    public function restore(string $product): JsonResponse
    {
        $model = $this->products->findForEdit($product);
        $this->products->restore($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.products.edit', $model),
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
        return response()->json(
            $this->products->options($request->query('q'), $request->query('id'))
        );
    }
}

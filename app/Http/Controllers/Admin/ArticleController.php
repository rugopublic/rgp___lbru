<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\Article\StoreRequest;
use App\Http\Requests\Admin\Form\Article\UpdateRequest;
use App\Repositories\Contracts\ArticleCategoryRepositoryInterface;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Admin\Content\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ArticleController extends Controller
{

    /**
     * Initializes the article controller instance and wires its required dependencies.
     *
     * @param ArticleRepositoryInterface $articles
     * @param ArticleCategoryRepositoryInterface $categories
     * @param ProductRepositoryInterface $products
     * @param ArticleService $service
     * @return void
     */
    public function __construct(
        private readonly ArticleRepositoryInterface $articles,
        private readonly ArticleCategoryRepositoryInterface $categories,
        private readonly ProductRepositoryInterface $products,
        private readonly ArticleService $service,
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
        $articles = $this->articles->paginateForAdmin(
            $request->only(['search', 'visible', 'schedule', 'deleted'])
        );
        $categories = $this->categories->options();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    /**
     * Prepares the create flow for the requested resource.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(): \Illuminate\Http\RedirectResponse
    {
        return redirect()
            ->route('admin.articles.index')
            ->with('open_create_modal', 'articleCreateModal');
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
            'redirect' => route('admin.articles.edit', $item),
        ]);
    }

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $article
     * @return View
     */
    public function edit(string $article): View
    {
        $article = $this->articles->findForEdit($article);
        $categories = $this->categories->options(null, (string) $article->category_id);
        $products = $this->products->options();
        $selected = $article->products
            ->map(fn ($product) => [
                'value' => (string) $product->id,
                'label' => $product->title,
            ])
            ->values();

        return view('admin.articles.edit', compact('article', 'categories', 'products', 'selected'));
    }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param UpdateRequest $request
     * @param string $article
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, string $article): JsonResponse
    {
        $model = $this->articles->findForEdit($article);
        $this->service->update($model, $request->user(), $request->safe()->all());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.articles.edit', $model),
        ]);
    }

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $article
     * @return JsonResponse
     */
    public function destroy(string $article): JsonResponse
    {
        $model = $this->articles->findForEdit($article);
        $this->articles->delete($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.articles.index'),
        ]);
    }

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $article
     * @return JsonResponse
     */
    public function restore(string $article): JsonResponse
    {
        $model = $this->articles->findForEdit($article);
        $this->articles->restore($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.articles.edit', $model),
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
            $this->articles->options($request->query('q'), $request->query('id'))
        );
    }
}

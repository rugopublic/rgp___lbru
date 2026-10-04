<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreContentCommentRequest;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\ContentInteractionRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

final class ContentInteractionController extends Controller
{

    /**
     * Initializes the content interaction controller instance and wires its required dependencies.
     *
     * @param ArticleRepositoryInterface $articles
     * @param ProductRepositoryInterface $products
     * @param RecipeRepositoryInterface $recipes
     * @param ReferenceRepositoryInterface $references
     * @param ContentInteractionRepositoryInterface $interactions
     * @return void
     */
    public function __construct(
        private readonly ArticleRepositoryInterface $articles,
        private readonly ProductRepositoryInterface $products,
        private readonly RecipeRepositoryInterface $recipes,
        private readonly ReferenceRepositoryInterface $references,
        private readonly ContentInteractionRepositoryInterface $interactions,
    ) {}

    /**
     * Handles the like HTTP action for the content interaction controller resource.
     *
     * @param Request $request
     * @param string $type
     * @param string $id
     * @return JsonResponse
     */
    public function like(Request $request, string $type, string $id): JsonResponse
    {
        $model = $this->resolveModel($type, $id);
        $active = $this->interactions->toggleLike($model, (int) $request->user()->id);

        return response()->json([
            'success' => true,
            'active' => $active,
            'context' => $this->interactions->context($model, (int) $request->user()->id),
        ]);
    }

    /**
     * Handles the bookmark HTTP action for the content interaction controller resource.
     *
     * @param Request $request
     * @param string $type
     * @param string $id
     * @return JsonResponse
     */
    public function bookmark(Request $request, string $type, string $id): JsonResponse
    {
        $model = $this->resolveModel($type, $id);
        $active = $this->interactions->toggleBookmark($model, (int) $request->user()->id);

        return response()->json([
            'success' => true,
            'active' => $active,
            'context' => $this->interactions->context($model, (int) $request->user()->id),
        ]);
    }

    /**
     * Handles the comment HTTP action for the content interaction controller resource.
     *
     * @param StoreContentCommentRequest $request
     * @param string $type
     * @param string $id
     * @return JsonResponse
     */
    public function comment(StoreContentCommentRequest $request, string $type, string $id): JsonResponse
    {
        $model = $this->resolveModel($type, $id);
        $this->interactions->addComment($model, (int) $request->user()->id, (string) $request->validated('body'));

        return response()->json([
            'success' => true,
            'redirect' => url()->previous() . '#comments',
        ], 201);
    }

    /**
     * Handles the resolve model HTTP action for the content interaction controller resource.
     *
     * @param string $type
     * @param string $id
     * @return Model
     */
    private function resolveModel(string $type, string $id): Model
    {
        $model = match ($type) {
            'article' => $this->articles->findPublished($id),
            'product' => $this->products->findSellable($id),
            'recipe' => $this->recipes->findPublished($id),
            'ingredient' => $this->references->findVisible($id),
            default => null,
        };

        abort_unless($model, 404);

        return $model;
    }
}

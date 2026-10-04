<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\SearchRequest;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\ContentInteractionRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductPurchaseRepositoryInterface;
use App\Repositories\Contracts\CourseEnrollmentRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CatalogController extends Controller
{

    /**
     * Initializes the catalog controller instance and wires its required dependencies.
     *
     * @param ArticleRepositoryInterface $articles
     * @param CourseRepositoryInterface $courses
     * @param ProductRepositoryInterface $products
     * @param RecipeRepositoryInterface $recipes
     * @param ReferenceRepositoryInterface $references
     * @param ContentInteractionRepositoryInterface $interactions
     * @param ProductPurchaseRepositoryInterface $productPurchases
     * @param CourseEnrollmentRepositoryInterface $courseEnrollments
     * @return void
     */
    public function __construct(
        private readonly ArticleRepositoryInterface $articles,
        private readonly CourseRepositoryInterface $courses,
        private readonly ProductRepositoryInterface $products,
        private readonly RecipeRepositoryInterface $recipes,
        private readonly ReferenceRepositoryInterface $references,
        private readonly ContentInteractionRepositoryInterface $interactions,
        private readonly ProductPurchaseRepositoryInterface $productPurchases,
        private readonly CourseEnrollmentRepositoryInterface $courseEnrollments,
    ) {}

    /**
     * Handles the home HTTP action for the catalog controller resource.
     * @return View
     */
    public function home(): View
    {
        return view('frontend.pages.home', [
            'courses' => $this->courses->paginatePublished(6),
            'products' => $this->products->paginateSellable(6),
            'articles' => $this->articles->paginatePublished(6),
        ]);
    }

    /**
     * Builds the listing response and loads selector/filter data.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        return view('frontend.catalog.index', [
            'courses' => $this->courses->paginatePublished(6),
            'products' => $this->products->paginateSellable(6),
            'articles' => $this->articles->paginatePublished(6),
        ]);
    }

    /**
     * Handles the courses HTTP action for the catalog controller resource.
     * @return View
     */
    public function courses(): View
    {
        return view('frontend.catalog.courses', [
            'courses' => $this->courses->paginatePublished(),
        ]);
    }

    /**
     * Handles the course HTTP action for the catalog controller resource.
     *
     * @param string $course
     * @return View
     */
    public function course(string $course): View
    {
        $item = $this->courses->findPublished($course);
        abort_unless($item, 404);

        $enrolled = auth()->check() && $this->courseEnrollments->findActiveForUser((int) auth()->id(), (int) $item->id) !== null;
        return view('frontend.catalog.course', ['course' => $item, 'enrolled' => $enrolled]);
    }

    /**
     * Handles the search HTTP action for the catalog controller resource.
     *
     * @param SearchRequest $request
     * @return View
     */
    public function search(SearchRequest $request): View
    {
        $query = trim((string) $request->validated('q'));
        return view('frontend.search', [
            'q' => $query,
            'recipes' => $this->recipes->searchPublished($query),
            'articles' => $this->articles->searchPublished($query),
            'ingredients' => $this->references->searchVisible($query),
        ]);
    }

    /**
     * Handles the articles HTTP action for the catalog controller resource.
     * @return View
     */
    public function articles(): View
    {
        return view('frontend.catalog.articles', [
            'articles' => $this->articles->paginatePublished(),
        ]);
    }

    /**
     * Handles the recipes HTTP action for the catalog controller resource.
     * @return View
     */
    public function recipes(): View
    {
        return view('frontend.catalog.recipes', [
            'recipes' => $this->recipes->paginatePublished(),
        ]);
    }

    /**
     * Handles the ingredients HTTP action for the catalog controller resource.
     * @return View
     */
    public function ingredients(): View
    {
        return view('frontend.catalog.ingredients', [
            'ingredients' => $this->references->paginateVisible(),
        ]);
    }

    /**
     * Handles the products HTTP action for the catalog controller resource.
     * @return View
     */
    public function products(): View
    {
        return view('frontend.catalog.products', [
            'products' => $this->products->paginateSellable(),
        ]);
    }

    /**
     * Handles the product HTTP action for the catalog controller resource.
     *
     * @param string $product
     * @return View
     */
    public function product(string $product): View
    {
        $item = $this->products->findSellable($product);
        abort_unless($item, 404);

        $owned = auth()->check() && $this->productPurchases->findActiveForUser((int) auth()->id(), (int) $item->id) !== null;
        return $this->detailView('frontend.catalog.product', 'product', $item, ['owned' => $owned]);
    }

    /**
     * Handles the article HTTP action for the catalog controller resource.
     *
     * @param string $article
     * @return View
     */
    public function article(string $article): View
    {
        $item = $this->articles->findPublished($article);
        abort_unless($item, 404);

        return $this->detailView('frontend.catalog.article', 'article', $item);
    }

    /**
     * Handles the recipe HTTP action for the catalog controller resource.
     *
     * @param string $recipe
     * @return View
     */
    public function recipe(string $recipe): View
    {
        $item = $this->recipes->findPublished($recipe);
        abort_unless($item, 404);

        return $this->detailView('frontend.catalog.recipe', 'recipe', $item);
    }

    /**
     * Handles the ingredient HTTP action for the catalog controller resource.
     *
     * @param string $ingredient
     * @return View
     */
    public function ingredient(string $ingredient): View
    {
        $item = $this->references->findVisible($ingredient);
        abort_unless($item, 404);

        return $this->detailView('frontend.catalog.ingredient', 'ingredient', $item);
    }

    /**
     * Handles the detail view HTTP action for the catalog controller resource.
     *
     * @param string $view
     * @param string $type
     * @param Model $item
     * @param array $extra
     * @return View
     */
    private function detailView(string $view, string $type, Model $item, array $extra = []): View
    {
        $userId = auth()->id();

        return view($view, [
            $type => $item,
            'interaction' => $this->interactions->context($item, $userId ? (int) $userId : null),
            'comments' => $this->interactions->publishedComments($item),
            ...$extra,
        ]);
    }
}

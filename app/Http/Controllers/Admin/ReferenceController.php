<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\Reference\StoreRequest;
use App\Http\Requests\Admin\Form\Reference\UpdateRequest;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ReferenceCategoryRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Services\Admin\Content\ReferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ReferenceController extends Controller
{

    /**
     * Initializes the reference controller instance and wires its required dependencies.
     *
     * @param ReferenceRepositoryInterface $items
     * @param ReferenceCategoryRepositoryInterface $categories
     * @param ProductRepositoryInterface $products
     * @param ReferenceService $service
     * @return void
     */
    public function __construct(
        private readonly ReferenceRepositoryInterface $items,
        private readonly ReferenceCategoryRepositoryInterface $categories,
        private readonly ProductRepositoryInterface $products,
        private readonly ReferenceService $service,
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
        $items = $this->items->paginateForAdmin(
            $request->only(['search', 'visible', 'deleted'])
        );
        $categories = $this->categories->options();

        return view('admin.reference.index', compact('items', 'categories'));
    }

    /**
     * Prepares the create flow for the requested resource.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(): \Illuminate\Http\RedirectResponse
    {
        return redirect()
            ->route('admin.reference.index')
            ->with('open_create_modal', 'referenceCreateModal');
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
            'is_visible' => false,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.reference.edit', $item),
        ]);
    }

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $reference
     * @return View
     */
    public function edit(string $reference): View
    {
        $item = $this->items->findForEdit($reference);
        $categories = $this->categories->options(null, (string) $item->category_id);
        $products = $this->products->options();
        $selected = $item->products
            ->map(fn ($product) => [
                'value' => (string) $product->id,
                'label' => $product->title,
            ])
            ->values();

        return view('admin.reference.edit', compact('item', 'categories', 'products', 'selected'));
    }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param UpdateRequest $request
     * @param string $reference
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, string $reference): JsonResponse
    {
        $item = $this->items->findForEdit($reference);
        $this->service->update($item, $request->user(), $request->safe()->all());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.reference.edit', $item),
        ]);
    }

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $reference
     * @return JsonResponse
     */
    public function destroy(string $reference): JsonResponse
    {
        $item = $this->items->findForEdit($reference);
        $this->items->delete($item);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.reference.index'),
        ]);
    }

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $reference
     * @return JsonResponse
     */
    public function restore(string $reference): JsonResponse
    {
        $item = $this->items->findForEdit($reference);
        $this->items->restore($item);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.reference.edit', $item),
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
            $this->items->options($request->query('q'), $request->query('id'))
        );
    }
}

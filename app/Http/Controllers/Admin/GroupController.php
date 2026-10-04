<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\Group\StoreRequest;
use App\Http\Requests\Admin\Form\Group\UpdateRequest;
use App\Repositories\Contracts\GroupRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class GroupController extends Controller
{

    /**
     * Initializes the group controller instance and wires its required dependencies.
     *
     * @param GroupRepositoryInterface $groups
     * @return void
     */
    public function __construct(
        private readonly GroupRepositoryInterface $groups,
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
        $groups = $this->groups->paginateForAdmin(
            $request->only(['search', 'deleted'])
        );

        return view('admin.groups.index', compact('groups'));
    }

    /**
     * Prepares the create flow for the requested resource.
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        return redirect()
            ->route('admin.groups.index')
            ->with('open_create_modal', 'groupCreateModal');
    }

    /**
     * Accepts validated input and delegates creation to the domain service or repository.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $group = $this->groups->create($request->validated());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.groups.edit', $group),
        ]);
    }

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $group
     * @return View
     */
    public function edit(string $group): View
    {
        $group = $this->groups->findByPublicId($group);

        abort_unless($group, 404);

        return view('admin.groups.edit', compact('group'));
    }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param UpdateRequest $request
     * @param string $group
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, string $group): JsonResponse
    {
        $model = $this->groups->findByPublicId($group);
        abort_unless($model, 404);
        $this->groups->update($model, $request->validated());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.groups.edit', $model),
        ]);
    }

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $group
     * @return JsonResponse
     */
    public function destroy(string $group): JsonResponse
    {
        $model = $this->groups->findByPublicId($group);
        abort_unless($model, 404);
        $this->groups->delete($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.groups.index'),
        ]);
    }

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $group
     * @return JsonResponse
     */
    public function restore(string $group): JsonResponse
    {
        $model = $this->groups->findByPublicId($group);
        abort_unless($model, 404);
        $this->groups->restore($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.groups.index'),
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
            $this->groups->options($request->query('q'))
        );
    }
}

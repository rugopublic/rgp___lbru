<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\User\StoreRequest;
use App\Http\Requests\Admin\Form\User\UpdateRequest;
use App\Repositories\Contracts\GroupRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Admin\User\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{

    /**
     * Initializes the user controller instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @param GroupRepositoryInterface $groups
     * @param AdminUserService $service
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly GroupRepositoryInterface $groups,
        private readonly AdminUserService $service,
    ) {}

    /**
     * Builds the listing response and loads selector/filter data.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $users = $this->users->paginateForAdmin($request->only(['search', 'group', 'deleted', 'sort']), 25);
        $groups = $this->groups->options(null, 100);
        return view('admin.users.index', compact('users', 'groups'));
    }

    /**
     * Prepares the create flow for the requested resource.
     * @return mixed
     */
    public function create()
    {
        $groups = $this->groups->options(null, 100);
        return view('admin.users.create', compact('groups'));
    }

    /**
     * Accepts validated input and delegates creation to the domain service or repository.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.users.edit', $user),
        ]);
    }

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $user
     * @return mixed
     */
    public function edit(string $user)
    {
        $user = $this->users->findForAdmin($user);
        $groups = $this->groups->options(null, 100);
        return view('admin.users.edit', compact('user', 'groups'));
    }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param UpdateRequest $request
     * @param string $user
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, string $user): JsonResponse
    {
        $model = $this->users->findForAdmin($user);
        $this->service->update($model, $request->validated());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.users.edit', $model),
        ]);
    }

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $user
     * @return JsonResponse
     */
    public function destroy(string $user): JsonResponse
    {
        $model = $this->users->findForAdmin($user);
        $this->users->delete($model);
        return response()->json(['success' => true, 'redirect' => route('admin.users.index')]);
    }

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $user
     * @return JsonResponse
     */
    public function restore(string $user): JsonResponse
    {
        $model = $this->users->findForAdmin($user);
        $this->users->restore($model);
        return response()->json(['success' => true, 'redirect' => route('admin.users.edit', $model)]);
    }

    /**
     * Returns selector options for the requested resource type.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function options(Request $request): JsonResponse
    {
        $users=$this->users->paginateForAdmin(['search'=>$request->query('q')],20)->getCollection();
        $selected=$request->query('id');
        if($selected && !$users->contains(fn($u)=>(string)$u->id===(string)$selected)){ $user=$this->users->findForAdmin($selected); $users=$users->prepend($user); }
        return response()->json($users->map(fn($u)=>['value'=>(string)$u->id,'label'=>$u->account?->name ?: $u->email])->values());
    }
}

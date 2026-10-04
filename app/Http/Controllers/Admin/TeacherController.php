<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\Teacher\StoreRequest;
use App\Http\Requests\Admin\Form\Teacher\UpdateRequest;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use App\Services\Admin\Course\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TeacherController extends Controller
{

    /**
     * Initializes the teacher controller instance and wires its required dependencies.
     *
     * @param TeacherRepositoryInterface $teachers
     * @param TeacherService $service
     * @return void
     */
    public function __construct(
        private readonly TeacherRepositoryInterface $teachers,
        private readonly TeacherService $service,
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
        $teachers = $this->teachers->paginateForAdmin(
            $request->only(['search', 'deleted'])
        );

        return view('admin.teachers.index', compact('teachers'));
    }

    /**
     * Prepares the create flow for the requested resource.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(): \Illuminate\Http\RedirectResponse
    {
        return redirect()
            ->route('admin.teachers.index')
            ->with('open_create_modal', 'teacherCreateModal');
    }

    /**
     * Accepts validated input and delegates creation to the domain service or repository.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $teacher = $this->teachers->create($request->validated());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.teachers.edit', $teacher),
        ]);
    }

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $teacher
     * @return View
     */
    public function edit(string $teacher): View
    {
        $teacher = $this->teachers->findForEdit($teacher);

        return view('admin.teachers.edit', compact('teacher'));
    }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param UpdateRequest $request
     * @param string $teacher
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, string $teacher): JsonResponse
    {
        $model = $this->teachers->findForEdit($teacher);
        $this->service->update($model, $request->user(), $request->safe()->all());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.teachers.edit', $model),
        ]);
    }

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $teacher
     * @return JsonResponse
     */
    public function destroy(string $teacher): JsonResponse
    {
        $model = $this->teachers->findForEdit($teacher);
        $this->teachers->delete($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.teachers.index'),
        ]);
    }

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $teacher
     * @return JsonResponse
     */
    public function restore(string $teacher): JsonResponse
    {
        $model = $this->teachers->findForEdit($teacher);
        $this->teachers->restore($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.teachers.edit', $model),
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
            $this->teachers->options($request->query('q'), $request->query('id'))
        );
    }
}

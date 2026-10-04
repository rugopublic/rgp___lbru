<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\Course\StoreRequest;
use App\Http\Requests\Admin\Form\Course\UpdateRequest;
use App\Repositories\Contracts\CourseCategoryRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Services\Admin\Course\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CourseController extends Controller
{

    /**
     * Initializes the course controller instance and wires its required dependencies.
     *
     * @param CourseRepositoryInterface $courses
     * @param CourseCategoryRepositoryInterface $categories
     * @param CourseService $service
     * @return void
     */
    public function __construct(
        private readonly CourseRepositoryInterface $courses,
        private readonly CourseCategoryRepositoryInterface $categories,
        private readonly CourseService $service,
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
        $courses = $this->courses->paginateForAdmin(
            $request->only(['search', 'category', 'status', 'deleted', 'sort', 'direction'])
        );
        $categories = $this->categories->options();

        return view('admin.courses.index', compact('courses', 'categories'));
    }

    /**
     * Prepares the create flow for the requested resource.
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        return redirect()
            ->route('admin.courses.index')
            ->with('open_create_modal', 'courseCreateModal');
    }

    /**
     * Accepts validated input and delegates creation to the domain service or repository.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $course = $this->service->create([
            'title' => $request->validated('title'),
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.courses.edit', $course),
        ]);
    }

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $course
     * @return View
     */
    public function edit(string $course): View
    {
        $course = $this->courses->findForEdit($course);
        $categories = $this->categories->options(null, (string) $course->category_id);

        return view('admin.courses.edit', compact('course', 'categories'));
    }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param UpdateRequest $request
     * @param string $course
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, string $course): JsonResponse
    {
        $model = $this->courses->findForEdit($course);
        $this->service->update($model, $request->user(), $request->safe()->collect());

        return response()->json([
            'success' => true,
            'redirect' => route('admin.courses.edit', $model),
        ]);
    }

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $course
     * @return JsonResponse
     */
    public function destroy(string $course): JsonResponse
    {
        $model = $this->courses->findForEdit($course);
        $this->courses->delete($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.courses.index'),
        ]);
    }

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $course
     * @return JsonResponse
     */
    public function restore(string $course): JsonResponse
    {
        $model = $this->courses->findForEdit($course);
        $this->courses->restore($model);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.courses.edit', $model),
        ]);
    }
}

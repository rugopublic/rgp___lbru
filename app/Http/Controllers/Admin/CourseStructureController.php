<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\Course\StructureRequest;
use App\Http\Requests\Admin\Form\Course\ModuleStoreRequest;
use App\Http\Requests\Admin\Form\Course\LessonStoreRequest;
use App\Http\Requests\Admin\Form\Course\TeacherSyncRequest;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\CourseStructureRepositoryInterface;
use App\Services\Admin\Course\CourseStructureService;
use Illuminate\Http\JsonResponse;

final class CourseStructureController extends Controller
{

    /**
     * Initializes the course structure controller instance and wires its required dependencies.
     *
     * @param CourseRepositoryInterface $courses
     * @param CourseStructureRepositoryInterface $structure
     * @param CourseStructureService $service
     * @return void
     */
    public function __construct(
        private readonly CourseRepositoryInterface $courses,
        private readonly CourseStructureRepositoryInterface $structure,
        private readonly CourseStructureService $service,
    ) {}

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param StructureRequest $request
     * @param string $course
     * @return JsonResponse
     */
    public function update(StructureRequest $request, string $course): JsonResponse
    {
        $model = $this->courses->findForEdit($course);
        $this->service->persist($model, $request->validated('structure.groups', []));

        return response()->json([
            'success' => true,
            'redirect' => route('admin.courses.edit', $model),
        ]);
    }

    /**
     * Handles the sync teachers HTTP action for the course structure controller resource.
     *
     * @param TeacherSyncRequest $request
     * @param string $course
     * @return JsonResponse
     */
    public function syncTeachers(TeacherSyncRequest $request, string $course): JsonResponse
    {
        $model = $this->courses->findForEdit($course);
        $this->service->syncTeachers($model, $request->validated('teacher_ids', []));

        return response()->json([
            'success' => true,
            'redirect' => route('admin.courses.edit', $model),
        ]);
    }

    /**
     * Handles the create lesson HTTP action for the course structure controller resource.
     *
     * @param string $course
     * @param string $module
     * @return mixed
     */
    public function createLesson(string $course, string $module)
    {
        return redirect()->route('admin.courses.edit',$course);
    }

    /**
     * Handles the store module HTTP action for the course structure controller resource.
     *
     * @param ModuleStoreRequest $request
     * @param string $course
     * @return JsonResponse
     */
    public function storeModule(ModuleStoreRequest $request, string $course): JsonResponse
    {
        $model=$this->courses->findForEdit($course);
        $data=$request->validated();
        $module=$this->structure->createModule($model,['title'=>$data['title'],'sort_order'=>$model->modules->count()]);
        return response()->json(['success'=>true,'redirect'=>route('admin.courses.edit',$model),'module_id'=>$module->id]);
    }

    /**
     * Handles the store lesson HTTP action for the course structure controller resource.
     *
     * @param LessonStoreRequest $request
     * @param string $course
     * @param string $module
     * @return JsonResponse
     */
    public function storeLesson(LessonStoreRequest $request, string $course, string $module): JsonResponse
    {
        $model=$this->courses->findForEdit($course);
        $parent=$model->modules->firstWhere('id',(int)$module);
        abort_unless($parent,404);
        $data=$request->validated();
        $lesson=$this->structure->createLesson($parent,['title'=>$data['title'],'sort_order'=>$parent->lessons->count()]);
        return response()->json(['success'=>true,'redirect'=>route('admin.courses.lessons.edit',[$model,$lesson])]);
    }
}

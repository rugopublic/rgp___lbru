<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\Course\LessonUpdateRequest;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\QuizRepositoryInterface;
use App\Services\Admin\Course\CourseLessonService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

final class CourseLessonController extends Controller
{

    /**
     * Initializes the course lesson controller instance and wires its required dependencies.
     *
     * @param CourseRepositoryInterface $courses
     * @param CourseLessonService $service
     * @param QuizRepositoryInterface $quizzes
     * @return void
     */
    public function __construct(
        private readonly CourseRepositoryInterface $courses,
        private readonly CourseLessonService $service,
        private readonly QuizRepositoryInterface $quizzes,
    ) {}

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $course
     * @param string $lesson
     * @return View
     */
    public function edit(string $course, string $lesson): View
    {
        $model=$this->courses->findForEdit($course);
        $lessonModel=$model->lessons->firstWhere('id',(int)$lesson);
        abort_unless($lessonModel,404);
        return view('admin.courses.lesson', ['model'=>$model,'lessonModel'=>$lessonModel,'quiz'=>$this->quizzes->findForLesson($lessonModel)]);
    }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param LessonUpdateRequest $request
     * @param string $course
     * @param string $lesson
     * @return JsonResponse
     */
    public function update(LessonUpdateRequest $request, string $course, string $lesson): JsonResponse
    {
        $model=$this->courses->findForEdit($course);
        $lessonModel=$model->lessons->firstWhere('id',(int)$lesson);
        abort_unless($lessonModel,404);
        $this->service->update($lessonModel, $request->user(), $request->validated());
        return response()->json(['success'=>true,'redirect'=>route('admin.courses.lessons.edit',[$model,$lessonModel])]);
    }
}

<?php
declare(strict_types=1);
namespace App\Repositories\Eloquent;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Repositories\Contracts\CourseStructureRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
final class CourseStructureRepository extends Repository implements CourseStructureRepositoryInterface
{

    /**
     * Queries or persists create module for the course structure repository repository.
     *
     * @param Course $course
     * @param array $attributes
     * @return Model
     */
    public function createModule(Course $course, array $attributes): Model
    {
        return $course->modules()->create($attributes);
    }

    /**
     * Queries or persists update module for the course structure repository repository.
     *
     * @param CourseModule $module
     * @param array $attributes
     * @return bool
     */
    public function updateModule(CourseModule $module, array $attributes): bool
    {
        return $module->fill($attributes)->save();
    }

    /**
     * Queries or persists delete module for the course structure repository repository.
     *
     * @param CourseModule $module
     * @return bool
     */
    public function deleteModule(CourseModule $module): bool
    {
        return (bool) $module->delete();
    }

    /**
     * Queries or persists create lesson for the course structure repository repository.
     *
     * @param CourseModule $module
     * @param array $attributes
     * @return Model
     */
    public function createLesson(CourseModule $module, array $attributes): Model
    {
        return $module->lessons()->create($attributes);
    }

    /**
     * Queries or persists update lesson for the course structure repository repository.
     *
     * @param Model $lesson
     * @param array $attributes
     * @return bool
     */
    public function updateLesson(Model $lesson, array $attributes): bool
    {
        return $lesson->fill($attributes)->save();
    }

    /**
     * Queries or persists update lesson content for the course structure repository repository.
     *
     * @param Model $lesson
     * @param array $data
     * @return void
     */
    public function updateLessonContent(Model $lesson, array $data): void
    {
        DB::transaction(function () use ($lesson, $data): void {
            $steps = $data['steps'] ?? null;
            $quizId = $data['quiz_id'] ?? null;
            unset($data['steps'], $data['quiz_id']);
            $lesson->fill($data)->save();

            if ($steps !== null) {
                $keep = [];
                $seenStepIds = [];
                foreach (array_values($steps) as $index => $step) {
                    $stepId = (int) ($step['id'] ?? 0);
                    $attributes = [
                        'type' => $step['type'],
                        'title' => $step['title'],
                        'content' => $step['content'] ?? null,
                        'video_url' => $step['video_url'] ?? null,
                        'duration_seconds' => $step['duration_seconds'] ?? null,
                        'sort_order' => $index,
                        'quiz_data' => $step['quiz_data'] ?? null,
                    ];
                    if ($stepId > 0) {
                        if (in_array($stepId, $seenStepIds, true)) {
                            throw ValidationException::withMessages([
                                "steps.{$index}.id" => 'Один шаг не может присутствовать в уроке дважды.',
                            ]);
                        }
                        $seenStepIds[] = $stepId;
                        $model = $lesson->steps()->whereKey($stepId)->firstOrFail();
                        $model->fill($attributes)->save();
                    } else {
                        $model = $lesson->steps()->create($attributes);
                    }
                    $keep[] = $model->id;
                }
                $lesson->steps()->whereNotIn('id', $keep ?: [0])->delete();
            }

            if ($quizId !== null) {
                $lesson->quizzes()->sync($quizId ? [(int) $quizId] : []);
            }
        });
    }

    /**
     * Queries or persists delete lesson for the course structure repository repository.
     *
     * @param Model $lesson
     * @return bool
     */
    public function deleteLesson(Model $lesson): bool
    {
        return (bool) $lesson->delete();
    }

    /**
     * Queries or persists persist structure for the course structure repository repository.
     *
     * @param Course $course
     * @param array $modules
     * @return void
     */
    public function persistStructure(Course $course, array $modules): void
    {
        DB::transaction(function () use ($course, $modules): void {
            $moduleIds = $course->modules()->pluck('id')->map(fn ($id) => (int) $id)->all();
            $submittedModuleIds = [];
            $submittedLessonIds = [];
            foreach (array_values($modules) as $moduleIndex => $moduleData) {
                $moduleId = (int) ($moduleData['id'] ?? 0);
                if ($moduleId <= 0 || !in_array($moduleId, $moduleIds, true)) {
                    throw ValidationException::withMessages(['structure' => 'В структуре курса обнаружен модуль, принадлежащий другому курсу.']);
                }
                if (in_array($moduleId, $submittedModuleIds, true)) {
                    throw ValidationException::withMessages(['structure' => 'Один модуль не может присутствовать в структуре курса дважды.']);
                }
                $submittedModuleIds[] = $moduleId;
                $module = CourseModule::query()->whereKey($moduleId)->where('course_id', $course->id)->firstOrFail();
                $module->update(['title' => $moduleData['title'] ?? $module->title, 'sort_order' => $moduleIndex]);

                foreach (array_values($moduleData['lessons'] ?? []) as $lessonIndex => $lessonData) {
                    $lessonId = (int) ($lessonData['id'] ?? 0);
                    if ($lessonId <= 0) {
                        throw ValidationException::withMessages(['structure' => 'В структуре курса обнаружен урок без корректного идентификатора.']);
                    }
                    if (in_array($lessonId, $submittedLessonIds, true)) {
                        throw ValidationException::withMessages(['structure' => 'Один урок не может присутствовать в структуре курса более одного раза.']);
                    }

                    // Cross-module moves are allowed only within the current course.
                    $lesson = CourseLesson::query()
                        ->whereKey($lessonId)
                        ->whereHas('module', fn ($q) => $q->where('course_id', $course->id))
                        ->first();
                    if (!$lesson) {
                        throw ValidationException::withMessages(['structure' => 'В структуре курса обнаружен урок, принадлежащий другому курсу.']);
                    }

                    $lesson->update(['module_id' => $module->id, 'sort_order' => $lessonIndex]);
                    $submittedLessonIds[] = (int) $lesson->id;
                }
            }
            $course->modules()->whereNotIn('id', $submittedModuleIds ?: [0])->delete();
        });
    }
}

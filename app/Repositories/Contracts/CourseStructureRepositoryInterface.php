<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Model;

interface CourseStructureRepositoryInterface
{

    /**
     * Queries or persists create module for the course structure repository interface repository.
     *
     * @param Course $course
     * @param array $attributes
     * @return Model
     */
    public function createModule(Course $course, array $attributes): Model;

    /**
     * Queries or persists update module for the course structure repository interface repository.
     *
     * @param CourseModule $module
     * @param array $attributes
     * @return bool
     */
    public function updateModule(CourseModule $module, array $attributes): bool;

    /**
     * Queries or persists delete module for the course structure repository interface repository.
     *
     * @param CourseModule $module
     * @return bool
     */
    public function deleteModule(CourseModule $module): bool;

    /**
     * Queries or persists create lesson for the course structure repository interface repository.
     *
     * @param CourseModule $module
     * @param array $attributes
     * @return Model
     */
    public function createLesson(CourseModule $module, array $attributes): Model;

    /**
     * Queries or persists update lesson for the course structure repository interface repository.
     *
     * @param Model $lesson
     * @param array $attributes
     * @return bool
     */
    public function updateLesson(Model $lesson, array $attributes): bool;

    /**
     * Queries or persists update lesson content for the course structure repository interface repository.
     *
     * @param Model $lesson
     * @param array $data
     * @return void
     */
    public function updateLessonContent(Model $lesson, array $data): void;

    /**
     * Queries or persists delete lesson for the course structure repository interface repository.
     *
     * @param Model $lesson
     * @return bool
     */
    public function deleteLesson(Model $lesson): bool;

    /**
     * Queries or persists persist structure for the course structure repository interface repository.
     *
     * @param Course $course
     * @param array $modules
     * @return void
     */
    public function persistStructure(Course $course, array $modules): void;
}

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\CourseLesson;
use App\Models\Quiz;

interface QuizRepositoryInterface
{

    /**
     * Queries for lesson and returns the matching persistence model.
     *
     * @param CourseLesson $lesson
     * @return ?Quiz
     */
    public function findForLesson(CourseLesson $lesson): ?Quiz;

    /**
     * Queries or persists ensure for lesson for the quiz repository interface repository.
     *
     * @param CourseLesson $lesson
     * @param string $title
     * @return Quiz
     */
    public function ensureForLesson(CourseLesson $lesson, string $title): Quiz;

    /**
     * Queries or persists detach from lesson for the quiz repository interface repository.
     *
     * @param CourseLesson $lesson
     * @return void
     */
    public function detachFromLesson(CourseLesson $lesson): void;

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Quiz $quiz
     * @param array $attributes
     * @return bool
     */
    public function update(Quiz $quiz, array $attributes): bool;

    /**
     * Synchronizes editor through the repository relationship.
     *
     * @param Quiz $quiz
     * @param array $questions
     * @return void
     */
    public function syncEditor(Quiz $quiz, array $questions): void;
}

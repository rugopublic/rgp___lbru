<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\CourseLesson;
use App\Models\Quiz;
use App\Repositories\Contracts\QuizRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class QuizRepository implements QuizRepositoryInterface
{

    /**
     * Queries for lesson and returns the matching persistence model.
     *
     * @param CourseLesson $lesson
     * @return ?Quiz
     */
    public function findForLesson(CourseLesson $lesson): ?Quiz
    {
        return $lesson->quizzes()->with('questions.options')->first();
    }

    /**
     * Queries or persists ensure for lesson for the quiz repository repository.
     *
     * @param CourseLesson $lesson
     * @param string $title
     * @return Quiz
     */
    public function ensureForLesson(CourseLesson $lesson, string $title): Quiz
    {
        return DB::transaction(function () use ($lesson, $title): Quiz {
            $lockedLesson = CourseLesson::query()->lockForUpdate()->findOrFail($lesson->id);
            $quiz = $lockedLesson->quizzes()->with('questions.options')->first();

            if ($quiz) {
                return $quiz;
            }

            $quiz = Quiz::create([
                'title' => trim($title) !== '' ? trim($title) : $lesson->title,
                'description' => null,
                'is_published' => false,
            ]);

            $lockedLesson->quizzes()->syncWithoutDetaching([$quiz->id]);

            return $quiz->load('questions.options');
        });
    }

    /**
     * Queries or persists detach from lesson for the quiz repository repository.
     *
     * @param CourseLesson $lesson
     * @return void
     */
    public function detachFromLesson(CourseLesson $lesson): void
    {
        $lesson->quizzes()->detach();
    }

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Quiz $quiz
     * @param array $attributes
     * @return bool
     */
    public function update(Quiz $quiz, array $attributes): bool
    {
        return $quiz->fill($attributes)->save();
    }

    /**
     * Synchronizes editor through the repository relationship.
     *
     * @param Quiz $quiz
     * @param array $questions
     * @return void
     */
    public function syncEditor(Quiz $quiz, array $questions): void
    {
        DB::transaction(function () use ($quiz, $questions): void {
            $questionIds = [];
            $seenQuestionIds = [];

            foreach (array_values($questions) as $questionIndex => $questionData) {
                $questionId = (int) ($questionData['id'] ?? 0);

                if ($questionId > 0 && in_array($questionId, $seenQuestionIds, true)) {
                    throw ValidationException::withMessages([
                        "quiz.questions.{$questionIndex}.id" => 'Один вопрос не может присутствовать в quiz дважды.',
                    ]);
                }

                $query = $quiz->questions();
                $question = $questionId > 0
                    ? $query->whereKey($questionId)->firstOrFail()
                    : $query->make();

                $question->fill([
                    'question_text' => trim((string) ($questionData['question_text'] ?? '')),
                    'sort_order' => $questionIndex,
                ]);

                if (!$question->exists) {
                    $question->quiz()->associate($quiz);
                }

                $question->save();
                $questionIds[] = (int) $question->id;
                $seenQuestionIds[] = (int) $question->id;

                $optionIds = [];
                $seenOptionIds = [];
                foreach (array_values($questionData['options'] ?? []) as $optionIndex => $optionData) {
                    $optionId = (int) ($optionData['id'] ?? 0);

                    if ($optionId > 0 && in_array($optionId, $seenOptionIds, true)) {
                        throw ValidationException::withMessages([
                            "quiz.questions.{$questionIndex}.options.{$optionIndex}.id" => 'Один вариант ответа не может присутствовать дважды.',
                        ]);
                    }

                    $optionQuery = $question->options();
                    $option = $optionId > 0
                        ? $optionQuery->whereKey($optionId)->firstOrFail()
                        : $optionQuery->make();

                    $option->fill([
                        'option_text' => trim((string) ($optionData['option_text'] ?? '')),
                        'is_correct' => (bool) ($optionData['is_correct'] ?? false),
                        'sort_order' => $optionIndex,
                    ]);
                    $option->quizQuestion()->associate($question);
                    $option->save();

                    $optionIds[] = (int) $option->id;
                    $seenOptionIds[] = (int) $option->id;
                }

                $question->options()->whereNotIn('id', $optionIds ?: [0])->delete();
            }

            $quiz->questions()->whereNotIn('id', $questionIds ?: [0])->delete();
        });
    }
}

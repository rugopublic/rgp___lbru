<?php

declare(strict_types=1);

namespace App\Services\Admin\Course;

use App\Models\Quiz;
use App\Models\User;
use App\Repositories\Contracts\CourseStructureRepositoryInterface;
use App\Repositories\Contracts\QuizRepositoryInterface;
use App\Services\Admin\Content\ContentMediaService;
use App\Services\Admin\Content\RichTextMediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class CourseLessonService
{

    /**
     * Initializes the course lesson service instance and wires its required dependencies.
     *
     * @param CourseStructureRepositoryInterface $structure
     * @param ContentMediaService $media
     * @param RichTextMediaService $richText
     * @param QuizRepositoryInterface $quizzes
     * @return void
     */
    public function __construct(
        private readonly CourseStructureRepositoryInterface $structure,
        private readonly ContentMediaService $media,
        private readonly RichTextMediaService $richText,
        private readonly QuizRepositoryInterface $quizzes,
    ) {}

    /** @param array<string,mixed> $data */
    public function update(Model $lesson, User $owner, array $data): void
    {
        $placed = [];

        try {
        if (array_key_exists('video', $data) && $data['video']) {
            $path = $this->media->placeUpload($owner, $lesson, (string) $data['video'], 'video');
            $placed[] = $path;
            $data['video_url'] = basename($path);
        }

        if (($data['video_remove'] ?? false) === true || ($data['video_remove'] ?? null) === '1') {
            $data['video_url'] = null;
        }
        unset($data['video'], $data['video_remove']);

        $quizData = $data['quiz'] ?? null;
        unset($data['quiz']);

        if (array_key_exists('steps', $data) && is_array($data['steps'])) {
            foreach ($data['steps'] as $index => $step) {
                if (array_key_exists('content', $step)) {
                    $resolved = $this->richText->resolvePendingImages($owner, $lesson, $step['content'] ?? null);
                    $data['steps'][$index]['content'] = $resolved['html'];
                    foreach ($resolved['paths'] as $path) {
                        $placed[] = $path;
                    }
                }
            }
        }

            DB::transaction(function () use ($lesson, $data, $quizData): void {
                $this->structure->updateLessonContent($lesson, $data);

                if (!is_array($quizData)) {
                    return;
                }

                $enabled = (bool) ($quizData['enabled'] ?? false);
                $quiz = $this->quizzes->findForLesson($lesson);

                if (!$enabled) {
                    if ($quiz) {
                        $this->quizzes->detachFromLesson($lesson);
                    }
                    return;
                }

                $quizTitle = trim((string) ($quizData['title'] ?? $lesson->title));
                $quiz ??= $this->quizzes->ensureForLesson($lesson, $quizTitle);

                $this->quizzes->update($quiz, [
                    'title' => $quizTitle !== '' ? $quizTitle : $lesson->title,
                    'description' => $quizData['description'] ?? $quiz->description,
                    'is_published' => (bool) ($quizData['is_published'] ?? false),
                ]);

                $this->quizzes->syncEditor(
                    $quiz,
                    is_array($quizData['questions'] ?? null)
                        ? $quizData['questions']
                        : [],
                );
            });
        } catch (\Throwable $e) {
            foreach ($placed as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Course;
use App\Repositories\Contracts\SeoRepositoryInterface;
use App\Services\Admin\Content\RichTextMediaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class CourseObserver
{

    /**
     * Handles the model creating lifecycle event.
     *
     * @param Course $course
     * @return void
     */
    public function creating(Course $course): void
    {
        if (blank($course->public_id)) {
            $course->public_id = Str::random(28);
        }
    }

    /**
     * Handles the model created lifecycle event.
     *
     * @param Course $course
     * @return void
     */
    public function created(Course $course): void
    {
        app(SeoRepositoryInterface::class)->firstOrCreate($course);
    }

    /**
     * Handles the model updated lifecycle event.
     *
     * @param Course $course
     * @return void
     */
    public function updated(Course $course): void
    {
        $coverChanged = $course->wasChanged('cover');
        $richTextFields = ['pitch', 'objective', 'skills'];
        $richTextChanged = array_values(array_filter(
            $richTextFields,
            static fn (string $field): bool => $course->wasChanged($field),
        ));

        if (!$coverChanged && $richTextChanged === []) {
            return;
        }

        $oldCover = (string) $course->getRawOriginal('cover');
        $oldRichText = [];
        $newRichText = [];

        foreach ($richTextFields as $field) {
            $oldRichText[] = (string) $course->getRawOriginal($field);
            $newRichText[] = (string) $course->getAttribute($field);
        }

        DB::afterCommit(function () use ($course, $coverChanged, $oldCover, $oldRichText, $newRichText): void {
            if ($coverChanged && $oldCover !== '') {
                $path = str_contains($oldCover, '/')
                    ? $oldCover
                    : $course->mediaFolder().'/'.$oldCover;

                Storage::disk('public')->delete($path);
            }

            if ($oldRichText === $newRichText) {
                return;
            }

            $media = app(RichTextMediaService::class);
            $oldPaths = [];
            $newPaths = [];

            foreach ($oldRichText as $html) {
                $oldPaths = array_merge($oldPaths, $media->ownedImagePaths($html, $course));
            }

            foreach ($newRichText as $html) {
                $newPaths = array_merge($newPaths, $media->ownedImagePaths($html, $course));
            }

            foreach (array_diff(array_unique($oldPaths), array_unique($newPaths)) as $path) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    /**
     * Handles permanent deletion and its cleanup.
     *
     * @param Course $course
     * @return void
     */
    public function forceDeleted(Course $course): void
    {
        $folder = $course->mediaFolder();

        DB::afterCommit(function () use ($folder): void {
            Storage::disk('public')->deleteDirectory($folder);
        });
    }
}

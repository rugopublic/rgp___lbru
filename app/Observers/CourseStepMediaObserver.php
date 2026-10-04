<?php

declare(strict_types=1);

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class CourseStepMediaObserver
{

    /**
     * Handles the model updated lifecycle event.
     *
     * @param Model $step
     * @return void
     */
    public function updated(Model $step): void
    {
        if (!$step->wasChanged('content')) {
            return;
        }

        $lesson = $step->relationLoaded('lesson') ? $step->lesson : $step->lesson()->first();
        if (!$lesson) {
            return;
        }

        $oldBody = (string) $step->getRawOriginal('content');
        $newBody = (string) $step->getAttribute('content');

        DB::afterCommit(function () use ($lesson, $oldBody, $newBody): void {
            $media = app(\App\Services\Admin\Content\RichTextMediaService::class);
            $oldPaths = $media->ownedImagePaths($oldBody, $lesson);
            $newPaths = $media->ownedImagePaths($newBody, $lesson);

            foreach (array_diff($oldPaths, $newPaths) as $path) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    /**
     * Handles the model deleted lifecycle event.
     *
     * @param Model $step
     * @return void
     */
    public function deleted(Model $step): void
    {
        $lesson = $step->relationLoaded('lesson') ? $step->lesson : $step->lesson()->first();
        if (!$lesson) {
            return;
        }

        $oldBody = (string) $step->getRawOriginal('content');
        if ($oldBody === '') {
            return;
        }

        DB::afterCommit(function () use ($lesson, $oldBody): void {
            $media = app(\App\Services\Admin\Content\RichTextMediaService::class);
            foreach ($media->ownedImagePaths($oldBody, $lesson) as $path) {
                Storage::disk('public')->delete($path);
            }
        });
    }
}

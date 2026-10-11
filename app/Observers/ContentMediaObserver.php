<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\Admin\Content\RichTextMediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ContentMediaObserver
{

    /**
     * Handles the model updated lifecycle event.
     *
     * @param Model $model
     * @return void
     */
    public function updated(Model $model): void
    {
        $bodyChanged = $model->wasChanged('body');
        $fileFields = array_values(array_filter(['cover', 'file', 'video', 'video_url', 'image'], fn (string $field) => $model->wasChanged($field)));
        if (!$bodyChanged && $fileFields === []) return;

        $oldBody = (string) $model->getRawOriginal('body');
        $newBody = (string) $model->getAttribute('body');
        $folder = $model->mediaFolder();

        DB::afterCommit(function () use ($model, $folder, $oldBody, $newBody, $bodyChanged, $fileFields): void {
            $publicDisk = Storage::disk('public');
            $media = app(RichTextMediaService::class);

            if ($bodyChanged) {
                foreach (array_diff($media->ownedImagePaths($oldBody, $model), $media->ownedImagePaths($newBody, $model)) as $path) {
                    $publicDisk->delete($path);
                }
            }

            foreach ($fileFields as $field) {
                $oldValue = (string) $model->getOriginal($field);
                if ($oldValue === '') continue;
                $relative = Str::contains($oldValue, '/') ? $oldValue : $folder.'/'.$oldValue;
                $diskName = method_exists($model, 'mediaDisk') ? $model->mediaDisk($field) : 'public';
                Storage::disk($diskName)->delete($relative);
                // Legacy product files were historically stored on the public disk.
                if ($diskName !== 'public' && method_exists($model, 'mediaDisks')) {
                    $publicDisk->delete($relative);
                }
            }
        });
    }

    /**
     * Handles deletion of models that do not use soft deletes.
     *
     * Soft-deleted content must retain its media. Models such as RecipeStep
     * have no SoftDeletes trait, however, so their deleted event represents a
     * permanent removal and their nested media directory must be cleaned.
     *
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model): void
    {
        if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
            return;
        }

        if (method_exists($model, 'trashed') && $model->trashed()) {
            return;
        }

        if (!method_exists($model, 'mediaFolder')) {
            return;
        }

        $folder = $model->mediaFolder();
        $disks = method_exists($model, 'mediaDisks') ? $model->mediaDisks() : ['public'];

        DB::afterCommit(static function () use ($folder, $disks): void {
            foreach ($disks as $disk) {
                Storage::disk($disk)->deleteDirectory($folder);
            }
        });
    }

    /**
     * Handles permanent deletion and its cleanup.
     *
     * @param Model $model
     * @return void
     */
    public function forceDeleted(Model $model): void
    {
        $folder = $model->mediaFolder();
        $disks = method_exists($model, 'mediaDisks') ? $model->mediaDisks() : ['public'];
        DB::afterCommit(static function () use ($folder, $disks): void {
            foreach ($disks as $disk) {
                Storage::disk($disk)->deleteDirectory($folder);
            }
        });
    }
}

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

        // Capture original file paths before the deferred callback runs. Eloquent
        // synchronizes a model's original attributes after save, so reading
        // getOriginal() inside afterCommit can return the new value and delete
        // the replacement file instead of the file being replaced.
        $oldFiles = [];
        foreach ($fileFields as $field) {
            $oldValue = (string) $model->getRawOriginal($field);
            if ($oldValue === '') continue;

            $diskName = method_exists($model, 'mediaDisk') ? $model->mediaDisk($field) : 'public';
            $oldFiles[] = [
                'path' => Str::contains($oldValue, '/') ? $oldValue : $folder.'/'.$oldValue,
                'disk' => $diskName,
                'delete_legacy_public_path' => $diskName !== 'public' && method_exists($model, 'mediaDisks'),
            ];
        }

        DB::afterCommit(function () use ($model, $oldBody, $newBody, $bodyChanged, $oldFiles): void {
            $publicDisk = Storage::disk('public');
            $media = app(RichTextMediaService::class);

            if ($bodyChanged) {
                foreach (array_diff($media->ownedImagePaths($oldBody, $model), $media->ownedImagePaths($newBody, $model)) as $path) {
                    $publicDisk->delete($path);
                }
            }

            foreach ($oldFiles as $oldFile) {
                Storage::disk($oldFile['disk'])->delete($oldFile['path']);

                // Legacy product files were historically stored on the public disk.
                if ($oldFile['delete_legacy_public_path']) {
                    $publicDisk->delete($oldFile['path']);
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

<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Course\Teacher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class TeacherObserver
{

    /**
     * Handles the model creating lifecycle event.
     *
     * @param Teacher $teacher
     * @return void
     */
    public function creating(Teacher $teacher): void
    {
        if (blank($teacher->public_id)) {
            $teacher->public_id = Str::random(28);
        }
    }

    /**
     * Handles the model updated lifecycle event.
     *
     * @param Teacher $teacher
     * @return void
     */
    public function updated(Teacher $teacher): void
    {
        if (!$teacher->wasChanged('avatar')) {
            return;
        }

        $oldAvatar = (string) $teacher->getRawOriginal('avatar');
        if ($oldAvatar === '') {
            return;
        }

        DB::afterCommit(function () use ($teacher, $oldAvatar): void {
            $path = str_contains($oldAvatar, '/')
                ? $oldAvatar
                : $teacher->mediaFolder().'/'.$oldAvatar;

            Storage::disk($teacher->mediaDisk('avatar'))->delete($path);
        });
    }

    /**
     * Handles permanent deletion and its cleanup.
     *
     * @param Teacher $teacher
     * @return void
     */
    public function forceDeleted(Teacher $teacher): void
    {
        $folder = $teacher->mediaFolder();
        DB::afterCommit(function () use ($folder): void {
            Storage::disk('public')->deleteDirectory($folder);
        });
    }
}

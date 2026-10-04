<?php

declare(strict_types=1);

namespace App\Services\Admin\Course;

use App\Models\Course\Teacher;
use App\Models\User;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use App\Services\Admin\Content\ContentMediaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class TeacherService
{

    /**
     * Initializes the teacher service instance and wires its required dependencies.
     *
     * @param TeacherRepositoryInterface $teachers
     * @param ContentMediaService $media
     * @return void
     */
    public function __construct(
        private readonly TeacherRepositoryInterface $teachers,
        private readonly ContentMediaService $media,
    ) {}

    /**
     * Creates the domain resource and applies the required business orchestration.
     *
     * @param array $attributes
     * @return Teacher
     */
    public function create(array $attributes): Teacher
    {
        return $this->teachers->create($attributes);
    }

    /**
     * Updates the domain resource and applies the required business orchestration.
     *
     * @param Teacher $teacher
     * @param User $owner
     * @param array $data
     * @return void
     */
    public function update(Teacher $teacher, User $owner, array $data): void
    {
        $placed = [];

        if (($data['avatar_remove'] ?? false) === true || ($data['avatar_remove'] ?? null) === '1') {
            $data['avatar'] = null;
        } elseif (!empty($data['avatar'])) {
            $path = $this->media->placeUpload($owner, $teacher, (string) $data['avatar'], 'avatar');
            $placed[] = $path;
            $data['avatar'] = basename($path);
        }

        unset($data['avatar_remove']);

        try {
            DB::transaction(function () use ($teacher, $data): void {
                if (!$this->teachers->update($teacher, $data)) {
                    throw new RuntimeException('Преподаватель не сохранён.');
                }
            });
        } catch (\Throwable $e) {
            foreach ($placed as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }
}

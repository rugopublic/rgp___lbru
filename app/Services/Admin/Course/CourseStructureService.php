<?php

declare(strict_types=1);

namespace App\Services\Admin\Course;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\CourseStructureRepositoryInterface;
use Illuminate\Validation\ValidationException;

final class CourseStructureService
{

    /**
     * Initializes the course structure service instance and wires its required dependencies.
     *
     * @param CourseStructureRepositoryInterface $structure
     * @param CourseRepositoryInterface $courses
     * @return void
     */
    public function __construct(
        private readonly CourseStructureRepositoryInterface $structure,
        private readonly CourseRepositoryInterface $courses,
    ) {
    }

    /** @param array<int,array<string,mixed>> $groups */
    public function persist(Course $course, array $groups): void
    {
        $modules = array_map(
            static function (array $group): array {
                $moduleId = (int) ($group['id'] ?? 0);
                if ($moduleId <= 0) {
                    throw ValidationException::withMessages([
                        'structure' => 'У структуры курса отсутствует корректный идентификатор модуля.',
                    ]);
                }

                return [
                    'id' => $moduleId,
                    'title' => trim((string) ($group['label'] ?? '')),
                    'lessons' => array_map(
                        static fn (array $item): array => ['id' => (int) ($item['value'] ?? 0)],
                        is_array($group['items'] ?? null) ? $group['items'] : [],
                    ),
                ];
            },
            array_values($groups),
        );

        $this->structure->persistStructure($course, $modules);
    }

    /** @param array<int,int> $teacherIds */
    public function syncTeachers(Course $course, array $teacherIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $teacherIds)));
        $this->courses->syncTeachers($course, $ids);
    }
}

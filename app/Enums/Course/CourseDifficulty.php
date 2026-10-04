<?php

namespace App\Enums\Course;

enum CourseDifficulty: string
{
    case Beginner       = 'beginner';
    case Intermediate   = 'intermediate';
    case Advanced       = 'advanced';

    /**
     * Returns the human-readable label for the current enum case.
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Beginner     => 'Для начинающих',
            self::Intermediate => 'Средний уровень',
            self::Advanced     => 'Продвинутый уровень',
        };
    }
}

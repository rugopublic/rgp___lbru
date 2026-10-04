<?php

namespace App\Enums\Material;

enum PublishingStatus: string
{
    case Draft          = 'draft';
    case Scheduled      = 'scheduled';
    case Published      = 'published';

    /**
     * Returns the human-readable label for the current enum case.
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft         => 'Черновик',
            self::Scheduled     => 'Запланировано',
            self::Published     => 'Опубликовано',
        };
    }
}

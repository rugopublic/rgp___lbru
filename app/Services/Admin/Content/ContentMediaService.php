<?php

declare(strict_types=1);

namespace App\Services\Admin\Content;

use App\Models\User;
use App\Services\Admin\Generic\FileService;
use Illuminate\Support\Str;
use RuntimeException;

final class ContentMediaService
{

    /**
     * Initializes the content media service instance and wires its required dependencies.
     *
     * @param FileService $files
     * @return void
     */
    public function __construct(private readonly FileService $files)
    {
    }

    /**
     * Returns the disk for slot value used by the service.
     *
     * @param string $slot
     * @return string
     */
    public function diskForSlot(string $slot): string
    {
        return $slot === 'file' ? (string) config('files.product_disk', 'local') : 'public';
    }

    /**
     * Places upload into permanent storage using the media lifecycle rules.
     *
     * @param User $owner
     * @param object $entity
     * @param string $uploadId
     * @param string $slot
     * @return string
     */
    public function placeUpload(User $owner, object $entity, string $uploadId, string $slot): string
    {
        if (!method_exists($entity, 'mediaFolder')) {
            throw new RuntimeException('Модель не поддерживает media lifecycle.');
        }

        if (!Str::isUuid($uploadId)) {
            throw new RuntimeException('Некорректный идентификатор загрузки.');
        }

        $folder = $entity->mediaFolder();
        if (!$this->files->ready($owner, $uploadId)) {
            throw new RuntimeException('Загрузка ещё не готова к размещению.');
        }

        $allowed = match ($slot) {
            'file' => array_fill_keys(config('media.allowed_document_types', []), true),
            'video' => ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'm4v' => 'video/x-m4v', 'mov' => 'video/quicktime'],
            'avatar', 'cover', 'body', 'step_image' => ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'],
            default => null,
        };
        $extension = strtolower((string) $this->files->extension($owner, $uploadId, true));
        $mime = (string) $this->files->mimeType($owner, $uploadId);
        if ($slot === 'file') {
            if (!isset($allowed[$mime])) {
                throw new RuntimeException('Тип файла товара не разрешён.');
            }
            $max = (int) config('media.document_max_size', 500 * 1024 * 1024);
            if (($this->files->size($owner, $uploadId) ?? 0) > $max) {
                throw new RuntimeException('Файл товара превышает максимально допустимый размер.');
            }
        } elseif (is_array($allowed) && (!isset($allowed[$extension]) || $mime !== $allowed[$extension])) {
            throw new RuntimeException('Тип файла не разрешён для этого поля.');
        }

        $name = Str::random(24);
        $targetFolder = match ($slot) {
            'body' => $folder.'/body',
            'step_image' => $folder.'/steps/'.$entity->getKey(),
            default => $folder,
        };
        $disk = $this->diskForSlot($slot);
        $result = $this->files->place($owner, $uploadId, $targetFolder, $name, true, $disk);

        if (!$result['success'] || !$result['extension']) {
            throw new RuntimeException("Не удалось разместить файл для {$slot}.");
        }

        return $targetFolder.'/'.$name.'.'.$result['extension'];
    }
}

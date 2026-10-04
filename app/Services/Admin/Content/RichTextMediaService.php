<?php

declare(strict_types=1);

namespace App\Services\Admin\Content;

use App\Models\User;
use App\Services\Admin\Generic\FileService;
use Illuminate\Support\Str;
use RuntimeException;

final class RichTextMediaService
{

    /**
     * Initializes the rich text media service instance and wires its required dependencies.
     *
     * @param FileService $files
     * @return void
     */
    public function __construct(private readonly FileService $files)
    {
    }

    /**
     * Resolves temporary upload IDs embedded by the existing RichTextarea/FormSender
     * and replaces them with permanent public URLs. Tables are intentionally unsupported.
     */
    /** @return array{html:?string,paths:list<string>} */
    public function resolvePendingImages(User $owner, object $entity, ?string $html): array
    {
        if ($html === null || trim($html) === '') return ['html'=>$html,'paths'=>[]];
        $placed = [];

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $wrapper = '<!DOCTYPE html><html><body><div id="__rta_root">'.$html.'</div></body></html>';
        $dom->loadHTML('<?xml encoding="UTF-8" ?>'.$wrapper, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('__rta_root');
        if (!$root) return ['html'=>$html,'paths'=>[]];

        foreach (iterator_to_array($root->getElementsByTagName('img')) as $image) {
            /** @var \DOMElement $image */
            $uploadId = $image->getAttribute('data-upload-id');
            if ($uploadId === '') continue;
            if (!Str::isUuid($uploadId)) {
                throw new RuntimeException('Некорректный идентификатор inline-изображения.');
            }

            if (!$this->files->ready($owner, $uploadId)) {
                throw new RuntimeException('Inline-изображение ещё не готово к размещению.');
            }
            $extension = strtolower((string) $this->files->extension($owner, $uploadId, true));
            $mime = (string) $this->files->mimeType($owner, $uploadId);
            $size = (int) ($this->files->size($owner, $uploadId) ?? 0);
            $maxSize = (int) config('media.image_max_size', 5 * 1024 * 1024);
            if ($size <= 0 || $size > $maxSize) {
                throw new RuntimeException('Inline-изображение превышает допустимый размер.');
            }
            $allowed = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif',
            ];
            if (!isset($allowed[$extension]) || $mime !== $allowed[$extension]) {
                throw new RuntimeException('Inline-файл должен быть изображением JPG, PNG, WEBP или GIF.');
            }

            $name = Str::random(24);
            $targetFolder = $this->mediaFolder($entity).'/body';
            $result = $this->files->place($owner, $uploadId, $targetFolder, $name, true, 'public');
            if (!$result['success'] || !$result['extension']) {
                throw new RuntimeException('Не удалось сохранить inline-изображение.');
            }

            $path = $targetFolder.'/'.$name.'.'.$result['extension'];
            $placed[] = $path;
            $image->removeAttribute('data-upload-id');
            $image->setAttribute('src', \Storage::disk('public')->url($path));
        }

        return ['html'=>$this->innerHtml($root, $dom),'paths'=>$placed];
    }

    /**
     * Places pending images into permanent storage using the media lifecycle rules.
     *
     * @param User $owner
     * @param object $entity
     * @param ?string $html
     * @return ?string
     */
    public function placePendingImages(User $owner, object $entity, ?string $html): ?string
    {
        return $this->resolvePendingImages($owner, $entity, $html)['html'];
    }

    /** @return list<string> */
    public function ownedImagePaths(?string $html, object $entity): array
    {
        if (!$html) return [];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8" ?><div id="__rta_root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $dom->getElementById('__rta_root');
        if (!$root) return [];
        $prefix = '/storage/'.$this->mediaFolder($entity).'/body/';
        $paths = [];
        foreach ($root->getElementsByTagName('img') as $image) {
            $src = $image->getAttribute('src');
            $path = (string) (parse_url($src, PHP_URL_PATH) ?: $src);
            if (Str::startsWith($path, $prefix)) $paths[] = Str::after($path, '/storage/');
        }
        return array_values(array_unique($paths));
    }

    /**
     * Returns the media folder value used by the service.
     *
     * @param object $entity
     * @return string
     */
    private function mediaFolder(object $entity): string
    {
        if (!method_exists($entity, 'mediaFolder')) throw new RuntimeException('Модель не поддерживает media lifecycle.');
        return $entity->mediaFolder();
    }

    /**
     * Returns the inner html value used by the service.
     *
     * @param \DOMElement $root
     * @param \DOMDocument $dom
     * @return string
     */
    private function innerHtml(\DOMElement $root, \DOMDocument $dom): string
    {
        $html = '';
        foreach ($root->childNodes as $child) $html .= $dom->saveHTML($child);
        return $html;
    }
}

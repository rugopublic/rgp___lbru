<?php

namespace App\View\Components\Ui\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

class MediaDropzone extends Component
{
    /**
     * Default aspect ratio for image cropping.
     */
    public const DEFAULT_RATIO = '1x1';

    /**
     * Maximum allowed image file size in bytes.
     */
    public int $imageMaxSize;

    /**
     * Maximum allowed video file size in bytes.
     */
    public int $videoMaxSize;

    /**
     * Width of the generated image in pixels.
     */
    public int $imageWidth;

    /**
     * Height of the generated image in pixels.
     */
    public int $imageHeight;

    /**
     * Quality of the generated image (0–1).
     */
    public float $imageQuality;

    /**
     * URL of an existing file to display in the dropzone.
     */
    public ?string $existingUrl;

    /**
     * Create a new component instance.
     *
     * The component accepts a subset of the globally allowed MIME types
     * defined in config/media.php.
     *
     * @param string $name             Input name attribute.
     * @param string $ratio            Aspect ratio for cropping (e.g., "1x1", "16x9").
     * @param array<int, string>|null $accept        Allowed MIME types or extensions.
     * @param mixed $old               Old input value (for form repopulation).
     * @param string|null $existingUrl URL of an existing file to display.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $name,
        public string $ratio = self::DEFAULT_RATIO,
        public ?array $accept = null,
        public mixed $old = null,
        ?string $existingUrl = null,
    ) {
        $allowedTypes = array_merge(
            config('media.allowed_image_types', []),
            config('media.allowed_video_types', []),
        );

        $this->accept ??= $allowedTypes;

        $unsupportedTypes = array_diff($this->accept, $allowedTypes);

        if ($unsupportedTypes !== []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported MIME type(s) passed to MediaDropzone: %s.',
                    implode(', ', $unsupportedTypes),
                ),
            );
        }

        $this->imageMaxSize = (int) config('media.image_max_size', 0);
        $this->videoMaxSize = (int) config('media.video_max_size', 0);

        $this->imageWidth = (int) config('media.image_width', 0);
        $this->imageHeight = (int) config('media.image_height', 0);
        $this->imageQuality = (float) config('media.image_quality', 0.9);

        // Если передан existingUrl, сохраняем его.
        // Это позволяет отображать уже загруженный файл без повторной загрузки.
        $this->existingUrl = $existingUrl;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.media-dropzone');
    }
}

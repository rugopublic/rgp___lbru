<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image
    |--------------------------------------------------------------------------
    |
    | Internal settings used when processing uploaded images.
    |
    */

    /**
     * Maximum allowed image file size in bytes.
     *
     * @var int
     */
    'image_max_size' => (int) env('MEDIA_IMAGE_MAX_SIZE', 5 * 1024 * 1024),

    /**
     * Width of the generated image in pixels.
     *
     * @var int
     */
    'image_width' => (int) env('MEDIA_IMAGE_WIDTH', 720),

    /**
     * Height of the generated image in pixels.
     *
     * @var int
     */
    'image_height' => (int) env('MEDIA_IMAGE_HEIGHT', 720),

    /**
     * Image quality used when generating the output image.
     *
     * The value is expected to be in the range from 0.0 to 1.0.
     *
     * @var float
     */
    'image_quality' => (float) env('MEDIA_IMAGE_QUALITY', 0.9),

    /*
    |--------------------------------------------------------------------------
    | Video
    |--------------------------------------------------------------------------
    |
    | Internal settings used when validating uploaded videos.
    |
    */

    /**
     * Maximum allowed video file size in bytes.
     *
     * @var int
     */
    'video_max_size' => (int) env('MEDIA_VIDEO_MAX_SIZE', 200 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Types
    |--------------------------------------------------------------------------
    |
    | MIME types accepted by the media upload component.
    |
    */

    /**
     * Allowed MIME types for image uploads.
     *
     * @var array<int, string>
     */
    'allowed_image_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ],

    /**
     * Allowed MIME types for video uploads.
     *
     * @var array<int, string>
     */
    'allowed_video_types' => [
        'video/mp4',
        'video/webm',
        'video/quicktime',
        'video/x-m4v',
    ],

    // после allowed_video_types

    'document_max_size' => (int) env('MEDIA_DOCUMENT_MAX_SIZE', 500 * 1024 * 1024),

    'allowed_document_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.presentation',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/json',
        'audio/mpeg',
        'audio/wav',
    ],
];

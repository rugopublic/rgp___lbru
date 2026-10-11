<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maximum filesize for uploads
    |--------------------------------------------------------------------------
    |
    | Specified in kilobytes.
    |
    | Defaults to 20 MB (20480 Kb)
    |
    */

    'max' => '20480',

    /*
    |--------------------------------------------------------------------------
    | Temporary path for files.
    |--------------------------------------------------------------------------
    |
    | This is the name of the path located at storage/app/private/
    |
    | Defaults to 'temp'.
    |
    */

    // Upload and cleanup must share the same storage disk and root.
    'temp_disk' => env('TEMP_UPLOAD_DISK', env('FILESYSTEM_DISK', 'local')),
    'temp_folder' => env('FILE_TEMP_FOLDER', env('TEMP_UPLOAD_PATH', 'temp')),

    // Backwards-compatible alias for integrations that still read temp_path.
    'temp_path' => env('FILE_TEMP_FOLDER', env('TEMP_UPLOAD_PATH', 'temp')),

    'product_disk' => env('PRODUCT_FILE_DISK', 'local'),
];

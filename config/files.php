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

    'temp_folder' => env('FILE_TEMP_FOLDER', 'temp'),

    'temp_path' => env('TEMP_UPLOAD_PATH', 'temp'),

    'product_disk' => env('PRODUCT_FILE_DISK', 'local'),
];

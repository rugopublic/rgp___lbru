<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chunked upload safety limits
    |--------------------------------------------------------------------------
    |
    | Each accepted chunk request triggers a merge-readiness check. Bound the
    | number of parts so a client cannot force unbounded loop work per request.
    |
    */
    'max_chunks' => (int) env('FILE_UPLOAD_MAX_CHUNKS', 1024),
];

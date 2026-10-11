<?php

declare(strict_types=1);

it('normalizes cloud cleanup paths before prefixing the temp root', function (): void {
    $source = file_get_contents(base_path('app/Console/Commands/CleanTempUploads.php'));

    expect($source)
        ->toContain("$"."basePrefix = rtrim($path, '/') . '/';")
        ->toContain("str_starts_with($item['path'], $basePrefix)")
        ->toContain("substr($item['path'], strlen($basePrefix))")
        ->toContain("$"."disk->deleteDirectory($dir)")
        ->not->toContain('@todo Implement actual directory deletion');
});

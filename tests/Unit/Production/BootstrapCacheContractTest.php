<?php

declare(strict_types=1);

it('keeps the Laravel bootstrap cache directory in a clean checkout', function (): void {
    expect(is_dir(base_path('bootstrap/cache')))->toBeTrue();
    expect(is_file(base_path('bootstrap/cache/.gitignore')))->toBeTrue();
    expect(is_writable(base_path('bootstrap/cache')))->toBeTrue();
});

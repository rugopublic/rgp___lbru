<?php

declare(strict_types=1);

it('ships the environment template required by setup and deployment instructions', function (): void {
    $path = base_path('.env.example');

    expect(is_file($path))->toBeTrue();

    $environment = file_get_contents($path);
    expect($environment)
        ->toContain('APP_KEY=')
        ->toContain('DB_CONNECTION=')
        ->toContain('PRODUCT_FILE_DISK=')
        ->toContain('YOOKASSA_SHOP_ID=')
        ->toContain('YOOKASSA_SECRET_KEY=');
});

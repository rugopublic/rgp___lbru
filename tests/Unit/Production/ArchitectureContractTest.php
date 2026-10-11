<?php

declare(strict_types=1);

    /**
     * Handles project files for the architecture contract test.
     *
     * @param string $root
     * @param string $extension
     * @return array
     */
    function projectFiles(string $root, string $extension): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === $extension) {
            $files[] = $file->getPathname();
        }
    }

    sort($files);
    return $files;
}

it('keeps all Blade views below resources/web', function () {
    $legacyViews = is_dir(base_path('resources/views'))
        ? projectFiles(base_path('resources/views'), 'php')
        : [];

    expect($legacyViews)->toBe([]);

    expect(projectFiles(base_path('resources/web'), 'php'))
        ->not->toBeEmpty();
});

it('keeps application services inside the repository boundary', function () {
    foreach (projectFiles(base_path('app/Services'), 'php') as $file) {
        $source = file_get_contents($file);
        expect($source)->not->toMatch('/\$[A-Za-z_][A-Za-z0-9_]*->(save|create|update|delete|forceDelete|restore)\(/');
        expect($source)->not->toMatch('/\b[A-Za-z_][A-Za-z0-9_]*::query\s*\(/');
    }
});

it('keeps admin controllers thin and request-driven', function () {
    $controllers = projectFiles(base_path('app/Http/Controllers/Admin'), 'php');

    foreach ($controllers as $file) {
        $source = file_get_contents($file);
        expect($source)->not->toContain('$this->validate(');
        expect($source)->not->toMatch('/\bvalidator\s*\(/');
        expect($source)->not->toMatch('/\b(DB|Model)::(query|where|find|create|update|delete|insert|upsert)\s*\(/');
    }
});

it('keeps rich textarea free of the abandoned table feature', function () {
    $paths = [
        base_path('resources/web/components/ui/form/rich-textarea.blade.php'),
        base_path('resources/scripts/components/ui/form/rich-textarea.js'),
    ];

    foreach ($paths as $file) {
        $source = file_get_contents($file);
        expect($source)->not->toMatch('/insertTable|table-button|data-rich-text-table/');
    }
});

it('protects the immutable custom form components with the checked-in hash baseline', function () {
    $manifest = base_path('qa/custom-ui.sha256');
    expect(is_file($manifest))->toBeTrue();

    foreach (file($manifest, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        [$hash, $relative] = preg_split('/\s+/', trim($line), 2);
        $relative = ltrim($relative, '*');
        $path = base_path($relative);
        expect(is_file($path))->toBeTrue("Missing protected UI component: {$relative}");
        expect(hash_file('sha256', $path))->toBe($hash, "Protected UI component changed: {$relative}");
    }
});


it('keeps Vitest free of Vue-specific runtime configuration', function () {
    $vitest = file_get_contents(base_path('vitest.config.js'));
    expect($vitest)->not->toContain("@vitejs/plugin-vue");
    expect($vitest)->not->toContain("plugin-vue");
});

it('keeps the canonical database baseline compatible with RichTextarea', function () {
    $migrations = glob(base_path('database/migrations/*.php'));
    expect($migrations)->toHaveCount(1);

    $migration = file_get_contents($migrations[0]);
    $request = file_get_contents(base_path('app/Http/Requests/Admin/Form/Course/UpdateRequest.php'));
    $model = file_get_contents(base_path('app/Models/Course.php'));

    expect($migration)
        ->toContain("\$table->text('objective')->nullable();")
        ->toContain("\$table->text('skills')->nullable();")
        ->not->toContain("'courses_old'")
        ->not->toContain("Schema::create('seo_data'");
    expect($request)->toContain("'objective'=>['sometimes','nullable','string','max:10000']");
    expect($request)->toContain("'skills'=>['sometimes','nullable','string','max:10000']");
    expect($model)->toMatch('/\'objective\'\s*=>\s*CleanHtmlInput::class/');
    expect($model)->toMatch('/\'skills\'\s*=>\s*CleanHtmlInput::class/');
});

it('does not duplicate the lesson quiz editor in the sidebar', function () {
    $aside = file_get_contents(base_path('resources/web/admin/partials/lessons/aside.blade.php'));
    $main = file_get_contents(base_path('resources/web/admin/partials/lessons/main.blade.php'));

    expect($aside)->toContain('lesson-quiz-select');
    expect($aside)->not->toContain('data-quiz-question-add');
    expect($main)->toContain('data-quiz-question-add');
});

it('cleans RichTextarea images when a course step is deleted', function () {
    $observer = file_get_contents(base_path('app/Observers/CourseStepMediaObserver.php'));
    expect($observer)->toContain('function deleted(Model $step)');
    expect($observer)->toContain('ownedImagePaths($oldBody, $lesson)');
    expect($observer)->toContain("Storage::disk('public')->delete(\$path)");
});

it('uses native JavaScript entrypoints without Vue or Tailwind Vite plugins', function () {
    $vite = file_get_contents(base_path('vite.config.js'));

    expect($vite)->not->toContain("@vitejs/plugin-vue");
    expect($vite)->not->toContain("@tailwindcss/vite");
    expect($vite)->not->toContain('vue()');
    expect($vite)->not->toContain('tailwindcss()');
});

it('contains no live reference to the excluded legacy campaign recipient job', function () {
    foreach (['app', 'routes', 'resources', 'database', 'tests'] as $directory) {
        $root = base_path($directory);
        foreach (projectFiles($root, 'php') as $file) {
            expect(file_get_contents($file))->not->toContain('EmailCampaign'.'Recipient');
        }
    }
});


it('keeps public commerce links on content surfaces', function () {
    $partial = file_get_contents(base_path('resources/web/frontend/components/related-products.blade.php'));
    $article = file_get_contents(base_path('resources/web/frontend/catalog/article.blade.php'));
    $recipe = file_get_contents(base_path('resources/web/frontend/catalog/recipe.blade.php'));
    $ingredient = file_get_contents(base_path('resources/web/frontend/catalog/ingredient.blade.php'));
    $product = file_get_contents(base_path('resources/web/frontend/catalog/product.blade.php'));

    expect($partial)->toContain("route('products.show'");
    expect($article)->toContain("frontend.components.related-products");
    expect($recipe)->toContain("frontend.components.related-products");
    expect($ingredient)->toContain("frontend.components.related-products");
    expect($product)->toContain("route('products.buy'")->toContain("route('products.download'");
});

it('keeps YooKassa webhook named and protected by provider status re-fetch', function () {
    $routes = file_get_contents(base_path('routes/web/public/payments.php'));
    $controller = file_get_contents(base_path('app/Http/Controllers/Public/PaymentController.php'));
    $client = file_get_contents(base_path('app/Services/Public/YooKassaClient.php'));

    expect($routes)->toContain("->name('payments.webhook')");
    expect($controller)->toContain('$this->yookassa->getPayment($paymentId)');
    expect($controller)->toContain('IpUtils::checkIp');
    expect($client)->toContain("'Idempotence-Key' => \$key");
});

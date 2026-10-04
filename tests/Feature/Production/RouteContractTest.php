<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('exposes the critical production route contract', function () {
    $routes = [
        'admin.dashboard',
        'admin.calendar.index',
        'admin.courses.index',
        'admin.courses.edit',
        'admin.courses.lessons.edit',
        'admin.courses.lessons.update',
        'admin.courses.restore',
        'admin.users.restore',
        'admin.groups.restore',
        'admin.products.index',
        'admin.products.edit',
        'admin.articles.index',
        'admin.recipes.index',
        'admin.reference.index',
        'admin.users.index',
        'admin.groups.index',
        'admin.teachers.index',
        'payments.pay',
        'payments.result',
        'courses.show',
        'products.show',
        'courses.buy',
        'products.buy',
    ];

    foreach ($routes as $name) {
        expect(Route::has($name))->toBeTrue("Missing route: {$name}");
    }
});

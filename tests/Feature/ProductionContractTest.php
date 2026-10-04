<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('production routes have named actions', function () {
    expect(Route::has('admin.dashboard'))->toBeTrue();
    expect(Route::has('admin.courses.index'))->toBeTrue();
    expect(Route::has('admin.products.index'))->toBeTrue();
    expect(Route::has('admin.calendar.index'))->toBeTrue();
    expect(Route::has('payments.pay'))->toBeTrue();
    expect(Route::has('payments.result'))->toBeTrue();
});

<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests opt into RefreshDatabase explicitly through this shared
| configuration. Unit/architecture tests stay database-free by default.
|
*/

pest()->extend(Tests\TestCase::class)->in('Feature');
pest()->extend(Tests\TestCase::class)->in('Unit');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

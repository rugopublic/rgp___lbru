<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{

    /**
     * Seeds the configured database records for this environment.
     * @return void
     */
    public function run(): void
    {
        if ((bool) env('SEED_ADMIN', false)) {
            $this->call(AdminUserSeeder::class);
        }
    }
}

<?php

declare(strict_types=1);

it('uses MySQL-safe names for polymorphic content uniqueness indexes', function (): void {
    $migration = file_get_contents(base_path('database/migrations/0001_01_01_000000_create_likablog_schema.php'));

    expect($migration)
        ->toContain("'content_likes_owner_target_unique'")
        ->toContain("'content_bookmarks_owner_target_unique'");
});

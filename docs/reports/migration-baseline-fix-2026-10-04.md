# Migration baseline fix — 2026-10-04

## Laravel foreign key/index chaining

Do not chain `->index()` after `->constrained(...)` on a `foreignId`/`foreignUuid`/`foreignUlid` definition.

Bad:

```php
$table->foreignId('reference_item_id')
    ->nullable()
    ->constrained('ingredients')
    ->nullOnDelete()
    ->index();
```

The `index()` call can mutate the foreign-key definition so Laravel emits a constraint named `1`.

Correct:

```php
$table->foreignId('reference_item_id')
    ->index()
    ->nullable()
    ->constrained('ingredients')
    ->nullOnDelete();
```

This baseline uses the corrected form for all affected foreign keys.

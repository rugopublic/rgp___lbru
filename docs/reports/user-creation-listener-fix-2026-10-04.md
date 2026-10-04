# User creation listener fix — 2026-10-04

## Failure

`AdminUserSeeder` failed while creating its administrator because `App\Listeners\User\PrepareCreation::handle()` declared two parameters:

- the dispatched `UserCreating` event;
- `UserRepositoryInterface`, which Laravel does not inject into the listener `handle()` method.

The event dispatcher therefore called the listener with one argument and raised `ArgumentCountError`.

## Fix

`UserRepositoryInterface` is now injected through `PrepareCreation::__construct()`. The event handler signature is intentionally limited to the dispatched event:

```php
public function handle(UserCreating $event): void
```

This matches the listener lifecycle used by Laravel: the container resolves the listener instance and its constructor dependencies, while the event dispatcher supplies the event object to `handle()`.

## Regression coverage

`tests/Feature/User/UserCreationTest.php` creates a real `User` model and asserts that creation completes and produces a non-empty `public_id`. This exercises the Eloquent `creating` event and `PrepareCreation` listener instead of merely instantiating the listener in isolation.

## Verification

- `php -l app/Listeners/User/PrepareCreation.php` — PASS
- `php -l tests/Feature/User/UserCreationTest.php` — PASS
- listener audit: every `app/Listeners/*` `handle()` method now accepts exactly one event parameter — PASS

A full Laravel integration test requires the project's local Composer dependencies and database, so it must be run in the user's dev environment with:

```powershell
php artisan test tests/Feature/User/UserCreationTest.php
php artisan db:seed --class=AdminUserSeeder
```

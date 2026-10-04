<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminUserSeeder extends Seeder
{

    /**
     * Seeds the configured database records for this environment.
     *
     * @param UserRepositoryInterface $users
     * @return void
     */
    public function run(UserRepositoryInterface $users): void
    {
        $email = mb_strtolower(trim((string) env('ADMIN_EMAIL', 'admin@likablog.ru')));
        $password = (string) env('ADMIN_PASSWORD', '');
        $name = trim((string) env('ADMIN_NAME', 'Администратор'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('ADMIN_EMAIL must contain a valid email address.');
        }

        if (mb_strlen($password) < 12) {
            throw new \RuntimeException('ADMIN_PASSWORD must be set and contain at least 12 characters.');
        }

        $user = $users->findByEmail($email, true);

        if ($user?->trashed()) {
            $users->restore($user);
        }

        if (! $user) {
            $user = $users->create([
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]);
        } else {
            $users->update($user, [
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => $user->email_verified_at ?: now(),
                'password_changed_at' => now(),
            ]);
        }

        if ($name !== '') {
            $users->updateAccount($user, ['name' => $name]);
        }

        $this->command?->info("Administrator ready: {$user->email} (ID {$user->id}).");
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

final class CreateAdminUser extends Command
{
    protected $signature = 'likablog:admin
                            {email : Administrator email address}
                            {--password= : Password; omit to enter it securely}
                            {--name= : Optional display name}';

    protected $description = 'Create or promote the primary LikaBlog administrator account.';

    /**
     * Executes the Artisan command and its associated side effects.
     *
     * @param UserRepositoryInterface $users
     * @return int
     */
    public function handle(UserRepositoryInterface $users): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $password = (string) ($this->option('password') ?: $this->secret('Password'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');
            return self::INVALID;
        }

        if (mb_strlen($password) < 12) {
            $this->error('Password must contain at least 12 characters.');
            return self::INVALID;
        }

        $user = $users->findByEmail($email, true);
        if ($user?->trashed()) {
            $users->restore($user);
        }
        if (!$user) {
            $user = $users->create(['email'=>$email,'password'=>Hash::make($password),'is_admin'=>true,'email_verified_at'=>now(),'password_changed_at'=>now()]);
        } else {
            $users->update($user, ['email'=>$email,'password'=>Hash::make($password),'is_admin'=>true,'email_verified_at'=>$user->email_verified_at ?: now(),'password_changed_at'=>now()]);
        }


        $name = trim((string) ($this->option('name') ?? ''));
        if ($name !== '') {
            $users->updateAccount($user, ['name' => $name]);
        }

        $this->info("Administrator ready: {$user->email} (ID {$user->id}).");
        return self::SUCCESS;
    }
}

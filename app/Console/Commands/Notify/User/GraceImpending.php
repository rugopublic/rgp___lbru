<?php

namespace App\Console\Commands\Notify\User;

use Illuminate\Console\Command;
use App\Events\User\DeleteGraceImpending;
use App\Repositories\Contracts\UserRepositoryInterface;

class GraceImpending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:user:grace-impending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emits the DeleteGraceImpending event for soft-deleted users'
                            .'which are going to be permanently deleted soon.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(UserRepositoryInterface $users): int
    {
        $this->info('Emitting DeleteGraceImpending events...');

        $matchedUsers = $users->deletionGraceImpending()
            ->each(fn ($user) => DeleteGraceImpending::dispatch($user));

        $this->info("{$matchedUsers->count()} events emitted.");

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\OperationalRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class OperationalRepository implements OperationalRepositoryInterface
{

    /**
     * Queries or persists failed jobs count for the operational repository repository.
     * @return int
     */
    public function failedJobsCount(): int
    {
        return (int) DB::table('failed_jobs')->count();
    }

    /**
     * Queries or persists latest failed jobs for the operational repository repository.
     *
     * @param int $limit
     * @return Collection
     */
    public function latestFailedJobs(int $limit = 5): Collection
    {
        return DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit($limit)
            ->get(['id', 'uuid', 'connection', 'queue', 'exception', 'failed_at']);
    }
}

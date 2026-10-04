<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface OperationalRepositoryInterface
{

    /**
     * Queries or persists failed jobs count for the operational repository interface repository.
     * @return int
     */
    public function failedJobsCount(): int;

    /**
     * Queries or persists latest failed jobs for the operational repository interface repository.
     *
     * @param int $limit
     * @return Collection
     */
    public function latestFailedJobs(int $limit = 5): Collection;
}

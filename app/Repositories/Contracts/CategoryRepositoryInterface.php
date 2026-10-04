<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{

    /**
     * Returns lightweight selector options from the repository.
     *
     * @param ?string $query
     * @param ?string $id
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query = null, ?string $id = null, int $limit = 20): Collection;
}

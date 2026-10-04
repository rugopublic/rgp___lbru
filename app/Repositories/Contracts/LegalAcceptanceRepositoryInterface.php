<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\LegalAcceptance;

interface LegalAcceptanceRepositoryInterface
{

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return LegalAcceptance
     */
    public function create(array $attributes): LegalAcceptance;
}

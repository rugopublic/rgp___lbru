<?php

declare(strict_types=1);
namespace App\Repositories\Eloquent;
use App\Models\LegalAcceptance;
use App\Repositories\Contracts\LegalAcceptanceRepositoryInterface;
final class LegalAcceptanceRepository implements LegalAcceptanceRepositoryInterface
{

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return LegalAcceptance
     */
    public function create(array $attributes): LegalAcceptance { return LegalAcceptance::create($attributes); }
}

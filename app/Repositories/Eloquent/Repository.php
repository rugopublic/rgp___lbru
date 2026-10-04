<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class Repository
{

    /**
     * Queries or persists apply text filter for the repository repository.
     *
     * @param Builder $query
     * @param ?string $search
     * @param array $columns
     * @return void
     */
    protected function applyTextFilter(Builder $query, ?string $search, array $columns): void
    {
        $search = trim((string) $search);
        if ($search === '') return;
        $query->where(function (Builder $query) use ($search, $columns): void {
            foreach ($columns as $i => $column) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $query->{$method}($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Queries or persists option collection for the repository repository.
     *
     * @param string $modelClass
     * @param ?string $query
     * @param ?string $id
     * @param int $limit
     * @param string $label
     * @param string $value
     * @return Collection
     */
    protected function optionCollection(string $modelClass, ?string $query, ?string $id, int $limit, string $label, string $value = 'public_id'): Collection
    {
        $builder = $modelClass::query();
        if ($id !== null && $id !== '') {
            if ($value === 'public_id' && ctype_digit((string) $id)) {
                $builder->whereKey((int) $id);
            } else {
                $builder->where($value, $id);
            }
        }
        elseif ($query !== null && trim($query) !== '') $builder->where($label, 'like', '%'.trim($query).'%');
        return $builder->orderBy($label)->limit($limit)->get()->map(fn(Model $item) => [
            'value' => (string) $item->getKey(),
            'label' => (string) $item->{$label},
            'public_id' => $item->public_id ?? null,
        ]);
    }
}

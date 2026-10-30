<?php

namespace App\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Whitelist: filter name => 'exact' | 'partial' | Closure(Builder, mixed): void
     * Override in the model. Only names listed here can ever reach a query.
     */
    public function filterableFields(): array
    {
        return [];
    }

    /** Columns searched by ?search= (a match in any one column counts). Override in the model. */
    public function searchableFields(): array
    {
        return [];
    }

    public function scopeFilter(Builder $query, array $filters = [], ?string $search = null): Builder
    {
        foreach ($this->filterableFields() as $field => $mode) {
            $value = $filters[$field] ?? null;

            if (! is_scalar($value) || $value === '') {
                continue;
            }

            // Column names come from the whitelist above; user input only ever
            // travels as a bound parameter (PDO prepared statement).
            match (true) {
                $mode instanceof Closure => $mode($query, $value),
                $mode === 'partial' => $query->where($field, 'like', "%{$value}%"),
                default => $query->where($field, $value),
            };
        }

        if (filled($search) && $this->searchableFields()) {
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->searchableFields() as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        return $query;
    }
}
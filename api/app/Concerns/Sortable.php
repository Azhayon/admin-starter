<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    /** Columns a client may sort by. Override in the model. */
    public function sortableFields(): array
    {
        return [];
    }

    /** Used when ?sort is missing or has no valid column. Leading "-" = descending. */
    public function defaultSort(): string
    {
        return '-id';
    }

    public function scopeSort(Builder $query, ?string $sort = null): Builder
    {
        $allowed = $this->sortableFields();
        $order = [];

        foreach (explode(',', (string) $sort) as $part) {
            $part = trim($part);
            $column = ltrim($part, '-');

            if ($column !== '' && in_array($column, $allowed, true)) {
                $order[$column] = str_starts_with($part, '-') ? 'desc' : 'asc';
            }
        }

        if ($order === []) {
            $default = $this->defaultSort();
            $order[ltrim($default, '-')] = str_starts_with($default, '-') ? 'desc' : 'asc';
        }

        // Tie-breaker: sorting by a non-unique column without one can repeat or skip rows across pages.
        $order[$this->getKeyName()] ??= 'asc';

        foreach ($order as $column => $direction) {
            $query->orderBy($column, $direction);
        }

        return $query;
    }
}

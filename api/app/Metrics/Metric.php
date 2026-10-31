<?php

namespace App\Metrics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Metric
{
    /** Permission needed to read this metric (checked through the Gate, like every other endpoint). */
    abstract public function permission(): string;

    /** Return a stat, series or distribution payload (see the three shapes in docs). */
    abstract public function resolve(Request $request): array;

    /** ?days=N, clamped to 1..90 so nobody can request a ten-year scan. */
    protected function days(Request $request, int $default): int
    {
        return max(1, min($request->integer('days', $default), 90));
    }

    /** Rows created in the last $days days, and in the $days before that ("vs previous period"). */
    protected function countInWindows(Builder $query, int $days, string $column = 'created_at'): array
    {
        $start = now()->subDays($days);
        $previousStart = now()->subDays($days * 2);

        return [
            'value' => (clone $query)->where($column, '>=', $start)->count(),
            'previous' => (clone $query)->where($column, '>=', $previousStart)->where($column, '<', $start)->count(),
        ];
    }
}
<?php

namespace App\Metrics;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditActivity extends Metric
{
    public function permission(): string
    {
        return 'audit-logs.view';
    }

    public function resolve(Request $request): array
    {
        $days = $this->days($request, 14);
        $start = now()->subDays($days - 1)->startOfDay();

        // One grouped query. The raw fragment is a constant string: no user input ever reaches it.
        $counts = AuditLog::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day'); // ['2026-10-30' => 4, ...]

        // The database only returns days that HAVE rows, but a chart needs every day, so fill the gaps with 0.
        $points = collect(range(0, $days - 1))->map(function (int $i) use ($start, $counts) {
            $date = $start->copy()->addDays($i)->toDateString();

            return ['date' => $date, 'value' => (int) ($counts[$date] ?? 0)];
        });

        return ['points' => $points->all()];
    }
}
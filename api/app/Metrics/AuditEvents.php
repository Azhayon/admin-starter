<?php

namespace App\Metrics;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditEvents extends Metric
{
    public function permission(): string
    {
        return 'audit-logs.view';
    }

    public function resolve(Request $request): array
    {
        return $this->countInWindows(AuditLog::query(), $this->days($request, 7));
    }
}
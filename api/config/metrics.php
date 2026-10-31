<?php

use App\Metrics;

return [
    // name used in the URL => Metric class
    'registry' => [
        'total-users' => Metrics\TotalUsers::class,
        'new-users' => Metrics\NewUsers::class,
        'users-by-role' => Metrics\UsersByRole::class,
        'audit-events' => Metrics\AuditEvents::class,
        'audit-activity' => Metrics\AuditActivity::class,
    ],
];

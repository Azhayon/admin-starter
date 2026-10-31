<?php

return [
    // Entries older than this are deleted by the daily `model:prune` job.
    'retention_days' => (int) env('AUDIT_RETENTION_DAYS', 365),
];

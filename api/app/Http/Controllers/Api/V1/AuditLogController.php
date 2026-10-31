<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;

class AuditLogController extends ResourceController
{
    protected string $model = AuditLog::class;

    protected string $resource = AuditLogResource::class;

    protected string $permission = 'audit-logs';

    protected array $with = ['user:id,name']; // eager load just what the resource prints
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\PermissionResource;
use App\Models\Permission;

class PermissionController extends ResourceController
{
    protected string $model = Permission::class;

    protected string $resource = PermissionResource::class;

    protected string $permission = 'roles'; // viewing the permission catalog is part of managing roles
}

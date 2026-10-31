<?php

use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MetricController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/me', MeController::class);
    Route::get('metrics/{name}', [MetricController::class, 'show']);

    Route::apiResources([
        'users' => UserController::class,
        'roles' => RoleController::class,
    ]);

    Route::apiResource('permissions', PermissionController::class)->only('index');

    Route::apiResource('audit-logs', AuditLogController::class)->only(['index', 'show']);
});

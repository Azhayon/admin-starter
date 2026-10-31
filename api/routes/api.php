<?php

use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuditLogController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/me', MeController::class);

    Route::apiResources([
        'users' => UserController::class,
        'roles' => RoleController::class,
    ]);

    Route::apiResource('permissions', PermissionController::class)->only('index');

    Route::apiResource('audit-logs', AuditLogController::class)->only(['index', 'show']);
});
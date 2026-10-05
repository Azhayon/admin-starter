<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Every API route lives under /api/v1/... (Laravel adds the /api prefix).
// Versioning in the URL means a breaking change later becomes /v2, not a surprise.
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Smoke-test route; becomes a UserResource with roles + permissions in the RBAC step.
    Route::get('/me', fn (Request $request) => $request->user());
});
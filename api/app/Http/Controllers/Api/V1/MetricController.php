<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Metrics\Metric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MetricController extends Controller
{
    public function show(Request $request, string $name): JsonResponse
    {
        $class = config('metrics.registry')[$name] ?? null;
        abort_if($class === null, 404, 'Unknown metric.');

        /** @var Metric $metric */
        $metric = app($class);
        Gate::authorize($metric->permission());

        return response()->json(['data' => $metric->resolve($request)]);
    }
}

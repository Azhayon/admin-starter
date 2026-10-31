<?php

namespace App\Metrics;

use App\Models\User;
use Illuminate\Http\Request;

class TotalUsers extends Metric
{
    public function permission(): string
    {
        return 'users.view';
    }

    public function resolve(Request $request): array
    {
        return ['value' => User::count()];
    }
}

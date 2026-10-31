<?php

namespace App\Metrics;

use App\Models\User;
use Illuminate\Http\Request;

class NewUsers extends Metric
{
    public function permission(): string
    {
        return 'users.view';
    }

    public function resolve(Request $request): array
    {
        return $this->countInWindows(User::query(), $this->days($request, 30));
    }
}

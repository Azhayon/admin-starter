<?php

namespace App\Metrics;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UsersByRole extends Metric
{
    public function permission(): string
    {
        return 'users.view';
    }

    public function resolve(Request $request): array
    {
        // withCount adds one COUNT subquery per role: one query total, however many users exist.
        $items = Role::query()
            ->withCount('users')
            ->orderByDesc('users_count')
            ->orderBy('label')
            ->get()
            ->map(fn (Role $role) => ['label' => $role->label, 'value' => $role->users_count]);

        $unassigned = User::doesntHave('roles')->count();
        if ($unassigned > 0) {
            $items->push(['label' => 'No role', 'value' => $unassigned]);
        }

        return ['items' => $items->values()->all()];
    }
}
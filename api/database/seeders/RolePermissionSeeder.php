<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class RolePermissionSeeder extends Seeder
{
    // Idempotent: safe to run on every deploy. It adds and syncs, never duplicates.
    public function run(): void
    {
        $config = config('rbac');

        foreach ($config['permissions'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $ids = Permission::pluck('id', 'name'); // ['users.view' => 1, ...]

        foreach ($config['roles'] as $name => $definition) {
            $role = Role::updateOrCreate(['name' => $name], ['label' => $definition['label']]);

            $wanted = $definition['permissions'] === '*'
                ? $ids->keys()
                : collect($definition['permissions']);

            $unknown = $wanted->diff($ids->keys());
            if ($unknown->isNotEmpty()) {
                throw new InvalidArgumentException(
                    "Role '$name' references unknown permissions: " . $unknown->implode(', ')
                );
            }

            $role->permissions()->sync($ids->only($wanted->all())->values());
        }
    }
}
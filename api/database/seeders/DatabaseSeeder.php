<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // Dev-only logins with a known password. Never seed these outside local.
        if (app()->environment('local')) {
            foreach (['admin', 'manager', 'viewer'] as $role) {
                $user = User::firstOrCreate(
                    ['email' => "$role@example.com"],
                    ['name' => ucfirst($role), 'password' => 'password']
                );
                $user->roles()->syncWithoutDetaching(Role::where('name', $role)->firstOrFail());
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Dev-only login. Never seed known credentials outside local.
        if (app()->environment('local')) {
            User::firstOrCreate(
                ['email' => 'admin@example.com'],
                ['name' => 'Admin', 'password' => 'password'] // the model's 'hashed' cast hashes it
            );
        }
    }
}
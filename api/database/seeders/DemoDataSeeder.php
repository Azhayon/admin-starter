<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->error('DemoDataSeeder only runs in the local environment.');

            return;
        }

        $roleIds = Role::pluck('id');

        // 30 users signed up at random times in the last 45 days; about half have a role.
        User::factory()->count(30)
            ->create(['created_at' => fn () => now()->subDays(rand(0, 45))])
            ->each(fn (User $user) => rand(0, 1) ? $user->roles()->attach($roleIds->random()) : null);

        // Two weeks of fake activity so the trend chart has a shape.
        foreach (range(0, 13) as $daysAgo) {
            foreach (range(1, rand(2, 9)) as $_) {
                AuditLog::create([
                    'event' => collect(['created', 'updated', 'updated', 'deleted'])->random(),
                    'auditable_type' => 'user',
                    'auditable_id' => 1,
                    'auditable_label' => 'demo@example.com',
                    'created_at' => now()->subDays($daysAgo)->setTime(rand(8, 18), rand(0, 59)),
                ]);
            }
        }
    }
}

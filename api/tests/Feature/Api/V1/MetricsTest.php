<?php

namespace Tests\Feature\Api\V1;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function actingAsRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $role)->firstOrFail());
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_total_users(): void
    {
        User::factory()->count(3)->create();
        $this->actingAsRole('viewer'); // +1 = 4

        $this->getJson('/api/v1/metrics/total-users')->assertOk()->assertJsonPath('data.value', 4);
    }

    public function test_new_users_compares_two_windows(): void
    {
        User::factory()->create(['created_at' => now()->subDays(40)]); // previous 30-day window
        User::factory()->count(2)->create();                           // current window
        $this->actingAsRole('viewer');                                 // current window too

        $this->getJson('/api/v1/metrics/new-users')
            ->assertOk()
            ->assertJsonPath('data.value', 3)
            ->assertJsonPath('data.previous', 1);
    }

    public function test_users_by_role_counts_every_role_and_unassigned_users(): void
    {
        $this->actingAsRole('viewer');
        User::factory()->create(); // no role

        $items = collect($this->getJson('/api/v1/metrics/users-by-role')->assertOk()->json('data.items'))
            ->pluck('value', 'label');

        $this->assertSame(1, $items['Viewer']);
        $this->assertSame(1, $items['No role']);
        $this->assertSame(0, $items['Administrator']);
    }

    public function test_audit_activity_has_one_point_per_day(): void
    {
        $this->actingAsRole('admin'); // creating the admin wrote audit rows dated today

        $points = $this->getJson('/api/v1/metrics/audit-activity?days=7')->assertOk()->json('data.points');

        $this->assertCount(7, $points);
        $this->assertGreaterThan(0, end($points)['value']);
    }

    public function test_metrics_respect_permissions_and_unknown_names(): void
    {
        $this->actingAsRole('viewer'); // has users.view, not audit-logs.view

        $this->getJson('/api/v1/metrics/audit-events')->assertForbidden();
        $this->getJson('/api/v1/metrics/total-users')->assertOk();
        $this->getJson('/api/v1/metrics/nope')->assertNotFound();
    }
}

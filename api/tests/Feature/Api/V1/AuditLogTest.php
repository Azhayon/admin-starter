<?php

namespace Tests\Feature\Api\V1;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use LogicException;
use Tests\TestCase;

class AuditLogTest extends TestCase
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

    public function test_creating_a_user_is_audited_without_the_password(): void
    {
        $admin = $this->actingAsRole('admin');

        $this->postJson('/api/v1/users', [
            'name' => 'New Person', 'email' => 'new@example.com', 'password' => 'password123',
        ])->assertCreated();

        $log = AuditLog::where('event', 'created')->where('auditable_label', 'new@example.com')->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('user', $log->auditable_type);
        $this->assertSame('New Person', $log->new_values['name']);
        $this->assertArrayNotHasKey('password', $log->new_values);
    }

    public function test_updates_store_only_the_changed_fields(): void
    {
        $this->actingAsRole('admin');
        $target = User::factory()->create(['name' => 'Before']);

        $this->patchJson("/api/v1/users/{$target->id}", ['name' => 'After'])->assertOk();

        $log = AuditLog::where('event', 'updated')->where('auditable_id', $target->id)->firstOrFail();
        $this->assertSame(['name' => 'Before'], $log->old_values);
        $this->assertSame(['name' => 'After'], $log->new_values);
    }

    public function test_role_assignment_is_audited(): void
    {
        $this->actingAsRole('admin');
        $target = User::factory()->create();

        $this->patchJson("/api/v1/users/{$target->id}", ['roles' => ['viewer']])->assertOk();

        $log = AuditLog::where('event', 'roles_changed')->where('auditable_id', $target->id)->firstOrFail();
        $this->assertSame(['roles' => []], $log->old_values);
        $this->assertSame(['roles' => ['viewer']], $log->new_values);
    }

    public function test_only_admins_can_read_the_log(): void
    {
        $this->actingAsRole('viewer');
        $this->getJson('/api/v1/audit-logs')->assertForbidden();
    }

    public function test_admin_can_list_the_log(): void
    {
        $this->actingAsRole('admin');

        $this->getJson('/api/v1/audit-logs')
            ->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['id', 'event', 'auditable_type', 'user', 'created_at']], 'meta']);
    }

    public function test_entries_cannot_be_modified(): void
    {
        User::factory()->create(); // writes a "created" entry
        $log = AuditLog::firstOrFail();

        $this->expectException(LogicException::class);
        $log->update(['event' => 'tampered']);
    }

    public function test_old_entries_are_pruned(): void
    {
        config(['audit.retention_days' => 30]);

        $old = AuditLog::create([
            'event' => 'updated', 'auditable_type' => 'user', 'auditable_id' => 1, 'created_at' => now()->subDays(40),
        ]);

        $this->artisan('model:prune', ['--model' => AuditLog::class])->assertSuccessful();

        $this->assertDatabaseMissing('audit_logs', ['id' => $old->id]);
    }
}

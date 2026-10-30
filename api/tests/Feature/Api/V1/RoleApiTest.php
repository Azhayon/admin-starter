<?php

namespace Tests\Feature\Api\V1;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->firstOrFail());
        Sanctum::actingAs($admin);
    }

    public function test_admin_can_create_a_role_with_permissions(): void
    {
        $this->postJson('/api/v1/roles', [
            'name' => 'auditor', 'label' => 'Auditor', 'permissions' => ['users.view'],
        ])->assertCreated()->assertJsonPath('data.permissions', ['users.view']);
    }

    public function test_protected_role_cannot_be_deleted(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $this->deleteJson("/api/v1/roles/{$adminRole->id}")->assertUnprocessable();
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
    }
}
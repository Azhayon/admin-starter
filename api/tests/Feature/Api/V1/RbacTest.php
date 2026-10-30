<?php

namespace Tests\Feature\Api\V1;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        // A throwaway route that requires a permission, to test the middleware.
        Route::middleware(['auth:sanctum', 'can:users.delete'])->get('/_guarded', fn () => 'ok');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $role)->firstOrFail());

        return $user;
    }

    public function test_permissions_come_from_roles(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->assertTrue($viewer->can('users.view'));
        $this->assertFalse($viewer->can('users.delete'));
        $this->assertTrue($this->userWithRole('admin')->can('roles.delete'));
    }

    public function test_me_returns_roles_and_permissions(): void
    {
        Sanctum::actingAs($this->userWithRole('viewer'));

        $response = $this->getJson('/api/v1/me')->assertOk();

        $this->assertSame(['viewer'], $response->json('data.roles'));
        $this->assertEqualsCanonicalizing(['users.view', 'roles.view'], $response->json('data.permissions'));
    }

    public function test_can_middleware_blocks_users_without_the_permission(): void
    {
        Sanctum::actingAs($this->userWithRole('viewer'));
        $this->getJson('/_guarded')->assertForbidden();
    }

    public function test_can_middleware_allows_users_with_the_permission(): void
    {
        Sanctum::actingAs($this->userWithRole('admin'));
        $this->getJson('/_guarded')->assertOk();
    }
}
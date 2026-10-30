<?php

namespace Tests\Feature\Api\V1;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
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

    public function test_list_is_paginated(): void
    {
        User::factory()->count(5)->create();
        $this->actingAsRole('viewer');

        $this->getJson('/api/v1/users?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 6);
    }

    public function test_list_can_be_filtered_and_sorted(): void
    {
        User::factory()->create(['name' => 'Zed Tester']);
        User::factory()->create(['name' => 'Amy Tester']);
        $this->actingAsRole('viewer');

        $response = $this->getJson('/api/v1/users?filter[name]=Tester&sort=name')->assertOk();

        $this->assertSame(['Amy Tester', 'Zed Tester'], collect($response->json('data'))->pluck('name')->all());
    }

    public function test_list_can_be_filtered_by_role(): void
    {
        $manager = User::factory()->create();
        $manager->roles()->attach(Role::where('name', 'manager')->firstOrFail());
        $this->actingAsRole('viewer');

        $this->getJson('/api/v1/users?filter[role]=manager')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $manager->id);
    }

    public function test_unknown_sort_columns_are_ignored(): void
    {
        $this->actingAsRole('viewer');

        $this->getJson('/api/v1/users?sort=password')->assertOk(); // not whitelisted: silently ignored, no SQL error
    }

    public function test_viewer_cannot_create_users(): void
    {
        $this->actingAsRole('viewer');

        $this->postJson('/api/v1/users', [
            'name' => 'X', 'email' => 'x@example.com', 'password' => 'password123',
        ])->assertForbidden();
    }

    public function test_manager_can_create_a_user_with_roles(): void
    {
        $this->actingAsRole('manager');

        $this->postJson('/api/v1/users', [
            'name' => 'New Person', 'email' => 'new@example.com',
            'password' => 'password123', 'roles' => ['viewer'],
        ])->assertCreated()->assertJsonPath('data.roles', ['viewer']);

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_invalid_input_returns_422_with_field_errors(): void
    {
        $this->actingAsRole('manager');

        $this->postJson('/api/v1/users', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_manager_can_update_but_not_delete(): void
    {
        $target = User::factory()->create();
        $this->actingAsRole('manager');

        $this->patchJson("/api/v1/users/{$target->id}", ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->deleteJson("/api/v1/users/{$target->id}")->assertForbidden();
    }

    public function test_admin_can_delete_others_but_not_themselves(): void
    {
        $target = User::factory()->create();
        $admin = $this->actingAsRole('admin');

        $this->deleteJson("/api/v1/users/{$admin->id}")->assertUnprocessable();
        $this->deleteJson("/api/v1/users/{$target->id}")->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }
}
<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_administrator(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->artisan('admin:create', ['email' => 'boss@example.com', 'name' => 'Boss'])
            ->expectsQuestion('Password (min 8 characters)', 'a-strong-password')
            ->assertSuccessful();

        $this->assertTrue(User::where('email', 'boss@example.com')->firstOrFail()->hasRole('admin'));
    }

    public function test_it_rejects_a_weak_password(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->artisan('admin:create', ['email' => 'boss@example.com'])
            ->expectsQuestion('Password (min 8 characters)', 'short')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'boss@example.com']);
    }
}

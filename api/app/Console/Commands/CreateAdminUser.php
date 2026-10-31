<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email : Login email} {name=Admin : Display name}';

    protected $description = 'Create an administrator (or reset the password of an existing user and make them admin)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->secret('Password (min 8 characters)'); // typed input is hidden

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => ['required', 'email'], 'password' => ['required', Password::defaults()]],
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $role = Role::where('name', 'admin')->first();
        if (! $role) {
            $this->error('The admin role does not exist. Run: php artisan db:seed --class=RolePermissionSeeder');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(['email' => $email], ['name' => $this->argument('name'), 'password' => $password]);
        $user->auditRelation('roles', 'roles_changed', fn () => $user->roles()->syncWithoutDetaching($role));

        $this->info("Administrator {$email} is ready.");

        return self::SUCCESS;
    }
}

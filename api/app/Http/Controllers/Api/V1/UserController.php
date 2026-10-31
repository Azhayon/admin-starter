<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class UserController extends ResourceController
{
    protected string $model = User::class;

    protected string $resource = UserResource::class;

    protected string $permission = 'users';

    protected string $storeRequest = StoreUserRequest::class;

    protected string $updateRequest = UpdateUserRequest::class;

    // UserResource prints roles + permissions: eager load them so a list of 50 users is 3 queries, not 100+.
    protected array $with = ['roles.permissions'];

    protected function createRecord(array $data): Model
    {
        $user = User::create(Arr::except($data, ['roles']));
        $this->syncRoles($user, $data);

        return $user;
    }

    protected function updateRecord(Model $record, array $data): Model
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']); // empty password on edit = "keep the current one"
        }

        $record->update(Arr::except($data, ['roles']));
        $this->syncRoles($record, $data);

        return $record;
    }

    protected function deleteRecord(Model $record): void
    {
        abort_if($record->is(request()->user()), 422, 'You cannot delete your own account.');

        parent::deleteRecord($record);
    }

    private function syncRoles(User $user, array $data): void
    {
        if (! array_key_exists('roles', $data)) {
            return;
        }

        // Assigning roles is its own permission, so users.update alone is never a path to becoming admin.
        Gate::authorize('users.assign-roles');
        abort_if($user->is(request()->user()), 422, 'You cannot change your own roles.');

        $user->auditRelation('roles', 'roles_changed', fn () => $user->roles()->sync(
            Role::whereIn('name', $data['roles'])->pluck('id')
        ));
    }
}

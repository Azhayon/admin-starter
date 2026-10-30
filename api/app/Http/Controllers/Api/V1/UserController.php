<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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
        if (array_key_exists('roles', $data)) {
            $user->roles()->sync(Role::whereIn('name', $data['roles'])->pluck('id'));
        }
    }
}
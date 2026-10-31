<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class RoleController extends ResourceController
{
    protected string $model = Role::class;
    protected string $resource = RoleResource::class;
    protected string $permission = 'roles';
    protected string $storeRequest = StoreRoleRequest::class;
    protected string $updateRequest = UpdateRoleRequest::class;
    protected array $with = ['permissions'];
    protected array $withCount = ['users'];

    protected function createRecord(array $data): Model
    {
        $role = Role::create(Arr::only($data, ['name', 'label']));
        $this->syncPermissions($role, $data);

        return $role;
    }

    protected function updateRecord(Model $record, array $data): Model
    {
        $this->assertNotProtected($record);

        $record->update(Arr::only($data, ['label']));
        $this->syncPermissions($record, $data);

        return $record;
    }

    protected function deleteRecord(Model $record): void
    {
        $this->assertNotProtected($record);

        parent::deleteRecord($record);
    }

    private function assertNotProtected(Role $role): void
    {
        abort_if(
            in_array($role->name, config('rbac.protected_roles', []), true),
            422,
            "The '{$role->name}' role is protected and cannot be changed."
        );
    }

    private function syncPermissions(Role $role, array $data): void
    {
        if (array_key_exists('permissions', $data)) {
            $role->auditRelation('permissions', 'permissions_changed', fn () => $role->permissions()->sync(
                Permission::whereIn('name', $data['permissions'])->pluck('id')
            ));
        }
    }
}
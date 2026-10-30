<?php

return [
    // Add new permissions here, then run: php artisan db:seed --class=RolePermissionSeeder
    'permissions' => [
        'users.view', 'users.create', 'users.update', 'users.delete',
        'roles.view', 'roles.create', 'roles.update', 'roles.delete',
    ],

    'roles' => [
        'admin'   => ['label' => 'Administrator', 'permissions' => '*'], // '*' = every permission
        'manager' => ['label' => 'Manager', 'permissions' => ['users.view', 'users.create', 'users.update']],
        'viewer'  => ['label' => 'Viewer', 'permissions' => ['users.view', 'roles.view']],
    ],

    // Roles that can't be edited or deleted through the API.
    'protected_roles' => ['admin'],
];
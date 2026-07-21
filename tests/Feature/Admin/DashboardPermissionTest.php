<?php

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('usuários autenticados sem dashboard.view não acessam o painel', function () {
    $user = User::factory()->withoutRoles()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('usuários com apenas dashboard.sidebar não acessam o painel', function () {
    $role = Role::create(['name' => 'DashboardMenuOnly', 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::findByName(PermissionEnum::DashboardSidebar->value, 'web'),
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('usuários com dashboard.view podem acessar o painel', function () {
    $role = Role::create(['name' => 'DashboardViewer', 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::findByName(PermissionEnum::DashboardView->value, 'web'),
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

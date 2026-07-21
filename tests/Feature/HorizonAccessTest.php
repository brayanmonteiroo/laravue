<?php

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('administradores autenticados acessam o horizon', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get('/horizon')
        ->assertOk();
});

test('usuários com horizon.view acessam o horizon', function () {
    $role = Role::create(['name' => 'HorizonViewer', 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::findByName(PermissionEnum::HorizonView->value, 'web'),
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/horizon')
        ->assertOk();
});

test('usuários autenticados sem horizon.view não acessam o horizon', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/horizon')
        ->assertForbidden();
});

test('visitantes não acessam o horizon', function () {
    $this->get('/horizon')
        ->assertRedirect(route('login'));
});

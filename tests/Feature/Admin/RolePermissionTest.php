<?php

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('usuários com permissions.view sem permissions.update não sincronizam permissões', function () {
    $role = Role::create(['name' => 'PermViewer', 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::findByName(PermissionEnum::PermissionsView->value, 'web'),
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $targetRole = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $this->actingAs($user)
        ->get(route('admin.roles.permissions.edit', $targetRole))
        ->assertOk();

    $this->actingAs($user)
        ->put(route('admin.roles.permissions.update', $targetRole), [
            'permissions' => [
                PermissionEnum::DashboardSidebar->value,
            ],
        ])
        ->assertForbidden();
});

test('administrador pode visualizar e sincronizar permissões de um perfil', function () {
    $admin = User::factory()->administrator()->create();
    $targetRole = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $this->actingAs($admin)
        ->get(route('admin.roles.permissions.edit', $targetRole))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/roles/Permissions')
            ->where('role.name', 'Editor')
            ->has('permissionGroups', 3)
            ->has('role.permissions')
        );

    $this->actingAs($admin)
        ->put(route('admin.roles.permissions.update', $targetRole), [
            'permissions' => [
                PermissionEnum::DashboardSidebar->value,
                PermissionEnum::UsersView->value,
            ],
        ])
        ->assertRedirect(route('admin.roles.permissions.edit', $targetRole))
        ->assertSessionHas('flash.type', 'success')
        ->assertSessionHas(
            'flash.message',
            __('Role permissions updated successfully.'),
        );

    expect($targetRole->fresh())
        ->hasPermissionTo(PermissionEnum::UsersView)
        ->toBeTrue();
});

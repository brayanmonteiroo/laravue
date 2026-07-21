<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('usuários com permissions.view sem permissions.update não sincronizam permissões', function () {
    $role = Role::create(['name' => 'PermViewer', 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::findByName(RolePermissionSeeder::PERMISSION_PERMISSIONS_VIEW, 'web'),
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
                RolePermissionSeeder::PERMISSION_DASHBOARD_SIDEBAR,
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
            ->has('permissionGroups', 2)
            ->has('role.permissions')
        );

    $this->actingAs($admin)
        ->put(route('admin.roles.permissions.update', $targetRole), [
            'permissions' => [
                RolePermissionSeeder::PERMISSION_DASHBOARD_SIDEBAR,
                RolePermissionSeeder::PERMISSION_USERS_VIEW,
            ],
        ])
        ->assertRedirect(route('admin.roles.permissions.edit', $targetRole))
        ->assertSessionHas('flash.type', 'success')
        ->assertSessionHas(
            'flash.message',
            'Permissões do perfil atualizadas com sucesso.',
        );

    expect($targetRole->fresh())
        ->hasPermissionTo(RolePermissionSeeder::PERMISSION_USERS_VIEW)
        ->toBeTrue();
});

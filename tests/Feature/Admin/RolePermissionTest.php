<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

test('visitantes não acessam a tela de permissões', function () {
    $this->get(route('admin.permissions.index'))
        ->assertRedirect(route('login'));
});

test('usuários sem permissions.manage não sincronizam permissões dos perfis', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.permissions.index'))
        ->assertForbidden();
});

test('administrador pode visualizar e sincronizar permissões dos perfis', function () {
    $admin = User::factory()->administrator()->create();

    $userRole = Role::findByName(RolePermissionSeeder::ROLE_USER, 'web');

    $this->actingAs($admin)
        ->get(route('admin.permissions.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->put(route('admin.permissions.update'), [
            'role_id' => $userRole->id,
            'permissions' => [
                RolePermissionSeeder::PERMISSION_DASHBOARD,
                RolePermissionSeeder::PERMISSION_USERS_MANAGE,
            ],
        ])
        ->assertRedirect(route('admin.permissions.index'))
        ->assertSessionHas('flash.type', 'success')
        ->assertSessionHas(
            'flash.message',
            'Permissões do perfil atualizadas com sucesso.',
        );

    expect($userRole->fresh())
        ->hasPermissionTo(RolePermissionSeeder::PERMISSION_USERS_MANAGE)
        ->toBeTrue();
});

test('administrador pode criar um novo perfil', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('admin.permissions.roles.store'), [
            'name' => 'Editor',
        ])
        ->assertRedirect(route('admin.permissions.index'))
        ->assertSessionHas('flash.type', 'success')
        ->assertSessionHas('flash.message', 'Perfil criado com sucesso.');

    expect(Role::findByName('Editor', 'web'))->not->toBeNull();
});

test('criar perfil com nome duplicado falha na validação', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('admin.permissions.roles.store'), [
            'name' => RolePermissionSeeder::ROLE_USER,
        ])
        ->assertSessionHasErrors('name');
});

<?php

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as AppRole;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('visitantes não acessam a lista de perfis', function () {
    $this->get(route('admin.roles.index'))
        ->assertRedirect(route('login'));
});

test('usuários sem permissions.view não acessam a lista de perfis', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.roles.index'))
        ->assertForbidden();
});

test('administrador pode listar perfis', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/roles/Index')
            ->has('roles')
            ->where('roles.0.name', AppRole::Administrator->value)
            ->where('roles.0.is_system', true)
            ->where('roles.0.can_rename', false)
            ->where('roles.0.can_delete', false)
        );
});

test('rota antiga de permissões redireciona para lista de perfis', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('admin.permissions.index'))
        ->assertRedirect('/admin/roles');
});

test('administrador pode criar um novo perfil', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('admin.roles.store'), [
            'name' => 'Editor',
        ])
        ->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('flash.type', 'success')
        ->assertSessionHas('flash.message', __('Role created successfully.'));

    expect(Role::findByName('Editor', 'web'))->not->toBeNull();
});

test('usuários com permissions.view sem permissions.create não criam perfil', function () {
    $role = Role::create(['name' => 'PermViewerOnly', 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::findByName(PermissionEnum::PermissionsView->value, 'web'),
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->post(route('admin.roles.store'), [
            'name' => 'NovoPerfil',
        ])
        ->assertForbidden();
});

test('criar perfil com nome duplicado falha na validação', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('admin.roles.store'), [
            'name' => AppRole::Administrator->value,
        ])
        ->assertSessionHasErrors('name');
});

test('administrador pode renomear perfil customizado', function () {
    $admin = User::factory()->administrator()->create();
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $this->actingAs($admin)
        ->put(route('admin.roles.update', $role), [
            'name' => 'Publisher',
        ])
        ->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('flash.type', 'success');

    expect($role->fresh()->name)->toBe('Publisher');
});

test('não é possível renomear perfil de sistema', function () {
    $admin = User::factory()->administrator()->create();
    $role = Role::findByName(AppRole::Administrator->value, 'web');

    $this->actingAs($admin)
        ->put(route('admin.roles.update', $role), [
            'name' => 'RenamedAdmin',
        ])
        ->assertRedirect()
        ->assertSessionHas('flash.type', 'error');

    expect($role->fresh()->name)->toBe(AppRole::Administrator->value);
});

test('administrador pode excluir perfil customizado sem usuários', function () {
    $admin = User::factory()->administrator()->create();
    $role = Role::create(['name' => 'Temporary', 'guard_name' => 'web']);

    $this->actingAs($admin)
        ->delete(route('admin.roles.destroy', $role))
        ->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('flash.type', 'success');

    expect(Role::query()->where('name', 'Temporary')->exists())->toBeFalse();
});

test('não é possível excluir perfil de sistema', function () {
    $admin = User::factory()->administrator()->create();
    $role = Role::findByName(AppRole::Administrator->value, 'web');

    $this->actingAs($admin)
        ->delete(route('admin.roles.destroy', $role))
        ->assertRedirect()
        ->assertSessionHas('flash.type', 'error');

    expect(Role::findByName(AppRole::Administrator->value, 'web'))->not->toBeNull();
});

test('perfil customizado sem usuários pode ser excluído', function () {
    $admin = User::factory()->administrator()->create();
    Role::create(['name' => 'Deletable', 'guard_name' => 'web']);

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where(
                'roles',
                fn ($roles) => collect($roles)->contains(
                    fn ($role) => $role['name'] === 'Deletable'
                        && $role['can_delete'] === true
                        && $role['users_count'] === 0,
                ),
            )
        );
});

test('não é possível excluir perfil com usuários vinculados', function () {
    $admin = User::factory()->administrator()->create();
    $role = Role::create(['name' => 'BusyRole', 'guard_name' => 'web']);
    $member = User::factory()->withoutRoles()->create();
    $member->assignRole($role);

    $this->actingAs($admin)
        ->delete(route('admin.roles.destroy', $role))
        ->assertRedirect()
        ->assertSessionHas('flash.type', 'error');

    expect(Role::query()->where('name', 'BusyRole')->exists())->toBeTrue();
});

test('usuários com apenas permissions.sidebar não acessam a lista de perfis', function () {
    $role = Role::create(['name' => 'PermMenuOnly', 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::findByName(PermissionEnum::PermissionsSidebar->value, 'web'),
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('admin.roles.index'))
        ->assertForbidden();
});

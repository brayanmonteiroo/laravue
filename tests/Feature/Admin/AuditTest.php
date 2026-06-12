<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Role;

test('visitantes não acessam a tela de auditoria', function () {
    $this->get(route('admin.audits.index'))
        ->assertRedirect(route('login'));
});

test('usuários sem audits.view não acessam a tela de auditoria', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.audits.index'))
        ->assertForbidden();
});

test('administrador visualiza as últimas auditorias traduzidas', function () {
    $admin = User::factory()->administrator()->create();
    $target = User::factory()->create(['name' => 'Maria Silva']);

    $target->update(['name' => 'Maria Santos']);

    $this->actingAs($admin)
        ->get(route('admin.audits.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/audits/Index')
            ->has('audits')
            ->where('audits.0.actor', fn ($value) => is_string($value))
            ->where('audits.0.action', fn ($value) => is_string($value))
            ->where('audits.0.subject', fn ($value) => is_string($value))
            ->where('audits.0.details', fn ($value) => is_string($value))
        );
});

test('sincronizar permissões de perfil gera registro de auditoria', function () {
    $admin = User::factory()->administrator()->create();
    $userRole = Role::findByName(RolePermissionSeeder::ROLE_USER, 'web');

    $beforeCount = Audit::query()->count();

    $this->actingAs($admin)
        ->put(route('admin.permissions.update'), [
            'role_id' => $userRole->id,
            'permissions' => [
                RolePermissionSeeder::PERMISSION_DASHBOARD,
                RolePermissionSeeder::PERMISSION_USERS_MANAGE,
            ],
        ])
        ->assertRedirect(route('admin.permissions.index'));

    expect(Audit::query()->count())->toBeGreaterThan($beforeCount);

    $audit = Audit::query()
        ->where('auditable_type', Role::class)
        ->where('auditable_id', $userRole->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->old_values)->toHaveKey('permissions');
    expect($audit->new_values)->toHaveKey('permissions');
});

test('atribuir papéis a usuário gera registro de auditoria', function () {
    $admin = User::factory()->administrator()->create();
    $user = User::factory()->withoutRoles()->create();

    $beforeCount = Audit::query()->count();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [RolePermissionSeeder::ROLE_USER],
        ])
        ->assertRedirect(route('admin.users.index'));

    expect(Audit::query()->count())->toBeGreaterThan($beforeCount);

    $audit = Audit::query()
        ->where('auditable_type', User::class)
        ->where('auditable_id', $user->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->new_values['roles'] ?? null)->toBe([RolePermissionSeeder::ROLE_USER]);
});

test('tela de permissões exibe grupos alinhados ao menu lateral', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('admin.permissions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/permissions/Index')
            ->has('permissionGroups', 2)
            ->where('permissionGroups.0.label', 'Menu')
            ->where('permissionGroups.1.label', 'Configurações')
        );
});

test('apresentador traduz alteração de permissões de perfil', function () {
    $role = Role::findByName('User', 'web');

    $audit = Audit::query()->create([
        'user_type' => User::class,
        'user_id' => User::factory()->create()->id,
        'event' => 'updated',
        'auditable_type' => Role::class,
        'auditable_id' => $role->id,
        'old_values' => [
            'role_name' => 'User',
            'permissions' => ['dashboard.access'],
        ],
        'new_values' => [
            'role_name' => 'User',
            'permissions' => ['dashboard.access', 'users.manage'],
        ],
    ]);

    $audit->load('user:id,name');

    $presented = app(\App\Support\AuditPresenter::class)->present($audit);

    expect($presented['action'])->toBe('Atualização');
    expect($presented['subject'])->toBe('Perfil · Usuário');
    expect($presented['details'])->toContain('Gerenciar usuários');
});

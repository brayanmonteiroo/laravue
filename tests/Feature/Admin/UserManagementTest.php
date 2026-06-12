<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('visitantes não acessam a gestão de usuários', function () {
    $this->get(route('admin.users.index'))
        ->assertRedirect(route('login'));
});

test('usuários sem users.manage não acessam a gestão de usuários', function () {
    $user = User::factory()->withoutRoles()->create();

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('usuários com users.manage podem listar usuários', function () {
    $user = User::factory()->administrator()->create();

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertOk();
});

test('listagem de usuários usa 10 itens por página e expõe estado de ordenação', function () {
    $admin = User::factory()->administrator()->create();

    User::factory()->count(12)->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('users.per_page', 10)
            ->where('sort.column', 'name')
            ->where('sort.direction', 'asc'));
});

test('listagem marca can_delete false para o próprio usuário logado', function () {
    $admin = User::factory()->administrator()->create(['name' => 'Admin Logado']);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('users.data', function ($rows) use ($admin): bool {
                foreach ($rows as $row) {
                    if ($row['id'] === $admin->id) {
                        return $row['can_delete'] === false;
                    }
                }

                return false;
            }));
});

test('listagem de usuários ordena por e-mail quando solicitado', function () {
    $admin = User::factory()->administrator()->create([
        'name' => 'Z Admin',
        'email' => 'zz_admin@sort.test',
    ]);

    User::factory()->create([
        'name' => 'B User',
        'email' => 'b@sort.test',
    ]);
    User::factory()->create([
        'name' => 'A User',
        'email' => 'a@sort.test',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', [
            'sort' => 'email',
            'direction' => 'asc',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('users.data.0.email', 'a@sort.test')
            ->where('users.data.1.email', 'b@sort.test')
            ->where('users.data.2.email', 'zz_admin@sort.test'));
});

test('usuários com users.manage podem criar usuário', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Novo Colaborador',
            'email' => 'novo@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'roles' => [RolePermissionSeeder::ROLE_USER],
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('flash.type', 'success')
        ->assertSessionHas('flash.message', 'Usuário criado com sucesso.');

    $this->assertDatabaseHas('users', ['email' => 'novo@example.com']);

    expect(User::query()->where('email', 'novo@example.com')->first())
        ->hasRole(RolePermissionSeeder::ROLE_USER)
        ->toBeTrue();
});

test('usuários não podem excluir a si mesmos pela gestão de usuários', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('flash.type', 'warning')
        ->assertSessionHas(
            'flash.message',
            'Você não pode excluir sua própria conta.',
        );

    expect($admin->fresh())->not->toBeNull();
});

test('usuários com users.manage podem ver a tela somente leitura do usuário', function () {
    $admin = User::factory()->administrator()->create();
    $target = User::factory()->create(['name' => 'Usuário Alvo']);

    $this->actingAs($admin)
        ->get(route('admin.users.show', $target))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/Show')
            ->where('user.name', 'Usuário Alvo')
            ->where('user.email', $target->email));
});

test('usuários sem users.manage não veem a página de detalhe do usuário', function () {
    $user = User::factory()->withoutRoles()->create();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.users.show', $target))
        ->assertForbidden();
});

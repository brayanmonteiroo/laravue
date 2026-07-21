<?php

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as AppRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('página 404 pública aponta para a home externa', function () {
    $response = $this->get('/rota-inexistente-para-teste-de-erro-404');

    $response->assertNotFound();
    $response->assertSee('404', false);
    $response->assertSee('Página não encontrada', false);
    $response->assertSee('Voltar ao início', false);
    $response->assertSee('href="'.route('home').'"', false);
    $response->assertSee(config('app.name'), false);
});

test('visitante em 404 no admin volta para a home pública', function () {
    $this->get('/admin/rota-inexistente-para-teste-de-erro-404')
        ->assertNotFound()
        ->assertSee('Voltar ao início', false)
        ->assertSee('href="'.route('home').'"', false)
        ->assertDontSee('Voltar ao painel', false);
});

test('usuário com painel em 404 fora do admin volta para o dashboard', function () {
    $user = User::factory()->administrator()->create();

    $this->actingAs($user)
        ->get('/rota-inexistente-para-teste-de-erro-404')
        ->assertNotFound()
        ->assertSee('Voltar ao painel', false)
        ->assertSee('href="'.route('admin.dashboard').'"', false);
});

test('usuário com painel em 404 no admin volta para o dashboard', function () {
    $user = User::factory()->administrator()->create();

    $this->actingAs($user)
        ->get('/admin/rota-inexistente-para-teste-de-erro-404')
        ->assertNotFound()
        ->assertSee('Voltar ao painel', false)
        ->assertSee('href="'.route('admin.dashboard').'"', false);
});

test('usuário autenticado via sessão em 404 no admin volta para o dashboard', function () {
    $user = User::factory()->administrator()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect();

    $this->get('/admin/901238098138901')
        ->assertNotFound()
        ->assertSee('Voltar ao painel', false)
        ->assertSee('href="'.route('admin.dashboard').'"', false)
        ->assertDontSee('Voltar ao início', false);
});

test('usuário autenticado sem nenhuma permissão admin em 403 volta para a home', function () {
    $user = User::factory()->withoutRoles()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden()
        ->assertSee('403', false)
        ->assertSee('Voltar ao início', false)
        ->assertSee('href="'.route('home').'"', false)
        ->assertDontSee('Voltar ao painel', false);
});

test('usuário sem painel mas com usuários em 403 vai para usuários', function () {
    $role = Role::create(['name' => 'so-usuarios', 'guard_name' => 'web']);
    $role->syncPermissions([
        PermissionEnum::UsersView,
        PermissionEnum::UsersSidebar,
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden()
        ->assertSee('Ir para Usuários', false)
        ->assertSee('href="'.route('admin.users.index').'"', false)
        ->assertDontSee('Voltar ao painel', false)
        ->assertDontSee('Voltar ao início', false);
});

test('textos de erro HTTP são traduzidos para português', function () {
    expect(__('Not Found'))->toBe('Página não encontrada');
    expect(__('Forbidden'))->toBe('Acesso negado');
    expect(__('Page Expired'))->toBe('Página expirada');
    expect(__('Server Error'))->toBe('Erro no servidor');
    expect(__('Service Unavailable'))->toBe('Serviço indisponível');
    expect(__('Too Many Requests'))->toBe('Muitas solicitações');
    expect(__('Unauthorized'))->toBe('Não autorizado');
    expect(__('Back to home'))->toBe('Voltar ao início');
    expect(__('Back to dashboard'))->toBe('Voltar ao painel');
    expect(__('Go to users'))->toBe('Ir para Usuários');
    expect(__('Go to roles'))->toBe('Ir para Perfis');
    expect(__('Go to audits'))->toBe('Ir para Auditoria');
});

test('mensagens de negação do Spatie Permission são traduzidas para português', function () {
    expect(__('User does not have the right permissions.'))
        ->toBe('O usuário não tem as permissões necessárias.');
    expect(__('User is not logged in.'))
        ->toBe('O usuário não está autenticado.');
});

test('visita Inertia 403 com painel renderiza página integrada e CTA do painel', function () {
    $role = Role::create(['name' => 'painel-sem-perfis', 'guard_name' => 'web']);
    $role->syncPermissions([
        PermissionEnum::DashboardView,
        PermissionEnum::DashboardSidebar,
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $targetRole = Role::findByName(AppRole::Administrator->value);
    $inertiaVersion = is_file(public_path('build/manifest.json'))
        ? hash_file('xxh128', public_path('build/manifest.json'))
        : '';

    $this->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $inertiaVersion,
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->get(route('admin.roles.permissions.edit', $targetRole))
        ->assertForbidden()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'errors/Error')
        ->assertJsonPath('props.status', 403)
        ->assertJsonPath('props.ctaUrl', route('admin.dashboard'))
        ->assertJsonPath('props.ctaLabel', 'Voltar ao painel')
        ->assertJsonPath('props.crumbTitle', 'Painel')
        ->assertJsonPath('props.message', 'O usuário não tem as permissões necessárias.');
});

test('visita Inertia 403 sem permissão admin oferece CTA da home', function () {
    $user = User::factory()->withoutRoles()->create();
    $inertiaVersion = is_file(public_path('build/manifest.json'))
        ? hash_file('xxh128', public_path('build/manifest.json'))
        : '';

    $this->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $inertiaVersion,
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->get(route('admin.dashboard'))
        ->assertForbidden()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'errors/Error')
        ->assertJsonPath('props.status', 403)
        ->assertJsonPath('props.ctaUrl', route('home'))
        ->assertJsonPath('props.ctaLabel', 'Voltar ao início')
        ->assertJsonPath('props.crumbTitle', 'Início');
});

test('visita Inertia 403 sem painel mas com perfis oferece CTA de perfis', function () {
    $role = Role::create(['name' => 'so-perfis', 'guard_name' => 'web']);
    $role->syncPermissions([
        PermissionEnum::PermissionsView,
        PermissionEnum::PermissionsSidebar,
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $inertiaVersion = is_file(public_path('build/manifest.json'))
        ? hash_file('xxh128', public_path('build/manifest.json'))
        : '';

    $this->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $inertiaVersion,
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->get(route('admin.dashboard'))
        ->assertForbidden()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'errors/Error')
        ->assertJsonPath('props.ctaUrl', route('admin.roles.index'))
        ->assertJsonPath('props.ctaLabel', 'Ir para Perfis')
        ->assertJsonPath('props.crumbTitle', 'Perfis');
});

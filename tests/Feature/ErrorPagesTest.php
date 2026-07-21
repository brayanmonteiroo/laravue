<?php

use App\Models\User;

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

test('usuário autenticado sem permissão vê página 403 e volta para a home', function () {
    $user = User::factory()->withoutRoles()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden()
        ->assertSee('403', false)
        ->assertSee('Voltar ao início', false)
        ->assertSee('href="'.route('home').'"', false);
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
});

test('mensagens de negação do Spatie Permission são traduzidas para português', function () {
    expect(__('User does not have the right permissions.'))
        ->toBe('O usuário não tem as permissões necessárias.');
    expect(__('User is not logged in.'))
        ->toBe('O usuário não está autenticado.');
});

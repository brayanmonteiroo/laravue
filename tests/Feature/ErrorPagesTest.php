<?php

test('página 404 exibe mensagem em português', function () {
    $response = $this->get('/rota-inexistente-para-teste-de-erro-404');

    $response->assertNotFound();
    $response->assertSee('Página não encontrada', false);
});

test('textos de erro HTTP são traduzidos para português', function () {
    expect(__('Not Found'))->toBe('Página não encontrada');
    expect(__('Forbidden'))->toBe('Acesso negado');
    expect(__('Page Expired'))->toBe('Página expirada');
    expect(__('Server Error'))->toBe('Erro no servidor');
    expect(__('Service Unavailable'))->toBe('Serviço indisponível');
    expect(__('Too Many Requests'))->toBe('Muitas solicitações');
    expect(__('Unauthorized'))->toBe('Não autorizado');
});

test('mensagens de negação do Spatie Permission são traduzidas para português', function () {
    expect(__('User does not have the right permissions.'))
        ->toBe('O usuário não tem as permissões necessárias.');
    expect(__('User is not logged in.'))
        ->toBe('O usuário não está autenticado.');
});

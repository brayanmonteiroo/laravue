<?php

use App\Models\User;

test('administradores autenticados acessam o horizon', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get('/horizon')
        ->assertOk();
});

test('usuários comuns não acessam o horizon', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/horizon')
        ->assertForbidden();
});

test('visitantes não acessam o horizon', function () {
    $this->get('/horizon')
        ->assertRedirect(route('login'));
});

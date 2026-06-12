<?php

test('cadastro público desativado — get em register retorna 404', function () {
    $this->get('/register')->assertNotFound();
});

test('cadastro público desativado — post em register retorna 404', function () {
    $this->post('/register', [
        'name' => 'Fulano',
        'email' => 'fulano@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertNotFound();
});

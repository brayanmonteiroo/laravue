<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('tela de confirmação de senha pode ser exibida', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/ConfirmPassword'),
    );
});

test('confirmação de senha exige autenticação', function () {
    $response = $this->get(route('password.confirm'));

    $response->assertRedirect(route('login'));
});

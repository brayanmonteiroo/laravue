<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('visitantes são redirecionados para a página de login', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('login'));
});

test('usuários autenticados podem acessar o painel', function () {
    $user = User::factory()->administrator()->create();
    $this->actingAs($user);

    $response = $this->get(route('admin.dashboard'));
    $response->assertOk();
});

test('painel exibe contagens reais de usuários e perfis', function () {
    $user = User::factory()->administrator()->create();
    User::factory()->count(2)->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Dashboard')
            ->where('userCount', User::query()->count())
            ->where('roleCount', Role::query()->count()));
});

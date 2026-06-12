<?php

use App\Models\User;

test('usuários autenticados sem dashboard.access não acessam o painel', function () {
    $user = User::factory()->withoutRoles()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('usuários com dashboard.access podem acessar o painel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('visitantes podem ver a página inicial', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('home/Index'));
});

test('usuários autenticados podem ver a página inicial', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('home/Index'));
});

<?php

use App\Models\User;
use App\Notifications\Auth\QueuedVerifyEmail;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
});

test('envia notificação de verificação de e-mail', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('home'));

    Notification::assertSentTo($user, QueuedVerifyEmail::class);
});

test('não envia notificação de verificação se o e-mail já estiver verificado', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('admin.dashboard', absolute: false));

    Notification::assertNothingSent();
});

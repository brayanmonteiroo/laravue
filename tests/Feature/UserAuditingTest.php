<?php

use App\Models\User;
use OwenIt\Auditing\Models\Audit;

test('atualização de usuário é registrada na auditoria', function () {
    $user = User::factory()->create([
        'name' => 'Nome Original',
    ]);

    $beforeCount = Audit::query()->count();

    $user->update(['name' => 'Nome Atualizado']);

    expect(Audit::query()->count())->toBeGreaterThan($beforeCount);

    $audit = Audit::query()
        ->where('auditable_type', User::class)
        ->where('auditable_id', $user->getKey())
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->old_values)->toHaveKey('name');
    expect($audit->old_values['name'])->toBe('Nome Original');
    expect($audit->new_values['name'])->toBe('Nome Atualizado');
});

test('senha nunca aparece nos dados de auditoria quando outros campos mudam', function () {
    $user = User::factory()->create(['name' => 'A']);

    $user->update([
        'name' => 'B',
        'password' => 'nova-senha-segura-123',
    ]);

    $audit = Audit::query()
        ->where('auditable_type', User::class)
        ->where('auditable_id', $user->getKey())
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->old_values)->not->toHaveKey('password');
    expect($audit->new_values)->not->toHaveKey('password');
});

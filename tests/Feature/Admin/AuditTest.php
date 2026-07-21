<?php

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use App\Support\AuditPresenter;
use Inertia\Testing\AssertableInertia as Assert;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('visitantes não acessam a tela de auditoria', function () {
    $this->get(route('admin.audits.index'))
        ->assertRedirect(route('login'));
});

test('usuários sem audits.view não acessam a tela de auditoria', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.audits.index'))
        ->assertForbidden();
});

test('usuários com apenas audits.sidebar não acessam a tela de auditoria', function () {
    $role = Role::create(['name' => 'AuditMenuOnly', 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::findByName(PermissionEnum::AuditsSidebar->value, 'web'),
    ]);

    $user = User::factory()->withoutRoles()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('admin.audits.index'))
        ->assertForbidden();
});

test('administrador visualiza as últimas auditorias traduzidas', function () {
    $admin = User::factory()->administrator()->create();
    $target = User::factory()->create(['name' => 'Maria Silva']);

    $target->update(['name' => 'Maria Santos']);

    $this->actingAs($admin)
        ->get(route('admin.audits.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/audits/Index')
            ->has('audits.data')
            ->where('audits.per_page', 10)
            ->where('audits.data.0.actor', fn ($value) => is_string($value))
            ->where('audits.data.0.action', fn ($value) => is_string($value))
            ->where('audits.data.0.subject', fn ($value) => is_string($value))
            ->where('audits.data.0.details', fn ($value) => is_string($value))
            ->has('filters')
            ->has('filterOptions.events')
        );
});

test('listagem de auditoria pagina de dez em dez', function () {
    $admin = User::factory()->administrator()->create();
    $actor = User::factory()->create();

    foreach (range(1, 11) as $i) {
        Audit::query()->create([
            'user_type' => User::class,
            'user_id' => $actor->id,
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $actor->id,
            'old_values' => ['name' => "Antes {$i}"],
            'new_values' => ['name' => "Depois {$i}"],
            'url' => 'http://localhost/admin/users/'.$actor->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest',
            'tags' => '',
        ]);
    }

    $total = Audit::query()->count();
    $secondPageCount = $total - 10;

    expect($total)->toBeGreaterThan(10);
    expect($secondPageCount)->toBeGreaterThan(0);

    $this->actingAs($admin)
        ->get(route('admin.audits.index', ['page' => 1]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/audits/Index')
            ->where('audits.current_page', 1)
            ->where('audits.per_page', 10)
            ->where('audits.total', $total)
            ->has('audits.data', 10)
        );

    $this->actingAs($admin)
        ->get(route('admin.audits.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/audits/Index')
            ->where('audits.current_page', 2)
            ->where('audits.per_page', 10)
            ->where('audits.total', $total)
            ->has('audits.data', $secondPageCount)
        );
});

test('administrador pode filtrar auditorias por evento', function () {
    $admin = User::factory()->administrator()->create();
    $actor = User::factory()->create(['name' => 'Ator Filtro']);

    Audit::query()->create([
        'user_type' => User::class,
        'user_id' => $actor->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $actor->id,
        'old_values' => [],
        'new_values' => ['name' => 'Novo'],
        'url' => 'http://localhost/admin/users',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'tags' => '',
    ]);

    Audit::query()->create([
        'user_type' => User::class,
        'user_id' => $actor->id,
        'event' => 'deleted',
        'auditable_type' => User::class,
        'auditable_id' => $actor->id,
        'old_values' => ['name' => 'Antigo'],
        'new_values' => [],
        'url' => 'http://localhost/admin/users',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'tags' => '',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.audits.index', [
            'event' => 'deleted',
            'search' => 'Ator',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.event', 'deleted')
            ->where('filters.search', 'Ator')
            ->where('audits.data', function ($rows): bool {
                return collect($rows)->isNotEmpty()
                    && collect($rows)->every(fn (array $row): bool => $row['action'] === 'Remoção');
            })
        );
});

test('sincronizar permissões de perfil gera registro de auditoria', function () {
    $admin = User::factory()->administrator()->create();
    $targetRole = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $beforeCount = Audit::query()->count();

    $this->actingAs($admin)
        ->put(route('admin.roles.permissions.update', $targetRole), [
            'permissions' => [
                PermissionEnum::DashboardSidebar->value,
                PermissionEnum::UsersView->value,
            ],
        ])
        ->assertRedirect(route('admin.roles.permissions.edit', $targetRole));

    expect(Audit::query()->count())->toBeGreaterThan($beforeCount);

    $audit = Audit::query()
        ->where('auditable_type', Role::class)
        ->where('auditable_id', $targetRole->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->old_values)->toHaveKey('permissions');
    expect($audit->new_values)->toHaveKey('permissions');
});

test('atribuir papéis a usuário gera registro de auditoria', function () {
    $admin = User::factory()->administrator()->create();
    $user = User::factory()->withoutRoles()->create();
    Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $beforeCount = Audit::query()->count();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => ['Editor'],
        ])
        ->assertRedirect(route('admin.users.index'));

    expect(Audit::query()->count())->toBeGreaterThan($beforeCount);

    $audit = Audit::query()
        ->where('auditable_type', User::class)
        ->where('auditable_id', $user->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->new_values['roles'] ?? null)->toBe(['Editor']);
});

test('tela de permissões exibe grupos alinhados ao menu lateral', function () {
    $admin = User::factory()->administrator()->create();
    $targetRole = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $this->actingAs($admin)
        ->get(route('admin.roles.permissions.edit', $targetRole))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/roles/Permissions')
            ->has('permissionGroups', 3)
            ->where('permissionGroups.0.label', __('Permission group: Menu'))
            ->where('permissionGroups.0.sections.0.label', __('Permission section: Dashboard'))
            ->where('permissionGroups.0.sections.0.permissions', [
                'dashboard.sidebar',
                'dashboard.view',
            ])
            ->where('permissionGroups.1.label', __('Permission group: Settings'))
            ->where('permissionGroups.1.sections.0.label', __('Permission section: Users'))
            ->where('permissionGroups.1.sections.1.label', __('Permission section: Permissions'))
            ->where('permissionGroups.1.sections.2.label', __('Permission section: Audits'))
            ->where('permissionGroups.2.label', __('Permission group: System'))
            ->where('permissionGroups.2.sections.0.label', __('Permission section: Horizon'))
            ->where('permissionGroups.2.sections.0.permissions', [
                'horizon.view',
            ])
        );
});

test('apresentador traduz alteração de permissões de perfil', function () {
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $audit = Audit::query()->create([
        'user_type' => User::class,
        'user_id' => User::factory()->create()->id,
        'event' => 'updated',
        'auditable_type' => Role::class,
        'auditable_id' => $role->id,
        'old_values' => [
            'role_name' => 'Editor',
            'permissions' => ['dashboard.sidebar'],
        ],
        'new_values' => [
            'role_name' => 'Editor',
            'permissions' => ['dashboard.sidebar', 'users.view'],
        ],
    ]);

    $audit->load('user:id,name');

    $presented = app(AuditPresenter::class)->present($audit);

    expect($presented['action'])->toBe('Atualização');
    expect($presented['subject'])->toBe('Perfil · Editor');
    expect($presented['details'])->toContain('Visualizar página de usuários');
});

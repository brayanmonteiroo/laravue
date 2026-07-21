<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\AuditRecorder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * @return list<string>
     */
    public static function systemRoleNames(): array
    {
        return [
            RolePermissionSeeder::ROLE_ADMIN,
        ];
    }

    public static function isSystemRole(Role $role): bool
    {
        return in_array($role->name, self::systemRoleNames(), true);
    }

    /**
     * Lista os perfis (roles) do guard web.
     */
    public function index(Request $request): Response
    {
        abort_unless(
            $request->user()?->can(RolePermissionSeeder::PERMISSION_PERMISSIONS_VIEW) ?? false,
            403,
        );

        $canUpdate = $request->user()?->can(RolePermissionSeeder::PERMISSION_PERMISSIONS_UPDATE) ?? false;

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) use ($canUpdate): array {
                $isSystem = self::isSystemRole($role);
                $usersCount = (int) $role->users_count;
                $permissionsCount = (int) $role->permissions_count;

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions_count' => $permissionsCount,
                    'users_count' => $usersCount,
                    'is_system' => $isSystem,
                    'can_rename' => $canUpdate && ! $isSystem,
                    'can_delete' => $canUpdate && ! $isSystem && $usersCount === 0,
                ];
            });

        return Inertia::render('admin/roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Cria um novo perfil sem permissões.
     */
    public function store(StoreRoleRequest $request, AuditRecorder $auditRecorder): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $auditRecorder->record(
            auditable: $role,
            event: 'created',
            newValues: [
                'name' => $role->name,
                'permissions' => [],
            ],
        );

        return to_route('admin.roles.index')->with('flash', [
            'type' => 'success',
            'message' => 'Perfil criado com sucesso.',
        ]);
    }

    /**
     * Renomeia um perfil (exceto roles de sistema).
     */
    public function update(UpdateRoleRequest $request, Role $role, AuditRecorder $auditRecorder): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);

        if (self::isSystemRole($role)) {
            return back()->with('flash', [
                'type' => 'error',
                'message' => 'Perfis de sistema não podem ser renomeados.',
            ]);
        }

        $oldName = $role->name;
        $newName = $request->validated('name');

        if ($oldName !== $newName) {
            $role->name = $newName;
            $role->save();

            $auditRecorder->record(
                auditable: $role,
                event: 'updated',
                oldValues: ['name' => $oldName],
                newValues: ['name' => $newName],
            );
        }

        return to_route('admin.roles.index')->with('flash', [
            'type' => 'success',
            'message' => 'Perfil atualizado com sucesso.',
        ]);
    }

    /**
     * Exclui um perfil (exceto sistema e com usuários vinculados).
     */
    public function destroy(Request $request, Role $role, AuditRecorder $auditRecorder): RedirectResponse
    {
        abort_unless(
            $request->user()?->can(RolePermissionSeeder::PERMISSION_PERMISSIONS_UPDATE) ?? false,
            403,
        );

        abort_unless($role->guard_name === 'web', 404);

        if (self::isSystemRole($role)) {
            return back()->with('flash', [
                'type' => 'error',
                'message' => 'Perfis de sistema não podem ser excluídos.',
            ]);
        }

        $usersCount = $role->users()->count();

        if ($usersCount > 0) {
            return back()->with('flash', [
                'type' => 'error',
                'message' => 'Não é possível excluir um perfil que ainda possui usuários vinculados.',
            ]);
        }

        $oldValues = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
        ];

        $auditRecorder->record(
            auditable: $role,
            event: 'deleted',
            oldValues: $oldValues,
        );

        $role->delete();

        return to_route('admin.roles.index')->with('flash', [
            'type' => 'success',
            'message' => 'Perfil excluído com sucesso.',
        ]);
    }
}

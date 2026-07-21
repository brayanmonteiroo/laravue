<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\RolePermission;

use App\Http\Controllers\Admin\Role\RoleController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RolePermission\SyncRolePermissionsRequest;
use App\Services\Audit\AuditRecorder;
use App\Support\PermissionCatalog;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * Matriz de permissões de um perfil.
     */
    public function edit(Request $request, Role $role): Response
    {
        abort_unless(
            $request->user()?->can(RolePermissionSeeder::PERMISSION_PERMISSIONS_VIEW) ?? false,
            403,
        );

        abort_unless($role->guard_name === 'web', 404);

        RolePermissionSeeder::ensurePermissionsExist();

        $role->load('permissions:id,name');

        return Inertia::render('admin/roles/Permissions', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
                'is_system' => RoleController::isSystemRole($role),
            ],
            'permissionGroups' => PermissionCatalog::grouped(),
        ]);
    }

    /**
     * Sincroniza as permissões de um perfil.
     */
    public function update(
        SyncRolePermissionsRequest $request,
        Role $role,
        AuditRecorder $auditRecorder,
    ): RedirectResponse {
        abort_unless($role->guard_name === 'web', 404);

        RolePermissionSeeder::ensurePermissionsExist();

        $oldPermissions = $role->permissions()->pluck('name')->sort()->values()->all();
        $newPermissions = collect($request->validated('permissions') ?? [])->sort()->values()->all();

        $role->syncPermissions($newPermissions);

        if ($oldPermissions !== $newPermissions) {
            $auditRecorder->record(
                auditable: $role,
                event: 'updated',
                oldValues: [
                    'role_name' => $role->name,
                    'permissions' => $oldPermissions,
                ],
                newValues: [
                    'role_name' => $role->name,
                    'permissions' => $newPermissions,
                ],
            );
        }

        return to_route('admin.roles.permissions.edit', $role)->with('flash', [
            'type' => 'success',
            'message' => 'Permissões do perfil atualizadas com sucesso.',
        ]);
    }
}

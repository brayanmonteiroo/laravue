<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\SyncRolePermissionsRequest;
use App\Services\AuditRecorder;
use App\Support\PermissionCatalog;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * Renderiza a página de permissões.
     */
    public function index(): Response
    {
        RolePermissionSeeder::ensurePermissionsExist();

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
            ]);

        return Inertia::render('admin/permissions/Index', [
            'roles' => $roles,
            'permissionGroups' => PermissionCatalog::grouped(),
        ]);
    }

    /**
     * Cria um novo perfil (role) com nenhuma permissão até ser configurado.
     */
    public function storeRole(StoreRoleRequest $request, AuditRecorder $auditRecorder): RedirectResponse
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

        return to_route('admin.permissions.index')->with('flash', [
            'type' => 'success',
            'message' => 'Perfil criado com sucesso.',
        ]);
    }

    /**
     * Sincroniza as permissões para um único perfil.
     */
    public function update(SyncRolePermissionsRequest $request, AuditRecorder $auditRecorder): RedirectResponse
    {
        RolePermissionSeeder::ensurePermissionsExist();

        $data = $request->validated();

        $role = Role::query()
            ->where('guard_name', 'web')
            ->whereKey($data['role_id'])
            ->firstOrFail();

        $oldPermissions = $role->permissions()->pluck('name')->sort()->values()->all();
        $newPermissions = collect($data['permissions'] ?? [])->sort()->values()->all();

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

        return to_route('admin.permissions.index')->with('flash', [
            'type' => 'success',
            'message' => 'Permissões do perfil atualizadas com sucesso.',
        ]);
    }
}

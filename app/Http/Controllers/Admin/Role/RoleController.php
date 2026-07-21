<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Role;

use App\Enums\Permission;
use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\IndexRoleRequest;
use App\Http\Requests\Admin\Role\StoreRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Services\Audit\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const int PER_PAGE = 10;

    /**
     * Nomes dos perfis de sistema.
     *
     * @return list<string>
     */
    public static function systemRoleNames(): array
    {
        return [
            RoleEnum::Administrator->value,
        ];
    }

    /**
     * Determina se um perfil é de sistema.
     */
    public static function isSystemRole(Role $role): bool
    {
        return in_array($role->name, self::systemRoleNames(), true);
    }

    /**
     * Lista os perfis (roles) do guard web.
     */
    public function index(IndexRoleRequest $request): Response
    {
        $filters = $request->filters();
        $sort = $filters['sort'];
        $direction = $filters['direction'];
        $canUpdate = $request->user()?->can(Permission::PermissionsUpdate) ?? false;
        $systemNames = self::systemRoleNames();

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount(['permissions', 'users'])
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->when($filters['system'] === 'yes', fn ($query) => $query->whereIn('name', $systemNames))
            ->when($filters['system'] === 'no', fn ($query) => $query->whereNotIn('name', $systemNames))
            ->when($filters['has_users'] === 'yes', fn ($query) => $query->has('users'))
            ->when($filters['has_users'] === 'no', fn ($query) => $query->doesntHave('users'))
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(function (Role $role) use ($canUpdate): array {
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
            'sort' => [
                'column' => $sort,
                'direction' => $direction,
            ],
            'filters' => [
                'search' => $filters['search'],
                'system' => $filters['system'],
                'has_users' => $filters['has_users'],
            ],
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
            'message' => __('Role created successfully.'),
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
                'message' => __('System roles cannot be renamed.'),
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
            'message' => __('Role updated successfully.'),
        ]);
    }

    /**
     * Exclui um perfil (exceto sistema e com usuários vinculados).
     */
    public function destroy(Role $role, AuditRecorder $auditRecorder): RedirectResponse
    {
        $this->authorize('delete', $role);

        abort_unless($role->guard_name === 'web', 404);

        if (self::isSystemRole($role)) {
            return back()->with('flash', [
                'type' => 'error',
                'message' => __('System roles cannot be deleted.'),
            ]);
        }

        $usersCount = $role->users()->count();

        if ($usersCount > 0) {
            return back()->with('flash', [
                'type' => 'error',
                'message' => __('Cannot delete a role that still has users assigned.'),
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
            'message' => __('Role deleted successfully.'),
        ]);
    }
}

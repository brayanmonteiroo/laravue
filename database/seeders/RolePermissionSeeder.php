<?php

namespace Database\Seeders;

use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public const PERMISSION_DASHBOARD_SIDEBAR = 'dashboard.sidebar';

    public const PERMISSION_DASHBOARD_VIEW = 'dashboard.view';

    public const PERMISSION_USERS_SIDEBAR = 'users.sidebar';

    public const PERMISSION_USERS_VIEW = 'users.view';

    public const PERMISSION_USERS_SHOW = 'users.show';

    public const PERMISSION_USERS_CREATE = 'users.create';

    public const PERMISSION_USERS_UPDATE = 'users.update';

    public const PERMISSION_USERS_DELETE = 'users.delete';

    public const PERMISSION_PERMISSIONS_SIDEBAR = 'permissions.sidebar';

    public const PERMISSION_PERMISSIONS_VIEW = 'permissions.view';

    public const PERMISSION_PERMISSIONS_CREATE = 'permissions.create';

    public const PERMISSION_PERMISSIONS_UPDATE = 'permissions.update';

    public const PERMISSION_AUDITS_SIDEBAR = 'audits.sidebar';

    public const PERMISSION_AUDITS_VIEW = 'audits.view';

    public const ROLE_ADMIN = 'Administrator';

    /**
     * Garante que todas as permissões do catálogo existam no banco.
     */
    public static function ensurePermissionsExist(string $guard = 'web'): void
    {
        foreach (PermissionCatalog::allPermissionNames() as $name) {
            Permission::findOrCreate($name, $guard);
        }
    }

    /**
     * Executa os seeds do banco de dados.
     */
    public function run(): void
    {
        $guard = 'web';

        self::ensurePermissionsExist($guard);

        $admin = Role::findOrCreate(self::ROLE_ADMIN, $guard);
        $admin->syncPermissions(Permission::query()->where('guard_name', $guard)->get());
    }
}

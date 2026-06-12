<?php

namespace Database\Seeders;

use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public const PERMISSION_DASHBOARD = 'dashboard.access';

    public const PERMISSION_USERS_MANAGE = 'users.manage';

    public const PERMISSION_PERMISSIONS_MANAGE = 'permissions.manage';

    public const PERMISSION_AUDITS_VIEW = 'audits.view';

    public const ROLE_ADMIN = 'Administrator';

    public const ROLE_USER = 'User';

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
     * Run the database seeds.
     */
    public function run(): void
    {
        $guard = 'web';

        self::ensurePermissionsExist($guard);

        $admin = Role::findOrCreate(self::ROLE_ADMIN, $guard);
        $user = Role::findOrCreate(self::ROLE_USER, $guard);

        $admin->syncPermissions(Permission::query()->where('guard_name', $guard)->get());
        $user->syncPermissions(
            Permission::query()->where('name', self::PERMISSION_DASHBOARD)->where('guard_name', $guard)->get(),
        );
    }
}

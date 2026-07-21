<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Garante que todas as permissões do catálogo existam no banco.
     */
    public static function ensurePermissionsExist(string $guard = 'web'): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value, $guard);
        }
    }

    /**
     * Executa os seeds do banco de dados.
     */
    public function run(): void
    {
        $guard = 'web';

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        self::ensurePermissionsExist($guard);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::findOrCreate(RoleEnum::Administrator->value, $guard);
        $admin->syncPermissions(Permission::query()->where('guard_name', $guard)->get());
    }
}

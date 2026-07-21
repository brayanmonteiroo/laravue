<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case DashboardSidebar = 'dashboard.sidebar';
    case DashboardView = 'dashboard.view';

    case UsersSidebar = 'users.sidebar';
    case UsersView = 'users.view';
    case UsersShow = 'users.show';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';

    case PermissionsSidebar = 'permissions.sidebar';
    case PermissionsView = 'permissions.view';
    case PermissionsCreate = 'permissions.create';
    case PermissionsUpdate = 'permissions.update';

    case AuditsSidebar = 'audits.sidebar';
    case AuditsView = 'audits.view';

    case HorizonView = 'horizon.view';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

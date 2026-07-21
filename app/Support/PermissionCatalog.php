<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Permission;

class PermissionCatalog
{
    /**
     * Retorna todas as permissões disponíveis.
     *
     * @return list<string>
     */
    public static function allPermissionNames(): array
    {
        return Permission::values();
    }

    /**
     * Permissões agrupadas conforme a estrutura do menu lateral.
     *
     * @return list<array{
     *     label: string,
     *     description: string,
     *     sections: list<array{label: string, permissions: list<string>}>
     * }>
     */
    public static function grouped(): array
    {
        return [
            [
                'label' => __('Permission group: Menu'),
                'description' => __('Permission group description: Menu'),
                'sections' => [
                    [
                        'label' => __('Permission section: Dashboard'),
                        'permissions' => [
                            Permission::DashboardSidebar->value,
                            Permission::DashboardView->value,
                        ],
                    ],
                ],
            ],
            [
                'label' => __('Permission group: Settings'),
                'description' => __('Permission group description: Settings'),
                'sections' => [
                    [
                        'label' => __('Permission section: Users'),
                        'permissions' => [
                            Permission::UsersSidebar->value,
                            Permission::UsersView->value,
                            Permission::UsersShow->value,
                            Permission::UsersCreate->value,
                            Permission::UsersUpdate->value,
                            Permission::UsersDelete->value,
                        ],
                    ],
                    [
                        'label' => __('Permission section: Permissions'),
                        'permissions' => [
                            Permission::PermissionsSidebar->value,
                            Permission::PermissionsView->value,
                            Permission::PermissionsCreate->value,
                            Permission::PermissionsUpdate->value,
                        ],
                    ],
                    [
                        'label' => __('Permission section: Audits'),
                        'permissions' => [
                            Permission::AuditsSidebar->value,
                            Permission::AuditsView->value,
                        ],
                    ],
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

use Database\Seeders\RolePermissionSeeder;

class PermissionCatalog
{
    /**
     * Retorna todas as permissões disponíveis.
     *
     * @return list<string>
     */
    public static function allPermissionNames(): array
    {
        $names = [];

        foreach (self::grouped() as $group) {
            foreach ($group['sections'] as $section) {
                foreach ($section['permissions'] as $permission) {
                    $names[] = $permission;
                }
            }
        }

        return $names;
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
                'label' => 'Menu',
                'description' => 'Itens exibidos no grupo principal do menu.',
                'sections' => [
                    [
                        'label' => 'Painel',
                        'permissions' => [
                            RolePermissionSeeder::PERMISSION_DASHBOARD_SIDEBAR,
                            RolePermissionSeeder::PERMISSION_DASHBOARD_VIEW,
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Configurações',
                'description' => 'Itens e ações do grupo Configurações da sidebar.',
                'sections' => [
                    [
                        'label' => 'Usuários',
                        'permissions' => [
                            RolePermissionSeeder::PERMISSION_USERS_SIDEBAR,
                            RolePermissionSeeder::PERMISSION_USERS_VIEW,
                            RolePermissionSeeder::PERMISSION_USERS_SHOW,
                            RolePermissionSeeder::PERMISSION_USERS_CREATE,
                            RolePermissionSeeder::PERMISSION_USERS_UPDATE,
                            RolePermissionSeeder::PERMISSION_USERS_DELETE,
                        ],
                    ],
                    [
                        'label' => 'Permissões',
                        'permissions' => [
                            RolePermissionSeeder::PERMISSION_PERMISSIONS_SIDEBAR,
                            RolePermissionSeeder::PERMISSION_PERMISSIONS_VIEW,
                            RolePermissionSeeder::PERMISSION_PERMISSIONS_CREATE,
                            RolePermissionSeeder::PERMISSION_PERMISSIONS_UPDATE,
                        ],
                    ],
                    [
                        'label' => 'Auditoria',
                        'permissions' => [
                            RolePermissionSeeder::PERMISSION_AUDITS_SIDEBAR,
                            RolePermissionSeeder::PERMISSION_AUDITS_VIEW,
                        ],
                    ],
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

use Database\Seeders\RolePermissionSeeder;

class PermissionCatalog
{
    /**
     * @return list<string>
     */
    public static function allPermissionNames(): array
    {
        return array_merge(
            ...array_map(
                fn (array $group): array => $group['permissions'],
                self::grouped(),
            ),
        );
    }

    /**
     * Permissões agrupadas conforme a estrutura do menu lateral.
     *
     * @return list<array{label: string, description: string, permissions: list<string>}>
     */
    public static function grouped(): array
    {
        return [
            [
                'label' => 'Menu',
                'description' => 'Itens exibidos no grupo principal do menu lateral.',
                'permissions' => [
                    RolePermissionSeeder::PERMISSION_DASHBOARD,
                ],
            ],
            [
                'label' => 'Configurações',
                'description' => 'Itens exibidos no grupo Configurações do menu lateral.',
                'permissions' => [
                    RolePermissionSeeder::PERMISSION_USERS_MANAGE,
                    RolePermissionSeeder::PERMISSION_PERMISSIONS_MANAGE,
                    RolePermissionSeeder::PERMISSION_AUDITS_VIEW,
                ],
            ],
        ];
    }
}

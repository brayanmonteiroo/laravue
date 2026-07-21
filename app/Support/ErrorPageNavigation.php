<?php

namespace App\Support;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;

class ErrorPageNavigation
{
    /**
     * Destino seguro para CTAs de páginas de erro (sem loop 403).
     *
     * @return array{url: string, label: string, crumbTitle: string}
     */
    public static function for(?Authenticatable $user): array
    {
        if ($user instanceof Authorizable) {
            foreach (self::adminDestinations() as $destination) {
                if ($user->can($destination['permission'])) {
                    return [
                        'url' => $destination['url'](),
                        'label' => $destination['label'](),
                        'crumbTitle' => $destination['crumb'](),
                    ];
                }
            }
        }

        return [
            'url' => route('home'),
            'label' => __('Back to home'),
            'crumbTitle' => __('Error crumb: Home'),
        ];
    }

    /**
     * @return list<array{permission: string, url: callable(): string, label: callable(): string, crumb: callable(): string}>
     */
    protected static function adminDestinations(): array
    {
        return [
            [
                'permission' => RolePermissionSeeder::PERMISSION_DASHBOARD_VIEW,
                'url' => fn (): string => route('admin.dashboard'),
                'label' => fn (): string => __('Back to dashboard'),
                'crumb' => fn (): string => __('Error crumb: Dashboard'),
            ],
            [
                'permission' => RolePermissionSeeder::PERMISSION_USERS_VIEW,
                'url' => fn (): string => route('admin.users.index'),
                'label' => fn (): string => __('Go to users'),
                'crumb' => fn (): string => __('Error crumb: Users'),
            ],
            [
                'permission' => RolePermissionSeeder::PERMISSION_PERMISSIONS_VIEW,
                'url' => fn (): string => route('admin.roles.index'),
                'label' => fn (): string => __('Go to roles'),
                'crumb' => fn (): string => __('Error crumb: Roles'),
            ],
            [
                'permission' => RolePermissionSeeder::PERMISSION_AUDITS_VIEW,
                'url' => fn (): string => route('admin.audits.index'),
                'label' => fn (): string => __('Go to audits'),
                'crumb' => fn (): string => __('Error crumb: Audits'),
            ],
        ];
    }

    public static function defaultMessage(int $status): string
    {
        return match ($status) {
            401 => __('Unauthorized'),
            403 => __('Forbidden'),
            404 => __('Not Found'),
            419 => __('Page Expired'),
            429 => __('Too Many Requests'),
            503 => __('Service Unavailable'),
            default => __('Server Error'),
        };
    }
}

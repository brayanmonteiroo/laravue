<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Permission;
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
     * Destinos seguros para CTAs de páginas de erro (sem loop 403).
     *
     * @return list<array{permission: Permission, url: callable(): string, label: callable(): string, crumb: callable(): string}>
     */
    protected static function adminDestinations(): array
    {
        return [
            [
                'permission' => Permission::DashboardView,
                'url' => fn (): string => route('admin.dashboard'),
                'label' => fn (): string => __('Back to dashboard'),
                'crumb' => fn (): string => __('Error crumb: Dashboard'),
            ],
            [
                'permission' => Permission::UsersView,
                'url' => fn (): string => route('admin.users.index'),
                'label' => fn (): string => __('Go to users'),
                'crumb' => fn (): string => __('Error crumb: Users'),
            ],
            [
                'permission' => Permission::PermissionsView,
                'url' => fn (): string => route('admin.roles.index'),
                'label' => fn (): string => __('Go to roles'),
                'crumb' => fn (): string => __('Error crumb: Roles'),
            ],
            [
                'permission' => Permission::AuditsView,
                'url' => fn (): string => route('admin.audits.index'),
                'label' => fn (): string => __('Go to audits'),
                'crumb' => fn (): string => __('Error crumb: Audits'),
            ],
        ];
    }

    /**
     * Mensagem padrão para o status do erro.
     */
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

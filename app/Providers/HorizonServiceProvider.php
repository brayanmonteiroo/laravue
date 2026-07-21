<?php

namespace App\Providers;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Inicializa o Horizon.
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        Horizon::auth(function ($request): bool {
            return Gate::check('viewHorizon', [$request->user()]);
        });
    }

    /**
     * Define o gate do Horizon.
     *
     * Este gate determina quem pode acessar o Horizon em ambientes não locais.
     * @return void
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user): bool {
            return $user !== null && $user->hasRole(RolePermissionSeeder::ROLE_ADMIN);
        });
    }
}

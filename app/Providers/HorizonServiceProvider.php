<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Inicializa o Horizon.
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
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user): bool {
            return $user !== null && $user->hasRole(RoleEnum::Administrator);
        });
    }
}

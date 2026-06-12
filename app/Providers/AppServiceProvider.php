<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra qualquer serviço da aplicação.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializa qualquer serviço da aplicação.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        RedirectIfAuthenticated::redirectUsing(function (): string {
            $user = auth()->user();

            if ($user?->hasPermissionTo(RolePermissionSeeder::PERMISSION_DASHBOARD)) {
                return route('admin.dashboard', absolute: false);
            }

            return route('home', absolute: false);
        });
    }

    /**
     * Configura o comportamento padrão para aplicativos de produção.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

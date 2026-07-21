<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Permission;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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

        Gate::guessPolicyNamesUsing(function (string $modelClass): array {
            $modelName = class_basename($modelClass);

            return [
                "App\\Policies\\{$modelName}\\{$modelName}Policy",
                "App\\Policies\\{$modelName}Policy",
            ];
        });

        RedirectIfAuthenticated::redirectUsing(function (): string {
            $user = auth()->user();

            if ($user?->can(Permission::DashboardView)) {
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

        Model::preventLazyLoading(! app()->isProduction());

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

<?php

declare(strict_types=1);

use App\Enums\Permission;
use App\Http\Controllers\Admin\Audit\AuditController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Role\RoleController;
use App\Http\Controllers\Admin\RolePermission\RolePermissionController;
use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Público
|--------------------------------------------------------------------------
*/
Route::inertia('/', 'home/Index')->name('home');

/*
|--------------------------------------------------------------------------
| Autenticado (admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('permission:'.Permission::DashboardView->value)->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:'.Permission::UsersView->value)
        ->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('permission:'.Permission::UsersCreate->value)
        ->name('users.create');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:'.Permission::UsersCreate->value)
        ->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])
        ->middleware('permission:'.Permission::UsersShow->value)
        ->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:'.Permission::UsersUpdate->value)
        ->name('users.edit');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])
        ->middleware('permission:'.Permission::UsersUpdate->value)
        ->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:'.Permission::UsersDelete->value)
        ->name('users.destroy');

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:'.Permission::PermissionsView->value)
        ->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('permission:'.Permission::PermissionsCreate->value)
        ->name('roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:'.Permission::PermissionsUpdate->value)
        ->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:'.Permission::PermissionsUpdate->value)
        ->name('roles.destroy');

    Route::get('roles/{role}/permissions', [RolePermissionController::class, 'edit'])
        ->middleware('permission:'.Permission::PermissionsView->value)
        ->name('roles.permissions.edit');
    Route::put('roles/{role}/permissions', [RolePermissionController::class, 'update'])
        ->middleware('permission:'.Permission::PermissionsUpdate->value)
        ->name('roles.permissions.update');

    Route::redirect('permissions', '/admin/roles')->name('permissions.index');

    Route::middleware('permission:'.Permission::AuditsView->value)->group(function () {
        Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    });
});

/*
|--------------------------------------------------------------------------
| Fallback
|--------------------------------------------------------------------------
|
| Garante middleware web (sessão/auth) em URLs inexistentes.
| Sem isso, páginas de erro 404 não veem o usuário logado.
|
| any() + fallback(): POST/PUT em rotas inexistentes também retornam 404
| (Route::fallback() só registra GET e gerava 405).
| withoutMiddleware CSRF: senão POST sem token vira 419 antes do abort(404).
*/
Route::any('{fallbackPlaceholder}', fn () => abort(404))
    ->where('fallbackPlaceholder', '.*')
    ->fallback()
    ->withoutMiddleware([
        ValidateCsrfToken::class,
    ]);

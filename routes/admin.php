<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserController;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('permission:'.RolePermissionSeeder::PERMISSION_DASHBOARD_VIEW)->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_USERS_VIEW)
        ->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_USERS_CREATE)
        ->name('users.create');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_USERS_CREATE)
        ->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_USERS_SHOW)
        ->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_USERS_UPDATE)
        ->name('users.edit');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_USERS_UPDATE)
        ->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_USERS_DELETE)
        ->name('users.destroy');

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_PERMISSIONS_VIEW)
        ->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_PERMISSIONS_CREATE)
        ->name('roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_PERMISSIONS_UPDATE)
        ->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_PERMISSIONS_UPDATE)
        ->name('roles.destroy');

    Route::get('roles/{role}/permissions', [RolePermissionController::class, 'edit'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_PERMISSIONS_VIEW)
        ->name('roles.permissions.edit');
    Route::put('roles/{role}/permissions', [RolePermissionController::class, 'update'])
        ->middleware('permission:'.RolePermissionSeeder::PERMISSION_PERMISSIONS_UPDATE)
        ->name('roles.permissions.update');

    Route::redirect('permissions', '/admin/roles')->name('permissions.index');

    Route::middleware('permission:'.RolePermissionSeeder::PERMISSION_AUDITS_VIEW)->group(function () {
        Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    });
});

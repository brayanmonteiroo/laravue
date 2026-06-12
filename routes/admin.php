<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserController;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('permission:dashboard.access')->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

    Route::middleware('permission:users.manage')->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware('permission:permissions.manage')->group(function () {
        Route::get('permissions', [RolePermissionController::class, 'index'])->name('permissions.index');
        Route::post('permissions/roles', [RolePermissionController::class, 'storeRole'])->name('permissions.roles.store');
        Route::put('permissions', [RolePermissionController::class, 'update'])->name('permissions.update');
    });

    Route::middleware('permission:'.RolePermissionSeeder::PERMISSION_AUDITS_VIEW)->group(function () {
        Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    });
});

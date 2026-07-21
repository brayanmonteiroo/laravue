<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Renderiza a página de dashboard.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'userCount' => User::query()->count(),
            'roleCount' => Role::query()->count(),
        ]);
    }
}

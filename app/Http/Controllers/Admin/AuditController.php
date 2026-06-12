<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AuditPresenter;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    private const int LIMIT = 10;

    /**
     * Lista as últimas auditorias do sistema.
     */
    public function index(Request $request, AuditPresenter $presenter): Response
    {
        abort_unless(
            $request->user()?->can(RolePermissionSeeder::PERMISSION_AUDITS_VIEW) ?? false,
            403,
        );

        $audits = Audit::query()
            ->with('user:id,name')
            ->latest('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Audit $audit): array => $presenter->present($audit))
            ->values()
            ->all();

        return Inertia::render('admin/audits/Index', [
            'audits' => $audits,
        ]);
    }
}

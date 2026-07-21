<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Support\AuditPresenter;
use Inertia\Inertia;
use Inertia\Response;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    private const int LIMIT = 10;

    /**
     * Lista as últimas auditorias do sistema.
     */
    public function index(AuditPresenter $presenter): Response
    {
        $this->authorize('viewAny', Audit::class);

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

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
    private const int PER_PAGE = 10;

    /**
     * Lista as auditorias do sistema (mais recentes primeiro).
     */
    public function index(AuditPresenter $presenter): Response
    {
        $this->authorize('viewAny', Audit::class);

        $audits = Audit::query()
            ->with('user:id,name')
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (Audit $audit): array => $presenter->present($audit));

        return Inertia::render('admin/audits/Index', [
            'audits' => $audits,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Audit\IndexAuditRequest;
use App\Models\User;
use App\Support\AuditPresenter;
use Inertia\Inertia;
use Inertia\Response;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Role;

class AuditController extends Controller
{
    private const int PER_PAGE = 10;

    /**
     * Lista as auditorias do sistema (mais recentes primeiro).
     */
    public function index(IndexAuditRequest $request, AuditPresenter $presenter): Response
    {
        $filters = $request->filters();

        $audits = Audit::query()
            ->with('user:id,name')
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $term = '%'.$filters['search'].'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->whereHas('user', fn ($user) => $user->where('name', 'like', $term))
                        ->orWhere('auditable_type', 'like', $term)
                        ->orWhere('event', 'like', $term);
                });
            })
            ->when(
                $filters['event'] !== '' && $filters['event'] !== 'all',
                fn ($query) => $query->where('event', $filters['event']),
            )
            ->when(
                $filters['auditable_type'] !== '' && $filters['auditable_type'] !== 'all',
                fn ($query) => $query->where('auditable_type', $filters['auditable_type']),
            )
            ->when($filters['date_from'] !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (Audit $audit): array => $presenter->present($audit));

        return Inertia::render('admin/audits/Index', [
            'audits' => $audits,
            'filters' => $filters,
            'filterOptions' => [
                'events' => [
                    ['value' => 'all', 'label' => 'Todos'],
                    ['value' => 'created', 'label' => 'Criação'],
                    ['value' => 'updated', 'label' => 'Atualização'],
                    ['value' => 'deleted', 'label' => 'Remoção'],
                    ['value' => 'restored', 'label' => 'Restauração'],
                ],
                'types' => [
                    ['value' => 'all', 'label' => 'Todos'],
                    ['value' => User::class, 'label' => 'Usuário'],
                    ['value' => Role::class, 'label' => 'Perfil'],
                ],
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Policies\Audit;

use App\Enums\Permission;
use App\Models\User;
use OwenIt\Auditing\Models\Audit;

class AuditPolicy
{
    /**
     * Determina se o usuário pode listar auditorias.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::AuditsView);
    }

    /**
     * Determina se o usuário pode visualizar uma auditoria.
     */
    public function view(User $user, Audit $audit): bool
    {
        return $user->can(Permission::AuditsView);
    }
}

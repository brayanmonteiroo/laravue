<?php

declare(strict_types=1);

namespace App\Policies\Role;

use App\Enums\Permission;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determina se o usuário pode listar perfis / ver matriz de permissões.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::PermissionsView);
    }

    /**
     * Determina se o usuário pode visualizar um perfil.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->can(Permission::PermissionsView);
    }

    /**
     * Determina se o usuário pode criar perfis.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::PermissionsCreate);
    }

    /**
     * Determina se o usuário pode atualizar um perfil ou suas permissões.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can(Permission::PermissionsUpdate);
    }

    /**
     * Determina se o usuário pode excluir um perfil.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->can(Permission::PermissionsUpdate);
    }
}

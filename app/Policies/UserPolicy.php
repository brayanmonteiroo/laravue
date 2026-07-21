<?php

namespace App\Policies;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

class UserPolicy
{
    /**
     * Determina se o usuário pode visualizar qualquer modelo.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(RolePermissionSeeder::PERMISSION_USERS_VIEW);
    }

    /**
     * Determina se o usuário pode visualizar o modelo.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can(RolePermissionSeeder::PERMISSION_USERS_SHOW);
    }

    /**
     * Determina se o usuário pode criar modelos.
     */
    public function create(User $user): bool
    {
        return $user->can(RolePermissionSeeder::PERMISSION_USERS_CREATE);
    }

    /**
     * Determina se o usuário pode atualizar o modelo.
     */
    public function update(User $user, User $model): bool
    {
        return $user->can(RolePermissionSeeder::PERMISSION_USERS_UPDATE);
    }

    /**
     * Determina se o usuário pode deletar o modelo.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return false;
        }

        return $user->can(RolePermissionSeeder::PERMISSION_USERS_DELETE);
    }

    /**
     * Determina se o usuário pode restaurar o modelo.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determina se o usuário pode deletar permanentemente o modelo.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

class UserPolicy
{
    /**
     * Determina se o usuário pode visualizar qualquer modelo.
     * @param User $user - O usuário.
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can(RolePermissionSeeder::PERMISSION_USERS_MANAGE);
    }

    /**
     * Determina se o usuário pode visualizar o modelo.
     * @param User $user - O usuário.
     * @param User $model - O modelo.
     * @return bool
     */
    public function view(User $user, User $model): bool
    {
        return $user->can(RolePermissionSeeder::PERMISSION_USERS_MANAGE);
    }

    /**
     * Determina se o usuário pode criar modelos.
     * @param User $user - O usuário.
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can(RolePermissionSeeder::PERMISSION_USERS_MANAGE);
    }

    /**
     * Determina se o usuário pode atualizar o modelo.
     * @param User $user - O usuário.
     * @param User $model - O modelo.
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        return $user->can(RolePermissionSeeder::PERMISSION_USERS_MANAGE);
    }

    /**
     * Determina se o usuário pode deletar o modelo.
     * @param User $user - O usuário.
     * @param User $model - O modelo.
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return false;
        }

        return $user->can(RolePermissionSeeder::PERMISSION_USERS_MANAGE);
    }

    /**
     * Determina se o usuário pode restaurar o modelo.
     * @param User $user - O usuário.
     * @param User $model - O modelo.
     * @return bool
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determina se o usuário pode deletar permanentemente o modelo.
     * @param User $user - O usuário.
     * @param User $model - O modelo.
     * @return bool
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}

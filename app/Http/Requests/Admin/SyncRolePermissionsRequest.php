<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\PermissionCatalog;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class SyncRolePermissionsRequest extends FormRequest
{
    /**
     * @return list<string>
     */
    public static function allowedPermissionNames(): array
    {
        return PermissionCatalog::allPermissionNames();
    }

    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(RolePermissionSeeder::PERMISSION_PERMISSIONS_UPDATE) ?? false;
    }

    /**
     * Define as regras de validação para a requisição.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(self::allowedPermissionNames())],
        ];
    }

    /**
     * Prepara os dados para a validação.
     */
    protected function prepareForValidation(): void
    {
        $permissions = Arr::wrap($this->input('permissions', []));
        $permissions = array_values(array_filter(
            $permissions,
            fn (mixed $p): bool => is_string($p) && $p !== '',
        ));

        $this->merge(['permissions' => $permissions]);
    }
}

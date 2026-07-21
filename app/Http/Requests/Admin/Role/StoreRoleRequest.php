<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Role;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(RolePermissionSeeder::PERMISSION_PERMISSIONS_CREATE) ?? false;
    }

    /**
     * Define as regras de validação para a requisição.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'web'),
            ],
        ];
    }

    /**
     * Prepara os dados para a validação.
     */
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        if (is_string($name)) {
            $this->merge(['name' => trim($name)]);
        }
    }
}

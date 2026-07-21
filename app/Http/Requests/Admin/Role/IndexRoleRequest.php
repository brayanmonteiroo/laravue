<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Role;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class IndexRoleRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    public const SORTABLE = ['name', 'permissions_count', 'users_count'];

    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Role::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sort' => ['sometimes', 'nullable', 'string', Rule::in(self::SORTABLE)],
            'direction' => ['sometimes', 'nullable', 'string', Rule::in(['asc', 'desc'])],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'system' => ['sometimes', 'nullable', 'string', Rule::in(['all', 'yes', 'no'])],
            'has_users' => ['sometimes', 'nullable', 'string', Rule::in(['all', 'yes', 'no'])],
        ];
    }

    /**
     * @return array{
     *     sort: string,
     *     direction: string,
     *     search: string,
     *     system: string,
     *     has_users: string
     * }
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'sort' => $validated['sort'] ?? 'name',
            'direction' => $validated['direction'] ?? 'asc',
            'search' => trim((string) ($validated['search'] ?? '')),
            'system' => (string) ($validated['system'] ?? 'all'),
            'has_users' => (string) ($validated['has_users'] ?? 'all'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUserRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    public const SORTABLE = ['name', 'email', 'created_at'];

    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
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
            'role' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::when(
                    fn (): bool => filled($this->input('role')) && $this->input('role') !== 'all',
                    [Rule::exists('roles', 'name')->where('guard_name', 'web')],
                ),
            ],
            'verified' => ['sometimes', 'nullable', 'string', Rule::in(['all', 'yes', 'no'])],
            'created_from' => ['sometimes', 'nullable', 'date'],
            'created_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:created_from'],
        ];
    }

    /**
     * @return array{
     *     sort: string,
     *     direction: string,
     *     search: string,
     *     role: string,
     *     verified: string,
     *     created_from: string,
     *     created_to: string
     * }
     */
    public function filters(): array
    {
        $validated = $this->validated();
        $role = (string) ($validated['role'] ?? '');

        if ($role === 'all') {
            $role = '';
        }

        return [
            'sort' => $validated['sort'] ?? 'name',
            'direction' => $validated['direction'] ?? 'asc',
            'search' => trim((string) ($validated['search'] ?? '')),
            'role' => $role,
            'verified' => (string) ($validated['verified'] ?? 'all'),
            'created_from' => (string) ($validated['created_from'] ?? ''),
            'created_to' => (string) ($validated['created_to'] ?? ''),
        ];
    }
}

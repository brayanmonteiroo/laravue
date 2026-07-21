<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Audit;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Role;

class IndexAuditRequest extends FormRequest
{
    /**
     * @return list<string>
     */
    public static function auditableTypes(): array
    {
        return [
            User::class,
            Role::class,
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Audit::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'event' => ['sometimes', 'nullable', 'string', Rule::in(['all', 'created', 'updated', 'deleted', 'restored'])],
            'auditable_type' => ['sometimes', 'nullable', 'string', Rule::in(['all', ...self::auditableTypes()])],
            'date_from' => ['sometimes', 'nullable', 'date'],
            'date_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    /**
     * @return array{
     *     search: string,
     *     event: string,
     *     auditable_type: string,
     *     date_from: string,
     *     date_to: string
     * }
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'event' => (string) ($validated['event'] ?? 'all'),
            'auditable_type' => (string) ($validated['auditable_type'] ?? 'all'),
            'date_from' => (string) ($validated['date_from'] ?? ''),
            'date_to' => (string) ($validated['date_to'] ?? ''),
        ];
    }
}

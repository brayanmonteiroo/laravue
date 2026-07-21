<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Role;

class AuditPresenter
{
    /**
     * @return array{
     *     id: int,
     *     occurred_at: string|null,
     *     actor: string,
     *     action: string,
     *     subject: string,
     *     details: string
     * }
     */
    public function present(Audit $audit): array
    {
        return [
            'id' => $audit->id,
            'occurred_at' => $audit->created_at?->toIso8601String(),
            'actor' => $audit->user?->name ?? 'Sistema',
            'action' => $this->actionLabel($audit->event),
            'subject' => $this->subjectLabel($audit),
            'details' => $this->detailsLabel($audit),
        ];
    }

    private function actionLabel(string $event): string
    {
        return match ($event) {
            'created' => 'Criação',
            'updated' => 'Atualização',
            'deleted' => 'Remoção',
            'restored' => 'Restauração',
            default => ucfirst($event),
        };
    }

    private function subjectLabel(Audit $audit): string
    {
        return match ($audit->auditable_type) {
            User::class => 'Usuário · '.$this->resolveUserSubjectName($audit),
            Role::class => 'Perfil · '.$this->resolveRoleSubjectName($audit),
            default => class_basename((string) $audit->auditable_type).' #'.$audit->auditable_id,
        };
    }

    private function resolveUserSubjectName(Audit $audit): string
    {
        $name = $audit->new_values['name'] ?? $audit->old_values['name'] ?? null;

        if (is_string($name) && $name !== '') {
            return $name;
        }

        $user = User::query()->find($audit->auditable_id);

        return $user?->name ?? '#'.$audit->auditable_id;
    }

    private function resolveRoleSubjectName(Audit $audit): string
    {
        $name = $audit->new_values['name']
            ?? $audit->old_values['name']
            ?? $audit->new_values['role_name']
            ?? $audit->old_values['role_name']
            ?? null;

        if (is_string($name) && $name !== '') {
            return $this->roleLabel($name);
        }

        $role = Role::query()->find($audit->auditable_id);

        return $role !== null ? $this->roleLabel($role->name) : '#'.$audit->auditable_id;
    }

    /**
     * @return list<string>
     */
    private function detailsLabel(Audit $audit): string
    {
        $parts = [];

        foreach ($this->changedAttributes($audit) as $attribute => [$old, $new]) {
            $parts[] = match ($attribute) {
                'name' => $this->formatScalarChange('Nome', $old, $new),
                'email' => $this->formatScalarChange('E-mail', $old, $new),
                'permissions' => $this->formatPermissionChange($old, $new),
                'roles' => $this->formatRoleChange($old, $new),
                default => $this->formatScalarChange($this->fieldLabel($attribute), $old, $new),
            };
        }

        if ($parts === []) {
            return '—';
        }

        return implode(' · ', $parts);
    }

    /**
     * @return array<string, array{mixed, mixed}>
     */
    private function changedAttributes(Audit $audit): array
    {
        $oldValues = is_array($audit->old_values) ? $audit->old_values : [];
        $newValues = is_array($audit->new_values) ? $audit->new_values : [];
        $keys = array_unique([...array_keys($oldValues), ...array_keys($newValues)]);
        $changes = [];

        foreach ($keys as $key) {
            if (! is_string($key)) {
                continue;
            }

            $old = $oldValues[$key] ?? null;
            $new = $newValues[$key] ?? null;

            if ($old === $new) {
                continue;
            }

            $changes[$key] = [$old, $new];
        }

        return $changes;
    }

    private function formatScalarChange(string $label, mixed $old, mixed $new): string
    {
        if ($old === null && $new !== null) {
            return sprintf('%s: %s', $label, $this->stringify($new));
        }

        if ($new === null) {
            return sprintf('%s removido (era %s)', $label, $this->stringify($old));
        }

        return sprintf('%s: %s → %s', $label, $this->stringify($old), $this->stringify($new));
    }

    private function formatPermissionChange(mixed $old, mixed $new): string
    {
        $oldList = $this->normalizeStringList($old);
        $newList = $this->normalizeStringList($new);
        $added = array_values(array_diff($newList, $oldList));
        $removed = array_values(array_diff($oldList, $newList));

        $segments = [];

        if ($added !== []) {
            $segments[] = 'concedeu '.implode(', ', array_map(
                fn (string $permission): string => $this->permissionLabel($permission),
                $added,
            ));
        }

        if ($removed !== []) {
            $segments[] = 'revogou '.implode(', ', array_map(
                fn (string $permission): string => $this->permissionLabel($permission),
                $removed,
            ));
        }

        if ($segments === []) {
            return 'Permissões do perfil atualizadas';
        }

        return 'Permissões: '.implode('; ', $segments);
    }

    private function formatRoleChange(mixed $old, mixed $new): string
    {
        $oldList = array_map(fn (string $role): string => $this->roleLabel($role), $this->normalizeStringList($old));
        $newList = array_map(fn (string $role): string => $this->roleLabel($role), $this->normalizeStringList($new));

        return sprintf('Perfis: %s → %s', implode(', ', $oldList) ?: '—', implode(', ', $newList) ?: '—');
    }

    /**
     * @return list<string>
     */
    private function normalizeStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            $value,
            fn (mixed $item): bool => is_string($item) && $item !== '',
        ));
    }

    private function stringify(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'sim' : 'não';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '—';
    }

    private function fieldLabel(string $attribute): string
    {
        return match ($attribute) {
            'email_verified_at' => 'E-mail verificado em',
            'two_factor_confirmed_at' => '2FA confirmado em',
            default => ucfirst(str_replace('_', ' ', $attribute)),
        };
    }

    private function roleLabel(string $name): string
    {
        return match ($name) {
            'Administrator' => 'Administrador',
            'User' => 'Usuário',
            default => $name,
        };
    }

    private function permissionLabel(string $name): string
    {
        return match ($name) {
            'dashboard.sidebar' => 'Exibir painel na sidebar',
            'dashboard.view' => 'Visualizar página de painel',
            'users.sidebar' => 'Exibir usuários na sidebar',
            'users.view' => 'Visualizar página de usuários',
            'users.show' => 'Visualizar usuário',
            'users.create' => 'Cadastrar usuário',
            'users.update' => 'Editar usuário',
            'users.delete' => 'Excluir usuário',
            'permissions.sidebar' => 'Exibir permissões na sidebar',
            'permissions.view' => 'Visualizar página de permissões',
            'permissions.create' => 'Cadastrar perfil',
            'permissions.update' => 'Editar permissões dos perfis',
            'audits.sidebar' => 'Exibir auditoria na sidebar',
            'audits.view' => 'Visualizar página de auditoria',
            default => $name,
        };
    }
}

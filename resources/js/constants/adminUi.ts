/**
 * Portuguese UI labels for internal English role / permission names.
 */
export function roleLabel(name: string): string {
    const map: Record<string, string> = {
        Administrator: 'Administrador',
        User: 'Usuário',
    };

    return map[name] ?? name;
}

export function permissionLabel(name: string): string {
    const map: Record<string, string> = {
        'dashboard.access': 'Acessar painel administrativo',
        'users.manage': 'Gerenciar usuários',
        'permissions.manage': 'Gerenciar permissões dos perfis',
        'audits.view': 'Visualizar auditoria',
    };

    return map[name] ?? name;
}

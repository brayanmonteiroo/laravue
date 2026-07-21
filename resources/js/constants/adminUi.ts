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
        'dashboard.sidebar': 'Exibir painel na sidebar',
        'dashboard.view': 'Visualizar página de painel',
        'users.sidebar': 'Exibir usuários na sidebar',
        'users.view': 'Visualizar página de usuários',
        'users.show': 'Visualizar usuário',
        'users.create': 'Cadastrar usuário',
        'users.update': 'Editar usuário',
        'users.delete': 'Excluir usuário',
        'permissions.sidebar': 'Exibir permissões na sidebar',
        'permissions.view': 'Visualizar página de permissões',
        'permissions.create': 'Cadastrar perfil',
        'permissions.update': 'Editar permissões dos perfis',
        'audits.sidebar': 'Exibir auditoria na sidebar',
        'audits.view': 'Visualizar página de auditoria',
    };

    return map[name] ?? name;
}

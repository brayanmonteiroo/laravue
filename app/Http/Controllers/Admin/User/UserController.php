<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const int PER_PAGE = 10;

    /**
     * Colunas ordenáveis.
     *
     * @var list<string>
     */
    private const array SORTABLE = ['name', 'email', 'created_at'];

    /**
     * Lista todos os usuários cadastrados no sistema.
     */
    public function index(Request $request): Response
    {
        // Verifica se o usuário tem permissão para visualizar todos os usuários.
        $this->authorize('viewAny', User::class);

        // Valida os parâmetros de ordenação.
        $validated = $request->validate([
            'sort' => ['sometimes', 'nullable', Rule::in(self::SORTABLE)],
            'direction' => ['sometimes', 'nullable', Rule::in(['asc', 'desc'])],
        ]);

        // Define os parâmetros de ordenação padrão.
        $sort = $validated['sort'] ?? 'name';
        $direction = $validated['direction'] ?? 'asc';

        // Busca todos os usuários cadastrados no sistema.
        $users = User::query()
            ->with('roles:id,name')
            ->select(['id', 'name', 'email', 'email_verified_at', 'created_at'])
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
                'roles' => $user->roles->pluck('name')->values()->all(),
                'can_delete' => $request->user()?->getKey() !== $user->getKey(),
            ]);

        // Renderiza a view de listagem de usuários.
        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'sort' => [
                'column' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    /**
     * Exibe os detalhes de um usuário específico.
     */
    public function show(User $user): Response
    {
        // Verifica se o usuário tem permissão para visualizar o usuário específico.
        $this->authorize('view', $user);

        // Carrega os papéis do usuário.
        $user->load('roles:id,name');

        // Renderiza a view de detalhes do usuário.
        return Inertia::render('admin/users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
                'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
                'roles' => $user->roles->pluck('name')->values()->all(),
            ],
        ]);
    }

    /**
     * Exibe o formulário de criação de um novo usuário.
     */
    public function create(): Response
    {
        // Verifica se o usuário tem permissão para criar um novo usuário.
        $this->authorize('create', User::class);

        // Renderiza o formulário de criação de um novo usuário.
        return Inertia::render('admin/users/Create', [
            'availableRoles' => $this->availableRoles(),
        ]);
    }

    /**
     * Cria um novo usuário no sistema.
     */
    public function store(StoreUserRequest $request, AuditRecorder $auditRecorder): RedirectResponse
    {
        // Valida os dados do formulário.
        $validated = $request->validated();

        // Obtém os papéis do usuário.
        $roles = $validated['roles'] ?? [];
        // Remove o campo de papéis do array validado.
        unset($validated['roles']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->syncRoles($roles);

        if ($roles !== []) {
            $auditRecorder->record(
                auditable: $user,
                event: 'updated',
                oldValues: ['roles' => []],
                newValues: ['roles' => array_values($roles)],
            );
        }

        return to_route('admin.users.index')->with('flash', [
            'type' => 'success',
            'message' => __('User created successfully.'),
        ]);
    }

    /**
     * Exibe o formulário de edição de um usuário específico.
     */
    public function edit(User $user): Response
    {
        // Verifica se o usuário tem permissão para editar o usuário específico.
        $this->authorize('update', $user);

        // Carrega os papéis do usuário.
        $user->load('roles:id,name');

        // Renderiza o formulário de edição de um usuário específico.
        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->values()->all(),
            ],
            'availableRoles' => $this->availableRoles(),
        ]);
    }

    /**
     * Atualiza um usuário específico no sistema.
     */
    public function update(UpdateUserRequest $request, User $user, AuditRecorder $auditRecorder): RedirectResponse
    {
        // Valida os dados do formulário.
        $validated = $request->validated();

        // Obtém os papéis do usuário.
        $roles = $validated['roles'] ?? [];
        $oldRoles = $user->roles()->pluck('name')->sort()->values()->all();
        $newRoles = collect($roles)->sort()->values()->all();

        // Atualiza o nome do usuário.
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Atualiza a senha do usuário.
        if ($request->filled('password')) {
            $user->password = $validated['password'];
        }

        // Salva o usuário no sistema.
        $user->save();

        // Sincroniza os papéis do usuário.
        $user->syncRoles($roles);

        if ($oldRoles !== $newRoles) {
            $auditRecorder->record(
                auditable: $user,
                event: 'updated',
                oldValues: ['roles' => $oldRoles],
                newValues: ['roles' => $newRoles],
            );
        }

        return to_route('admin.users.index')->with('flash', [
            'type' => 'success',
            'message' => __('User updated successfully.'),
        ]);
    }

    /**
     * Remove um usuário específico do sistema.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->getKey()) {
            return to_route('admin.users.index')->with('flash', [
                'type' => 'warning',
                'message' => __('You cannot delete your own account.'),
            ]);
        }

        $this->authorize('delete', $user);

        $user->delete();

        return to_route('admin.users.index')->with('flash', [
            'type' => 'success',
            'message' => __('User deleted successfully.'),
        ]);
    }

    /**
     * Obtém os papéis disponíveis para serem atribuídos a um usuário.
     *
     * @return list<array{name: string}>
     */
    private function availableRoles(): array
    {
        // Busca todos os papéis disponíveis para serem atribuídos a um usuário.
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['name'])
            // Converte os papéis em um array de objetos.
            ->map(fn (Role $role) => ['name' => $role->name])
            ->values()
            ->all();
    }
}

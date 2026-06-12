<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Trash2, UserPlus } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import DataTable from '@/components/tables/DataTable.vue';
import { Button } from '@/components/ui/button';
import { roleLabel } from '@/constants/adminUi';
import { dashboard } from '@/routes/admin';
import {
    create as createRoute,
    edit as editRoute,
    index as indexRoute,
    show as showRoute,
} from '@/routes/admin/users';
import type { DataTableColumn } from '@/types/tables';

type UserRow = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string | null;
    roles: string[];
    can_delete: boolean;
};

type Props = {
    users: {
        data: UserRow[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    sort: {
        column: string;
        direction: 'asc' | 'desc';
    };
};

defineProps<Props>();

const deleteOpen = ref(false);
const userToDelete = ref<UserRow | null>(null);

watch(deleteOpen, (isOpen) => {
    if (!isOpen) {
        userToDelete.value = null;
    }
});

function openDeleteConfirm(user: UserRow): void {
    userToDelete.value = user;
    deleteOpen.value = true;
}

function confirmDeleteUser(): void {
    const user = userToDelete.value;

    if (!user) {
        return;
    }

    router.delete(UserController.destroy.url(user.id), {
        preserveScroll: true,
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Painel', href: dashboard() },
            { title: 'Usuários', href: indexRoute() },
        ],
    },
});

const tableColumns: DataTableColumn[] = [
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'email', label: 'E-mail', sortable: true },
    { key: 'roles', label: 'Perfis', sortable: false },
    {
        key: 'actions',
        label: 'Ações',
        sortable: false,
        align: 'right',
        headerClass: 'text-right',
    },
];

function formatRoles(roles: string[]): string {
    return roles.map((r) => roleLabel(r)).join(', ');
}

function rowAsUser(row: Record<string, unknown>): UserRow {
    return row as unknown as UserRow;
}
</script>

<template>
    <Head title="Usuários" />

    <ConfirmDialog
        v-model:open="deleteOpen"
        :title="
            userToDelete
                ? `Excluir ${userToDelete.name}?`
                : 'Excluir usuário?'
        "
        description="Esta ação não pode ser desfeita. O usuário será removido permanentemente."
        confirm-text="Excluir"
        cancel-text="Cancelar"
        confirm-variant="destructive"
        @confirm="confirmDeleteUser"
    />

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <Heading
                title="Usuários"
                description="Contas cadastradas no sistema"
            />
            <Button as-child class="shrink-0 sm:mt-1">
                <Link
                    :href="createRoute()"
                    class="inline-flex items-center gap-2"
                >
                    <UserPlus class="size-4" />
                    Novo usuário
                </Link>
            </Button>
        </div>

        <DataTable
            :columns="tableColumns"
            :paginator="users"
            :sort-column="sort.column"
            :sort-direction="sort.direction"
            :index-url="indexRoute.url()"
        >
            <template #default="{ rows }">
                <tr
                    v-for="row in rows"
                    :key="rowAsUser(row).id"
                    class="border-b border-sidebar-border/50 last:border-0 dark:border-sidebar-border/50"
                >
                    <td class="px-4 py-3">{{ rowAsUser(row).name }}</td>
                    <td class="px-4 py-3">{{ rowAsUser(row).email }}</td>
                    <td class="px-4 py-3">
                        <span
                            v-if="rowAsUser(row).roles.length === 0"
                            class="text-muted-foreground"
                            >—</span
                        >
                        <span v-else>{{
                            formatRoles(rowAsUser(row).roles)
                        }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            <Button variant="outline" size="icon-sm" as-child>
                                <Link
                                    :href="showRoute(rowAsUser(row).id)"
                                    :aria-label="`Visualizar ${rowAsUser(row).name}`"
                                >
                                    <Eye class="size-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" size="icon-sm" as-child>
                                <Link
                                    :href="editRoute(rowAsUser(row).id)"
                                    :aria-label="`Editar ${rowAsUser(row).name}`"
                                >
                                    <Pencil class="size-4" />
                                </Link>
                            </Button>
                            <Button
                                v-if="rowAsUser(row).can_delete"
                                type="button"
                                variant="destructive"
                                size="icon-sm"
                                :aria-label="`Excluir ${rowAsUser(row).name}`"
                                @click="openDeleteConfirm(rowAsUser(row))"
                            >
                                <Trash2 class="size-4" />
                            </Button>
                        </div>
                    </td>
                </tr>
            </template>
        </DataTable>
    </div>
</template>

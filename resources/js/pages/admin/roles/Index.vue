<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Shield, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import RoleController from '@/actions/App/Http/Controllers/Admin/Role/RoleController';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import DataTable from '@/components/tables/DataTable.vue';
import TableFilters from '@/components/tables/TableFilters.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { roleLabel } from '@/constants/adminUi';
import { PERMISSIONS } from '@/constants/permissions';
import { dashboard } from '@/routes/admin';
import { index as rolesIndex } from '@/routes/admin/roles';
import { edit as rolePermissionsEdit } from '@/routes/admin/roles/permissions';
import type {
    DataTableColumn,
    LaravelPaginator,
    TableFilterField,
} from '@/types/tables';

type RoleRow = {
    id: number;
    name: string;
    permissions_count: number;
    users_count: number;
    is_system: boolean;
    can_rename: boolean;
    can_delete: boolean;
};

type Props = {
    roles: LaravelPaginator<RoleRow>;
    sort: {
        column: string;
        direction: 'asc' | 'desc';
    };
    filters: {
        search: string;
        system: string;
        has_users: string;
    };
};

const page = usePage<Props>();

const props = defineProps<Props>();

const authPermissions = computed(() => page.props.auth.permissions ?? []);

const canCreate = computed(() =>
    authPermissions.value.includes(PERMISSIONS.permissionsCreate),
);
const canUpdate = computed(() =>
    authPermissions.value.includes(PERMISSIONS.permissionsUpdate),
);
const canViewPermissions = computed(() =>
    authPermissions.value.includes(PERMISSIONS.permissionsView),
);

const createDialogOpen = ref(false);
const renameDialogOpen = ref(false);
const roleToRename = ref<RoleRow | null>(null);
const deleteOpen = ref(false);
const roleToDelete = ref<RoleRow | null>(null);

watch(renameDialogOpen, (isOpen) => {
    if (!isOpen) {
        roleToRename.value = null;
    }
});

watch(deleteOpen, (isOpen) => {
    if (!isOpen) {
        roleToDelete.value = null;
    }
});

function openRenameDialog(role: RoleRow): void {
    roleToRename.value = role;
    renameDialogOpen.value = true;
}

function openDeleteConfirm(role: RoleRow): void {
    roleToDelete.value = role;
    deleteOpen.value = true;
}

function confirmDeleteRole(): void {
    const role = roleToDelete.value;

    if (!role) {
        return;
    }

    router.delete(RoleController.destroy.url(role.id), {
        preserveScroll: true,
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Painel', href: dashboard() },
            { title: 'Perfis', href: rolesIndex() },
        ],
    },
});

const tableColumns: DataTableColumn[] = [
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'permissions_count', label: 'Permissões', sortable: true },
    { key: 'users_count', label: 'Usuários', sortable: true },
    {
        key: 'actions',
        label: 'Ações',
        sortable: false,
        align: 'right',
        headerClass: 'text-right',
    },
];

const filterFields: TableFilterField[] = [
    {
        key: 'search',
        label: 'Busca',
        type: 'text',
        placeholder: 'Nome do perfil',
    },
    {
        key: 'system',
        label: 'Tipo',
        type: 'select',
        options: [
            { value: 'all', label: 'Todos' },
            { value: 'yes', label: 'Somente sistema' },
            { value: 'no', label: 'Somente personalizados' },
        ],
    },
    {
        key: 'has_users',
        label: 'Usuários vinculados',
        type: 'select',
        options: [
            { value: 'all', label: 'Todos' },
            { value: 'yes', label: 'Com usuários' },
            { value: 'no', label: 'Sem usuários' },
        ],
    },
];

function rowAsRole(row: Record<string, unknown>): RoleRow {
    return row as unknown as RoleRow;
}
</script>

<template>
    <Head title="Perfis" />

    <ConfirmDialog
        v-model:open="deleteOpen"
        :title="
            roleToDelete
                ? `Excluir ${roleLabel(roleToDelete.name)}?`
                : 'Excluir perfil?'
        "
        description="Esta ação não pode ser desfeita. O perfil será removido permanentemente."
        confirm-text="Excluir"
        cancel-text="Cancelar"
        confirm-variant="destructive"
        @confirm="confirmDeleteRole"
    />

    <div class="space-y-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <Heading
                title="Perfis"
                description="Gerencie os perfis de acesso e suas permissões."
            />

            <Dialog v-if="canCreate" v-model:open="createDialogOpen">
                <DialogTrigger as-child>
                    <Button
                        type="button"
                        class="w-full shrink-0 sm:mt-1 sm:w-auto"
                    >
                        <Plus class="size-4" />
                        Novo perfil
                    </Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Novo perfil</DialogTitle>
                        <DialogDescription>
                            O nome interno é em inglês (ex.: Editor). Depois você
                            pode atribuir permissões a este perfil.
                        </DialogDescription>
                    </DialogHeader>
                    <Form
                        v-bind="RoleController.store.form()"
                        class="space-y-4"
                        reset-on-success
                        v-slot="{ errors, processing }"
                        @success="createDialogOpen = false"
                    >
                        <div class="space-y-2">
                            <Label for="new-role-name">Nome do perfil</Label>
                            <Input
                                id="new-role-name"
                                name="name"
                                type="text"
                                autocomplete="off"
                                placeholder="Ex.: Editor"
                                required
                            />
                            <InputError :message="errors.name" />
                        </div>
                        <DialogFooter class="gap-2 sm:gap-0">
                            <Button
                                type="button"
                                variant="outline"
                                @click="createDialogOpen = false"
                            >
                                Cancelar
                            </Button>
                            <Button type="submit" :disabled="processing">
                                Criar perfil
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>

        <Dialog v-model:open="renameDialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Renomear perfil</DialogTitle>
                    <DialogDescription>
                        Altere o nome interno do perfil (em inglês).
                    </DialogDescription>
                </DialogHeader>
                <Form
                    v-if="roleToRename"
                    :key="roleToRename.id"
                    v-bind="RoleController.update.form(roleToRename.id)"
                    class="space-y-4"
                    v-slot="{ errors, processing }"
                    @success="renameDialogOpen = false"
                >
                    <div class="space-y-2">
                        <Label for="rename-role-name">Nome do perfil</Label>
                        <Input
                            id="rename-role-name"
                            name="name"
                            type="text"
                            autocomplete="off"
                            :default-value="roleToRename.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <DialogFooter class="gap-2 sm:gap-0">
                        <Button
                            type="button"
                            variant="outline"
                            @click="renameDialogOpen = false"
                        >
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="processing">
                            Salvar
                        </Button>
                    </DialogFooter>
                </Form>
            </DialogContent>
        </Dialog>

        <TableFilters
            :index-url="rolesIndex.url()"
            :fields="filterFields"
            :filters="filters"
            :preserve="{
                sort: sort.column,
                direction: sort.direction,
            }"
        />

        <DataTable
            :columns="tableColumns"
            :paginator="roles"
            :sort-column="sort.column"
            :sort-direction="sort.direction"
            :index-url="rolesIndex.url()"
            empty-message="Nenhum perfil encontrado."
        >
            <template #default="{ rows }">
                <tr
                    v-for="row in rows"
                    :key="rowAsRole(row).id"
                    class="border-b border-sidebar-border/50 last:border-0 dark:border-sidebar-border/50"
                >
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-0.5">
                            <span class="font-medium">
                                {{ roleLabel(rowAsRole(row).name) }}
                            </span>
                            <span
                                v-if="rowAsRole(row).is_system"
                                class="text-xs text-muted-foreground"
                            >
                                Perfil de sistema
                            </span>
                            <span
                                v-else-if="
                                    roleLabel(rowAsRole(row).name) !==
                                    rowAsRole(row).name
                                "
                                class="text-xs text-muted-foreground"
                            >
                                {{ rowAsRole(row).name }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 tabular-nums">
                        {{ rowAsRole(row).permissions_count }}
                    </td>
                    <td class="px-4 py-3 tabular-nums">
                        {{ rowAsRole(row).users_count }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            <Button
                                v-if="canViewPermissions"
                                variant="outline"
                                size="icon-sm"
                                as-child
                            >
                                <Link
                                    :href="
                                        rolePermissionsEdit(rowAsRole(row).id)
                                    "
                                    :aria-label="`Permissões de ${roleLabel(rowAsRole(row).name)}`"
                                >
                                    <Shield class="size-4" />
                                </Link>
                            </Button>
                            <Button
                                v-if="canUpdate && rowAsRole(row).can_rename"
                                type="button"
                                variant="outline"
                                size="icon-sm"
                                :aria-label="`Renomear ${roleLabel(rowAsRole(row).name)}`"
                                @click="openRenameDialog(rowAsRole(row))"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <Button
                                v-if="canUpdate && rowAsRole(row).can_delete"
                                type="button"
                                variant="destructive"
                                size="icon-sm"
                                :aria-label="`Excluir ${roleLabel(rowAsRole(row).name)}`"
                                @click="openDeleteConfirm(rowAsRole(row))"
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

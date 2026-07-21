<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Shield, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import RoleController from '@/actions/App/Http/Controllers/Admin/Role/RoleController';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
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
    roles: RoleRow[];
};

const page = usePage<Props>();

const roles = computed(() => page.props.roles);
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
    if (! isOpen) {
        roleToRename.value = null;
    }
});

watch(deleteOpen, (isOpen) => {
    if (! isOpen) {
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

    if (! role) {
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
                    <Button type="button" class="w-full shrink-0 sm:mt-1 sm:w-auto">
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

        <div
            v-if="roles.length === 0"
            class="rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center text-sm text-muted-foreground dark:border-sidebar-border"
        >
            Nenhum perfil cadastrado. Crie um perfil para começar.
        </div>

        <div
            v-else
            class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-sidebar-border/70 bg-muted/40 dark:border-sidebar-border"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Nome</th>
                            <th class="px-4 py-3 font-medium">Permissões</th>
                            <th class="px-4 py-3 font-medium">Usuários</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="role in roles"
                            :key="role.id"
                            class="border-b border-sidebar-border/50 last:border-0 dark:border-sidebar-border/50"
                        >
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-medium">
                                        {{ roleLabel(role.name) }}
                                    </span>
                                    <span
                                        v-if="role.is_system"
                                        class="text-xs text-muted-foreground"
                                    >
                                        Perfil de sistema
                                    </span>
                                    <span
                                        v-else-if="
                                            roleLabel(role.name) !== role.name
                                        "
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ role.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ role.permissions_count }}
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ role.users_count }}
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
                                                rolePermissionsEdit(role.id)
                                            "
                                            :aria-label="`Permissões de ${roleLabel(role.name)}`"
                                        >
                                            <Shield class="size-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        v-if="canUpdate && role.can_rename"
                                        type="button"
                                        variant="outline"
                                        size="icon-sm"
                                        :aria-label="`Renomear ${roleLabel(role.name)}`"
                                        @click="openRenameDialog(role)"
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        v-if="canUpdate && role.can_delete"
                                        type="button"
                                        variant="destructive"
                                        size="icon-sm"
                                        :aria-label="`Excluir ${roleLabel(role.name)}`"
                                        @click="openDeleteConfirm(role)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

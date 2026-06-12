<script setup lang="ts">
import { Form, Head, useForm, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Plus, Save, Settings2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import RolePermissionController from '@/actions/App/Http/Controllers/Admin/RolePermissionController';
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
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { permissionLabel, roleLabel } from '@/constants/adminUi';
import { dashboard } from '@/routes/admin';
import { index as permissionsIndex } from '@/routes/admin/permissions';

type RoleRow = {
    id: number;
    name: string;
    permissions: string[];
};

type PermissionGroup = {
    label: string;
    description: string;
    permissions: string[];
};

type Props = {
    roles: RoleRow[];
    permissionGroups: PermissionGroup[];
};

const page = usePage<Props>();

const roles = computed(() => page.props.roles);
const permissionGroups = computed(() => page.props.permissionGroups);

const selectedRoleId = ref<number | null>(null);
const createDialogOpen = ref(false);
const selectNewestAfterCreate = ref(false);

const selectedRoleIdModel = computed({
    get(): string | undefined {
        return selectedRoleId.value !== null
            ? String(selectedRoleId.value)
            : undefined;
    },
    set(v: string | undefined) {
        if (v === undefined || v === '') {
            selectedRoleId.value = null;

            return;
        }

        selectedRoleId.value = Number.parseInt(v, 10);
    },
});

const syncForm = useForm({
    role_id: 0,
    permissions: [] as string[],
});

function loadRoleIntoForm(): void {
    const id = selectedRoleId.value;

    if (id === null) {
        return;
    }

    const role = roles.value.find((r) => r.id === id);

    if (! role) {
        return;
    }

    syncForm.role_id = role.id;
    syncForm.permissions = [...role.permissions];
}

watch(selectedRoleId, () => {
    loadRoleIntoForm();
});

watch(
    roles,
    () => {
        if (selectNewestAfterCreate.value && roles.value.length > 0) {
            const maxId = Math.max(...roles.value.map((r) => r.id));
            selectedRoleId.value = maxId;
            selectNewestAfterCreate.value = false;
        }

        if (
            selectedRoleId.value !== null
            && ! roles.value.some((r) => r.id === selectedRoleId.value)
        ) {
            selectedRoleId.value = roles.value[0]?.id ?? null;
        }

        if (roles.value.length > 0 && selectedRoleId.value === null) {
            selectedRoleId.value = roles.value[0].id;
        }

        loadRoleIntoForm();
    },
    { deep: true, immediate: true },
);

function isPermEnabled(perm: string): boolean {
    return syncForm.permissions.includes(perm);
}

function setPerm(perm: string, enabled: boolean): void {
    const next = new Set(syncForm.permissions);

    if (enabled) {
        next.add(perm);
    } else {
        next.delete(perm);
    }

    syncForm.permissions = Array.from(next);
}

function onPermModelUpdate(perm: string, value: boolean | string): void {
    setPerm(perm, Boolean(value));
}

function submitPermissions(): void {
    syncForm.put(RolePermissionController.update.url(), {
        preserveScroll: true,
    });
}

function onRoleCreated(): void {
    createDialogOpen.value = false;
    selectNewestAfterCreate.value = true;
}

function groupIcon(label: string) {
    return label === 'Menu' ? LayoutGrid : Settings2;
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Painel', href: dashboard() },
            { title: 'Permissões', href: permissionsIndex() },
        ],
    },
});
</script>

<template>
    <Head title="Permissões por perfil" />

    <div class="w-full space-y-8">
        <Heading
            title="Permissões por perfil"
            description="As permissões refletem o menu lateral: Menu principal e Configurações. Escolha um perfil, ajuste os interruptores e salve."
        />

        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[12rem] flex-1 space-y-2">
                <Label id="role-select-label">Perfil</Label>
                <Select
                    v-model="selectedRoleIdModel"
                    :disabled="roles.length === 0"
                >
                    <SelectTrigger
                        id="role-select"
                        class="h-9 w-full md:w-80"
                        aria-labelledby="role-select-label"
                    >
                        <SelectValue placeholder="Selecione um perfil" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Perfis</SelectLabel>
                            <SelectItem
                                v-for="role in roles"
                                :key="role.id"
                                :value="String(role.id)"
                            >
                                {{ roleLabel(role.name) }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>

            <Dialog v-model:open="createDialogOpen">
                <DialogTrigger as-child>
                    <Button type="button" variant="outline" class="shrink-0">
                        <Plus class="size-4" />
                        Novo perfil
                    </Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Novo perfil</DialogTitle>
                        <DialogDescription>
                            O nome interno é em inglês (ex.: Editor). Depois
                            você pode atribuir permissões a este perfil.
                        </DialogDescription>
                    </DialogHeader>
                    <Form
                        v-bind="RolePermissionController.storeRole.form()"
                        class="space-y-4"
                        reset-on-success
                        v-slot="{ errors, processing }"
                        @success="onRoleCreated"
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

        <div
            v-if="roles.length === 0"
            class="rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center text-sm text-muted-foreground dark:border-sidebar-border"
        >
            Nenhum perfil cadastrado. Crie um perfil para começar.
        </div>

        <form
            v-else
            class="space-y-6"
            @submit.prevent="submitPermissions"
        >
            <div class="space-y-4">
                <section
                    v-for="group in permissionGroups"
                    :key="group.label"
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div
                        class="flex items-start gap-3 border-b border-sidebar-border/70 bg-muted/30 px-4 py-3 dark:border-sidebar-border"
                    >
                        <component
                            :is="groupIcon(group.label)"
                            class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                        />
                        <div class="min-w-0 space-y-1">
                            <p class="text-sm font-medium">
                                {{ group.label }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ group.description }}
                            </p>
                        </div>
                    </div>

                    <div class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        <div
                            v-for="perm in group.permissions"
                            :key="perm"
                            class="flex items-center justify-between gap-4 px-4 py-3"
                        >
                            <Label
                                :for="`perm-${perm}`"
                                class="min-w-0 flex-1 cursor-pointer text-sm font-normal leading-snug"
                            >
                                {{ permissionLabel(perm) }}
                            </Label>
                            <Switch
                                :id="`perm-${perm}`"
                                class="shrink-0"
                                :model-value="isPermEnabled(perm)"
                                @update:model-value="
                                    (v) => onPermModelUpdate(perm, v)
                                "
                            />
                        </div>
                    </div>
                </section>
            </div>

            <InputError :message="syncForm.errors.role_id" />
            <InputError :message="syncForm.errors.permissions" />

            <Button type="submit" :disabled="syncForm.processing">
                <Save class="size-4" />
                Salvar permissões deste perfil
            </Button>
        </form>
    </div>
</template>

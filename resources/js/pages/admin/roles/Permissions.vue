<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ChevronRight,
    History,
    LayoutGrid,
    Save,
    Settings2,
    Shield,
    Users,
} from 'lucide-vue-next';
import { computed, watch, watchEffect } from 'vue';
import type { Component } from 'vue';
import RolePermissionController from '@/actions/App/Http/Controllers/Admin/RolePermission/RolePermissionController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { permissionLabel, roleLabel } from '@/constants/adminUi';
import { PERMISSIONS } from '@/constants/permissions';
import { dashboard } from '@/routes/admin';
import { index as rolesIndex } from '@/routes/admin/roles';
import { edit as rolePermissionsEdit } from '@/routes/admin/roles/permissions';

type RoleProp = {
    id: number;
    name: string;
    permissions: string[];
    is_system: boolean;
};

type PermissionSection = {
    label: string;
    permissions: string[];
};

type PermissionGroup = {
    label: string;
    description: string;
    sections: PermissionSection[];
};

type Props = {
    role: RoleProp;
    permissionGroups: PermissionGroup[];
};

const page = usePage<Props>();

const role = computed(() => page.props.role);
const permissionGroups = computed(() => page.props.permissionGroups);
const authPermissions = computed(() => page.props.auth.permissions ?? []);

const canUpdatePermissions = computed(() =>
    authPermissions.value.includes(PERMISSIONS.permissionsUpdate),
);

const syncForm = useForm({
    permissions: [...page.props.role.permissions],
});

watch(
    () => page.props.role,
    (next) => {
        syncForm.defaults({ permissions: [...next.permissions] });
        syncForm.reset();
    },
    { deep: true },
);

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Painel', href: dashboard() },
            { title: 'Perfis', href: rolesIndex() },
            {
                title: 'Permissões',
                href: rolePermissionsEdit(role.value.id),
            },
        ],
    });
});


function sectionPermissions(section: PermissionSection): string[] {
    return section.permissions;
}

function groupPermissions(group: PermissionGroup): string[] {
    return group.sections.flatMap((section) => section.permissions);
}

const allPermissions = computed((): string[] =>
    permissionGroups.value.flatMap((group) => groupPermissions(group)),
);

function isPermEnabled(perm: string): boolean {
    return syncForm.permissions.includes(perm);
}

function enabledCount(perms: string[]): number {
    return perms.filter((perm) => isPermEnabled(perm)).length;
}

function areAllEnabled(perms: string[]): boolean {
    return perms.length > 0 && perms.every((perm) => isPermEnabled(perm));
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

function togglePermissions(perms: string[]): void {
    const next = new Set(syncForm.permissions);

    if (areAllEnabled(perms)) {
        for (const perm of perms) {
            next.delete(perm);
        }
    } else {
        for (const perm of perms) {
            next.add(perm);
        }
    }

    syncForm.permissions = Array.from(next);
}

function toggleLabel(perms: string[]): string {
    return areAllEnabled(perms) ? 'Desmarcar todas' : 'Marcar todas';
}

function onCheckboxUpdate(perm: string, value: boolean | 'indeterminate'): void {
    setPerm(perm, value === true);
}

function submitPermissions(): void {
    syncForm.put(RolePermissionController.update.url(role.value.id), {
        preserveScroll: true,
    });
}

function groupIcon(label: string): Component {
    return label === 'Menu' ? LayoutGrid : Settings2;
}

function sectionIcon(label: string): Component {
    const icons: Record<string, Component> = {
        Painel: LayoutGrid,
        Usuários: Users,
        Permissões: Shield,
        Auditoria: History,
    };

    return icons[label] ?? Settings2;
}
</script>

<template>
    <Head :title="`Permissões — ${roleLabel(role.name)}`" />

    <div class="w-full space-y-8">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <Heading
                :title="`Permissões — ${roleLabel(role.name)}`"
                description="Marque as permissões desejadas para este perfil e salve."
            />
            <Button
                variant="outline"
                as-child
                class="w-full shrink-0 sm:mt-1 sm:w-auto"
            >
                <Link
                    :href="rolesIndex()"
                    class="inline-flex items-center gap-2"
                >
                    <ArrowLeft class="size-4" />
                    Voltar
                </Link>
            </Button>
        </div>

        <form class="space-y-6" @submit.prevent="submitPermissions">
            <div
                v-if="canUpdatePermissions"
                class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    {{ enabledCount(allPermissions) }} de
                    {{ allPermissions.length }} permissões marcadas
                </p>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="w-full sm:w-auto"
                    @click="togglePermissions(allPermissions)"
                >
                    {{ toggleLabel(allPermissions) }}
                </Button>
            </div>

            <div class="space-y-3 sm:space-y-4">
                <Collapsible
                    v-for="group in permissionGroups"
                    :key="group.label"
                    v-slot="{ open }"
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div
                        class="flex flex-col gap-2 bg-muted/30 p-2 sm:flex-row sm:items-center sm:gap-2 sm:px-3 sm:py-2"
                        :class="{
                            'border-b border-sidebar-border/70 dark:border-sidebar-border':
                                open,
                        }"
                    >
                        <CollapsibleTrigger
                            class="flex min-w-0 flex-1 items-start gap-2 rounded-lg px-2 py-1.5 text-left transition-colors hover:bg-muted/50 sm:gap-3"
                        >
                            <ChevronRight
                                class="mt-0.5 size-4 shrink-0 text-muted-foreground transition-transform"
                                :class="{ 'rotate-90': open }"
                            />
                            <component
                                :is="groupIcon(group.label)"
                                class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                            />
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium">
                                        {{ group.label }}
                                    </p>
                                    <span
                                        class="text-xs text-muted-foreground tabular-nums"
                                    >
                                        {{
                                            enabledCount(
                                                groupPermissions(group),
                                            )
                                        }}/{{
                                            groupPermissions(group).length
                                        }}
                                    </span>
                                </div>
                                <p
                                    class="hidden text-xs text-muted-foreground sm:block"
                                >
                                    {{ group.description }}
                                </p>
                            </div>
                        </CollapsibleTrigger>

                        <Button
                            v-if="canUpdatePermissions"
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="w-full shrink-0 sm:w-auto"
                            @click="
                                togglePermissions(groupPermissions(group))
                            "
                        >
                            {{ toggleLabel(groupPermissions(group)) }}
                        </Button>
                    </div>

                    <CollapsibleContent>
                        <div
                            class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border"
                        >
                            <div
                                v-for="section in group.sections"
                                :key="section.label"
                                class="space-y-3 p-3 sm:p-4"
                            >
                                <div
                                    class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
                                >
                                    <div class="flex min-w-0 items-center gap-2">
                                        <component
                                            :is="sectionIcon(section.label)"
                                            class="size-3.5 shrink-0 text-muted-foreground"
                                        />
                                        <p class="text-sm font-medium">
                                            {{ section.label }}
                                        </p>
                                        <span
                                            class="text-xs text-muted-foreground tabular-nums"
                                        >
                                            {{
                                                enabledCount(
                                                    sectionPermissions(
                                                        section,
                                                    ),
                                                )
                                            }}/{{
                                                sectionPermissions(section)
                                                    .length
                                            }}
                                        </span>
                                    </div>

                                    <Button
                                        v-if="canUpdatePermissions"
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="w-full sm:w-auto"
                                        @click="
                                            togglePermissions(
                                                sectionPermissions(section),
                                            )
                                        "
                                    >
                                        {{
                                            toggleLabel(
                                                sectionPermissions(section),
                                            )
                                        }}
                                    </Button>
                                </div>

                                <div
                                    class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3 xl:grid-cols-3"
                                >
                                    <label
                                        v-for="perm in section.permissions"
                                        :key="perm"
                                        :for="`perm-${perm}`"
                                        class="flex min-h-10 cursor-pointer items-start gap-3 rounded-lg border border-sidebar-border/50 px-3 py-2.5 dark:border-sidebar-border/50"
                                        :class="{
                                            'pointer-events-none opacity-50':
                                                !canUpdatePermissions,
                                        }"
                                    >
                                        <Checkbox
                                            :id="`perm-${perm}`"
                                            class="mt-0.5"
                                            :model-value="isPermEnabled(perm)"
                                            :disabled="!canUpdatePermissions"
                                            @update:model-value="
                                                (
                                                    value:
                                                        | boolean
                                                        | 'indeterminate',
                                                ) =>
                                                    onCheckboxUpdate(
                                                        perm,
                                                        value,
                                                    )
                                            "
                                        />
                                        <span
                                            class="min-w-0 flex-1 text-sm leading-snug font-normal break-words text-muted-foreground"
                                        >
                                            {{ permissionLabel(perm) }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </CollapsibleContent>
                </Collapsible>
            </div>

            <InputError :message="syncForm.errors.permissions" />

            <div
                v-if="canUpdatePermissions"
                class="flex justify-end"
            >
                <Button
                    type="submit"
                    class="w-full sm:w-auto"
                    :disabled="syncForm.processing"
                >
                    <Save class="size-4" />
                    Salvar permissões
                </Button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { History, LayoutGrid, Shield, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { PERMISSIONS } from '@/constants/permissions';
import { home } from '@/routes';
import { index as auditsIndex } from '@/routes/admin/audits';
import { dashboard } from '@/routes/admin';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as usersIndex } from '@/routes/admin/users';
import type { NavGroup } from '@/types';

const page = usePage();
const permissions = computed(() => page.props.auth.permissions ?? []);

const showDashboardInMenu = computed(() =>
    permissions.value.includes(PERMISSIONS.dashboardSidebar),
);

const logoHref = computed(() =>
    permissions.value.includes(PERMISSIONS.dashboardView) ? dashboard() : home(),
);

const navGroups = computed((): NavGroup[] => {
    const menuItems = [];

    if (showDashboardInMenu.value) {
        menuItems.push({
            title: 'Painel',
            href: dashboard(),
            icon: LayoutGrid,
        });
    }

    const configItems = [];

    if (permissions.value.includes(PERMISSIONS.usersSidebar)) {
        configItems.push({
            title: 'Usuários',
            href: usersIndex(),
            icon: Users,
        });
    }

    if (permissions.value.includes(PERMISSIONS.permissionsSidebar)) {
        configItems.push({
            title: 'Perfis',
            href: rolesIndex(),
            icon: Shield,
        });
    }

    if (permissions.value.includes(PERMISSIONS.auditsSidebar)) {
        configItems.push({
            title: 'Auditoria',
            href: auditsIndex(),
            icon: History,
        });
    }

    return [
        { label: 'Menu', items: menuItems },
        { label: 'Configurações', items: configItems },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="logoHref">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

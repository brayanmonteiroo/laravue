<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { History, LayoutGrid, Menu, Search, Shield, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppearanceHeaderControls from '@/components/AppearanceHeaderControls.vue';
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { PERMISSIONS } from '@/constants/permissions';
import { home } from '@/routes';
import { index as auditsIndex } from '@/routes/admin/audits';
import { dashboard } from '@/routes/admin';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as usersIndex } from '@/routes/admin/users';
import type { BreadcrumbItem, NavGroup } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const permissions = computed(() => page.props.auth.permissions ?? []);

const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const activeItemStyles =
    'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100';

function navItemActive(
    href: NonNullable<Parameters<typeof isCurrentUrl>[0]>,
): boolean {
    return isCurrentUrl(href) || isCurrentOrParentUrl(href);
}

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

const desktopNavItems = computed(() =>
    navGroups.value.flatMap((g) => g.items),
);
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9"
                            >
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon
                                    class="size-6 fill-current text-black dark:text-white"
                                />
                            </SheetHeader>
                            <div
                                class="flex h-full flex-1 flex-col justify-between space-y-4 py-6"
                            >
                                <nav class="-mx-3 space-y-4">
                                    <template
                                        v-for="group in navGroups"
                                        :key="group.label"
                                    >
                                        <div
                                            v-if="group.items.length > 0"
                                            class="space-y-1"
                                        >
                                            <p
                                                class="px-3 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                            >
                                                {{ group.label }}
                                            </p>
                                            <Link
                                                v-for="item in group.items"
                                                :key="item.title"
                                                :href="item.href"
                                                class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                                :class="
                                                    navItemActive(item.href)
                                                        ? activeItemStyles
                                                        : ''
                                                "
                                            >
                                                <component
                                                    v-if="item.icon"
                                                    :is="item.icon"
                                                    class="h-5 w-5"
                                                />
                                                {{ item.title }}
                                            </Link>
                                        </div>
                                    </template>
                                </nav>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="logoHref" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList
                            class="flex h-full items-stretch space-x-2"
                        >
                            <NavigationMenuItem
                                v-for="(item, index) in desktopNavItems"
                                :key="index"
                                class="relative flex h-full items-center"
                            >
                                <Link
                                    :class="[
                                        navigationMenuTriggerStyle(),
                                        navItemActive(item.href)
                                            ? activeItemStyles
                                            : '',
                                        'h-9 cursor-pointer px-3',
                                    ]"
                                    :href="item.href"
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="mr-2 h-4 w-4"
                                    />
                                    {{ item.title }}
                                </Link>
                                <div
                                    v-if="navItemActive(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center gap-2">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="group h-9 w-9 cursor-pointer"
                    >
                        <Search
                            class="size-5 opacity-80 group-hover:opacity-100"
                        />
                    </Button>

                    <AppearanceHeaderControls />

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar
                                    class="size-8 overflow-hidden rounded-full"
                                >
                                    <AvatarImage
                                        v-if="auth.user?.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent
                                v-if="auth.user"
                                :user="auth.user"
                            />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>

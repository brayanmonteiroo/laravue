<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { roleLabel } from '@/constants/adminUi';
import { dashboard } from '@/routes/admin';
import { edit as editRoute, index as indexRoute } from '@/routes/admin/users';

type UserShow = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string | null;
    two_factor_enabled: boolean;
    roles: string[];
};

type Props = {
    user: UserShow;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Painel', href: dashboard() },
            { title: 'Usuários', href: indexRoute() },
        ],
    },
});

function formatDateTime(iso: string | null): string {
    if (!iso) {
        return '—';
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(iso));
}

function formatRoles(roles: string[]): string {
    if (roles.length === 0) {
        return 'Nenhum perfil atribuído';
    }

    return roles.map((r) => roleLabel(r)).join(', ');
}
</script>

<template>
    <Head :title="`${user.name} · Usuário`" />

    <div class="w-full space-y-8">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <Heading
                :title="user.name"
                description="Visualização somente leitura. Alterações são feitas em Editar."
            />
            <div class="flex shrink-0 flex-wrap gap-2 sm:mt-1">
                <Button variant="outline" as-child>
                    <Link
                        :href="indexRoute()"
                        class="inline-flex items-center gap-2"
                    >
                        <ArrowLeft class="size-4" />
                        Voltar
                    </Link>
                </Button>
                <Button as-child>
                    <Link
                        :href="editRoute(user.id)"
                        class="inline-flex items-center gap-2"
                    >
                        <Pencil class="size-4" />
                        Editar
                    </Link>
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Dados da conta</CardTitle>
                <CardDescription>
                    Informações cadastrais e segurança básica.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4 text-sm">
                <div class="grid gap-1">
                    <span class="text-muted-foreground">E-mail</span>
                    <span class="font-medium">{{ user.email }}</span>
                </div>
                <div class="grid gap-1">
                    <span class="text-muted-foreground">E-mail verificado</span>
                    <span class="font-medium">{{
                        user.email_verified_at ? 'Sim' : 'Não'
                    }}</span>
                </div>
                <div class="grid gap-1">
                    <span class="text-muted-foreground">Cadastrado em</span>
                    <span class="font-medium">{{
                        formatDateTime(user.created_at)
                    }}</span>
                </div>
                <div class="grid gap-1">
                    <span class="text-muted-foreground"
                        >Autenticação em dois fatores</span
                    >
                    <span class="font-medium">{{
                        user.two_factor_enabled ? 'Ativada' : 'Desativada'
                    }}</span>
                </div>
                <div class="grid gap-1">
                    <span class="text-muted-foreground">Perfis</span>
                    <span class="font-medium">{{
                        formatRoles(user.roles)
                    }}</span>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

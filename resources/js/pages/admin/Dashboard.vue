<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BarChart3, Layers, Shield, Users } from 'lucide-vue-next';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes/admin';

type Props = {
    userCount: number;
    roleCount: number;
};

defineProps<Props>();

/** Placeholders até existirem métricas reais nos outros cards. */
const placeholderStats = {
    alpha: 12,
    beta: 94,
} as const;

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Painel',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Painel" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Painel</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Resumo do sistema; os dois últimos indicadores ainda são
                exemplos.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card class="gap-0 py-0 shadow-sm">
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pt-6 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        Usuários
                    </CardTitle>
                    <Users
                        class="size-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                </CardHeader>
                <CardContent class="pb-6">
                    <p class="text-3xl font-bold tabular-nums">
                        {{ userCount }}
                    </p>
                    <CardDescription class="mt-1">
                        Total cadastrado
                    </CardDescription>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0 shadow-sm">
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pt-6 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        Perfis
                    </CardTitle>
                    <Shield
                        class="size-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                </CardHeader>
                <CardContent class="pb-6">
                    <p class="text-3xl font-bold tabular-nums">
                        {{ roleCount }}
                    </p>
                    <CardDescription class="mt-1">
                        Perfis no sistema
                    </CardDescription>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0 shadow-sm">
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pt-6 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        Indicador A
                    </CardTitle>
                    <Layers
                        class="size-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                </CardHeader>
                <CardContent class="pb-6">
                    <p class="text-3xl font-bold tabular-nums">
                        {{ placeholderStats.alpha }}
                    </p>
                    <CardDescription class="mt-1">
                        Placeholder para métrica futura
                    </CardDescription>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0 shadow-sm">
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pt-6 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        Indicador B
                    </CardTitle>
                    <BarChart3
                        class="size-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                </CardHeader>
                <CardContent class="pb-6">
                    <p class="text-3xl font-bold tabular-nums">
                        {{ placeholderStats.beta }}
                    </p>
                    <CardDescription class="mt-1">
                        Placeholder para métrica futura
                    </CardDescription>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

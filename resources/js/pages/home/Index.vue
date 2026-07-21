<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Bell,
    Calendar,
    FileText,
    Megaphone,
    Newspaper,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home, login } from '@/routes';

const page = usePage();
const appName = computed(() => (page.props.name as string) || 'Laravue');

/** Home pública: sem shell admin nem estado de sessão na interface. */
defineOptions({
    layout: () => null,
});

const mockDestaques = [
    {
        titulo: 'Campanha de vacinação 2026',
        resumo: 'Cronograma e locais divulgados pela coordenação de saúde ocupacional.',
    },
    {
        titulo: 'Novo portal de formulários',
        resumo: 'Atualização prevista para o fim do mês; equipes serão avisadas por e-mail.',
    },
    {
        titulo: 'Treinamento obrigatório LGPD',
        resumo: 'Módulo online disponível no ambiente de capacitação (conteúdo de exemplo).',
    },
] as const;

const mockNoticias = [
    {
        titulo: 'Reunião de equipe',
        corpo: 'Próxima segunda, 9h — link será divulgado pelo RH.',
        meta: 'Publicado em 2 de abril de 2026 · Comunicação',
    },
    {
        titulo: 'Manutenção programada',
        corpo: 'Sistemas internos — janela prevista para domingo à noite.',
        meta: 'Publicado em 1 de abril de 2026 · TI',
    },
] as const;

const mockDocumentos = [
    { nome: 'Política de uso aceitável.pdf', tipo: 'PDF' },
    { nome: 'Manual do colaborador 2026.pdf', tipo: 'PDF' },
    { nome: 'Fluxo de férias.docx', tipo: 'DOC' },
] as const;
</script>

<template>
    <Head title="Início" />

    <div class="min-h-svh bg-background">
        <header
            class="flex items-center justify-between gap-4 border-b border-border px-4 py-3 sm:px-6"
        >
            <Link
                :href="home()"
                class="flex items-center gap-2 font-semibold text-foreground"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-md bg-primary text-primary-foreground"
                >
                    <AppLogoIcon class="size-5 fill-current" />
                </span>
                <span class="truncate">{{ appName }}</span>
            </Link>
            <nav class="flex items-center gap-2">
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="login()">Entrar</Link>
                </Button>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6">
            <div class="space-y-3">
                <p
                    class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground"
                >
                    <Newspaper class="size-4" aria-hidden="true" />
                    Conteúdo institucional
                </p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                    Bem-vindo
                </h1>
                <p class="max-w-2xl text-muted-foreground">
                    Esta página simula o que virá de um CMS: comunicados,
                    arquivos e agenda. O mesmo conteúdo é exibido para todos os
                    visitantes.
                </p>
            </div>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">
                    Em destaque
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="(item, i) in mockDestaques"
                        :key="i"
                        class="border-border/80"
                    >
                        <CardHeader class="pb-2">
                            <Megaphone
                                class="mb-2 size-8 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <CardTitle class="text-base leading-snug">
                                {{ item.titulo }}
                            </CardTitle>
                            <CardDescription class="text-sm leading-relaxed">
                                {{ item.resumo }}
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </div>
            </section>

            <div class="grid gap-8 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Bell class="size-5 text-muted-foreground" />
                            <CardTitle class="text-lg">Notícias</CardTitle>
                        </div>
                        <CardDescription>
                            Publicações recentes (dados de exemplo)
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <article
                            v-for="(item, i) in mockNoticias"
                            :key="i"
                            class="rounded-lg border border-border/80 p-4"
                        >
                            <h3 class="font-medium">{{ item.titulo }}</h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ item.corpo }}
                            </p>
                            <p class="mt-2 text-xs text-muted-foreground">
                                {{ item.meta }}
                            </p>
                        </article>
                    </CardContent>
                </Card>

                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <FileText
                                    class="size-5 text-muted-foreground"
                                />
                                <CardTitle class="text-lg"
                                    >Documentos</CardTitle
                                >
                            </div>
                            <CardDescription
                                >Últimos arquivos (mock)</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <div
                                v-for="doc in mockDocumentos"
                                :key="doc.nome"
                                class="flex items-center justify-between gap-2 rounded-md border border-transparent px-2 py-1.5 hover:bg-muted/60"
                            >
                                <span class="truncate">{{ doc.nome }}</span>
                                <span
                                    class="shrink-0 text-xs text-muted-foreground"
                                    >{{ doc.tipo }}</span
                                >
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <Calendar
                                    class="size-5 text-muted-foreground"
                                />
                                <CardTitle class="text-lg">Agenda</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent
                            class="space-y-3 text-sm text-muted-foreground"
                        >
                            <p>Nenhum evento público nesta visualização.</p>
                            <p class="text-xs">
                                Eventos poderão ser sincronizados aqui após
                                integração com o calendário institucional.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </main>
    </div>
</template>

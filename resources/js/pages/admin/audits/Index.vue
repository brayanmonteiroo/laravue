<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes/admin';
import { index as auditsIndex } from '@/routes/admin/audits';

type AuditRow = {
    id: number;
    occurred_at: string | null;
    actor: string;
    action: string;
    subject: string;
    details: string;
};

type Props = {
    audits: AuditRow[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Painel', href: dashboard() },
            { title: 'Auditoria', href: auditsIndex() },
        ],
    },
});

function formatDateTime(iso: string | null): string {
    if (! iso) {
        return '—';
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(iso));
}
</script>

<template>
    <Head title="Auditoria" />

    <div class="w-full space-y-8">
        <Heading
            title="Auditoria"
            description="Últimas 10 alterações registradas no sistema — usuários, perfis e permissões."
        />

        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <table class="w-full text-sm">
                <thead class="border-b border-sidebar-border/70 bg-muted/40 dark:border-sidebar-border">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">
                            Quando
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            Quem
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            Ação
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            Registro
                        </th>
                        <th class="hidden px-4 py-3 text-left font-medium lg:table-cell">
                            Detalhes
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-if="audits.length === 0"
                    >
                        <td
                            colspan="5"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Nenhuma auditoria registrada ainda.
                        </td>
                    </tr>
                    <tr
                        v-for="audit in audits"
                        :key="audit.id"
                        class="border-b border-sidebar-border/70 last:border-b-0 dark:border-sidebar-border"
                    >
                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            {{ formatDateTime(audit.occurred_at) }}
                        </td>
                        <td class="px-4 py-3 align-top">
                            {{ audit.actor }}
                        </td>
                        <td class="px-4 py-3 align-top">
                            {{ audit.action }}
                        </td>
                        <td class="px-4 py-3 align-top">
                            {{ audit.subject }}
                        </td>
                        <td class="hidden px-4 py-3 align-top text-muted-foreground lg:table-cell">
                            {{ audit.details }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

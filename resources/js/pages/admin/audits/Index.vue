<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import DataTable from '@/components/tables/DataTable.vue';
import { dashboard } from '@/routes/admin';
import { index as auditsIndex } from '@/routes/admin/audits';
import type { DataTableColumn, LaravelPaginator } from '@/types/tables';

type AuditRow = {
    id: number;
    occurred_at: string | null;
    actor: string;
    action: string;
    subject: string;
    details: string;
};

type Props = {
    audits: LaravelPaginator<AuditRow>;
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

const tableColumns: DataTableColumn[] = [
    { key: 'occurred_at', label: 'Quando' },
    { key: 'actor', label: 'Quem' },
    { key: 'action', label: 'Ação' },
    { key: 'subject', label: 'Registro' },
    {
        key: 'details',
        label: 'Detalhes',
        headerClass: 'hidden lg:table-cell',
        cellClass: 'hidden lg:table-cell',
    },
];

function formatDateTime(iso: string | null): string {
    if (!iso) {
        return '—';
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(iso));
}

function rowAsAudit(row: Record<string, unknown>): AuditRow {
    return row as unknown as AuditRow;
}
</script>

<template>
    <Head title="Auditoria" />

    <div class="space-y-6">
        <Heading
            title="Auditoria"
            description="Histórico de alterações no sistema — usuários, perfis e permissões (10 por página)."
        />

        <DataTable
            :columns="tableColumns"
            :paginator="audits"
            sort-column="id"
            sort-direction="desc"
            :index-url="auditsIndex.url()"
        >
            <template #default="{ rows }">
                <tr
                    v-for="row in rows"
                    :key="rowAsAudit(row).id"
                    class="border-b border-sidebar-border/50 last:border-0 dark:border-sidebar-border/50"
                >
                    <td class="px-4 py-3 align-top whitespace-nowrap">
                        {{ formatDateTime(rowAsAudit(row).occurred_at) }}
                    </td>
                    <td class="px-4 py-3 align-top">
                        {{ rowAsAudit(row).actor }}
                    </td>
                    <td class="px-4 py-3 align-top">
                        {{ rowAsAudit(row).action }}
                    </td>
                    <td class="px-4 py-3 align-top">
                        {{ rowAsAudit(row).subject }}
                    </td>
                    <td
                        class="hidden px-4 py-3 align-top text-muted-foreground lg:table-cell"
                    >
                        {{ rowAsAudit(row).details }}
                    </td>
                </tr>
            </template>
        </DataTable>
    </div>
</template>

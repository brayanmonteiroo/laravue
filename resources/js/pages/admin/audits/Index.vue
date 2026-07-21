<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import DataTable from '@/components/tables/DataTable.vue';
import TableFilters from '@/components/tables/TableFilters.vue';
import { dashboard } from '@/routes/admin';
import { index as auditsIndex } from '@/routes/admin/audits';
import type {
    DataTableColumn,
    LaravelPaginator,
    TableFilterField,
    TableFilterOption,
} from '@/types/tables';

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
    filters: {
        search: string;
        event: string;
        auditable_type: string;
        date_from: string;
        date_to: string;
    };
    filterOptions: {
        events: TableFilterOption[];
        types: TableFilterOption[];
    };
};

const props = defineProps<Props>();

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

const filterFields = computed((): TableFilterField[] => [
    {
        key: 'search',
        label: 'Busca',
        type: 'text',
        placeholder: 'Quem, tipo ou evento',
    },
    {
        key: 'event',
        label: 'Evento',
        type: 'select',
        options: props.filterOptions.events,
    },
    {
        key: 'auditable_type',
        label: 'Registro',
        type: 'select',
        options: props.filterOptions.types,
    },
    {
        key: 'date_from',
        label: 'De',
        type: 'date',
    },
    {
        key: 'date_to',
        label: 'Até',
        type: 'date',
    },
]);

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

        <TableFilters
            :index-url="auditsIndex.url()"
            :fields="filterFields"
            :filters="filters"
        />

        <DataTable
            :columns="tableColumns"
            :paginator="audits"
            sort-column="id"
            sort-direction="desc"
            :index-url="auditsIndex.url()"
            empty-message="Nenhuma auditoria registrada ainda."
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

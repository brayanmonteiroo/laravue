<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { DataTableColumn, LaravelPaginator } from '@/types/tables';

type Props = {
    columns: DataTableColumn[];
    paginator: LaravelPaginator;
    sortColumn: string;
    sortDirection: 'asc' | 'desc';
    /** URL relativa ou absoluta da listagem (ex.: resultado de index.url()) */
    indexUrl: string;
};

const props = defineProps<Props>();

const rows = computed(() => props.paginator.data);

function visitPage(url: string | null): void {
    if (url) {
        router.visit(url, { preserveState: true, preserveScroll: true });
    }
}

function nextDirection(column: string): 'asc' | 'desc' {
    if (props.sortColumn !== column) {
        return 'asc';
    }

    return props.sortDirection === 'asc' ? 'desc' : 'asc';
}

function requestSort(column: string): void {
    router.get(
        props.indexUrl,
        {
            sort: column,
            direction: nextDirection(column),
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function sortIcon(column: string) {
    if (props.sortColumn !== column) {
        return ArrowUpDown;
    }

    return props.sortDirection === 'asc' ? ArrowUp : ArrowDown;
}
</script>

<template>
    <div class="space-y-4">
        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-sidebar-border/70 bg-muted/40 dark:border-sidebar-border"
                    >
                        <tr>
                            <th
                                v-for="col in columns"
                                :key="col.key"
                                :class="
                                    cn(
                                        'px-4 py-3 font-medium',
                                        col.align === 'right' && 'text-right',
                                        col.align === 'center' && 'text-center',
                                        col.headerClass,
                                    )
                                "
                            >
                                <button
                                    v-if="col.sortable"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-md hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    @click="requestSort(col.key)"
                                >
                                    <span>{{ col.label }}</span>
                                    <component
                                        :is="sortIcon(col.key)"
                                        class="size-4 shrink-0 text-muted-foreground"
                                        :class="{
                                            'text-foreground':
                                                sortColumn === col.key,
                                        }"
                                    />
                                </button>
                                <span v-else>{{ col.label }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <slot v-if="rows.length > 0" :rows="rows" />
                        <tr v-else>
                            <td
                                :colspan="columns.length"
                                class="px-4 py-8 text-center text-sm text-muted-foreground"
                            >
                                Nenhum registro encontrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="paginator.last_page > 1"
            class="flex flex-wrap items-center justify-between gap-2 text-sm"
        >
            <p class="text-muted-foreground">
                Página {{ paginator.current_page }} de
                {{ paginator.last_page }} ({{ paginator.total }} registros)
            </p>
            <div class="flex gap-1">
                <Button
                    v-for="(link, i) in paginator.links"
                    :key="i"
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="!link.url"
                    :class="{ 'bg-muted': link.active }"
                    @click="visitPage(link.url)"
                >
                    <span v-html="link.label" />
                </Button>
            </div>
        </div>
    </div>
</template>

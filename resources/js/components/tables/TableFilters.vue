<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronDown, Filter } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import DatePickerField from '@/components/tables/DatePickerField.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import type { TableFilterField } from '@/types/tables';

type FilterValues = Record<string, string>;

type Props = {
    indexUrl: string;
    fields: TableFilterField[];
    filters: FilterValues;
    /** Mantidos ao limpar / aplicar (ex.: sort, direction). */
    preserve?: FilterValues;
};

const props = withDefaults(defineProps<Props>(), {
    preserve: () => ({}),
});

const open = ref(false);
const local = reactive<FilterValues>({});

function syncFromProps(): void {
    for (const field of props.fields) {
        const value = props.filters[field.key] ?? '';
        local[field.key] =
            field.type === 'select' && (value === '' || value === undefined)
                ? 'all'
                : value;
    }
}

syncFromProps();

watch(
    () => props.filters,
    () => syncFromProps(),
    { deep: true },
);

const activeFilterCount = computed(() =>
    props.fields.filter((field) => {
        const value = props.filters[field.key] ?? '';

        return value !== '' && value !== 'all';
    }).length,
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

function buildPayload(values: FilterValues): Record<string, string> {
    const payload: Record<string, string> = {};

    for (const [key, value] of Object.entries(props.preserve)) {
        if (value !== '') {
            payload[key] = value;
        }
    }

    for (const field of props.fields) {
        const value = values[field.key]?.trim() ?? '';

        if (value === '' || value === 'all') {
            continue;
        }

        payload[field.key] = value;
    }

    return payload;
}

function applyFilters(): void {
    router.get(props.indexUrl, buildPayload({ ...local }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters(): void {
    for (const field of props.fields) {
        local[field.key] = field.type === 'select' ? 'all' : '';
    }

    router.get(props.indexUrl, buildPayload({}), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function onSelectUpdate(key: string, value: unknown): void {
    local[key] = value == null || value === '' ? 'all' : String(value);
}
</script>

<template>
    <Card class="gap-0 overflow-hidden py-0 shadow-sm">
        <Collapsible v-model:open="open">
            <CardHeader class="p-0">
                <CollapsibleTrigger
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition-colors hover:bg-muted/40"
                >
                    <div class="flex min-w-0 items-center gap-2">
                        <Filter
                            class="text-muted-foreground size-4 shrink-0"
                            aria-hidden="true"
                        />
                        <span class="text-sm font-medium">Filtros</span>
                        <Badge
                            v-if="hasActiveFilters"
                            variant="secondary"
                            class="tabular-nums"
                        >
                            {{ activeFilterCount }}
                        </Badge>
                        <span
                            v-else
                            class="text-muted-foreground truncate text-xs"
                        >
                            Recolhido — expandir para filtrar
                        </span>
                    </div>
                    <ChevronDown
                        class="text-muted-foreground size-4 shrink-0 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"
                        aria-hidden="true"
                    />
                </CollapsibleTrigger>
            </CardHeader>

            <CollapsibleContent>
                <Separator />
                <CardContent class="space-y-4 p-4">
                    <div
                        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                    >
                        <div
                            v-for="field in fields"
                            :key="field.key"
                            class="space-y-2"
                        >
                            <Label :for="`filter-${field.key}`">{{
                                field.label
                            }}</Label>

                            <Input
                                v-if="field.type === 'text'"
                                :id="`filter-${field.key}`"
                                v-model="local[field.key]"
                                type="search"
                                :placeholder="field.placeholder ?? field.label"
                                @keydown.enter.prevent="applyFilters"
                            />

                            <DatePickerField
                                v-else-if="field.type === 'date'"
                                :id="`filter-${field.key}`"
                                v-model="local[field.key]"
                                :placeholder="field.placeholder ?? field.label"
                            />

                            <Select
                                v-else
                                :model-value="local[field.key] || 'all'"
                                @update:model-value="
                                    (value) => onSelectUpdate(field.key, value)
                                "
                            >
                                <SelectTrigger
                                    :id="`filter-${field.key}`"
                                    class="w-full"
                                >
                                    <SelectValue
                                        :placeholder="
                                            field.placeholder ?? field.label
                                        "
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="option in field.options ?? []"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button type="button" @click="applyFilters">
                            Filtrar
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="!hasActiveFilters"
                            @click="clearFilters"
                        >
                            Limpar
                        </Button>
                    </div>
                </CardContent>
            </CollapsibleContent>
        </Collapsible>
    </Card>
</template>

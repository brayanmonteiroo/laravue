<script setup lang="ts">
import type { DateValue } from '@internationalized/date';
import {
    CalendarDate,
    DateFormatter,
    getLocalTimeZone,
    parseDate,
    today,
} from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';

const props = defineProps<{
    modelValue: string;
    id?: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const defaultPlaceholder = today(getLocalTimeZone());
const df = new DateFormatter('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
});

const inputText = ref('');
const open = ref(false);

function toCalendarDate(value: DateValue): CalendarDate {
    return value instanceof CalendarDate
        ? value
        : new CalendarDate(value.year, value.month, value.day);
}

function formatDisplay(value: DateValue): string {
    return df.format(value.toDate(getLocalTimeZone()));
}

/**
 * Máscara dd/mm/aaaa a partir dos dígitos digitados.
 */
function maskDateInput(raw: string): string {
    const digits = raw.replace(/\D/g, '').slice(0, 8);

    if (digits.length <= 2) {
        return digits;
    }

    if (digits.length <= 4) {
        return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    }

    return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
}

function emitIso(value: DateValue | undefined | null): void {
    if (!value) {
        emit('update:modelValue', '');

        return;
    }

    emit('update:modelValue', toCalendarDate(value).toString());
}

const date = computed<DateValue | undefined>({
    get() {
        if (!props.modelValue) {
            return undefined;
        }

        try {
            return parseDate(props.modelValue);
        } catch {
            return undefined;
        }
    },
    set(value) {
        emitIso(value);
    },
});

function syncInputFromModel(): void {
    if (!props.modelValue) {
        inputText.value = '';

        return;
    }

    try {
        inputText.value = formatDisplay(parseDate(props.modelValue));
    } catch {
        inputText.value = props.modelValue;
    }
}

syncInputFromModel();

watch(
    () => props.modelValue,
    () => syncInputFromModel(),
);

function onMaskedInput(value: string | number | null | undefined): void {
    inputText.value = maskDateInput(String(value ?? ''));
}

/**
 * Aceita dd/mm/aaaa, dígitos (ddmmaaaa) ou aaaa-mm-dd.
 */
function parseTypedDate(raw: string): CalendarDate | null {
    const value = raw.trim();

    if (value === '') {
        return null;
    }

    const isoMatch = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);

    if (isoMatch) {
        try {
            return parseDate(value);
        } catch {
            return null;
        }
    }

    const digits = value.replace(/\D/g, '');

    if (digits.length === 8) {
        const day = Number(digits.slice(0, 2));
        const month = Number(digits.slice(2, 4));
        const year = Number(digits.slice(4, 8));

        try {
            return new CalendarDate(year, month, day);
        } catch {
            return null;
        }
    }

    const brMatch = /^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$/.exec(value);

    if (!brMatch) {
        return null;
    }

    const day = Number(brMatch[1]);
    const month = Number(brMatch[2]);
    const year = Number(brMatch[3]);

    try {
        return new CalendarDate(year, month, day);
    } catch {
        return null;
    }
}

function commitTypedDate(): void {
    const typed = inputText.value.trim();

    if (typed === '') {
        emitIso(null);
        inputText.value = '';

        return;
    }

    const parsed = parseTypedDate(typed);

    if (!parsed) {
        syncInputFromModel();

        return;
    }

    emitIso(parsed);
    inputText.value = formatDisplay(parsed);
}

function onCalendarSelect(value: DateValue | undefined): void {
    date.value = value;
    open.value = false;
}
</script>

<template>
    <div class="relative flex w-full items-center gap-0">
        <Input
            :id="id"
            :model-value="inputText"
            type="text"
            inputmode="numeric"
            maxlength="10"
            autocomplete="off"
            class="rounded-r-none border-r-0 pr-2"
            :placeholder="placeholder ?? 'dd/mm/aaaa'"
            @update:model-value="onMaskedInput"
            @keydown.enter.prevent="commitTypedDate"
            @blur="commitTypedDate"
        />
        <Popover v-model:open="open">
            <PopoverTrigger as-child>
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    class="rounded-l-none border-l-0 shrink-0"
                    :aria-label="placeholder ?? 'Abrir calendário'"
                >
                    <CalendarIcon class="size-4" />
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0" align="end">
                <Calendar
                    v-model="date"
                    :default-placeholder="defaultPlaceholder"
                    layout="month-and-year"
                    initial-focus
                    locale="pt-BR"
                    @update:model-value="onCalendarSelect"
                />
            </PopoverContent>
        </Popover>
    </div>
</template>

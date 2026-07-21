<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

type Props = {
    status: number;
    message: string;
    ctaUrl: string;
    ctaLabel: string;
    crumbTitle: string;
};

const props = defineProps<Props>();

const title = computed(() => String(props.status));
</script>

<template>
    <Head :title="title" />

    <div
        class="flex w-full flex-col items-center justify-center px-4 py-16 text-center"
    >
        <p
            class="mb-2 text-sm font-medium tracking-wider text-muted-foreground"
        >
            {{ status }}
        </p>
        <h1
            class="mb-6 max-w-md text-xl font-semibold tracking-tight sm:text-2xl"
        >
            {{ message }}
        </h1>
        <Button as-child>
            <Link :href="ctaUrl">{{ ctaLabel }}</Link>
        </Button>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Toaster } from '@/components/ui/sonner';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <Toaster />
    <div
        class="bg-neutral-50 dark:bg-neutral-900 flex min-h-svh flex-col items-center justify-center p-4 sm:p-6 md:p-10"
    >
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center gap-2 self-center font-medium"
            >
                <div class="flex size-9 items-center justify-center">
                    <AppLogoIcon
                        class="size-9 fill-current text-black dark:text-white"
                    />
                </div>
                <span class="sr-only">{{ title }}</span>
            </Link>

            <Card
                class="bg-background w-full rounded-xl border shadow-md dark:shadow-lg dark:ring-1 dark:ring-white/10"
            >
                <CardHeader class="px-4 pt-6 pb-0 text-center sm:px-8 sm:pt-8">
                    <CardTitle class="text-xl">{{ title }}</CardTitle>
                    <CardDescription>
                        {{ description }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="px-4 py-6 sm:px-8 sm:py-8">
                    <slot />
                </CardContent>
            </Card>
        </div>
    </div>
</template>

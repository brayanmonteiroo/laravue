<script lang="ts" setup>
import 'vue-sonner/style.css';
import {
    CircleCheckIcon,
    InfoIcon,
    Loader2Icon,
    OctagonXIcon,
    TriangleAlertIcon,
    XIcon,
} from 'lucide-vue-next';
import { computed } from 'vue';
import type { ToasterProps } from 'vue-sonner';
import { Toaster as Sonner } from 'vue-sonner';
import { useAppearance } from '@/composables/useAppearance';
import { useFlashToast } from '@/composables/useFlashToast';
import { cn } from '@/lib/utils';

const props = defineProps<ToasterProps>();

useFlashToast();

const { resolvedAppearance } = useAppearance();

const toastDefaults = computed(() => ({
    ...props.toastOptions,
    duration: props.toastOptions?.duration ?? 4800,
    closeButton: props.toastOptions?.closeButton ?? true,
    class: cn(
        'items-start gap-3 !py-3.5 !px-4',
        props.toastOptions?.class,
    ),
}));

const toasterStyle = computed(() => ({
    '--width': 'min(28rem, calc(100vw - 1.75rem))',
    ...(props.style && typeof props.style === 'object' && !Array.isArray(props.style)
        ? props.style
        : {}),
}));
</script>

<template>
    <Sonner
        v-bind="props"
        :class="cn('toaster group', props.class)"
        :style="toasterStyle"
        :gap="props.gap ?? 12"
        :offset="props.offset ?? { bottom: '1rem' }"
        :mobile-offset="props.mobileOffset ?? { bottom: '1rem' }"
        :position="props.position ?? 'bottom-center'"
        :rich-colors="props.richColors ?? true"
        :close-button="props.closeButton ?? true"
        :close-button-position="props.closeButtonPosition ?? 'top-right'"
        :expand="props.expand ?? true"
        :theme="props.theme ?? resolvedAppearance"
        :toast-options="toastDefaults"
    >
        <template #success-icon>
            <CircleCheckIcon class="size-4 shrink-0 text-current" />
        </template>
        <template #info-icon>
            <InfoIcon class="size-4 shrink-0 text-current" />
        </template>
        <template #warning-icon>
            <TriangleAlertIcon class="size-4 shrink-0 text-current" />
        </template>
        <template #error-icon>
            <OctagonXIcon class="size-4 shrink-0 text-current" />
        </template>
        <template #loading-icon>
            <div>
                <Loader2Icon class="size-4 shrink-0 animate-spin text-current" />
            </div>
        </template>
        <template #close-icon>
            <XIcon class="size-4 shrink-0 text-current opacity-80" />
        </template>
    </Sonner>
</template>

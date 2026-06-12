<script setup lang="ts">
import type { AlertDialogTitleProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { AlertDialogTitle as AlertDialogTitlePrimitive } from 'reka-ui';
import { useForwardProps } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<
    AlertDialogTitleProps & { class?: HTMLAttributes['class'] }
>();

const delegatedProps = reactiveOmit(props, 'class');
const forwarded = useForwardProps(delegatedProps);
</script>

<template>
    <AlertDialogTitlePrimitive
        data-slot="alert-dialog-title"
        v-bind="forwarded"
        :class="cn('text-lg font-semibold', props.class)"
    >
        <slot />
    </AlertDialogTitlePrimitive>
</template>

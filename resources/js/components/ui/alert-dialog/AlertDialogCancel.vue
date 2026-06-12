<script setup lang="ts">
import type { AlertDialogCancelProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { AlertDialogCancel as AlertDialogCancelPrimitive } from 'reka-ui';
import { useForwardProps } from 'reka-ui';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<
        AlertDialogCancelProps & { class?: HTMLAttributes['class'] }
    >(),
    {
        as: 'button',
    },
);

const delegatedProps = reactiveOmit(props, 'class');
const forwarded = useForwardProps(delegatedProps);
</script>

<template>
    <AlertDialogCancelPrimitive
        data-slot="alert-dialog-cancel"
        v-bind="forwarded"
        :class="cn(buttonVariants({ variant: 'outline' }), props.class)"
    >
        <slot />
    </AlertDialogCancelPrimitive>
</template>

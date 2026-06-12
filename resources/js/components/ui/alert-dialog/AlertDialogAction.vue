<script setup lang="ts">
import type { AlertDialogActionProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { AlertDialogAction as AlertDialogActionPrimitive } from 'reka-ui';
import { useForwardProps } from 'reka-ui';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type ActionVariant = 'default' | 'destructive';

const props = withDefaults(
    defineProps<
        AlertDialogActionProps & {
            class?: HTMLAttributes['class'];
            variant?: ActionVariant;
        }
    >(),
    {
        as: 'button',
        variant: 'default',
    },
);

const delegatedProps = reactiveOmit(props, 'class', 'variant');
const forwarded = useForwardProps(delegatedProps);
</script>

<template>
    <AlertDialogActionPrimitive
        data-slot="alert-dialog-action"
        v-bind="forwarded"
        :class="cn(buttonVariants({ variant: props.variant }), props.class)"
    >
        <slot />
    </AlertDialogActionPrimitive>
</template>

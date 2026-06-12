<script setup lang="ts">
import type { AlertDialogTriggerProps } from 'reka-ui';
import {
    AlertDialogTrigger as AlertDialogTriggerPrimitive,
    useForwardProps,
} from 'reka-ui';

const props = defineProps<AlertDialogTriggerProps>();

const forwarded = useForwardProps(props);
</script>

<template>
    <AlertDialogTriggerPrimitive
        data-slot="alert-dialog-trigger"
        v-bind="forwarded"
    >
        <slot />
    </AlertDialogTriggerPrimitive>
</template>

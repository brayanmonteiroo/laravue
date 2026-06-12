<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

type ConfirmVariant = 'default' | 'destructive';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        confirmText?: string;
        cancelText?: string;
        confirmVariant?: ConfirmVariant;
    }>(),
    {
        description: undefined,
        confirmText: 'Confirmar',
        cancelText: 'Cancelar',
        confirmVariant: 'default',
    },
);

const open = defineModel<boolean>('open', { default: false });

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();

function onConfirm(): void {
    emit('confirm');
    open.value = false;
}

function onCancel(): void {
    emit('cancel');
    open.value = false;
}
</script>

<template>
    <AlertDialog v-model:open="open">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription v-if="description">
                    {{ description }}
                </AlertDialogDescription>
                <AlertDialogDescription v-else class="sr-only">
                    Confirme ou cancele esta ação.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="onCancel">
                    {{ cancelText }}
                </AlertDialogCancel>
                <AlertDialogAction
                    :variant="confirmVariant"
                    @click="onConfirm"
                >
                    {{ confirmText }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

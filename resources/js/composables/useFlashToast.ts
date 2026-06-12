import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

export type FlashType = 'success' | 'error' | 'info' | 'warning';

export type FlashPayload = {
    message: string;
    type?: FlashType;
} | null;

/**
 * Exibe uma notificação Sonner quando as propriedades `flash` compartilhadas do Inertia contêm uma mensagem.
 */
export function useFlashToast(): void {
    const page = usePage();

    watch(
        () => page.props.flash,
        (flash) => {
            if (!flash?.message) {
                return;
            }

            const message = flash.message;
            const type = flash.type ?? 'success';

            /** Garante cores semánticas e botão fechar mesmo se o Toaster omitir atributos no DOM. */
            const opts = {
                richColors: true,
                closeButton: true,
            } as const;

            switch (type) {
                case 'error':
                    toast.error(message, opts);
                    break;
                case 'info':
                    toast.info(message, opts);
                    break;
                case 'warning':
                    toast.warning(message, opts);
                    break;
                default:
                    toast.success(message, opts);
            }
        },
        { deep: true, immediate: true },
    );
}

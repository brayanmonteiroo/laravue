<script setup lang="ts">
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useAppearance } from '@/composables/useAppearance';
import { cn } from '@/lib/utils';

const { appearance, updateAppearance } = useAppearance();

const options = [
    { value: 'light' as const, Icon: Sun, label: 'Tema claro' },
    { value: 'dark' as const, Icon: Moon, label: 'Tema escuro' },
    { value: 'system' as const, Icon: Monitor, label: 'Seguir o sistema' },
];
</script>

<template>
    <TooltipProvider :delay-duration="0">
        <div
            role="group"
            aria-label="Aparência"
            class="flex shrink-0 items-center gap-0.5 rounded-lg border border-sidebar-border/60 bg-muted/30 p-0.5 dark:border-sidebar-border"
        >
            <Tooltip v-for="opt in options" :key="opt.value">
                <TooltipTrigger as-child>
                    <Button
                        type="button"
                        size="icon-sm"
                        variant="ghost"
                        :class="
                            cn(
                                'size-8 sm:size-9',
                                appearance === opt.value &&
                                    'bg-background shadow-sm dark:bg-background/80',
                            )
                        "
                        :aria-pressed="appearance === opt.value"
                        :aria-label="opt.label"
                        @click="updateAppearance(opt.value)"
                    >
                        <component :is="opt.Icon" class="size-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent side="bottom">
                    <p>{{ opt.label }}</p>
                </TooltipContent>
            </Tooltip>
        </div>
    </TooltipProvider>
</template>

<script setup lang="ts"></script>

<template>
    <div class="min-w-0 px-4 py-6 md:px-6 lg:px-8">
        <div
            class="w-full rounded-xl border border-sidebar-border/70 bg-card text-card-foreground shadow-sm dark:border-sidebar-border"
        >
            <div class="p-6 md:p-8">
                <slot />
            </div>
        </div>
    </div>
</template>

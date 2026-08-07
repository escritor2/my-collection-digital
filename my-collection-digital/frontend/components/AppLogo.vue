<script setup lang="ts">
import { Library } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-brand/10 ring-1 ring-brand/20"
    >
        <Library class="size-5 text-brand" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-serif font-semibold text-sidebar-foreground">
            Meu Acervo Digital
        </span>
    </div>
</template>
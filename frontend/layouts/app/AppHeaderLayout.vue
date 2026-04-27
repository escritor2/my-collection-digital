<script setup lang="ts">
import AppContent from '~/components/AppContent.vue';
import AppHeader from '~/components/AppHeader.vue';
import AppShell from '~/components/AppShell.vue';
import type { BreadcrumbItem } from '~/types';

const logout = () => {
    // router.post('/logout');
};

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="header">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent variant="header">
            <slot />
        </AppContent>
    </AppShell>
    
</template>


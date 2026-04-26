<script setup lang="ts">
import SidebarLayout from '~/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '~/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <slot />
    </SidebarLayout>
</template>


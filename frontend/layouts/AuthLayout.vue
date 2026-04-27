<script setup lang="ts">
import SimpleAuthLayout from '~/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <SimpleAuthLayout :title="title" :description="description">
        <slot />
    </SimpleAuthLayout>
</template>


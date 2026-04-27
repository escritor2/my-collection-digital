<script setup>
import { onMounted } from 'vue';
import { useAuth } from '~/composables/useAuth';

const { fetchUser } = useAuth();

// Try to fetch the user when the app mounts to restore the session
onMounted(async () => {
    await fetchUser();
});
</script>

<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
  <Toaster position="top-right" richColors />
</template>

<script setup lang="ts">
import { initializeTheme } from '~/composables/useAppearance';
import { Toaster } from 'vue-sonner';

// Flush queued offline mutations when connection returns.
onMounted(() => {
  initializeTheme();
  
  const { flushOfflineQueue } = useApi()
  const onOnline = () => flushOfflineQueue()
  window.addEventListener('online', onOnline)
  flushOfflineQueue()
})
</script>

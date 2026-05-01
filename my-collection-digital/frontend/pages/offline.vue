<script setup lang="ts">
definePageMeta({ layout: false })
useHead({ title: 'Offline - Meu Acervo Digital' })
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-zinc-950 text-zinc-100 px-6">
    <div class="max-w-md w-full rounded-xl border border-zinc-800 bg-zinc-900/40 p-6">
      <h1 class="text-xl font-semibold">Você está offline</h1>
      <p class="mt-2 text-sm text-zinc-400">
        Algumas telas podem funcionar em modo offline (incluindo leitura se o arquivo já foi aberto antes).
      </p>
      <div class="mt-4 flex gap-2">
        <button
          class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-zinc-900 hover:bg-zinc-200"
          @click="location.reload()"
        >
          Tentar novamente
        </button>
        <NuxtLink to="/dashboard" class="inline-flex items-center rounded-md bg-zinc-800 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-700 border border-zinc-700">
          Ir ao Dashboard
        </NuxtLink>
      </div>
    </div>
  </div>
</template>


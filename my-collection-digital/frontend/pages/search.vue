<script setup lang="ts">
useHead({ title: 'Busca - Meu Acervo Digital' });

const { apiFetch } = useApi();

type Book = { id: number; title: string; author: string; isbn: string | null };
type UserBook = { id: number; status: string; book: Book };

const q = ref('');
const isSearching = ref(false);
const results = ref<{ books: Book[]; shelf: UserBook[] } | null>(null);

let timer: any = null;
watch(q, (val) => {
  if (timer) clearTimeout(timer);
  const s = (val ?? '').trim();
  if (s.length < 2) {
    results.value = null;
    return;
  }
  timer = setTimeout(async () => {
    isSearching.value = true;
    try {
      const res: any = await apiFetch('/search', { query: { q: s, limit: 8 } });
      results.value = res?.data ?? null;
    } finally {
      isSearching.value = false;
    }
  }, 250);
});
</script>

<template>
  <div class="py-6">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Busca global</h1>
      </div>

      <div class="mb-4">
        <input
          v-model="q"
          type="text"
          placeholder="busque por título, autor ou ISBN..."
          aria-label="Busca global por título autor ou ISBN"
          class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
        />
        <div v-if="isSearching" class="mt-2 text-xs text-gray-500 dark:text-zinc-400">buscando...</div>
      </div>

      <div v-if="results" class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
          <div class="p-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Catálogo</h2>
            <div v-if="results.books?.length" class="space-y-2">
              <div v-for="b in results.books" :key="b.id" class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ b.title }}</div>
                  <div class="text-xs text-gray-500 dark:text-zinc-400 truncate">{{ b.author }} · {{ b.isbn || 'sem ISBN' }}</div>
                </div>
                <NuxtLink :to="`/books/${b.id}`" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">Ver</NuxtLink>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500 dark:text-zinc-400">Sem resultados no catálogo.</div>
          </div>
        </div>

        <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
          <div class="p-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Minha estante</h2>
            <div v-if="results.shelf?.length" class="space-y-2">
              <div v-for="ub in results.shelf" :key="ub.id" class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ ub.book.title }}</div>
                  <div class="text-xs text-gray-500 dark:text-zinc-400 truncate">{{ ub.book.author }} · {{ ub.status }}</div>
                </div>
                <NuxtLink :to="`/reader/${ub.id}`" class="text-sm text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">Abrir</NuxtLink>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500 dark:text-zinc-400">Sem resultados na estante.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


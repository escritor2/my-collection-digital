<script setup lang="ts">
useHead({ title: 'Coleções - Meu Acervo Digital' });

const { apiFetch } = useApi();

type Collection = { id: number; name: string; description: string | null };

const collections = ref<Collection[]>([]);
const isLoading = ref(true);
const name = ref('');
const description = ref('');
const error = ref<string | null>(null);

const load = async () => {
  isLoading.value = true;
  try {
    const res: any = await apiFetch('/collections');
    collections.value = res?.data ?? [];
  } finally {
    isLoading.value = false;
  }
};

onMounted(load);

const createCollection = async () => {
  error.value = null;
  const n = name.value.trim();
  if (!n) return;

  const payload: any = { name: n };
  if (description.value.trim()) payload.description = description.value.trim();

  try {
    const res: any = await apiFetch('/collections', { method: 'POST', body: payload });
    const created = res?.data;
    if (created) {
      collections.value = [...collections.value.filter(c => c.id !== created.id), created].sort((a, b) => a.name.localeCompare(b.name));
      name.value = '';
      description.value = '';
    } else {
      await load();
    }
  } catch (e: any) {
    error.value = 'Não foi possível criar a coleção.';
    throw e;
  }
};

const removeCollection = async (c: Collection) => {
  error.value = null;
  await apiFetch(`/collections/${c.id}`, { method: 'DELETE' });
  collections.value = collections.value.filter(x => x.id !== c.id);
};
</script>

<template>
  <div class="py-6">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Coleções</h1>
        <NuxtLink to="/user-shelf" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">Voltar para estante</NuxtLink>
      </div>

      <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
        <div class="p-6">
          <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="md:col-span-5">
              <label class="block text-xs font-medium text-gray-600 dark:text-zinc-300 mb-1">Nome</label>
              <input
                v-model="name"
                type="text"
                placeholder="ex: 2026 • Ficção científica"
                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
              />
            </div>
            <div class="md:col-span-5">
              <label class="block text-xs font-medium text-gray-600 dark:text-zinc-300 mb-1">Descrição (opcional)</label>
              <input
                v-model="description"
                type="text"
                placeholder="ex: leituras favoritas do ano"
                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
              />
            </div>
            <div class="md:col-span-2 flex items-end">
              <button
                class="w-full inline-flex items-center justify-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500"
                @click="createCollection"
              >
                Criar
              </button>
            </div>
          </div>

          <p v-if="error" class="mt-3 text-sm text-red-600 dark:text-red-400">{{ error }}</p>

          <div v-if="isLoading" class="text-center py-12">
            <p class="text-gray-500 dark:text-zinc-400">Carregando...</p>
          </div>

          <div v-else class="mt-6">
            <div v-if="collections.length === 0" class="text-sm text-gray-500 dark:text-zinc-400">Nenhuma coleção ainda.</div>
            <ul v-else class="divide-y divide-gray-100 dark:divide-zinc-800">
              <li v-for="c in collections" :key="c.id" class="py-3">
                <div class="flex items-start justify-between gap-4">
                  <div class="min-w-0">
                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ c.name }}</div>
                    <div v-if="c.description" class="text-xs text-gray-500 dark:text-zinc-400 truncate">{{ c.description }}</div>
                  </div>
                  <button class="text-sm text-red-600 hover:text-red-700 dark:text-red-400" @click="removeCollection(c)">
                    Remover
                  </button>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


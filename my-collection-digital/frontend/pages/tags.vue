<script setup lang="ts">
useHead({ title: 'Tags - Meu Acervo Digital' });

const { apiFetch } = useApi();

type Tag = { id: number; name: string; color: string | null };

const tags = ref<Tag[]>([]);
const isLoading = ref(true);
const name = ref('');
const color = ref<string>('#7c3aed'); // purple-600
const error = ref<string | null>(null);

const load = async () => {
  isLoading.value = true;
  try {
    const res: any = await apiFetch('/tags');
    tags.value = res?.data ?? [];
  } finally {
    isLoading.value = false;
  }
};

onMounted(load);

const createTag = async () => {
  error.value = null;
  const n = name.value.trim();
  if (!n) return;

  try {
    const res: any = await apiFetch('/tags', { method: 'POST', body: { name: n, color: color.value } });
    const created = res?.data;
    if (created) {
      tags.value = [...tags.value.filter(t => t.id !== created.id), created].sort((a, b) => a.name.localeCompare(b.name));
      name.value = '';
    } else {
      await load();
    }
  } catch (e: any) {
    error.value = 'Não foi possível criar a tag.';
    throw e;
  }
};

const removeTag = async (t: Tag) => {
  error.value = null;
  await apiFetch(`/tags/${t.id}`, { method: 'DELETE' });
  tags.value = tags.value.filter(x => x.id !== t.id);
};
</script>

<template>
  <div class="py-6">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tags</h1>
        <NuxtLink to="/user-shelf" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">Voltar para estante</NuxtLink>
      </div>

      <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
        <div class="p-6">
          <div class="flex items-end gap-3">
            <div class="flex-1">
              <label class="block text-xs font-medium text-gray-600 dark:text-zinc-300 mb-1">Nome</label>
              <input
                v-model="name"
                type="text"
                placeholder="ex: fantasia"
                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-zinc-300 mb-1">Cor</label>
              <input v-model="color" type="color" class="h-9 w-12 rounded border border-gray-300 dark:border-zinc-700" />
            </div>
            <button
              class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500"
              @click="createTag"
            >
              Criar
            </button>
          </div>

          <p v-if="error" class="mt-3 text-sm text-red-600 dark:text-red-400">{{ error }}</p>

          <div v-if="isLoading" class="text-center py-12">
            <p class="text-gray-500 dark:text-zinc-400">Carregando...</p>
          </div>

          <div v-else class="mt-6">
            <div v-if="tags.length === 0" class="text-sm text-gray-500 dark:text-zinc-400">Nenhuma tag ainda.</div>
            <ul v-else class="divide-y divide-gray-100 dark:divide-zinc-800">
              <li v-for="t in tags" :key="t.id" class="flex items-center justify-between py-3">
                <div class="flex items-center gap-3 min-w-0">
                  <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: t.color || '#a1a1aa' }" />
                  <span class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ t.name }}</span>
                </div>
                <button class="text-sm text-red-600 hover:text-red-700 dark:text-red-400" @click="removeTag(t)">
                  Remover
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


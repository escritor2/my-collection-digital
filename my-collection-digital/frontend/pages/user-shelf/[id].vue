<script setup lang="ts">
useHead({ title: 'Detalhes do livro - Minha Estante' });

const route = useRoute();
const userBookId = computed(() => String(route.params.id));
const { apiFetch } = useApi();

type Tag = { id: number; name: string; color: string | null };
type Collection = { id: number; name: string; description: string | null };
type UserBook = {
  id: number;
  status: 'quero_ler' | 'lendo' | 'lido' | 'abandonei';
  progress_pages: number;
  rating: number | null;
  started_at: string | null;
  finished_at: string | null;
  book: {
    id: number;
    title: string;
    author: string;
    page_count: number | null;
    isbn?: string | null;
    cover_url?: string | null;
    publisher?: string | null;
    published_date?: string | null;
    language?: string | null;
    categories?: string[] | null;
    description?: string | null;
  };
  tags: Tag[];
  collections: Collection[];
};

const isLoading = ref(true);
const userBook = ref<UserBook | null>(null);
const tags = ref<Tag[]>([]);
const collections = ref<Collection[]>([]);

const form = reactive({
  status: 'quero_ler' as UserBook['status'],
  progress_pages: 0,
  rating: null as number | null,
});

const selectedTagIds = ref<number[]>([]);
const selectedCollectionIds = ref<number[]>([]);

const load = async () => {
  isLoading.value = true;
  try {
    const [ub, t, c]: any = await Promise.all([
      apiFetch(`/user-shelf/${userBookId.value}`),
      apiFetch('/tags'),
      apiFetch('/collections'),
    ]);

    userBook.value = ub?.data ?? null;
    tags.value = t?.data ?? [];
    collections.value = c?.data ?? [];

    if (userBook.value) {
      form.status = userBook.value.status;
      form.progress_pages = userBook.value.progress_pages ?? 0;
      form.rating = userBook.value.rating ?? null;
      selectedTagIds.value = (userBook.value.tags ?? []).map(x => x.id);
      selectedCollectionIds.value = (userBook.value.collections ?? []).map(x => x.id);
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(load);

const saveShelf = async () => {
  const res: any = await apiFetch(`/user-shelf/${userBookId.value}`, {
    method: 'PUT',
    body: {
      status: form.status,
      progress_pages: form.progress_pages,
      rating: form.rating,
    },
  });
  userBook.value = res?.data ?? userBook.value;
};

const syncTags = async () => {
  const res: any = await apiFetch(`/user-shelf/${userBookId.value}/tags`, {
    method: 'PUT',
    body: { tag_ids: selectedTagIds.value },
  });
  userBook.value = res?.data ?? userBook.value;
};

const syncCollections = async () => {
  const res: any = await apiFetch(`/user-shelf/${userBookId.value}/collections`, {
    method: 'PUT',
    body: { collection_ids: selectedCollectionIds.value },
  });
  userBook.value = res?.data ?? userBook.value;
};

const removeFromShelf = async () => {
  const ok = window.confirm('Remover este livro da sua estante?');
  if (!ok) return;
  await apiFetch(`/user-shelf/${userBookId.value}`, { method: 'DELETE' });
  await navigateTo('/user-shelf');
};
</script>

<template>
  <div class="py-6">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detalhes</h1>
        <div class="flex items-center gap-3">
          <NuxtLink to="/user-shelf" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">Voltar</NuxtLink>
          <button
            class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
            @click="removeFromShelf"
          >
            Remover da estante
          </button>
        </div>
      </div>

      <div v-if="isLoading" class="text-center py-12">
        <p class="text-gray-500 dark:text-zinc-400">Carregando...</p>
      </div>

      <div v-else-if="!userBook" class="text-center py-12">
        <p class="text-gray-500 dark:text-zinc-400">Não encontrado.</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800 lg:col-span-2">
          <div class="p-6">
            <div class="flex gap-4">
              <div class="h-28 w-20 rounded bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 overflow-hidden shrink-0">
                <img v-if="userBook.book.cover_url" :src="userBook.book.cover_url" class="h-full w-full object-cover" />
              </div>
              <div class="min-w-0">
                <div class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ userBook.book.title }}</div>
                <div class="text-sm text-gray-500 dark:text-zinc-400 truncate">{{ userBook.book.author }}</div>
                <div class="mt-2 text-xs text-gray-500 dark:text-zinc-400">
                  <span v-if="userBook.book.page_count">{{ userBook.book.page_count }} págs</span>
                  <span v-if="userBook.book.isbn"> · ISBN {{ userBook.book.isbn }}</span>
                  <span v-if="userBook.book.publisher"> · {{ userBook.book.publisher }}</span>
                  <span v-if="userBook.book.published_date"> · {{ userBook.book.published_date }}</span>
                </div>
                <div v-if="userBook.book.categories?.length" class="mt-2 flex flex-wrap gap-2">
                  <span v-for="cat in userBook.book.categories" :key="cat" class="text-[11px] rounded-full bg-purple-100 text-purple-800 px-2 py-0.5 dark:bg-purple-900/30 dark:text-purple-300">
                    {{ cat }}
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-zinc-300 mb-1">Status</label>
                <select v-model="form.status" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                  <option value="quero_ler">Quero ler</option>
                  <option value="lendo">Lendo</option>
                  <option value="lido">Lido</option>
                  <option value="abandonei">Abandonei</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-zinc-300 mb-1">Progresso (páginas)</label>
                <input v-model.number="form.progress_pages" type="number" min="0"
                  class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-zinc-300 mb-1">Nota (1–5)</label>
                <input v-model.number="form.rating" type="number" min="1" max="5"
                  class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
              </div>
            </div>

            <div class="mt-4 flex gap-2">
              <button class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500" @click="saveShelf">
                Salvar
              </button>
              <NuxtLink :to="`/reader/${userBook.id}`" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                Abrir reader
              </NuxtLink>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6">
              <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Tags</h2>
                <NuxtLink to="/tags" class="text-xs text-purple-600 hover:text-purple-700 dark:text-purple-400">gerenciar</NuxtLink>
              </div>
              <div class="space-y-2 max-h-56 overflow-auto pr-1">
                <label v-for="t in tags" :key="t.id" class="flex items-center gap-2 text-sm text-gray-700 dark:text-zinc-300">
                  <input type="checkbox" :value="t.id" v-model="selectedTagIds" />
                  <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: t.color || '#a1a1aa' }" />
                  <span class="truncate">{{ t.name }}</span>
                </label>
                <div v-if="tags.length === 0" class="text-sm text-gray-500 dark:text-zinc-400">Nenhuma tag.</div>
              </div>
              <button class="mt-4 w-full inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 border border-gray-200 dark:bg-zinc-900 dark:text-white dark:border-zinc-800 dark:hover:bg-zinc-800" @click="syncTags">
                Aplicar tags
              </button>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6">
              <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Coleções</h2>
                <NuxtLink to="/collections" class="text-xs text-purple-600 hover:text-purple-700 dark:text-purple-400">gerenciar</NuxtLink>
              </div>
              <div class="space-y-2 max-h-56 overflow-auto pr-1">
                <label v-for="c in collections" :key="c.id" class="flex items-center gap-2 text-sm text-gray-700 dark:text-zinc-300">
                  <input type="checkbox" :value="c.id" v-model="selectedCollectionIds" />
                  <span class="truncate">{{ c.name }}</span>
                </label>
                <div v-if="collections.length === 0" class="text-sm text-gray-500 dark:text-zinc-400">Nenhuma coleção.</div>
              </div>
              <button class="mt-4 w-full inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 border border-gray-200 dark:bg-zinc-900 dark:text-white dark:border-zinc-800 dark:hover:bg-zinc-800" @click="syncCollections">
                Aplicar coleções
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


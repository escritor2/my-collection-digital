<script setup lang="ts">
import { toast } from 'vue-sonner';
useHead({ title: 'Livro - Catálogo' });

const route = useRoute();
const bookId = computed(() => String(route.params.id));
const { apiFetch } = useApi();

type Book = {
  id: number;
  title: string;
  author: string;
  description: string | null;
  isbn: string | null;
  page_count: number | null;
  cover_url: string | null;
  language: string | null;
  publisher: string | null;
  published_date: string | null;
  categories: string[] | null;
  in_shelf?: boolean;
};

const isLoading = ref(true);
const book = ref<Book | null>(null);

const load = async () => {
  isLoading.value = true;
  try {
    const res: any = await apiFetch(`/books/${bookId.value}`);
    book.value = res?.data ?? null;
    if (book.value) {
      useHead({ title: `${book.value.title} - Catálogo` });
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(load);

const addToShelf = async () => {
  if (!book.value) return;
  try {
    const res: any = await apiFetch('/user-shelf', { method: 'POST', body: { book_id: book.value.id } });
    if (res?.data) {
        book.value.in_shelf = true;
        toast.success('Livro adicionado à sua estante!');
    }
  } catch (e: any) {
    toast.error('Erro', { description: e.data?.message || e.message || 'Não foi possível adicionar o livro.' });
  }
};
</script>

<template>
  <div class="py-6">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Livro</h1>
        <NuxtLink to="/books" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">Voltar</NuxtLink>
      </div>

      <div v-if="isLoading" class="text-center py-12">
        <p class="text-gray-500 dark:text-zinc-400">Carregando...</p>
      </div>

      <div v-else-if="!book" class="text-center py-12">
        <p class="text-gray-500 dark:text-zinc-400">Não encontrado.</p>
      </div>

      <div v-else class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
        <div class="p-6">
          <div class="flex gap-4">
            <div class="h-40 w-28 rounded bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 overflow-hidden shrink-0">
              <img v-if="book.cover_url" :src="book.cover_url" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0 flex-1">
              <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ book.title }}</div>
              <div class="text-sm text-gray-500 dark:text-zinc-400">{{ book.author }}</div>

              <div class="mt-3 text-sm text-gray-700 dark:text-zinc-300 space-y-1">
                <div v-if="book.page_count"><span class="font-medium">Páginas:</span> {{ book.page_count }}</div>
                <div v-if="book.isbn"><span class="font-medium">ISBN:</span> {{ book.isbn }}</div>
                <div v-if="book.publisher"><span class="font-medium">Editora:</span> {{ book.publisher }}</div>
                <div v-if="book.published_date"><span class="font-medium">Publicação:</span> {{ book.published_date }}</div>
                <div v-if="book.language"><span class="font-medium">Idioma:</span> {{ book.language }}</div>
              </div>

              <div v-if="book.categories?.length" class="mt-3 flex flex-wrap gap-2">
                <span v-for="cat in book.categories" :key="cat" class="text-[11px] rounded-full bg-purple-100 text-purple-800 px-2 py-0.5 dark:bg-purple-900/30 dark:text-purple-300">
                  {{ cat }}
                </span>
              </div>

              <div class="mt-4 flex gap-2">
                <button
                  class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50"
                  :disabled="book.in_shelf"
                  @click="addToShelf"
                >
                  {{ book.in_shelf ? 'Na estante' : 'Adicionar à estante' }}
                </button>
                <NuxtLink to="/user-shelf" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 border border-gray-200 dark:bg-zinc-900 dark:text-white dark:border-zinc-800 dark:hover:bg-zinc-800">
                  Ver minha estante
                </NuxtLink>
              </div>
            </div>
          </div>

          <div v-if="book.description" class="mt-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Descrição</h2>
            <p class="text-sm text-gray-700 dark:text-zinc-300 whitespace-pre-line">{{ book.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


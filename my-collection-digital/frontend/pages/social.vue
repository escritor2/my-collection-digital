<script setup lang="ts">
import { toast } from 'vue-sonner';
useHead({ title: 'Social - Meu Acervo Digital' });

const { apiFetch } = useApi();

const reviewForm = reactive({
  book_id: '',
  rating: 5,
  title: '',
  content: '',
});

const listForm = reactive({
  name: '',
  description: '',
});

const clubForm = reactive({
  name: '',
  description: '',
});

const { data: socialData, status, refresh: load } = await useAsyncData('social-data', async () => {
  const [f, c, l, s]: any = await Promise.all([
    apiFetch('/social/feed'),
    apiFetch('/social/clubs'),
    apiFetch('/social/lists'),
    apiFetch('/user-shelf')
  ]);
  
  return {
    feed: f?.data ?? [],
    clubs: c?.data ?? [],
    lists: l?.data ?? [],
    shelf: s?.data ?? []
  };
});

const isLoading = computed(() => status.value === 'pending');
const feed = computed(() => socialData.value?.feed ?? []);
const clubs = computed(() => socialData.value?.clubs ?? []);
const lists = computed(() => socialData.value?.lists ?? []);
const shelf = computed(() => socialData.value?.shelf ?? []);

const publishReview = async () => {
  if (!reviewForm.book_id || !reviewForm.content.trim()) return;
  try {
    await apiFetch('/social/reviews', {
      method: 'POST',
      body: {
        book_id: Number(reviewForm.book_id),
        rating: reviewForm.rating,
        title: reviewForm.title || null,
        content: reviewForm.content.trim(),
        is_public: true,
      },
    });
    toast.success('Review publicada com sucesso!');
    reviewForm.book_id = '';
    reviewForm.title = '';
    reviewForm.content = '';
    await load();
  } catch (e: any) {
    toast.error('Erro ao publicar review', { description: e.message });
  }
};

const createList = async () => {
  if (!listForm.name.trim()) return;
  await apiFetch('/social/lists', {
    method: 'POST',
    body: { name: listForm.name.trim(), description: listForm.description.trim() || null, is_public: true },
  });
  listForm.name = '';
  listForm.description = '';
  await load();
};

const createClub = async () => {
  if (!clubForm.name.trim()) return;
  await apiFetch('/social/clubs', {
    method: 'POST',
    body: { name: clubForm.name.trim(), description: clubForm.description.trim() || null, is_public: true },
  });
  clubForm.name = '';
  clubForm.description = '';
  await load();
};

const joinClub = async (club: any) => {
  await apiFetch(`/social/clubs/${club.id}/join`, { method: 'POST' });
  await load();
};
</script>

<template>
  <div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-serif font-semibold text-foreground">Social e Comunidade</h1>
      </div>

      <div v-if="isLoading" class="text-center py-12">
        <p class="text-muted-foreground">Carregando social...</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
          <section class="overflow-hidden bg-card shadow-book sm:rounded-2xl border border-border">
            <div class="p-6">
              <h2 class="text-sm font-semibold text-foreground mb-4">Feed social leve</h2>
              <div v-if="feed.length === 0" class="text-sm text-muted-foreground">Sem atividade ainda.</div>
              <div v-else class="space-y-3">
                <div v-for="(item, i) in feed" :key="i" class="rounded-md border border-border p-3">
                  <div class="text-xs text-muted-foreground mb-1">
                    {{ item.type }} · {{ new Date(item.created_at).toLocaleString() }}
                  </div>
                  <pre class="text-xs whitespace-pre-wrap text-foreground/80 bg-muted rounded-md p-2">{{ JSON.stringify(item.data, null, 2) }}</pre>
                </div>
              </div>
            </div>
          </section>

          <section class="overflow-hidden bg-card shadow-book sm:rounded-2xl border border-border">
            <div class="p-6 space-y-3">
              <h2 class="text-sm font-semibold text-foreground">Publicar review</h2>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                <select v-model="reviewForm.book_id" class="rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                  <option value="" disabled>Selecione um livro...</option>
                  <option v-for="item in shelf" :key="item.book_id" :value="item.book_id">{{ item.book?.title }}</option>
                </select>
                <input v-model.number="reviewForm.rating" type="number" min="1" max="5" placeholder="Nota" class="rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
                <input v-model="reviewForm.title" type="text" placeholder="Título da review" class="md:col-span-2 rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              </div>
              <textarea v-model="reviewForm.content" rows="4" placeholder="Escreva sua review pública..." class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <button class="inline-flex items-center rounded-md bg-brand px-3 py-2 text-sm font-semibold text-brand-foreground shadow-sm hover:bg-brand/90" @click="publishReview">
                Publicar review
              </button>
            </div>
          </section>
        </div>

        <div class="space-y-6">
          <section class="overflow-hidden bg-card shadow-book sm:rounded-2xl border border-border">
            <div class="p-6 space-y-3">
              <h2 class="text-sm font-semibold text-foreground">Listas curadas</h2>
              <input v-model="listForm.name" type="text" placeholder="Nome da lista" class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <textarea v-model="listForm.description" rows="3" placeholder="Descrição" class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <button class="inline-flex items-center rounded-md bg-brand px-3 py-2 text-sm font-semibold text-brand-foreground shadow-sm hover:bg-brand/90" @click="createList">
                Criar lista
              </button>
              <ul class="text-sm text-foreground/80 space-y-1">
                <li v-for="l in lists" :key="l.id">• {{ l.name }} ({{ l.items?.length || 0 }} livros)</li>
              </ul>
            </div>
          </section>

          <section class="overflow-hidden bg-card shadow-book sm:rounded-2xl border border-border">
            <div class="p-6 space-y-3">
              <h2 class="text-sm font-semibold text-foreground">Clube do livro</h2>
              <input v-model="clubForm.name" type="text" placeholder="Nome do clube" class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <textarea v-model="clubForm.description" rows="3" placeholder="Descrição" class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <button class="inline-flex items-center rounded-md bg-brand px-3 py-2 text-sm font-semibold text-brand-foreground shadow-sm hover:bg-brand/90" @click="createClub">
                Criar clube
              </button>
              <ul class="space-y-2">
                <li v-for="club in clubs" :key="club.id" class="text-sm">
                  <div class="flex items-center justify-between gap-2">
                    <span class="text-foreground/80">{{ club.name }} ({{ club.memberships_count }} membros)</span>
                    <button class="text-xs rounded-md border border-border px-2 py-1 text-foreground hover:bg-secondary transition-colors" @click="joinClub(club)">Entrar</button>
                  </div>
                </li>
              </ul>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>
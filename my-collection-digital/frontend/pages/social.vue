<script setup lang="ts">
import { toast } from 'vue-sonner';
import { Star, BookOpen, Highlighter, ListChecks, MessageSquare, Search, UserPlus, UserCheck, Users } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '~/components/ui/card';
import { Button } from '~/components/ui/button';
import { Badge } from '~/components/ui/badge';
import { useInitials } from '~/composables/useInitials';

useHead({ title: 'Social - Meu Acervo Digital' });

const { apiFetch } = useApi();
const { getInitials } = useInitials();

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

const userQuery = ref('');
const userResults = ref<any[]>([]);
const isSearchingUsers = ref(false);

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

// Iniciais do nome usadas como avatar de "letra", já que ainda não existe foto de perfil.
const timeAgo = (dateStr: string) => {
  const diffMs = Date.now() - new Date(dateStr).getTime();
  const mins = Math.floor(diffMs / 60000);
  if (mins < 1) return 'agora mesmo';
  if (mins < 60) return `há ${mins} min`;
  const hours = Math.floor(mins / 60);
  if (hours < 24) return `há ${hours}h`;
  const days = Math.floor(hours / 24);
  if (days < 30) return `há ${days}d`;
  return new Date(dateStr).toLocaleDateString('pt-BR');
};

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

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(userQuery, (value) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  if (!value.trim()) {
    userResults.value = [];
    return;
  }
  searchTimeout = setTimeout(async () => {
    isSearchingUsers.value = true;
    try {
      const res: any = await apiFetch('/social/users/search', { query: { q: value.trim() } });
      userResults.value = res?.data ?? [];
    } finally {
      isSearchingUsers.value = false;
    }
  }, 350);
});

const toggleFollow = async (person: any) => {
  if (person.is_following) {
    await apiFetch(`/social/follow/${person.id}`, { method: 'DELETE' });
    person.is_following = false;
  } else {
    await apiFetch(`/social/follow/${person.id}`, { method: 'POST' });
    person.is_following = true;
  }
};
</script>

<template>
  <div class="py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-muted-foreground">Comunidade</p>
        <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-foreground mt-1">Social e Leitura em Grupo</h1>
        <p class="text-muted-foreground mt-2 max-w-2xl">Reviews, listas e clubes de leitura de quem você segue.</p>
      </div>

      <div v-if="isLoading" class="text-center py-16 text-muted-foreground">Carregando...</div>

      <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
          <!-- Feed -->
          <Card class="border-border bg-card shadow-book">
            <CardHeader>
              <CardTitle class="text-lg font-serif font-semibold text-foreground">Feed</CardTitle>
              <CardDescription>Atividade recente sua e de quem você segue</CardDescription>
            </CardHeader>
            <CardContent>
              <div v-if="feed.length === 0" class="text-sm text-muted-foreground py-6 text-center">
                Nada por aqui ainda. Siga leitores ou publique uma review pra começar.
              </div>
              <div v-else class="space-y-4">
                <div v-for="(item, i) in feed" :key="i" class="rounded-xl border border-border bg-background p-4">
                  <!-- Cabeçalho comum: avatar de iniciais + autor + tempo -->
                  <div class="flex items-center gap-3 mb-3">
                    <NuxtLink :to="`/social/profile/${item.data.user?.id}`" class="flex items-center gap-3 min-w-0 group">
                      <div class="h-9 w-9 rounded-full bg-brand text-brand-foreground flex items-center justify-center text-xs font-bold shrink-0 overflow-hidden">
                        <img v-if="item.data.user?.avatar_url" :src="item.data.user.avatar_url" :alt="item.data.user?.name" class="h-full w-full object-cover" />
                        <template v-else>{{ getInitials(item.data.user?.name) }}</template>
                      </div>
                      <div class="min-w-0">
                        <div class="text-sm font-semibold text-foreground truncate group-hover:text-brand transition-colors">{{ item.data.user?.name || 'Alguém' }}</div>
                        <div class="text-[11px] text-muted-foreground">{{ timeAgo(item.created_at) }}</div>
                      </div>
                    </NuxtLink>
                    <div class="ml-auto">
                      <Badge variant="outline" class="text-[10px] gap-1 border-border text-muted-foreground">
                        <BookOpen v-if="item.type === 'review'" class="h-3 w-3" />
                        <Highlighter v-else-if="item.type === 'highlight'" class="h-3 w-3" />
                        <ListChecks v-else-if="item.type === 'list'" class="h-3 w-3" />
                        <MessageSquare v-else class="h-3 w-3" />
                        {{ item.type === 'review' ? 'review' : item.type === 'highlight' ? 'trecho' : item.type === 'list' ? 'lista' : 'clube' }}
                      </Badge>
                    </div>
                  </div>

                  <!-- Review -->
                  <div v-if="item.type === 'review'">
                    <div class="flex items-center gap-2 mb-1">
                      <h4 class="font-serif font-semibold text-foreground">{{ item.data.title || item.data.book?.title }}</h4>
                      <div v-if="item.data.rating" class="flex items-center gap-0.5 text-chart-5">
                        <Star v-for="n in 5" :key="n" class="h-3 w-3" :class="n <= item.data.rating ? 'fill-chart-5' : 'fill-none opacity-30'" />
                      </div>
                    </div>
                    <p class="text-xs text-muted-foreground mb-2">{{ item.data.book?.title }} · {{ item.data.book?.author }}</p>
                    <p class="text-sm text-foreground leading-relaxed">{{ item.data.content }}</p>
                  </div>

                  <!-- Trecho compartilhado -->
                  <div v-else-if="item.type === 'highlight'">
                    <p class="text-sm text-muted-foreground mb-2">compartilhou um trecho de <strong class="text-foreground">{{ item.data.annotation?.userBook?.book?.title }}</strong></p>
                    <blockquote class="border-l-2 border-brand pl-3 text-sm italic text-foreground">
                      "{{ item.data.annotation?.text || item.data.annotation?.note }}"
                    </blockquote>
                  </div>

                  <!-- Lista curada -->
                  <div v-else-if="item.type === 'list'">
                    <h4 class="font-serif font-semibold text-foreground mb-1">{{ item.data.name }}</h4>
                    <p v-if="item.data.description" class="text-sm text-muted-foreground mb-2">{{ item.data.description }}</p>
                    <p class="text-xs text-muted-foreground">{{ item.data.items?.length || 0 }} livros nesta lista</p>
                  </div>

                  <!-- Post de clube -->
                  <div v-else-if="item.type === 'club_post'">
                    <p class="text-xs text-muted-foreground mb-1">no clube <strong class="text-foreground">{{ item.data.club?.name }}</strong></p>
                    <h4 v-if="item.data.title" class="font-serif font-semibold text-foreground mb-1">{{ item.data.title }}</h4>
                    <p class="text-sm text-foreground leading-relaxed">{{ item.data.content }}</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Publicar review -->
          <Card class="border-border bg-card shadow-book">
            <CardHeader>
              <CardTitle class="text-lg font-serif font-semibold text-foreground">Publicar review</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                <select v-model="reviewForm.book_id" class="rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                  <option value="" disabled>Selecione um livro...</option>
                  <option v-for="item in shelf" :key="item.book_id" :value="item.book_id">{{ item.book?.title }}</option>
                </select>
                <input v-model.number="reviewForm.rating" type="number" min="1" max="5" placeholder="Nota" class="rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
                <input v-model="reviewForm.title" type="text" placeholder="Título da review" class="md:col-span-2 rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              </div>
              <textarea v-model="reviewForm.content" rows="4" placeholder="Escreva sua review pública..." class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <Button class="bg-brand hover:bg-brand/90 text-brand-foreground" @click="publishReview">Publicar review</Button>
            </CardContent>
          </Card>
        </div>

        <div class="space-y-6">
          <!-- Buscar leitores -->
          <Card class="border-border bg-card shadow-book">
            <CardHeader>
              <CardTitle class="text-lg font-serif font-semibold text-foreground flex items-center gap-2">
                <Users class="h-5 w-5 text-brand" />
                Encontrar leitores
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <input
                  v-model="userQuery"
                  type="text"
                  placeholder="Buscar pelo nome..."
                  class="w-full rounded-md border border-border bg-background pl-9 pr-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                />
              </div>

              <p v-if="isSearchingUsers" class="text-xs text-muted-foreground">Buscando...</p>

              <ul v-else-if="userResults.length" class="space-y-2">
                <li v-for="person in userResults" :key="person.id" class="flex items-center gap-3">
                  <NuxtLink :to="`/social/profile/${person.id}`" class="flex items-center gap-3 min-w-0 flex-1 group">
                    <div class="h-9 w-9 rounded-full bg-brand text-brand-foreground flex items-center justify-center text-xs font-bold shrink-0 overflow-hidden">
                      <img v-if="person.avatar_url" :src="person.avatar_url" :alt="person.name" class="h-full w-full object-cover" />
                      <template v-else>{{ getInitials(person.name) }}</template>
                    </div>
                    <span class="text-sm font-medium text-foreground truncate group-hover:text-brand transition-colors">{{ person.name }}</span>
                  </NuxtLink>
                  <Button size="sm" :variant="person.is_following ? 'outline' : 'default'" :class="!person.is_following ? 'bg-brand hover:bg-brand/90 text-brand-foreground' : ''" class="gap-1.5 shrink-0" @click="toggleFollow(person)">
                    <UserCheck v-if="person.is_following" class="h-3.5 w-3.5" />
                    <UserPlus v-else class="h-3.5 w-3.5" />
                    {{ person.is_following ? 'Seguindo' : 'Seguir' }}
                  </Button>
                </li>
              </ul>

              <p v-else-if="userQuery.trim()" class="text-xs text-muted-foreground">Nenhum leitor encontrado.</p>
              <p v-else class="text-xs text-muted-foreground">Digite um nome pra encontrar outros leitores e seguir o que estão lendo.</p>
            </CardContent>
          </Card>

          <!-- Listas curadas -->
          <Card class="border-border bg-card shadow-book">
            <CardHeader>
              <CardTitle class="text-lg font-serif font-semibold text-foreground">Listas curadas</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <input v-model="listForm.name" type="text" placeholder="Nome da lista" class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <textarea v-model="listForm.description" rows="3" placeholder="Descrição" class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <Button variant="outline" class="border-brand/30 hover:bg-brand/10" @click="createList">Criar lista</Button>
              <ul class="text-sm text-foreground space-y-1 pt-2">
                <li v-for="l in lists" :key="l.id" class="flex items-center gap-2">
                  <ListChecks class="h-3.5 w-3.5 text-brand shrink-0" />
                  {{ l.name }} <span class="text-muted-foreground text-xs">({{ l.items?.length || 0 }} livros)</span>
                </li>
              </ul>
            </CardContent>
          </Card>

          <!-- Clube do livro -->
          <Card class="border-border bg-card shadow-book">
            <CardHeader>
              <CardTitle class="text-lg font-serif font-semibold text-foreground">Clube do livro</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <input v-model="clubForm.name" type="text" placeholder="Nome do clube" class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <textarea v-model="clubForm.description" rows="3" placeholder="Descrição" class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
              <Button variant="outline" class="border-brand/30 hover:bg-brand/10" @click="createClub">Criar clube</Button>
              <ul class="space-y-2 pt-2">
                <li v-for="club in clubs" :key="club.id" class="flex items-center justify-between gap-2 text-sm">
                  <NuxtLink :to="`/social/clubs/${club.id}`" class="text-foreground hover:text-brand transition-colors truncate">
                    {{ club.name }} <span class="text-muted-foreground text-xs">({{ club.memberships_count }} membros)</span>
                  </NuxtLink>
                  <Button size="sm" variant="outline" class="shrink-0" @click="joinClub(club)">Entrar</Button>
                </li>
              </ul>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Send, Users } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '~/components/ui/card';
import { Button } from '~/components/ui/button';
import { useInitials } from '~/composables/useInitials';

const route = useRoute();
const clubId = computed(() => String(route.params.id));
useHead({ title: 'Clube do livro - Meu Acervo Digital' });

const { apiFetch } = useApi();
const { getInitials } = useInitials();

const club = ref<any>(null);
const posts = ref<any[]>([]);
const isLoading = ref(true);
const postContent = ref('');
const postTitle = ref('');
const isPosting = ref(false);

const timeAgo = (dateStr: string) => {
  const diffMs = Date.now() - new Date(dateStr).getTime();
  const mins = Math.floor(diffMs / 60000);
  if (mins < 1) return 'agora mesmo';
  if (mins < 60) return `há ${mins} min`;
  const hours = Math.floor(mins / 60);
  if (hours < 24) return `há ${hours}h`;
  return `há ${Math.floor(hours / 24)}d`;
};

const load = async () => {
  isLoading.value = true;
  try {
    const [clubsRes, postsRes]: any = await Promise.all([
      apiFetch('/social/clubs'),
      apiFetch(`/social/clubs/${clubId.value}/posts`),
    ]);
    club.value = (clubsRes?.data ?? []).find((c: any) => String(c.id) === clubId.value) ?? null;
    posts.value = postsRes?.data ?? [];
  } finally {
    isLoading.value = false;
  }
};

onMounted(load);

const publishPost = async () => {
  if (!postContent.value.trim()) return;
  isPosting.value = true;
  try {
    await apiFetch(`/social/clubs/${clubId.value}/posts`, {
      method: 'POST',
      body: { title: postTitle.value.trim() || null, content: postContent.value.trim() },
    });
    postContent.value = '';
    postTitle.value = '';
    await load();
  } finally {
    isPosting.value = false;
  }
};
</script>

<template>
  <div class="py-10">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">
      <div v-if="isLoading" class="text-center py-16 text-muted-foreground">Carregando clube...</div>

      <template v-else-if="club">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-muted-foreground flex items-center gap-2">
            <Users class="h-3.5 w-3.5" /> Clube do livro
          </p>
          <h1 class="font-serif text-3xl font-semibold text-foreground mt-1">{{ club.name }}</h1>
          <p v-if="club.description" class="text-muted-foreground mt-2">{{ club.description }}</p>
          <p class="text-xs text-muted-foreground mt-2">{{ club.memberships_count }} membros</p>
        </div>

        <Card class="border-border bg-card shadow-book">
          <CardHeader>
            <CardTitle class="text-base font-serif font-semibold text-foreground">Publicar no clube</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <input v-model="postTitle" type="text" placeholder="Título (opcional)" class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
            <textarea v-model="postContent" rows="3" placeholder="Compartilhe algo com o clube..." class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
            <Button :disabled="isPosting || !postContent.trim()" class="gap-2 bg-brand hover:bg-brand/90 text-brand-foreground" @click="publishPost">
              <Send class="h-4 w-4" /> Publicar
            </Button>
          </CardContent>
        </Card>

        <div class="space-y-4">
          <div v-if="!posts.length" class="text-sm text-muted-foreground text-center py-8">Nenhum post ainda. Seja o primeiro a compartilhar algo.</div>
          <Card v-for="post in posts" :key="post.id" class="border-border bg-card shadow-book">
            <CardContent class="p-4">
              <div class="flex items-center gap-3 mb-3">
                <div class="h-9 w-9 rounded-full bg-brand text-brand-foreground flex items-center justify-center text-xs font-bold shrink-0 overflow-hidden">
                  <img v-if="post.user?.avatar_url" :src="post.user.avatar_url" :alt="post.user?.name" class="h-full w-full object-cover" />
                  <template v-else>{{ getInitials(post.user?.name) }}</template>
                </div>
                <div class="min-w-0">
                  <div class="text-sm font-semibold text-foreground truncate">{{ post.user?.name }}</div>
                  <div class="text-[11px] text-muted-foreground">{{ timeAgo(post.created_at) }}</div>
                </div>
              </div>
              <h4 v-if="post.title" class="font-serif font-semibold text-foreground mb-1">{{ post.title }}</h4>
              <p class="text-sm text-foreground leading-relaxed whitespace-pre-wrap">{{ post.content }}</p>
            </CardContent>
          </Card>
        </div>
      </template>

      <div v-else class="text-center py-16 text-muted-foreground">Clube não encontrado (ou você não é membro de um clube privado).</div>
    </div>
  </div>
</template>

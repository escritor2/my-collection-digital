<script setup lang="ts">
import { UserPlus, UserCheck, Star } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '~/components/ui/card';
import { Button } from '~/components/ui/button';
import { useInitials } from '~/composables/useInitials';

const route = useRoute();
const profileId = computed(() => String(route.params.id));
useHead({ title: 'Perfil social - Meu Acervo Digital' });

const { apiFetch } = useApi();
const { getInitials } = useInitials();

const profile = ref<any>(null);
const isLoading = ref(true);

const load = async () => {
  isLoading.value = true;
  try {
    const res: any = await apiFetch(`/social/profile/${profileId.value}`);
    profile.value = res?.data ?? null;
  } finally {
    isLoading.value = false;
  }
};

onMounted(load);

const toggleFollow = async () => {
  if (!profile.value?.user?.id) return;
  const id = profile.value.user.id;
  if (profile.value.is_following) {
    await apiFetch(`/social/follow/${id}`, { method: 'DELETE' });
    profile.value.is_following = false;
  } else {
    await apiFetch(`/social/follow/${id}`, { method: 'POST' });
    profile.value.is_following = true;
  }
};
</script>

<template>
  <div class="py-10">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
      <div v-if="isLoading" class="text-center py-16 text-muted-foreground">Carregando perfil...</div>
      <div v-else-if="!profile" class="text-center py-16 text-muted-foreground">Perfil não encontrado.</div>
      <div v-else class="space-y-6">
        <Card class="border-border bg-card shadow-book">
          <CardContent class="p-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
              <div class="flex items-center gap-4">
                <div class="h-16 w-16 rounded-full bg-brand text-brand-foreground flex items-center justify-center text-lg font-bold shrink-0 overflow-hidden">
                  <img v-if="profile.user.avatar_url" :src="profile.user.avatar_url" :alt="profile.user.name" class="h-full w-full object-cover" />
                  <template v-else>{{ getInitials(profile.user.name) }}</template>
                </div>
                <div>
                  <h1 class="font-serif text-2xl font-semibold text-foreground">{{ profile.user.name }}</h1>
                  <p class="text-sm text-muted-foreground">{{ profile.followers_count }} seguidores · {{ profile.following_count }} seguindo</p>
                </div>
              </div>
              <Button :variant="profile.is_following ? 'outline' : 'default'" :class="!profile.is_following ? 'bg-brand hover:bg-brand/90 text-brand-foreground' : ''" class="gap-2" @click="toggleFollow">
                <UserCheck v-if="profile.is_following" class="h-4 w-4" />
                <UserPlus v-else class="h-4 w-4" />
                {{ profile.is_following ? 'Seguindo' : 'Seguir' }}
              </Button>
            </div>
          </CardContent>
        </Card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <Card class="border-border bg-card shadow-book">
            <CardHeader>
              <CardTitle class="text-base font-serif font-semibold text-foreground">Reviews públicas</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="!profile.reviews?.length" class="text-sm text-muted-foreground">Nenhuma review.</div>
              <div v-else class="space-y-3">
                <article v-for="r in profile.reviews" :key="r.id" class="rounded-lg border border-border bg-background p-3">
                  <div class="flex items-center gap-2">
                    <div class="text-sm font-semibold text-foreground">{{ r.title || 'Review' }}</div>
                    <div v-if="r.rating" class="flex items-center gap-0.5 text-chart-5">
                      <Star v-for="n in 5" :key="n" class="h-3 w-3" :class="n <= r.rating ? 'fill-chart-5' : 'fill-none opacity-30'" />
                    </div>
                  </div>
                  <div class="text-xs text-muted-foreground">{{ r.book?.title }}</div>
                  <p class="text-sm text-foreground mt-2">{{ r.content }}</p>
                </article>
              </div>
            </CardContent>
          </Card>

          <Card class="border-border bg-card shadow-book">
            <CardHeader>
              <CardTitle class="text-base font-serif font-semibold text-foreground">Listas curadas</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="!profile.lists?.length" class="text-sm text-muted-foreground">Nenhuma lista.</div>
              <div v-else class="space-y-3">
                <article v-for="l in profile.lists" :key="l.id" class="rounded-lg border border-border bg-background p-3">
                  <div class="text-sm font-semibold text-foreground">{{ l.name }}</div>
                  <p class="text-xs text-muted-foreground">{{ l.description || 'Sem descrição' }}</p>
                  <div class="text-xs text-muted-foreground mt-2">{{ l.items?.length || 0 }} livros</div>
                </article>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>

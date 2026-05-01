<script setup lang="ts">
const route = useRoute();
const profileId = computed(() => String(route.params.id));
useHead({ title: 'Perfil social - Meu Acervo Digital' });

const { apiFetch } = useApi();

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
  <div class="py-6">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
      <div v-if="isLoading" class="text-center py-12 text-gray-500 dark:text-zinc-400">Carregando perfil...</div>
      <div v-else-if="!profile" class="text-center py-12 text-gray-500 dark:text-zinc-400">Perfil não encontrado.</div>
      <div v-else class="space-y-6">
        <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ profile.user.name }}</h1>
                <p class="text-sm text-gray-500 dark:text-zinc-400">{{ profile.user.email }}</p>
              </div>
              <button class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500" @click="toggleFollow">
                {{ profile.is_following ? 'Deixar de seguir' : 'Seguir' }}
              </button>
            </div>
            <div class="mt-4 text-sm text-gray-700 dark:text-zinc-300">
              {{ profile.followers_count }} seguidores · {{ profile.following_count }} seguindo
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <section class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Reviews públicas</h2>
              <div v-if="!profile.reviews?.length" class="text-sm text-gray-500 dark:text-zinc-400">Nenhuma review.</div>
              <div v-else class="space-y-3">
                <article v-for="r in profile.reviews" :key="r.id" class="rounded border border-gray-200 dark:border-zinc-800 p-3">
                  <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ r.title || 'Review' }}</div>
                  <div class="text-xs text-gray-500 dark:text-zinc-400">{{ r.book?.title }} · {{ r.rating || '—' }}/5</div>
                  <p class="text-sm text-gray-700 dark:text-zinc-300 mt-2">{{ r.content }}</p>
                </article>
              </div>
            </div>
          </section>

          <section class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Listas curadas</h2>
              <div v-if="!profile.lists?.length" class="text-sm text-gray-500 dark:text-zinc-400">Nenhuma lista.</div>
              <div v-else class="space-y-3">
                <article v-for="l in profile.lists" :key="l.id" class="rounded border border-gray-200 dark:border-zinc-800 p-3">
                  <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ l.name }}</div>
                  <p class="text-xs text-gray-500 dark:text-zinc-400">{{ l.description || 'Sem descrição' }}</p>
                  <div class="text-xs text-gray-600 dark:text-zinc-300 mt-2">{{ l.items?.length || 0 }} livros</div>
                </article>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import ReadingGoalCard from '@/components/ReadingGoalCard.vue';
import ReadingStreakCard from '@/components/ReadingStreakCard.vue';
import ReadingGoalDialog from '@/components/ReadingGoalDialog.vue';

definePageMeta({
    middleware: 'auth',
    layout: 'dashboard'
});

useHead({
    title: 'Dashboard - My Digital Collection'
});

const config = useRuntimeConfig();

const { data: dashboardData, pending, error, refresh } = await useFetch('/api/dashboard', {
    baseURL: config.public.apiBase,
    headers: {
        Accept: 'application/json'
    },
});

const stats = computed(() => dashboardData.value?.statistics || {
    total_books_read: 0,
    total_pages_read: 0,
    total_reading_time_minutes: 0,
    monthly_average_pages: 0,
    monthly_average_time_minutes: 0
});

const activeGoal = computed(() => dashboardData.value?.active_goal);
const streak = computed(() => dashboardData.value?.streak || { current_streak: 0, longest_streak: 0 });

function reloadDashboard() {
    refresh();
}
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Visão Geral</h1>
        
        <div v-if="pending" class="text-gray-500">Carregando estatísticas...</div>
        <div v-else-if="error" class="text-red-500">Erro ao carregar estatísticas.</div>
        <div v-else class="space-y-6">
            
            <!-- Metas e Ofensivas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <ReadingGoalCard :goal="activeGoal">
                    <template #action>
                        <ReadingGoalDialog :goal="activeGoal" @saved="reloadDashboard" />
                    </template>
                </ReadingGoalCard>
                
                <ReadingStreakCard :streak="streak" />
            </div>

            <!-- Estatísticas Gerais -->
            <h2 class="text-xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">Estatísticas Gerais</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Livros lidos -->
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow sm:rounded-lg border dark:border-zinc-800">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Livros Lidos</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.total_books_read }}</dd>
                </div>
            </div>

            <!-- Páginas lidas -->
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow sm:rounded-lg border dark:border-zinc-800">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Páginas Lidas (Total)</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.total_pages_read }}</dd>
                </div>
            </div>

            <!-- Tempo de leitura -->
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow sm:rounded-lg border dark:border-zinc-800">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Tempo Total (minutos)</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.total_reading_time_minutes }}</dd>
                </div>
            </div>

            <!-- Média mensal de páginas -->
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow sm:rounded-lg border dark:border-zinc-800">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Média de Páginas/Mês</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.monthly_average_pages }}</dd>
                </div>
            </div>

            <!-- Média mensal de tempo -->
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow sm:rounded-lg border dark:border-zinc-800">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Média de Tempo/Mês (min)</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.monthly_average_time_minutes }}</dd>
                </div>
            </div>
        </div>
        </div>
    </div>
</template>

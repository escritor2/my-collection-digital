<script setup lang="ts">
import { 
  TrendingUp, 
  BookOpen, 
  Clock, 
  Calendar, 
  Download, 
  Terminal, 
  Code, 
  BrainCircuit, 
  ChevronRight,
  Target,
  Search,
  Zap,
  Award,
  Check,
  Flame,
  Lightbulb,
  Sparkles,
  BarChart3,
  Rocket
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '~/components/ui/card';
import { Button } from '~/components/ui/button';
import { Badge } from '~/components/ui/badge';
import { Skeleton } from '~/components/ui/skeleton';

useHead({ title: 'Dashboard - Meu Acervo Digital' });

definePageMeta({
    middleware: ['auth']
});

const { apiFetch } = useApi();
const { dyslexiaMode, setDyslexiaMode } = useAccessibilityPrefs();

type HeatmapDay = { date: string; minutes: number; pages: number; sessions: number };
type Speed = { days: number; total_pages: number; total_minutes: number; pages_per_hour: number };
type Yearly = { year: number; books_finished: number; pages_read: number; reading_time_minutes: number; best_streak_days: number };
type Learning = {
  total_tech_books: number;
  finished_tech_books: number;
  total_pages_tech: number;
  total_minutes_tech: number;
  books: Array<{
    id: number;
    title: string;
    author: string;
    progress: number;
    pages_read: number;
    total_pages: number;
  }>;
};

const selectedYear = ref<number>(new Date().getFullYear());
const isExporting = ref(false);

const { data: dashboardData, status } = await useAsyncData('dashboard-data', async () => {
    const [h, s, y, l, a]: any = await Promise.all([
      apiFetch('/analytics/heatmap', { query: { days: 120 } }),
      apiFetch('/analytics/speed', { query: { days: 30 } }),
      apiFetch('/analytics/yearly', { query: { year: selectedYear.value } }),
      apiFetch('/analytics/learning'),
      apiFetch('/achievements'),
    ]);

    let sug = [];
    const learningData = l?.data;
    if (!learningData?.books?.length) {
      const sugReq: any = await apiFetch('/analytics/suggestions/tech');
      sug = sugReq?.data ?? [];
    }

    return {
      heatmap: h?.data ?? [],
      speed: s?.data ?? null,
      yearly: y?.data ?? null,
      learning: learningData ?? null,
      achievements: a?.data ?? [],
      suggestions: sug
    };
}, { watch: [selectedYear] });

const isLoading = computed(() => status.value === 'pending');
const heatmapDays = computed(() => dashboardData.value?.heatmap ?? []);
const speed = computed(() => dashboardData.value?.speed ?? null);
const yearly = computed(() => dashboardData.value?.yearly ?? null);
const learning = computed(() => dashboardData.value?.learning ?? null);
const achievements = computed(() => dashboardData.value?.achievements ?? []);
const suggestions = computed(() => dashboardData.value?.suggestions ?? []);

const maxMinutes = computed(() => Math.max(1, ...heatmapDays.value.map(d => d.minutes || 0)));

const heatColorClass = (minutes: number) => {
  const ratio = minutes / maxMinutes.value;
  if (minutes <= 0) return 'bg-gray-100 dark:bg-zinc-800';
  if (ratio < 0.25) return 'bg-purple-200 dark:bg-purple-900/30';
  if (ratio < 0.5) return 'bg-purple-300 dark:bg-purple-900/50';
  if (ratio < 0.75) return 'bg-purple-400 dark:bg-purple-800/70';
  return 'bg-purple-600 dark:bg-purple-700';
};

const { user } = useAuth();

const nextLevelXp = computed(() => (user.value?.level ?? 1) * 1000);
const currentLevelXp = computed(() => ((user.value?.level ?? 1) - 1) * 1000);
const xpProgress = computed(() => {
  if (!user.value) return 0;
  const relativeXp = user.value.xp % 1000;
  return (relativeXp / 1000) * 100;
});

const exportAnalytics = async (format: 'json' | 'csv') => {
  isExporting.value = true;
  try {
    const { baseURL } = useApi();
    const url = new URL(`${baseURL}/api/analytics/export`);
    url.searchParams.set('format', format);
    url.searchParams.set('days', '120');
    url.searchParams.set('year', String(selectedYear.value));

    const response = await fetch(url.toString(), { credentials: 'include' });
    if (!response.ok) throw new Error('Falha ao exportar analytics');
    const blob = await response.blob();
    const href = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = href;
    a.download = format === 'csv' ? 'analytics-heatmap.csv' : `analytics-${selectedYear.value}.json`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(href);
  } finally {
    isExporting.value = false;
  }
};
</script>

<template>
  <div class="py-10 space-y-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
        <div>
          <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">Seu Progresso</h1>
          <p class="text-neutral-500 dark:text-neutral-400 mt-1">Acompanhe suas métricas e evolução de leitura.</p>
        </div>

        <div class="flex items-center gap-3">
          <Badge variant="outline" class="h-9 px-3 gap-2 font-medium">
            <span :class="['h-2 w-2 rounded-full', dyslexiaMode ? 'bg-purple-500' : 'bg-gray-300']"></span>
            Modo dislexia
            <input type="checkbox" class="sr-only" :checked="dyslexiaMode" @change="setDyslexiaMode(($event.target as HTMLInputElement).checked)" />
          </Badge>

          <select
            v-model="selectedYear"
            class="h-9 rounded-md border border-gray-200 bg-white px-3 py-1 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
          >
            <option v-for="y in [selectedYear, selectedYear-1, selectedYear-2]" :key="y" :value="y">{{ y }}</option>
          </select>

          <Button variant="outline" size="sm" :disabled="isExporting" @click="exportAnalytics('json')" class="gap-2">
            <Download class="h-4 w-4" />
            Exportar
          </Button>
        </div>
      </div>

      <!-- Gamification Row -->
      <div v-if="user" class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
        <Card class="lg:col-span-2 overflow-hidden border-purple-500/20 bg-gradient-to-br from-white to-purple-50/30 dark:from-zinc-950 dark:to-purple-950/10">
          <CardContent class="p-6">
            <div class="flex flex-col md:flex-row items-center gap-6">
              <div class="relative flex items-center justify-center">
                <div class="h-20 w-20 rounded-full border-4 border-purple-500/20 flex items-center justify-center bg-white dark:bg-zinc-900 shadow-xl">
                  <span class="text-3xl font-black text-purple-600">{{ user.level }}</span>
                </div>
                <div class="absolute -bottom-2 bg-purple-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-tighter">Nível</div>
              </div>
              
              <div class="flex-1 w-full space-y-4">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-lg font-bold flex items-center gap-2">
                      Experiência (XP)
                      <Zap class="h-4 w-4 text-amber-500 fill-amber-500" />
                    </h3>
                    <p class="text-xs text-neutral-500">{{ user.xp }} / {{ nextLevelXp }} XP para o próximo nível</p>
                  </div>
                  <div class="text-right">
                    <span class="text-sm font-bold text-purple-600">{{ Math.round(xpProgress) }}%</span>
                  </div>
                </div>
                
                <div class="h-3 w-full bg-gray-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-gradient-to-r from-purple-500 to-indigo-600 transition-all duration-1000 ease-out"
                    :style="{ width: `${xpProgress}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-amber-500/20 bg-gradient-to-br from-white to-amber-50/30 dark:from-zinc-950 dark:to-amber-950/10">
          <CardContent class="p-6 flex items-center gap-6">
            <div class="h-16 w-16 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500 shadow-inner">
              <Flame class="h-8 w-8 fill-amber-500" />
            </div>
            <div>
              <div class="text-3xl font-black text-amber-600">{{ user.streak_days }} dias</div>
              <div class="text-sm font-bold text-neutral-500 uppercase tracking-widest mt-1">Sequência Atual</div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium text-neutral-500">Livros Concluídos</CardTitle>
            <BookOpen class="h-4 w-4 text-purple-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ yearly?.books_finished ?? 0 }}</div>
            <p class="text-xs text-neutral-500 mt-1">Em {{ selectedYear }}</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium text-neutral-500">Páginas Lidas</CardTitle>
            <TrendingUp class="h-4 w-4 text-blue-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ yearly?.pages_read?.toLocaleString() ?? 0 }}</div>
            <p class="text-xs text-neutral-500 mt-1">Total acumulado no ano</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium text-neutral-500">Tempo de Leitura</CardTitle>
            <Clock class="h-4 w-4 text-orange-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ Math.round((yearly?.reading_time_minutes ?? 0) / 60) }}h {{ (yearly?.reading_time_minutes ?? 0) % 60 }}m</div>
            <p class="text-xs text-neutral-500 mt-1">Foco total investido</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium text-neutral-500">Maior Sequência</CardTitle>
            <Target class="h-4 w-4 text-emerald-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ yearly?.best_streak_days ?? 0 }} dias</div>
            <p class="text-xs text-neutral-500 mt-1">Seu recorde pessoal</p>
          </CardContent>
        </Card>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Heatmap Section -->
        <Card class="lg:col-span-2">
          <CardHeader>
            <CardTitle class="text-lg font-bold flex items-center gap-2">
              <Calendar class="h-5 w-5 text-purple-500" />
              Atividade de Leitura
            </CardTitle>
            <CardDescription>Consistência nos últimos 120 dias</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="isLoading" class="flex flex-wrap gap-1">
              <Skeleton v-for="i in 120" :key="i" class="h-3 w-3 rounded-sm" />
            </div>
            <div v-else class="flex flex-wrap gap-1">
              <div
                v-for="day in heatmapDays"
                :key="day.date"
                class="h-3 w-3 rounded-sm cursor-pointer transition-all hover:ring-2 hover:ring-purple-400"
                :class="heatColorClass(day.minutes)"
                :title="`${day.date}: ${day.minutes} min, ${day.pages} pág`"
              ></div>
            </div>
            <div class="mt-4 flex items-center justify-end gap-2 text-[10px] text-neutral-500 uppercase tracking-wider font-semibold">
              <span>Menos</span>
              <div class="h-2.5 w-2.5 rounded-sm bg-gray-100 dark:bg-zinc-800"></div>
              <div class="h-2.5 w-2.5 rounded-sm bg-purple-200 dark:bg-purple-900/30"></div>
              <div class="h-2.5 w-2.5 rounded-sm bg-purple-400 dark:bg-purple-800/70"></div>
              <div class="h-2.5 w-2.5 rounded-sm bg-purple-600 dark:bg-purple-700"></div>
              <span>Mais</span>
            </div>
          </CardContent>
        </Card>

        <!-- Reading Speed -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-bold flex items-center gap-2">
              <TrendingUp class="h-5 w-5 text-blue-500" />
              Velocidade
            </CardTitle>
            <CardDescription>Últimos 30 dias</CardDescription>
          </CardHeader>
          <CardContent class="flex flex-col items-center justify-center py-6">
            <div class="text-5xl font-black text-blue-500">{{ speed?.pages_per_hour ?? 0 }}</div>
            <div class="text-sm font-bold text-neutral-500 uppercase mt-2 tracking-widest">Páginas / Hora</div>
            <div class="w-full mt-6 space-y-2">
              <div class="flex justify-between text-xs font-medium">
                <span class="text-neutral-500">Total de páginas</span>
                <span>{{ speed?.total_pages ?? 0 }}</span>
              </div>
              <div class="flex justify-between text-xs font-medium">
                <span class="text-neutral-500">Tempo investido</span>
                <span>{{ Math.round((speed?.total_minutes ?? 0) / 60) }}h</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Programming / Learning Path Section -->
        <div class="lg:col-span-3 space-y-8">
          <Card class="border-purple-500/20 bg-purple-500/5 overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
              <Rocket class="h-32 w-32 -mr-8 -mt-8 text-purple-600" />
            </div>
            
            <CardHeader class="relative">
              <div class="flex items-center justify-between">
                <div>
                  <CardTitle class="text-2xl font-black flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-purple-600 text-white shadow-lg shadow-purple-500/20">
                      <Terminal class="h-6 w-6" />
                    </div>
                    Trilha de Aprendizado Tech
                  </CardTitle>
                  <CardDescription class="mt-2 text-base">Domine novas tecnologias com seu acervo digital</CardDescription>
                </div>
                <div class="hidden sm:block">
                  <Badge class="bg-purple-600 hover:bg-purple-700 px-3 py-1">PREMIUM INSIGHTS</Badge>
                </div>
              </div>
            </CardHeader>

            <CardContent class="relative">
              <!-- AI Insights Row -->
              <div v-if="learning?.books?.length" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
                <div class="md:col-span-2 p-6 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-700 text-white shadow-xl shadow-purple-500/20">
                  <div class="flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-white/20 backdrop-blur-sm">
                      <Sparkles class="h-6 w-6 text-amber-300" />
                    </div>
                    <div>
                      <h4 class="text-lg font-bold mb-2">Análise de IA: Seu Foco Atual</h4>
                      <p class="text-purple-100 text-sm leading-relaxed">
                        Você está progredindo rapidamente em conceitos de <span class="font-bold text-white underline decoration-amber-400">Desenvolvimento Web</span>. 
                        Sua consistência em <strong>{{ learning.books[0]?.title }}</strong> indica que você está pronto para explorar tópicos avançados de arquitetura.
                      </p>
                      <div class="mt-4 flex flex-wrap gap-2">
                        <Badge variant="outline" class="border-white/30 text-white bg-white/10">#NextSteps</Badge>
                        <Badge variant="outline" class="border-white/30 text-white bg-white/10">#AdvancedArchitecture</Badge>
                        <Badge variant="outline" class="border-white/30 text-white bg-white/10">#FullstackExpert</Badge>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="p-6 rounded-2xl border border-purple-500/20 bg-white dark:bg-zinc-900 shadow-sm flex flex-col justify-between">
                  <div>
                    <div class="flex items-center gap-2 mb-4">
                      <BarChart3 class="h-5 w-5 text-purple-600" />
                      <span class="text-sm font-bold uppercase tracking-wider text-neutral-500">Métrica de Retenção</span>
                    </div>
                    <div class="text-3xl font-black text-purple-600">84%</div>
                    <p class="text-xs text-neutral-500 mt-1">Baseado nos seus flashcards e quizzes recentes.</p>
                  </div>
                  <div class="mt-4 h-2 w-full bg-neutral-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <div class="h-full bg-purple-500" style="width: 84%"></div>
                  </div>
                </div>
              </div>

              <div v-if="!learning?.books?.length" class="py-16 text-center">
                <div class="relative inline-block mb-6">
                  <div class="absolute inset-0 bg-purple-500 blur-3xl opacity-20 rounded-full"></div>
                  <Code class="h-20 w-20 text-purple-300 relative mx-auto" />
                </div>
                <h3 class="text-2xl font-bold mb-2">Inicie sua Jornada Tech</h3>
                <p class="text-neutral-500 max-w-md mx-auto mb-10">Adicione livros de programação à sua estante para desbloquear métricas de aprendizado personalizadas.</p>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-12">
                  <div v-for="sug in suggestions" :key="sug.isbn" class="flex flex-col items-center group cursor-pointer" @click="navigateTo(`/catalog?q=${sug.title}`)">
                    <div class="relative w-full aspect-[2/3] rounded-xl overflow-hidden shadow-lg group-hover:scale-105 transition-all duration-300 ring-1 ring-black/5 dark:ring-white/10">
                      <img v-if="sug.cover_url" :src="sug.cover_url" class="h-full w-full object-cover" />
                      <div v-else class="flex h-full items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                        <BookOpen class="h-10 w-10" />
                      </div>
                      <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
                        <Button size="xs" class="w-full bg-purple-600 hover:bg-purple-700 text-[10px] font-bold">ADICIONAR</Button>
                      </div>
                    </div>
                    <h5 class="mt-3 text-[11px] font-bold line-clamp-1 text-neutral-700 dark:text-neutral-300">{{ sug.title }}</h5>
                  </div>
                </div>

                <Button variant="outline" size="lg" class="gap-3 border-purple-200 hover:bg-purple-50 dark:border-purple-900/30 dark:hover:bg-purple-900/20" @click="navigateTo('/books')">
                  <Search class="h-5 w-5 text-purple-500" />
                  Explorar Catálogo de Tecnologia
                </Button>
              </div>
              
              <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="book in learning.books" :key="book.id" class="group relative rounded-2xl border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-6 transition-all hover:shadow-xl hover:shadow-purple-500/5 hover:-translate-y-1">
                  <div class="flex justify-between items-start mb-6">
                    <div class="flex-1">
                      <h4 class="font-bold text-base leading-tight group-hover:text-purple-600 transition-colors">{{ book.title }}</h4>
                      <p class="text-xs text-neutral-500 mt-1">{{ book.author }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 font-bold text-sm">
                      {{ book.progress }}%
                    </div>
                  </div>
                  
                  <div class="space-y-3">
                    <div class="flex justify-between text-[10px] font-bold text-neutral-400 uppercase tracking-widest">
                      <span>{{ book.pages_read }} de {{ book.total_pages }} pág.</span>
                      <span>{{ Math.round(book.total_pages - book.pages_read) }} restantes</span>
                    </div>
                    <div class="h-2 w-full bg-neutral-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                      <div 
                        class="h-full bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full transition-all duration-1000 ease-out" 
                        :style="{ width: `${book.progress}%` }"
                      ></div>
                    </div>
                  </div>

                  <div class="mt-8 flex items-center justify-between">
                    <button class="flex items-center gap-2 text-[10px] font-black text-purple-600 uppercase tracking-widest hover:opacity-80 transition-opacity">
                      <Lightbulb class="h-4 w-4" />
                      Revisão por IA
                    </button>
                    <NuxtLink :to="`/reader/${book.id}`">
                      <Button size="sm" class="rounded-xl font-bold bg-zinc-900 hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-neutral-200">
                        Lendo agora
                      </Button>
                    </NuxtLink>
                  </div>
                </div>
              </div>

              <!-- Tech Summary Stats -->
              <div v-if="learning?.books?.length" class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4 p-8 rounded-3xl bg-white dark:bg-zinc-900/50 border border-neutral-100 dark:border-zinc-800">
                <div class="text-center md:border-r border-neutral-100 dark:border-zinc-800">
                  <div class="text-3xl font-black text-purple-600">{{ learning.total_tech_books }}</div>
                  <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">Livros no Radar</div>
                </div>
                <div class="text-center md:border-r border-neutral-100 dark:border-zinc-800">
                  <div class="text-3xl font-black text-purple-600">{{ learning.finished_tech_books }}</div>
                  <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">Skills Concluídas</div>
                </div>
                <div class="text-center md:border-r border-neutral-100 dark:border-zinc-800">
                  <div class="text-3xl font-black text-purple-600">{{ Math.round((learning.total_minutes_tech ?? 0) / 60) }}h</div>
                  <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">Tempo de Foco</div>
                </div>
                <div class="text-center">
                  <div class="text-3xl font-black text-purple-600">{{ learning.total_pages_tech.toLocaleString() }}</div>
                  <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">Páginas Tech</div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Achievements Section -->
        <div class="lg:col-span-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-black flex items-center gap-2">
              <Award class="h-6 w-6 text-amber-500" />
              Conquistas Desbloqueadas
            </h3>
            <span class="text-xs font-bold text-neutral-500 uppercase tracking-widest">
              {{ achievements.filter(a => a.is_earned).length }} de {{ achievements.length }} concluídas
            </span>
          </div>
          
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <Card 
              v-for="achievement in achievements" 
              :key="achievement.id"
              :class="[
                'relative overflow-hidden transition-all duration-300 group',
                achievement.is_earned 
                  ? 'border-amber-500/30 bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/10 dark:to-zinc-900 shadow-lg shadow-amber-500/5' 
                  : 'border-neutral-200 dark:border-zinc-800 bg-neutral-50/50 dark:bg-zinc-900/30 grayscale opacity-60'
              ]"
            >
              <CardContent class="p-4 flex flex-col items-center text-center">
                <div 
                  :class="[
                    'h-12 w-12 rounded-full flex items-center justify-center mb-3 transition-transform group-hover:scale-110',
                    achievement.is_earned ? 'bg-amber-500 text-white shadow-lg' : 'bg-neutral-200 dark:bg-zinc-800 text-neutral-400'
                  ]"
                >
                  <component :is="achievement.icon === 'BookOpen' ? BookOpen : achievement.icon === 'Library' ? BookOpen : achievement.icon === 'Terminal' ? Terminal : achievement.icon === 'Flame' ? Flame : Sparkles" class="h-6 w-6" />
                </div>
                <h4 class="text-xs font-black mb-1 line-clamp-1 uppercase tracking-tight">{{ achievement.name }}</h4>
                <p class="text-[10px] text-neutral-500 leading-tight line-clamp-2">{{ achievement.description }}</p>
                
                <div v-if="achievement.is_earned" class="absolute top-1 right-1">
                  <Badge class="bg-amber-500 h-4 w-4 p-0 flex items-center justify-center rounded-full">
                    <Check class="h-2 w-2 text-white" />
                  </Badge>
                </div>
                
                <div v-else class="mt-2">
                  <Badge variant="outline" class="text-[8px] font-bold border-neutral-300 text-neutral-400">+{{ achievement.xp_reward }} XP</Badge>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Yearly Recap Section -->
      </div>
    </div>
  </div>
</template>

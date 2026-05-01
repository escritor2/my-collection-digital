<script setup lang="ts">
useHead({ title: 'Importação/Exportação e Retenção - Meu Acervo Digital' });

const { apiFetch } = useApi();

const isLoading = ref(true);
const saveStatus = ref('');
const importStatus = ref('');
const exportStatus = ref('');

const settings = reactive({
  daily_goal_pages: 20,
  weekly_goal_pages: 140,
  reminders_enabled: true,
  reminder_hour: 20,
  timezone: 'America/Sao_Paulo',
});

const kindleContent = ref('');
const csvContent = ref('');
const csvSource = ref<'goodreads' | 'csv'>('goodreads');

const weeklySummary = ref<any>(null);
const goalRisk = ref<any>(null);

const load = async () => {
  isLoading.value = true;
  try {
    const [s, w, r]: any = await Promise.all([
      apiFetch('/retention/settings'),
      apiFetch('/retention/weekly-summary'),
      apiFetch('/retention/goal-risk'),
    ]);

    Object.assign(settings, s?.data ?? {});
    weeklySummary.value = w?.data ?? null;
    goalRisk.value = r?.data ?? null;
  } finally {
    isLoading.value = false;
  }
};

onMounted(load);

const saveSettings = async () => {
  saveStatus.value = '';
  await apiFetch('/retention/settings', { method: 'PUT', body: settings });
  saveStatus.value = 'Configurações salvas.';
};

const importKindle = async () => {
  importStatus.value = '';
  const content = kindleContent.value.trim();
  if (!content) return;
  const res: any = await apiFetch('/retention/import/kindle', { method: 'POST', body: { content } });
  importStatus.value = `Kindle importado: ${res?.data?.imported ?? 0} destaques.`;
};

const importCsv = async () => {
  importStatus.value = '';
  const content = csvContent.value.trim();
  if (!content) return;
  const res: any = await apiFetch('/retention/import/csv', {
    method: 'POST',
    body: { csv_content: content, source: csvSource.value },
  });
  importStatus.value = `CSV importado: ${res?.data?.imported ?? 0} registros.`;
};

const onFileToText = async (event: Event, target: 'kindle' | 'csv') => {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;
  const text = await file.text();
  if (target === 'kindle') kindleContent.value = text;
  else csvContent.value = text;
};

const exportData = async () => {
  exportStatus.value = '';
  const res: any = await apiFetch('/retention/export');
  const blob = new Blob([JSON.stringify(res?.data ?? {}, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `acervo-export-${new Date().toISOString().slice(0, 10)}.json`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
  exportStatus.value = 'Export concluído.';
};

const checkNotifications = async () => {
  await apiFetch('/retention/notifications/check', { method: 'POST' });
  await load();
};
</script>

<template>
  <div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Fase 6 · Importação/Exportação e Retenção</h1>
      </div>

      <div v-if="isLoading" class="text-center py-12">
        <p class="text-gray-500 dark:text-zinc-400">Carregando...</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
          <section class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6 space-y-3">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Lembretes e metas</h2>
              <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                  <label class="block text-xs text-gray-600 dark:text-zinc-300 mb-1">Meta diária (páginas)</label>
                  <input v-model.number="settings.daily_goal_pages" type="number" min="1" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                </div>
                <div>
                  <label class="block text-xs text-gray-600 dark:text-zinc-300 mb-1">Meta semanal (páginas)</label>
                  <input v-model.number="settings.weekly_goal_pages" type="number" min="1" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                </div>
                <div>
                  <label class="block text-xs text-gray-600 dark:text-zinc-300 mb-1">Hora do lembrete</label>
                  <input v-model.number="settings.reminder_hour" type="number" min="0" max="23" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                </div>
                <div>
                  <label class="block text-xs text-gray-600 dark:text-zinc-300 mb-1">Timezone</label>
                  <input v-model="settings.timezone" type="text" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                </div>
              </div>
              <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-zinc-300">
                <input v-model="settings.reminders_enabled" type="checkbox" />
                Lembretes ativos
              </label>
              <div class="flex gap-2">
                <button class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500" @click="saveSettings">
                  Salvar configurações
                </button>
                <button class="inline-flex items-center rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500" @click="checkNotifications">
                  Verificar alerta de meta em risco
                </button>
              </div>
              <p v-if="saveStatus" class="text-xs text-emerald-600 dark:text-emerald-400">{{ saveStatus }}</p>
            </div>
          </section>

          <section class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6 space-y-4">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Importar Kindle Highlights</h2>
              <input type="file" accept=".txt" @change="onFileToText($event, 'kindle')" />
              <textarea v-model="kindleContent" rows="8" placeholder="Cole aqui o conteúdo de 'My Clippings.txt'..." class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
              <button class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500" @click="importKindle">
                Importar Kindle
              </button>
            </div>
          </section>

          <section class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6 space-y-4">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Importar Goodreads / CSV</h2>
              <div class="flex items-center gap-2 text-sm">
                <label>Fonte:</label>
                <select v-model="csvSource" class="rounded-md border border-gray-300 bg-white px-2 py-1 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                  <option value="goodreads">Goodreads</option>
                  <option value="csv">CSV genérico</option>
                </select>
              </div>
              <input type="file" accept=".csv,text/csv" @change="onFileToText($event, 'csv')" />
              <textarea v-model="csvContent" rows="8" placeholder="Cole aqui o CSV..." class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
              <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500" @click="importCsv">
                Importar CSV
              </button>
              <p v-if="importStatus" class="text-xs text-emerald-600 dark:text-emerald-400">{{ importStatus }}</p>
            </div>
          </section>

          <section class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6 space-y-3">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Exportar dados</h2>
              <button class="inline-flex items-center rounded-md bg-zinc-800 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-zinc-700" @click="exportData">
                Exportar JSON completo
              </button>
              <p v-if="exportStatus" class="text-xs text-emerald-600 dark:text-emerald-400">{{ exportStatus }}</p>
            </div>
          </section>
        </div>

        <div class="space-y-6">
          <section class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Resumo semanal</h2>
              <div v-if="weeklySummary" class="space-y-1 text-sm text-gray-700 dark:text-zinc-300">
                <div class="flex justify-between"><span>Páginas</span><span class="font-semibold">{{ weeklySummary.pages_read }}</span></div>
                <div class="flex justify-between"><span>Minutos</span><span class="font-semibold">{{ weeklySummary.reading_minutes }}</span></div>
                <div class="flex justify-between"><span>Sessões</span><span class="font-semibold">{{ weeklySummary.sessions_count }}</span></div>
              </div>
            </div>
          </section>

          <section class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
            <div class="p-6">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Meta em risco</h2>
              <div v-if="goalRisk" class="space-y-1 text-sm text-gray-700 dark:text-zinc-300">
                <div class="flex justify-between"><span>Meta semanal</span><span class="font-semibold">{{ goalRisk.weekly_goal_pages }}</span></div>
                <div class="flex justify-between"><span>Lido na semana</span><span class="font-semibold">{{ goalRisk.pages_this_week }}</span></div>
                <div class="flex justify-between"><span>Esperado até hoje</span><span class="font-semibold">{{ goalRisk.expected_by_now }}</span></div>
                <div class="flex justify-between"><span>Risco</span><span class="font-semibold">{{ goalRisk.risk_level }}</span></div>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>


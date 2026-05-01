<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { getDocument, GlobalWorkerOptions, type PDFDocumentProxy } from 'pdfjs-dist';
import pdfWorker from 'pdfjs-dist/build/pdf.worker.mjs?url';

GlobalWorkerOptions.workerSrc = pdfWorker;

const props = defineProps<{
  src: string;
  initialPage?: number;
  scale?: number;
}>();

const emit = defineEmits<{
  (e: 'ready', payload: { totalPages: number }): void;
  (e: 'page', payload: { page: number; totalPages: number }): void;
}>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

let pdf: PDFDocumentProxy | null = null;
const page = ref<number>(props.initialPage ?? 1);
const totalPages = ref<number>(1);
const scale = ref<number>(props.scale ?? 1.25);

const getPageText = async (pageNumber: number) => {
  if (!pdf) return '';
  const pdfPage = await pdf.getPage(pageNumber);
  const content = await pdfPage.getTextContent();
  return content.items
    .map((item: any) => item?.str ?? '')
    .join(' ')
    .replace(/\s+/g, ' ')
    .trim();
};

const renderPage = async () => {
  if (!pdf || !canvasRef.value) return;
  isLoading.value = true;
  error.value = null;

  const clamped = Math.max(1, Math.min(page.value, pdf.numPages));
  page.value = clamped;

  const pdfPage = await pdf.getPage(page.value);
  const viewport = pdfPage.getViewport({ scale: scale.value });

  const canvas = canvasRef.value;
  const context = canvas.getContext('2d');
  if (!context) return;

  canvas.width = Math.floor(viewport.width);
  canvas.height = Math.floor(viewport.height);

  await (pdfPage as any).render({ canvasContext: context, viewport }).promise;

  isLoading.value = false;
  emit('page', { page: page.value, totalPages: totalPages.value });
};

const load = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const task = getDocument(props.src);
    pdf = await task.promise;
    totalPages.value = pdf.numPages;
    emit('ready', { totalPages: totalPages.value });
    if (props.initialPage) page.value = props.initialPage;
    await renderPage();
  } catch (e: any) {
    error.value = e?.message || 'Falha ao carregar PDF';
    isLoading.value = false;
  }
};

watch(
  () => props.src,
  async () => {
    pdf = null;
    page.value = props.initialPage ?? 1;
    await load();
  }
);

watch(page, async () => {
  await renderPage();
});

const next = () => {
  if (page.value < totalPages.value) page.value++;
};
const prev = () => {
  if (page.value > 1) page.value--;
};

const search = async (query: string, maxResults: number = 50) => {
  const q = query.trim().toLowerCase();
  if (!pdf || !q) return [];

  const results: Array<{ page: number; excerpt: string }> = [];

  for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
    if (results.length >= maxResults) break;

    try {
      const pdfPage = await pdf.getPage(pageNumber);
      const content = await pdfPage.getTextContent();
      const text = content.items
        .map((item: any) => item?.str ?? '')
        .join(' ')
        .replace(/\s+/g, ' ');

      const lower = text.toLowerCase();
      const matchIndex = lower.indexOf(q);

      if (matchIndex >= 0) {
        const start = Math.max(0, matchIndex - 40);
        const end = Math.min(text.length, matchIndex + q.length + 40);
        results.push({
          page: pageNumber,
          excerpt: text.slice(start, end),
        });
      }
    } catch {
      // ignore malformed pages and continue search
    }
  }

  return results;
};

defineExpose({
  next,
  prev,
  setPage: (p: number) => (page.value = p),
  getPage: () => page.value,
  search,
  getCurrentPageText: async () => (pdf ? await getPageText(page.value) : ''),
});

onMounted(load);
onUnmounted(() => {
  pdf = null;
});
</script>

<template>
  <div class="w-full h-full flex items-center justify-center relative">
    <div v-if="error" class="text-sm text-red-400 bg-red-950/30 border border-red-900 rounded-md p-4">
      {{ error }}
    </div>

    <div v-else class="w-full h-full flex items-center justify-center overflow-auto">
      <canvas ref="canvasRef" class="shadow-2xl bg-white rounded-sm" />
    </div>

    <div v-if="isLoading && !error" class="absolute inset-0 bg-zinc-950/40 flex items-center justify-center">
      <div class="text-xs text-zinc-300 bg-zinc-900/80 border border-zinc-800 rounded-md px-3 py-2">
        Renderizando PDF...
      </div>
    </div>
  </div>
</template>


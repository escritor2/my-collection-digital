<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, defineAsyncComponent } from 'vue'
import { ArrowLeft, Bookmark, Upload, Type, Maximize2, Minimize2, Trash2, FileText, List, Search, Palette, Sparkles, Volume2, Pause, Square, HelpCircle, BookOpen, ExternalLink } from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const userBookId = computed(() => route.params.id as string)

const openExternalLink = (url: string | null | undefined) => {
    if (!url) return;
    window.open(url, '_blank', 'noopener,noreferrer');
};

definePageMeta({
    layout: false, // Full screen, no dashboard sidebar
    middleware: 'auth'
})

type ReaderFile = { name: string; mime_type: string | null; size: number; download_url: string }
type ReaderPosition = { format: 'pdf' | 'epub'; locator: any; percentage: string | number | null } | null
type ReaderAnnotation = {
    id: number;
    type: 'bookmark' | 'highlight' | 'note';
    locator: any;
    selected_text: string | null;
    note: string | null;
    color: string | null;
    created_at: string;
};

const isLoading = ref(true);
const error = ref<string | null>(null);

const title = ref('');
const author = ref('');
const file = ref<ReaderFile | null>(null);
const fileBlobUrl = ref<string | null>(null);
const format = ref<'pdf' | 'epub' | null>(null);

const position = ref<ReaderPosition>(null);
const annotations = ref<ReaderAnnotation[]>([]);
const webReaderLink = ref<string | null>(null);

const theme = ref<'night' | 'sepia' | 'contrast' | 'default'>('night');
const fontSizePct = ref<number>(110);
const highlightColor = ref<'yellow' | 'pink' | 'blue' | 'green'>('yellow');

type TocItem = { id?: string; label: string; href?: string; cfi?: string; subitems?: TocItem[] };
const toc = ref<TocItem[]>([]);
const activeTab = ref<'notes' | 'toc' | 'search' | 'ai'>('notes');
const searchQuery = ref('');
const isSearching = ref(false);
const searchResults = ref<Array<{ cfi?: string; excerpt: string; href?: string; page?: number }>>([]);
const annotationFilter = ref<'all' | 'bookmark' | 'highlight' | 'note'>('all');
const annotationSearchQuery = ref('');

const isFullscreen = ref(false);
const totalPages = ref<number>(1);
const currentPage = ref<number>(1);
const lastTrackedPage = ref<number>(1);
const currentCfi = ref<string | null>(null);
const aiSelectedText = ref<string | null>(null);
const aiContextText = ref<string>('');

// Lazy load heavy components
const PdfReader = defineAsyncComponent(() => import('~/components/reader/PdfReader.vue'))
const EpubReader = defineAsyncComponent(() => import('~/components/reader/EpubReader.vue'))
const AiChatPanel = defineAsyncComponent(() => import('~/components/reader/AiChatPanel.vue'))

// TTS (Web Speech API)
const ttsSupported = computed(() => typeof window !== 'undefined' && 'speechSynthesis' in window);
const ttsState = ref<'idle' | 'speaking' | 'paused'>('idle');
let utterance: SpeechSynthesisUtterance | null = null;

const ttsSpeak = async () => {
    if (!ttsSupported.value) return;
    await refreshAiContextText();
    const text = (aiContextText.value || aiSelectedText.value || '').trim();
    if (!text) return;

    window.speechSynthesis.cancel();
    utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'pt-BR';
    utterance.onend = () => { ttsState.value = 'idle'; };
    utterance.onerror = () => { ttsState.value = 'idle'; };
    ttsState.value = 'speaking';
    window.speechSynthesis.speak(utterance);
};

const ttsTogglePause = () => {
    if (!ttsSupported.value) return;
    if (ttsState.value === 'speaking') {
        window.speechSynthesis.pause();
        ttsState.value = 'paused';
    } else if (ttsState.value === 'paused') {
        window.speechSynthesis.resume();
        ttsState.value = 'speaking';
    }
};

const ttsStop = () => {
    if (!ttsSupported.value) return;
    window.speechSynthesis.cancel();
    ttsState.value = 'idle';
};

const pdfRef = ref<any>(null);
const epubRef = ref<any>(null);

let saveTimer: any = null;
let lastSavedSignature: string | null = null;
let lastSavedAt = 0;

const filteredAnnotations = computed(() => {
    if (annotationFilter.value === 'all') return annotations.value;
    return annotations.value.filter((annotation) => annotation.type === annotationFilter.value);
});

const searchedAnnotations = computed(() => {
    const q = annotationSearchQuery.value.trim().toLowerCase();
    if (!q) return filteredAnnotations.value;
    return filteredAnnotations.value.filter((a) =>
        [a.selected_text || '', a.note || '', a.type]
            .join(' ')
            .toLowerCase()
            .includes(q)
    );
});

const themeOptions: Array<{ value: 'default' | 'night' | 'sepia' | 'contrast'; label: string }> = [
    { value: 'default', label: 'Claro' },
    { value: 'night', label: 'Noturno' },
    { value: 'sepia', label: 'Sepia' },
    { value: 'contrast', label: 'Contraste' },
];

const highlightOptions: Array<{ value: 'yellow' | 'pink' | 'blue' | 'green'; label: string; swatch: string }> = [
    { value: 'yellow', label: 'Amarelo', swatch: 'bg-yellow-400' },
    { value: 'pink', label: 'Rosa', swatch: 'bg-pink-400' },
    { value: 'blue', label: 'Azul', swatch: 'bg-sky-400' },
    { value: 'green', label: 'Verde', swatch: 'bg-emerald-400' },
];

const viewerThemeClass = computed(() => {
    switch (theme.value) {
        case 'default':
            return 'bg-zinc-100';
        case 'sepia':
            return 'bg-[#f4ecd8]';
        case 'contrast':
            return 'bg-black';
        default:
            return 'bg-zinc-950/50';
    }
});

const goBack = () => {
    // Save progress before leaving
    saveProgress();
    router.push('/user-shelf');
};

const load = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        const { apiFetch, baseURL } = useApi();
        const res: any = await apiFetch(`/reader/${userBookId.value}`);
        const data = res?.data;

        title.value = data?.user_book?.book?.title ?? 'Leitor';
        author.value = data?.user_book?.book?.author ?? '';
        file.value = data?.file ?? null;
        position.value = data?.position ?? null;
        annotations.value = data?.annotations ?? [];
        webReaderLink.value = data?.user_book?.book?.web_reader_link ?? null;
        const existingHighlightColor = annotations.value.find((annotation) => annotation.type === 'highlight' && annotation.color)?.color;
        if (existingHighlightColor === 'yellow' || existingHighlightColor === 'pink' || existingHighlightColor === 'blue' || existingHighlightColor === 'green') {
            highlightColor.value = existingHighlightColor;
        }

        if (!file.value) {
            format.value = null;
            return;
        }

        // Download as blob using Sanctum cookie (credentials include)
        const response = await fetch(`${baseURL}/api/user-shelf/${userBookId.value}/file`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/octet-stream' }
        });

        if (!response.ok) throw new Error(`Falha ao baixar arquivo (${response.status})`);

        const mime = response.headers.get('Content-Type') || file.value.mime_type || '';
        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        if (fileBlobUrl.value) URL.revokeObjectURL(fileBlobUrl.value);
        fileBlobUrl.value = url;

        if (mime.includes('application/pdf')) format.value = 'pdf';
        else format.value = 'epub';

        if (format.value === 'pdf') {
            const pageFromApi = Number((position.value as any)?.locator?.page ?? 1);
            currentPage.value = Number.isFinite(pageFromApi) ? pageFromApi : 1;
            lastTrackedPage.value = currentPage.value;
        } else {
            currentCfi.value = (position.value as any)?.locator?.cfi ?? null;
        }
    } catch (e: any) {
        error.value = e?.message || 'Erro ao carregar reader';
    } finally {
        isLoading.value = false;
    }
};

const queueSave = (payload: { format: 'pdf' | 'epub'; locator: any; percentage?: number | null }, force = false) => {
    const signature = JSON.stringify({
        format: payload.format,
        locator: payload.locator,
        percentage: payload.percentage ?? null,
    });
    const now = Date.now();
    const isDuplicate = signature === lastSavedSignature;
    const tooSoon = now - lastSavedAt < 5000;
    if (!force && isDuplicate && tooSoon) return;

    if (saveTimer) clearTimeout(saveTimer);
    const delay = force ? 0 : 800;
    saveTimer = setTimeout(async () => {
        const { apiFetch } = useApi();
        
        const pageIncrement = format.value === 'pdf' ? Math.max(0, currentPage.value - lastTrackedPage.value) : 0;
        
        const res: any = await apiFetch(`/reader/${userBookId.value}/position`, {
            method: 'PUT',
            body: {
                format: payload.format,
                locator: payload.locator,
                percentage: payload.percentage ?? null,
                pages_read_increment: pageIncrement,
            }
        });

        if (format.value === 'pdf' && pageIncrement > 0) {
            lastTrackedPage.value = currentPage.value;
        }

        if (res?.gamification?.xp_gained > 0) {
            // TODO: Show XP gain toast/animation
            console.log(`XP Gained: ${res.gamification.xp_gained}`);
        }

        lastSavedSignature = signature;
        lastSavedAt = Date.now();
    }, delay);
};

const saveProgress = async () => {
    try {
        if (format.value === 'pdf') {
            queueSave({ format: 'pdf', locator: { page: currentPage.value }, percentage: totalPages.value ? (currentPage.value / totalPages.value) * 100 : null }, true);
        }
        if (format.value === 'epub') {
            const cfi = currentCfi.value ?? epubRef.value?.currentCfi?.() ?? null;
            if (cfi) queueSave({ format: 'epub', locator: { cfi }, percentage: null }, true);
        }
    } catch {
        // ignore
    }
};

const createBookmark = async () => {
    const { apiFetch } = useApi();
    const locator = format.value === 'pdf'
        ? { page: currentPage.value }
        : { cfi: currentCfi.value ?? epubRef.value?.currentCfi?.() ?? null };

    const res: any = await apiFetch(`/reader/${userBookId.value}/annotations`, {
        method: 'POST',
        body: { type: 'bookmark', locator }
    });
    if (res?.data) annotations.value.unshift(res.data);
};

const createNote = async () => {
    const text = window.prompt('Digite sua nota');
    if (!text || text.trim().length === 0) return;

    const { apiFetch } = useApi();
    const locator = format.value === 'pdf'
        ? { page: currentPage.value }
        : { cfi: currentCfi.value ?? epubRef.value?.currentCfi?.() ?? null };

    const res: any = await apiFetch(`/reader/${userBookId.value}/annotations`, {
        method: 'POST',
        body: { type: 'note', locator, note: text.trim() }
    });
    if (res?.data) annotations.value.unshift(res.data);
};

const onEpubHighlight = async (payload: { cfiRange: string; selectedText: string }) => {
    const { apiFetch } = useApi();
    const res: any = await apiFetch(`/reader/${userBookId.value}/annotations`, {
        method: 'POST',
        body: {
            type: 'highlight',
            locator: { cfiRange: payload.cfiRange },
            selected_text: payload.selectedText,
            color: highlightColor.value
        }
    });
    if (res?.data) annotations.value.unshift(res.data);
};

const goToTocItem = async (item: TocItem) => {
    const target = item.href || item.cfi;
    if (target) {
        await epubRef.value?.display?.(target);
    }
};

const goToAnnotation = async (a: ReaderAnnotation) => {
    if (format.value === 'pdf') {
        const page = Number(a.locator?.page);
        if (Number.isFinite(page) && page > 0) {
            pdfRef.value?.setPage?.(page);
        }
        return;
    }

    const cfi = a.locator?.cfi || a.locator?.cfiRange;
    if (cfi) {
        await epubRef.value?.display?.(cfi);
    }
};

const deleteAnnotation = async (a: ReaderAnnotation) => {
    const ok = window.confirm('Remover anotação?');
    if (!ok) return;
    const { apiFetch } = useApi();
    await apiFetch(`/reader/${userBookId.value}/annotations/${a.id}`, { method: 'DELETE' });
    annotations.value = annotations.value.filter(x => x.id !== a.id);
};

const editAnnotationNote = async (a: ReaderAnnotation) => {
    if (a.type !== 'note') return;
    const next = window.prompt('Editar nota', a.note || '');
    if (next === null) return;
    const { apiFetch } = useApi();
    const res: any = await apiFetch(`/reader/${userBookId.value}/annotations/${a.id}`, {
        method: 'PUT',
        body: { note: next.trim() || null },
    });
    if (res?.data) {
        annotations.value = annotations.value.map((x) => (x.id === a.id ? res.data : x));
    }
};

const runSearch = async () => {
    const q = searchQuery.value.trim();
    if (!q) {
        searchResults.value = [];
        return;
    }
    isSearching.value = true;
    try {
        let res: any[] = [];
        if (format.value === 'epub') {
            res = await epubRef.value?.search?.(q, 50);
        } else if (format.value === 'pdf') {
            res = await pdfRef.value?.search?.(q, 50);
        }
        searchResults.value = Array.isArray(res) ? res : [];
    } finally {
        isSearching.value = false;
    }
};

const goToSearchResult = async (result: { cfi?: string; href?: string; page?: number }) => {
    if (format.value === 'pdf' && result.page) {
        pdfRef.value?.setPage?.(result.page);
        return;
    }

    const target = result.href || result.cfi;
    if (target) {
        await epubRef.value?.display?.(target);
    }
};

const refreshAiContextText = async (): Promise<void> => {
    if (format.value === 'pdf') {
        const text = await pdfRef.value?.getCurrentPageText?.();
        aiContextText.value = String(text || '').slice(0, 8000);
        return;
    }
    if (format.value === 'epub') {
        const text = await epubRef.value?.getCurrentSectionText?.();
        aiContextText.value = String(text || '').slice(0, 8000);
        return;
    }
    aiContextText.value = '';
};

const uploadFile = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const selected = input.files?.[0];
    if (!selected) return;

    const { baseURL, getXsrfToken } = useApi();
    const xsrfToken = getXsrfToken();

    const fd = new FormData();
    fd.append('file', selected);

    const resp = await fetch(`${baseURL}/api/user-shelf/${userBookId.value}/file`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
        },
        body: fd,
    });

    if (!resp.ok) {
        const txt = await resp.text();
        throw new Error(txt || 'Falha no upload');
    }

    await load();
};

const toggleFullscreen = async () => {
    try {
        if (!document.fullscreenElement) {
            await document.documentElement.requestFullscreen();
            isFullscreen.value = true;
        } else {
            await document.exitFullscreen();
            isFullscreen.value = false;
        }
    } catch {
        // ignore
    }
};

// Listen for beforeunload to save progress when closing tab
onMounted(() => {
    window.addEventListener('beforeunload', saveProgress);
    window.addEventListener('keydown', onKeydown);
    load();
});

onUnmounted(() => {
    window.removeEventListener('beforeunload', saveProgress);
    window.removeEventListener('keydown', onKeydown);
    saveProgress();
    if (fileBlobUrl.value) URL.revokeObjectURL(fileBlobUrl.value);
});

const onKeydown = (e: KeyboardEvent) => {
    // Avoid interfering with typing
    const target = e.target as HTMLElement | null;
    const tag = target?.tagName?.toLowerCase();
    if (tag === 'input' || tag === 'textarea' || (target as any)?.isContentEditable) return;

    // PDF navigation
    if (format.value === 'pdf') {
        if (e.key === 'ArrowLeft') pdfRef.value?.prev?.();
        if (e.key === 'ArrowRight') pdfRef.value?.next?.();
    }

    // TTS shortcuts
    if (e.key === 't' || e.key === 'T') {
        e.preventDefault();
        ttsSpeak();
    }
    if (e.key === ' ') {
        // pause/resume when already speaking
        if (ttsState.value !== 'idle') {
            e.preventDefault();
            ttsTogglePause();
        }
    }
    if (e.key === 'Escape') {
        if (ttsState.value !== 'idle') ttsStop();
    }
};

watch(
    () => activeTab.value,
    async (tab) => {
        if (tab === 'ai') {
            await refreshAiContextText();
        }
    },
);

</script>

<template>
    <div class="h-screen w-full bg-zinc-950 flex flex-col text-zinc-300">
        <!-- Reader Header -->
        <header class="h-14 bg-zinc-900 border-b border-zinc-800 flex items-center justify-between px-4 shrink-0 shadow-sm z-10">
            <div class="flex items-center gap-4">
                <button @click="goBack" aria-label="Voltar" class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white group">
                    <ArrowLeft class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                </button>
                <div>
                    <h1 class="text-sm font-semibold text-white leading-tight">{{ title }}</h1>
                    <span class="text-xs text-zinc-500">{{ author }}</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div v-if="format === 'pdf'" class="text-xs font-medium text-zinc-400 bg-zinc-800 px-3 py-1 rounded-full border border-zinc-700">
                    Página {{ currentPage }} de {{ totalPages }}
                </div>

                <div class="flex items-center gap-1">
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md border border-zinc-800 bg-zinc-900/60">
                        <Palette class="w-4 h-4 text-zinc-500" />
                        <select v-model="theme" class="bg-transparent text-xs text-zinc-300 focus:outline-none">
                            <option v-for="option in themeOptions" :key="option.value" :value="option.value" class="bg-zinc-900 text-zinc-100">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <button v-if="format === 'epub'" @click="fontSizePct = Math.min(160, fontSizePct + 10)" class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white" title="Aumentar fonte">
                        <Type class="w-5 h-5" />
                    </button>
                    <button v-if="format === 'epub'" @click="fontSizePct = Math.max(80, fontSizePct - 10)" class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white" title="Diminuir fonte">
                        <Type class="w-5 h-5 rotate-180" />
                    </button>
                    <button @click="toggleFullscreen" class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white" title="Fullscreen">
                        <Minimize2 v-if="isFullscreen" class="w-5 h-5" />
                        <Maximize2 v-else class="w-5 h-5" />
                    </button>
                </div>

                <label class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white cursor-pointer" title="Upload (PDF/EPUB)" aria-label="Upload de arquivo">
                    <input type="file" class="hidden" accept=".pdf,.epub,application/pdf,application/epub+zip" @change="uploadFile" />
                    <Upload class="w-5 h-5" />
                </label>

                <button @click="createBookmark" aria-label="Criar bookmark" class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white" title="Marcar (bookmark)">
                    <Bookmark class="w-5 h-5" />
                </button>

                <div v-if="ttsSupported" class="flex items-center gap-1 pl-2 ml-1 border-l border-zinc-800">
                    <button @click="ttsSpeak" class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white" title="TTS (T)" aria-label="Ler em voz alta">
                        <Volume2 class="w-5 h-5" />
                    </button>
                    <button v-if="ttsState !== 'idle'" @click="ttsTogglePause" class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white" title="Pausar/retomar (Espaço)" aria-label="Pausar ou retomar">
                        <Pause class="w-5 h-5" />
                    </button>
                    <button v-if="ttsState !== 'idle'" @click="ttsStop" class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white" title="Parar (Esc)" aria-label="Parar leitura">
                        <Square class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </header>

        <!-- Reader Viewer Area -->
        <main :class="['flex-1 overflow-hidden relative flex items-center justify-center', viewerThemeClass]">
            <div class="w-full h-full flex">
                <!-- Viewer -->
                <div class="flex-1 relative">
                    <!-- Navigation Areas -->
                    <div
                        v-if="format === 'pdf'"
                        @click="pdfRef?.prev?.()"
                        class="absolute left-0 top-0 bottom-0 w-1/6 z-10 cursor-pointer hover:bg-white/5 transition-colors"
                    />
                    <div
                        v-if="format === 'pdf'"
                        @click="pdfRef?.next?.()"
                        class="absolute right-0 top-0 bottom-0 w-1/6 z-10 cursor-pointer hover:bg-white/5 transition-colors"
                    />

                    <div v-if="isLoading" class="w-full h-full flex items-center justify-center">
                        <p class="text-sm text-zinc-500">Carregando reader...</p>
                    </div>
                    <div v-else-if="error" class="w-full h-full flex items-center justify-center">
                        <p class="text-sm text-red-400">{{ error }}</p>
                    </div>
                    <div v-else-if="!fileBlobUrl" class="w-full h-full flex flex-col items-center justify-center space-y-4">
                        <p class="text-sm text-zinc-500">Nenhum arquivo enviado ainda.</p>
                        <div v-if="webReaderLink" class="flex flex-col items-center gap-3">
                            <p class="text-xs text-zinc-400">Mas você pode ler este livro online:</p>
                            <Button 
                                variant="secondary" 
                                class="bg-purple-600 hover:bg-purple-500 text-white flex items-center gap-2"
                                @click="openExternalLink(webReaderLink)"
                            >
                                <ExternalLink class="w-4 h-4" /> Abrir Web Reader do Google
                            </Button>
                        </div>
                        <p v-else class="text-xs text-zinc-600">Faça upload de um PDF/EPUB para habilitar a IA.</p>
                    </div>

                    <PdfReader
                        v-else-if="format === 'pdf'"
                        ref="pdfRef"
                        :src="fileBlobUrl"
                        :initial-page="currentPage"
                        @ready="({ totalPages: tp }: { totalPages: number }) => (totalPages = tp)"
                        @page="({ page, totalPages: tp }: { page: number; totalPages: number }) => { currentPage = page; totalPages = tp; queueSave({ format: 'pdf', locator: { page }, percentage: tp ? (page / tp) * 100 : null }); }"
                    />

                    <EpubReader
                        v-else-if="format === 'epub'"
                        ref="epubRef"
                        :src="fileBlobUrl"
                        :initial-cfi="currentCfi"
                        :theme="theme"
                        :font-size-pct="fontSizePct"
                        @position="({ cfi, percentage }: { cfi: string | null; percentage?: number | null }) => { currentCfi = cfi; if (cfi) queueSave({ format: 'epub', locator: { cfi }, percentage: percentage ?? null }); }"
                        @highlight="(payload: { cfiRange: string; selectedText: string }) => { aiSelectedText = payload.selectedText; onEpubHighlight(payload); }"
                        @ready="({ toc: t }: { toc: TocItem[] }) => { toc = t; }"
                    />
                </div>

                <!-- Sidebar -->
                <aside class="w-80 border-l border-zinc-800 bg-zinc-950 hidden md:flex flex-col">
                    <div class="p-4 border-b border-zinc-800">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-white">Reader</h2>
                            <button @click="createNote" class="text-xs px-2 py-1 rounded-md bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-zinc-200">
                                + Nota
                            </button>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="text-[11px] text-zinc-500">Highlight</span>
                            <button
                                v-for="option in highlightOptions"
                                :key="option.value"
                                @click="highlightColor = option.value"
                                :class="[
                                    'w-5 h-5 rounded-full border',
                                    option.swatch,
                                    highlightColor === option.value ? 'border-white ring-2 ring-white/30' : 'border-zinc-700'
                                ]"
                                :title="option.label"
                            />
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <button @click="activeTab = 'notes'" :class="['flex items-center gap-2 text-xs px-2 py-1 rounded-md border', activeTab === 'notes' ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-transparent border-zinc-800 text-zinc-400 hover:text-zinc-200']">
                                <FileText class="w-4 h-4" /> Anotações
                            </button>
                            <button v-if="format === 'epub'" @click="activeTab = 'toc'" :class="['flex items-center gap-2 text-xs px-2 py-1 rounded-md border', activeTab === 'toc' ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-transparent border-zinc-800 text-zinc-400 hover:text-zinc-200']">
                                <List class="w-4 h-4" /> Sumário
                            </button>
                            <button @click="activeTab = 'search'" :class="['flex items-center gap-2 text-xs px-2 py-1 rounded-md border', activeTab === 'search' ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-transparent border-zinc-800 text-zinc-400 hover:text-zinc-200']">
                                <Search class="w-4 h-4" /> Busca
                            </button>
                            <button @click="activeTab = 'ai'" :class="['flex items-center gap-2 text-xs px-2 py-1 rounded-md border', activeTab === 'ai' ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-transparent border-zinc-800 text-zinc-400 hover:text-zinc-200']">
                                <Sparkles class="w-4 h-4" /> IA
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 overflow-auto p-3 space-y-2">
                        <!-- Notes tab -->
                        <template v-if="activeTab === 'notes'">
                            <div class="flex items-center gap-2 px-1">
                                <select v-model="annotationFilter" class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-xs text-zinc-200 focus:outline-none">
                                    <option value="all">Todas</option>
                                    <option value="bookmark">Bookmarks</option>
                                    <option value="highlight">Highlights</option>
                                    <option value="note">Notas</option>
                                </select>
                            </div>
                            <div class="px-1">
                                <input
                                    v-model="annotationSearchQuery"
                                    type="text"
                                    placeholder="Buscar em anotações..."
                                    class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-xs text-zinc-200 placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-purple-600/40"
                                />
                            </div>
                            <div v-if="searchedAnnotations.length === 0" class="text-xs text-zinc-500 p-3">
                                Ainda sem anotações.
                            </div>
                            <div
                                v-for="a in searchedAnnotations"
                                :key="a.id"
                                class="border border-zinc-800 rounded-md p-3 bg-zinc-900/30 hover:bg-zinc-900/50 transition-colors cursor-pointer"
                                @click="goToAnnotation(a)"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium text-zinc-300">{{ a.type }}</span>
                                        <span
                                            v-if="a.type === 'highlight' && a.color"
                                            :class="[
                                                'inline-block w-3 h-3 rounded-full border border-zinc-700',
                                                a.color === 'yellow' ? 'bg-yellow-400' : '',
                                                a.color === 'pink' ? 'bg-pink-400' : '',
                                                a.color === 'blue' ? 'bg-sky-400' : '',
                                                a.color === 'green' ? 'bg-emerald-400' : '',
                                            ]"
                                        />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button v-if="a.type === 'note'" class="p-1 rounded hover:bg-zinc-800 text-zinc-400 hover:text-white" @click.stop="editAnnotationNote(a)" title="Editar nota">
                                            <FileText class="w-4 h-4" />
                                        </button>
                                        <span class="text-[10px] text-zinc-500">{{ new Date(a.created_at).toLocaleString() }}</span>
                                        <button class="p-1 rounded hover:bg-zinc-800 text-zinc-400 hover:text-white" @click.stop="deleteAnnotation(a)" title="Remover">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                <p v-if="a.selected_text" class="text-xs text-zinc-300 mt-2 line-clamp-3">“{{ a.selected_text }}”</p>
                                <p v-if="a.note" class="text-xs text-zinc-400 mt-2 line-clamp-3">{{ a.note }}</p>
                            </div>
                        </template>

                        <!-- TOC tab -->
                        <template v-else-if="activeTab === 'toc'">
                            <div v-if="format !== 'epub'" class="text-xs text-zinc-500 p-3">
                                Sumário disponível apenas para EPUB.
                            </div>
                            <div v-else-if="toc.length === 0" class="text-xs text-zinc-500 p-3">
                                Este EPUB não tem sumário.
                            </div>
                            <TocTree v-else :items="toc" @navigate="goToTocItem" />
                        </template>

                        <!-- Search tab -->
                        <template v-else-if="activeTab === 'search'">
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="searchQuery"
                                    @keydown.enter="runSearch"
                                    type="text"
                                    placeholder="Buscar no texto..."
                                    class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-xs text-zinc-200 placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-purple-600/40"
                                />
                                <button @click="runSearch" class="px-3 py-2 rounded-md bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-xs text-zinc-200">
                                    OK
                                </button>
                            </div>

                            <div v-if="isSearching" class="text-xs text-zinc-500 p-3">
                                Buscando...
                            </div>
                            <div v-else-if="searchResults.length === 0" class="text-xs text-zinc-500 p-3">
                                Nenhum resultado.
                            </div>
                            <div v-else class="space-y-2">
                                <button
                                    v-for="(r, idx) in searchResults"
                                    :key="idx"
                                    @click="goToSearchResult(r)"
                                    class="w-full text-left text-xs px-3 py-2 rounded border border-zinc-800 hover:bg-zinc-900 text-zinc-200"
                                >
                                    <span v-if="r.page" class="mr-2 text-[10px] uppercase tracking-wide text-purple-400">Pág. {{ r.page }}</span>
                                    <span class="text-zinc-400">…</span>{{ r.excerpt }}<span class="text-zinc-400">…</span>
                                </button>
                            </div>
                        </template>

                        <!-- AI tab -->
                        <template v-else>
                            <AiChatPanel
                                :user-book-id="userBookId"
                                :title="title"
                                :author="author"
                                :format="format"
                                :position="position ? { format: (position as any).format, locator: (position as any).locator, percentage: Number((position as any).percentage ?? null) } : { format, locator: format === 'pdf' ? { page: currentPage } : { cfi: currentCfi }, percentage: null }"
                                :selected-text="aiSelectedText"
                                :context-text="aiContextText"
                            />
                        </template>
                    </div>
                </aside>
            </div>
        </main>

        <!-- Floating Study Mode Toolbar -->
        <div 
            v-if="aiSelectedText" 
            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-zinc-900 border border-purple-500/30 rounded-full shadow-2xl shadow-purple-500/10 px-4 py-2 flex items-center gap-3 animate-in slide-in-from-bottom-5"
        >
            <span class="text-xs font-bold text-purple-400 border-r border-zinc-700 pr-3 flex items-center gap-1">
                <Sparkles class="w-3 h-3" /> Modo Estudo
            </span>
            <button @click="activeTab = 'ai'; highlightColor = 'yellow';" class="text-[11px] text-zinc-300 hover:text-white px-2 py-1 rounded-md hover:bg-zinc-800 transition-colors flex items-center gap-1">
                <HelpCircle class="w-3 h-3 text-emerald-400" /> Explicar
            </button>
            <button @click="activeTab = 'ai'; highlightColor = 'pink';" class="text-[11px] text-zinc-300 hover:text-white px-2 py-1 rounded-md hover:bg-zinc-800 transition-colors flex items-center gap-1">
                <BookOpen class="w-3 h-3 text-blue-400" /> Resumir
            </button>
            <button @click="aiSelectedText = null" class="text-zinc-500 hover:text-zinc-300 p-1 ml-1 rounded-full hover:bg-zinc-800">
                <Trash2 class="w-3 h-3" />
            </button>
        </div>
    </div>
</template>

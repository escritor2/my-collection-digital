<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
// epubjs has no perfect TS types in all setups
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
import ePub from 'epubjs';

const props = defineProps<{
  src: string;
  initialCfi?: string | null;
  theme?: 'night' | 'sepia' | 'contrast' | 'default';
  fontSizePct?: number; // 80..160
}>();

const emit = defineEmits<{
  (e: 'ready', payload: { toc: Array<{ id?: string; label: string; href?: string; cfi?: string; subitems?: any[] }> }): void;
  (e: 'position', payload: { cfi: string | null; percentage?: number | null }): void;
  (e: 'highlight', payload: { cfiRange: string; selectedText: string }): void;
}>();

const containerRef = ref<HTMLDivElement | null>(null);
const error = ref<string | null>(null);

let book: any = null;
let rendition: any = null;

const mapToc = (items: any[]): Array<{ id?: string; label: string; href?: string; cfi?: string; subitems?: any[] }> => {
  return (items || []).map((i: any) => ({
    id: i?.id,
    label: String(i?.label ?? i?.title ?? '—'),
    href: i?.href,
    cfi: i?.cfi,
    subitems: i?.subitems ? mapToc(i.subitems) : [],
  }));
};

const applyTheme = () => {
  if (!rendition) return;

  rendition.themes.register('default', {
    body: { background: '#ffffff', color: '#111827' },
    '::selection': { background: 'rgba(99,102,241,0.25)' },
  });
  rendition.themes.register('night', {
    body: { background: '#09090b', color: '#e4e4e7' },
    a: { color: '#a78bfa' },
    '::selection': { background: 'rgba(167,139,250,0.25)' },
  });
  rendition.themes.register('sepia', {
    body: { background: '#f4ecd8', color: '#2d2a24' },
    a: { color: '#7c3aed' },
  });
  rendition.themes.register('contrast', {
    body: { background: '#000000', color: '#ffffff' },
    a: { color: '#22c55e' },
  });

  rendition.themes.select(props.theme ?? 'default');
  if (props.fontSizePct) rendition.themes.fontSize(`${props.fontSizePct}%`);
};

const attachHandlers = () => {
  if (!rendition) return;

  rendition.on('relocated', (location: any) => {
    const cfi = location?.start?.cfi ?? null;
    const percentage = typeof location?.start?.percentage === 'number' ? location.start.percentage * 100 : null;
    emit('position', { cfi, percentage });
  });

  rendition.on('selected', (cfiRange: string, contents: any) => {
    try {
      const selectedText = contents?.window?.getSelection?.()?.toString?.() ?? '';
      if (selectedText.trim().length === 0) return;
      emit('highlight', { cfiRange, selectedText });
    } finally {
      try {
        contents?.window?.getSelection?.()?.removeAllRanges?.();
      } catch {}
    }
  });
};

const load = async () => {
  error.value = null;
  if (!containerRef.value) return;

  try {
    // (re)create
    book = ePub(props.src);
    rendition = book.renderTo(containerRef.value, {
      width: '100%',
      height: '100%',
      spread: 'none',
      allowScriptedContent: true,
    });

    attachHandlers();
    applyTheme();

    await rendition.display(props.initialCfi || undefined);
    try {
      const nav = await book?.loaded?.navigation;
      const toc = mapToc(nav?.toc ?? []);
      emit('ready', { toc });
    } catch {
      emit('ready', { toc: [] });
    }
  } catch (e: any) {
    error.value = e?.message || 'Falha ao carregar EPUB';
  }
};

watch(
  () => [props.src, props.initialCfi] as const,
  async () => {
    if (rendition) {
      try {
        rendition.destroy();
      } catch {}
      rendition = null;
    }
    if (book) {
      try {
        book.destroy();
      } catch {}
      book = null;
    }
    await load();
  }
);

watch(
  () => [props.theme, props.fontSizePct] as const,
  () => applyTheme()
);

const next = async () => rendition?.next?.();
const prev = async () => rendition?.prev?.();
const display = async (target: string) => rendition?.display?.(target);
const getCurrentSectionText = () => {
  try {
    const contents = rendition?.getContents?.() ?? [];
    const first = contents?.[0];
    const doc = first?.document;
    const text = doc?.body?.innerText ?? '';
    return String(text).replace(/\s+/g, ' ').trim();
  } catch {
    return '';
  }
};
const currentCfi = () => {
  try {
    const loc = rendition?.currentLocation?.();
    return loc?.start?.cfi ?? null;
  } catch {
    return null;
  }
};

const search = async (query: string, maxResults: number = 50) => {
  const q = query.trim();
  if (!q || !book?.spine?.spineItems?.length) return [];

  const results: Array<{ cfi: string; excerpt: string; href?: string }> = [];
  for (const item of book.spine.spineItems) {
    if (results.length >= maxResults) break;
    try {
      const loaded = await item.load(book.load.bind(book));
      const text = (loaded?.textContent || loaded?.body?.textContent || '').toString();
      const haystack = text.replace(/\s+/g, ' ');
      const idx = haystack.toLowerCase().indexOf(q.toLowerCase());
      if (idx >= 0) {
        const start = Math.max(0, idx - 40);
        const end = Math.min(haystack.length, idx + q.length + 40);
        const excerpt = haystack.slice(start, end);
        // best-effort cfi: fall back to item.cfiBase if present
        const cfi = item?.cfiBase ? `${item.cfiBase}!/4/2` : (props.initialCfi || '');
        if (cfi) results.push({ cfi, excerpt, href: item?.href });
      }
      await item.unload();
    } catch {
      // ignore chapters that fail to load
    }
  }

  return results;
};

defineExpose({ next, prev, currentCfi, display, search, getCurrentSectionText });

onMounted(load);
onUnmounted(() => {
  try {
    rendition?.destroy?.();
  } catch {}
  try {
    book?.destroy?.();
  } catch {}
  rendition = null;
  book = null;
});
</script>

<template>
  <div class="w-full h-full relative">
    <div v-if="error" class="text-sm text-red-400 bg-red-950/30 border border-red-900 rounded-md p-4">
      {{ error }}
    </div>
    <div v-else ref="containerRef" class="w-full h-full" />
  </div>
</template>


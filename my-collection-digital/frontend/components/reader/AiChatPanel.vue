<script setup lang="ts">
import { computed, ref } from 'vue';
import { Sparkles, Send, Loader2, BookOpen, HelpCircle, Trophy, Layers } from 'lucide-vue-next';

type ChatMessage = { role: 'user' | 'assistant'; content: string };

const props = defineProps<{
  userBookId: string;
  title: string;
  author: string;
  format: 'pdf' | 'epub' | null;
  position: { format?: 'pdf' | 'epub'; locator?: any; percentage?: number | null } | null;
  contextText: string;
  selectedText?: string | null;
}>();

const messages = ref<ChatMessage[]>([
  { role: 'assistant', content: 'Posso ajudar com resumo, explicação, flashcards e perguntas — sem spoilers (só com base no trecho atual).' },
]);

const input = ref('');
const isSending = ref(false);
const error = ref<string | null>(null);
const info = ref<string | null>(null);

const canSend = computed(() => input.value.trim().length > 0 && !isSending.value);

const send = async (mode: 'chat' | 'summary' | 'explain' | 'questions' | 'tags' | 'recommendations' | 'flashcards' | 'quiz', content?: string) => {
  const text = (content ?? input.value).trim();
  if (!text && mode === 'chat') return;

  error.value = null;
  info.value = null;
  isSending.value = true;

  if (mode === 'chat') {
    messages.value.push({ role: 'user', content: text });
    input.value = '';
  }

  try {
    const { apiFetch } = useApi();
    const history = messages.value.slice(-10).map((m) => ({ role: m.role, content: m.content }));

    const res: any = await apiFetch(`/ai/chat/${props.userBookId}`, {
      method: 'POST',
      body: {
        mode,
        message: mode === 'chat' ? text : '',
        history,
        context: {
          text: props.contextText?.slice(0, 8000) || '',
          selected_text: (props.selectedText || '').slice(0, 2000),
        },
        client_position: props.position ?? null,
      },
    });

    const reply = res?.data?.reply ?? 'Sem resposta.';
    const meta = res?.data?.meta ?? {};
    if (meta?.cached) info.value = 'Resposta em cache (rápida).';
    else if (meta?.fallback) info.value = 'IA indisponível: usando resposta segura sem spoilers.';
    messages.value.push({ role: 'assistant', content: reply });
  } catch (e: any) {
    error.value = e?.message || 'Falha ao chamar IA';
  } finally {
    isSending.value = false;
  }
};
</script>

<template>
  <div class="h-full flex flex-col">
    <div class="p-4 border-b border-zinc-800">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-white flex items-center gap-2">
          <Sparkles class="w-4 h-4 text-purple-400" /> IA (anti-spoiler)
        </h2>
      </div>
      <p class="text-xs text-zinc-500 mt-1 line-clamp-2">
        {{ title }} — {{ author }}
      </p>
      <div class="mt-3 grid grid-cols-2 gap-2">
        <button
          class="text-[11px] px-2 py-1.5 rounded-md bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-zinc-200 flex items-center justify-center gap-1.5"
          :disabled="isSending"
          @click="send('summary')"
        >
          <BookOpen class="w-3 h-3 text-blue-400" /> Resumir
        </button>
        <button
          class="text-[11px] px-2 py-1.5 rounded-md bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-zinc-200 flex items-center justify-center gap-1.5"
          :disabled="isSending"
          @click="send('explain')"
        >
          <HelpCircle class="w-3 h-3 text-emerald-400" /> Explicar
        </button>
        <button
          class="text-[11px] px-2 py-1.5 rounded-md bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-zinc-200 flex items-center justify-center gap-1.5"
          :disabled="isSending"
          @click="send('quiz')"
        >
          <Trophy class="w-3 h-3 text-amber-400" /> Iniciar Quiz
        </button>
        <button
          class="text-[11px] px-2 py-1.5 rounded-md bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-zinc-200 flex items-center justify-center gap-1.5"
          :disabled="isSending"
          @click="send('flashcards')"
        >
          <Layers class="w-3 h-3 text-purple-400" /> Flashcards
        </button>
      </div>
      <div class="mt-2 flex flex-wrap gap-2">
        <button
          class="text-[10px] px-2 py-0.5 rounded-md bg-transparent hover:bg-zinc-800 border border-transparent hover:border-zinc-700 text-zinc-400"
          :disabled="isSending"
          @click="send('tags')"
        >
          Tags
        </button>
        <button
          class="text-[10px] px-2 py-0.5 rounded-md bg-transparent hover:bg-zinc-800 border border-transparent hover:border-zinc-700 text-zinc-400"
          :disabled="isSending"
          @click="send('recommendations')"
        >
          Dicas
        </button>
      </div>
    </div>

    <div class="flex-1 overflow-auto p-3 space-y-2">
      <div v-if="error" class="text-xs text-red-400 bg-red-950/30 border border-red-900 rounded-md p-3">
        {{ error }}
      </div>
      <div v-if="info" class="text-xs text-amber-300 bg-amber-950/30 border border-amber-900 rounded-md p-3">
        {{ info }}
      </div>

      <div
        v-for="(m, idx) in messages"
        :key="idx"
        :class="[
          'text-xs rounded-md border p-3 whitespace-pre-wrap',
          m.role === 'user'
            ? 'bg-zinc-900/40 border-zinc-800 text-zinc-200'
            : 'bg-purple-950/20 border-purple-900/40 text-zinc-200'
        ]"
      >
        <div class="text-[10px] uppercase tracking-wide mb-2" :class="m.role === 'user' ? 'text-zinc-500' : 'text-purple-300'">
          {{ m.role === 'user' ? 'Você' : 'IA' }}
        </div>
        {{ m.content }}
      </div>
    </div>

    <div class="p-3 border-t border-zinc-800">
      <div class="flex items-end gap-2">
        <textarea
          v-model="input"
          rows="2"
          class="flex-1 bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-xs text-zinc-200 placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-purple-600/40 resize-none"
          placeholder="Pergunte algo sobre o trecho atual..."
          @keydown.enter.exact.prevent="canSend && send('chat')"
        />
        <button
          class="h-9 w-9 flex items-center justify-center rounded-md bg-purple-600 hover:bg-purple-500 text-white disabled:opacity-50"
          :disabled="!canSend"
          @click="send('chat')"
          title="Enviar"
        >
          <Loader2 v-if="isSending" class="w-4 h-4 animate-spin" />
          <Send v-else class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>


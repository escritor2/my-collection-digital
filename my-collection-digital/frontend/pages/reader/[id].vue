<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { ArrowLeft, Bookmark } from 'lucide-vue-next';
import { useRoute, useRouter } from 'vue-router';

definePageMeta({
    layout: false, // Full screen, no dashboard sidebar
    middleware: 'auth' // Assuming we add a middleware later, or just handle manually
});

const route = useRoute();
const router = useRouter();
const bookId = route.params.id;

// Mock data for now, will be fetched via API
const bookData = ref({
    title: 'O Senhor dos Anéis',
    author: 'J.R.R. Tolkien',
    format: 'pdf', // or 'epub'
    url: '/mock-books/lotr.pdf', // Protected URL from Laravel
    lastPage: 12
});

const currentPage = ref(bookData.value.lastPage);
const totalPages = ref(100);

const goBack = () => {
    // Save progress before leaving
    saveProgress();
    router.push('/user-shelf');
};

const saveProgress = async () => {
    console.log(`Saving progress for book ${bookId}: page ${currentPage.value}`);
    // await apiFetch(`/user-shelf/${bookId}/progress`, { method: 'POST', body: { page: currentPage.value } })
};

// Listen for beforeunload to save progress when closing tab
onMounted(() => {
    window.addEventListener('beforeunload', saveProgress);
});

onUnmounted(() => {
    window.removeEventListener('beforeunload', saveProgress);
    saveProgress();
});

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        saveProgress(); // Optional: debounce this or save periodically
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

</script>

<template>
    <div class="h-screen w-full bg-zinc-950 flex flex-col text-zinc-300">
        <!-- Reader Header -->
        <header class="h-14 bg-zinc-900 border-b border-zinc-800 flex items-center justify-between px-4 shrink-0 shadow-sm z-10">
            <div class="flex items-center gap-4">
                <button @click="goBack" class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white group">
                    <ArrowLeft class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                </button>
                <div>
                    <h1 class="text-sm font-semibold text-white leading-tight">{{ bookData.title }}</h1>
                    <span class="text-xs text-zinc-500">{{ bookData.author }}</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-xs font-medium text-zinc-400 bg-zinc-800 px-3 py-1 rounded-full border border-zinc-700">
                    Página {{ currentPage }} de {{ totalPages }}
                </div>
                <button class="p-2 hover:bg-zinc-800 rounded-full transition-colors text-zinc-400 hover:text-white" title="Marcar página">
                    <Bookmark class="w-5 h-5" />
                </button>
            </div>
        </header>

        <!-- Reader Viewer Area -->
        <main class="flex-1 overflow-hidden relative flex items-center justify-center bg-zinc-950/50">
            <!-- Navigation Areas (Click sides to turn pages) -->
            <div @click="prevPage" class="absolute left-0 top-0 bottom-0 w-1/6 z-10 cursor-pointer hover:bg-white/5 transition-colors"></div>
            <div @click="nextPage" class="absolute right-0 top-0 bottom-0 w-1/6 z-10 cursor-pointer hover:bg-white/5 transition-colors"></div>

            <!-- PDF/EPUB Container (Placeholder) -->
            <div class="w-full h-full max-w-4xl mx-auto bg-zinc-100 dark:bg-zinc-900 shadow-2xl flex flex-col items-center justify-center p-8 text-center border-x border-zinc-800 transition-all duration-300">
                
                <div class="animate-pulse flex flex-col items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-zinc-800 flex items-center justify-center">
                        <span class="text-xs text-zinc-500">Render</span>
                    </div>
                    <p class="text-sm text-zinc-500">Carregando visualizador de {{ bookData.format.toUpperCase() }}...</p>
                    <p class="text-xs text-emerald-500 font-mono mt-4">Página atual simulada: {{ currentPage }}</p>
                </div>

            </div>
        </main>
    </div>
</template>

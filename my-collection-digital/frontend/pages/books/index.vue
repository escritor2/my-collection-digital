<script setup lang="ts">
import { toast } from 'vue-sonner';
import { Search, Plus, BookPlus, Info, ExternalLink } from 'lucide-vue-next';
import { Input } from '~/components/ui/input';
import { Button } from '~/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '~/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '~/components/ui/table';
import { Badge } from '~/components/ui/badge';
import { Skeleton } from '~/components/ui/skeleton';

useHead({
    title: 'Catálogo de Livros - Meu Acervo Digital'
});

type CatalogBook = {
    id: number;
    title: string;
    author: string;
    page_count: number | null;
    cover_url?: string | null;
    isbn?: string | null;
    in_shelf?: boolean;
};

type ExternalSuggestion = {
    source: 'google' | 'openlibrary' | 'itunes';
    external_id: string | null;
    title: string | null;
    author: string | null;
    description: string | null;
    isbn: string | null;
    page_count: number | null;
    cover_url: string | null;
    language: string | null;
    publisher: string | null;
    published_date: string | null;
    categories: string[] | null;
    preview_link?: string | null;
    web_reader_link?: string | null;
    pdf_link?: string | null;
    epub_link?: string | null;
};

const { apiFetch } = useApi();

const catalogQ = ref('');
const query = ref('');
const openExternalLink = (url: string | null | undefined) => {
    if (!url) return;
    window.open(url, '_blank', 'noopener,noreferrer');
};

const searchQuery = ref('');
const suggestions = ref<ExternalSuggestion[]>([]);
const isSearching = ref(false);
let searchTimer: any = null;

const { data: booksData, status } = await useAsyncData('catalog-books', async () => {
    const res: any = await apiFetch('/books', { query: { q: catalogQ.value.trim() || undefined } });
    return res?.data ?? [];
}, { watch: [catalogQ] });

const books = computed<CatalogBook[]>(() => booksData.value ?? []);
const isLoading = computed(() => status.value === 'pending');



watch(query, (val) => {
    if (searchTimer) clearTimeout(searchTimer);
    const q = (val ?? '').trim();
    if (q.length < 2) {
        suggestions.value = [];
        return;
    }

    searchTimer = setTimeout(async () => {
        isSearching.value = true;
        try {
            const res: any = await apiFetch('/catalog/search', { query: { q, limit: 8 } });
            suggestions.value = res?.data ?? [];
        } finally {
            isSearching.value = false;
        }
    }, 250);
});

const addSuggestionToCatalog = async (s: ExternalSuggestion) => {
    if (!s.title || !s.author) return;

    const payload: any = {
        title: s.title,
        author: s.author,
        description: s.description,
        isbn: s.isbn,
        page_count: s.page_count,
        cover_url: s.cover_url,
        language: s.language,
        publisher: s.publisher,
        published_date: s.published_date,
        categories: s.categories,
        preview_link: s.preview_link,
        web_reader_link: s.web_reader_link,
        pdf_link: s.pdf_link,
        epub_link: s.epub_link,
        google_volume_id: s.source === 'google' ? s.external_id : null,
        open_library_key: s.source === 'openlibrary' ? s.external_id : null,
    };

    try {
        const res: any = await apiFetch('/books', { method: 'POST', body: payload });
        const created = res?.data;
        if (created) {
            if (booksData.value) {
                booksData.value = [created, ...booksData.value.filter((b: any) => b.id !== created.id)];
            }
            query.value = '';
            suggestions.value = [];
            toast.success('Livro importado para o catálogo!');
        }
    } catch (e: any) {
        toast.error('Erro ao importar livro', { description: e.message || 'Tente novamente.' });
    }
};

const addToShelf = async (book: CatalogBook) => {
    try {
        const res: any = await apiFetch('/user-shelf', { method: 'POST', body: { book_id: book.id } });
        if (res?.data) {
            book.in_shelf = true;
            toast.success('Livro adicionado à sua estante!');
        }
    } catch (e: any) {
        toast.error('Erro', { description: e.data?.message || e.message || 'Não foi possível adicionar o livro.' });
    }
};
</script>

<template>
    <div class="py-10 space-y-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">Catálogo Global</h1>
                    <p class="text-neutral-500 dark:text-neutral-400 mt-1">Explore livros da comunidade ou adicione novos títulos.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Sidebar: Search & Add -->
                <div class="space-y-6 lg:col-span-1">
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-bold uppercase tracking-wider text-neutral-500 flex items-center gap-2">
                                <Search class="h-4 w-4" />
                                Filtrar Catálogo
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Input
                                v-model="catalogQ"
                                placeholder="Título, autor, ISBN..."
                                class="w-full"
                            />
                        </CardContent>
                    </Card>

                    <Card class="border-purple-500/20 bg-purple-50/10 dark:bg-purple-950/5">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 flex items-center gap-2">
                                <Plus class="h-4 w-4" />
                                Adicionar Novo
                            </CardTitle>
                            <CardDescription class="text-[11px]">
                                Busque no Google Books ou Open Library
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="relative">
                                <Input
                                    v-model="query"
                                    placeholder="Digite para buscar..."
                                    class="w-full pr-10"
                                />
                                <div v-if="isSearching" class="absolute right-3 top-2.5">
                                    <div class="h-4 w-4 animate-spin rounded-full border-2 border-purple-500 border-t-transparent"></div>
                                </div>
                            </div>

                            <div v-if="suggestions.length" class="space-y-3 max-h-[400px] overflow-auto pr-1">
                                <div
                                    v-for="s in suggestions"
                                    :key="(s.source + ':' + (s.external_id || s.isbn || s.title))"
                                    class="group p-3 rounded-xl border border-neutral-200 dark:border-neutral-800 hover:border-purple-500/30 hover:bg-purple-500/5 transition-all"
                                >
                                    <div class="cursor-pointer" @click="addSuggestionToCatalog(s)">
                                        <div class="text-xs font-bold truncate">{{ s.title }}</div>
                                        <div class="text-[10px] text-neutral-500 truncate">{{ s.author }}</div>
                                    </div>
                                    
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <Badge variant="outline" class="text-[9px] px-1 py-0 h-4 capitalize">{{ s.source }}</Badge>
                                        <Badge v-if="s.pdf_link || s.epub_link" variant="secondary" class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[9px] px-1 h-4">
                                            Grátis / DP
                                        </Badge>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <Button 
                                            variant="secondary" 
                                            size="sm" 
                                            class="col-span-2 h-8 text-[11px] bg-purple-600 hover:bg-purple-500 text-white font-bold"
                                            @click="addSuggestionToCatalog(s)"
                                        >
                                            <BookPlus class="w-3.5 h-3.5 mr-2" /> Adicionar à Estante
                                        </Button>

                                        <Button 
                                            variant="outline" 
                                            size="sm" 
                                            class="h-7 text-[10px] px-2 border-dashed border-purple-500/40 hover:bg-purple-500/10 text-purple-600 dark:text-purple-400"
                                            @click="openExternalLink(`https://www.google.com/search?q=${encodeURIComponent(s.title + ' ' + (s.author || '') + ' filetype:pdf')}`)"
                                        >
                                            <Search class="w-3 h-3 mr-1" /> Achar PDF
                                        </Button>
                                        
                                        <Button 
                                            v-if="s.web_reader_link || s.preview_link"
                                            variant="ghost" 
                                            size="sm" 
                                            class="h-7 text-[10px] px-2 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200"
                                            @click="openExternalLink(s.web_reader_link || s.preview_link)"
                                        >
                                            <ExternalLink class="w-3 h-3 mr-1" /> Ler Online
                                        </Button>
                                        
                                        <Button 
                                            v-if="s.pdf_link || s.epub_link"
                                            variant="secondary" 
                                            size="sm" 
                                            class="h-7 text-[10px] px-2 bg-emerald-600 hover:bg-emerald-500 text-white"
                                            @click="openExternalLink(s.pdf_link || s.epub_link)"
                                        >
                                            <Plus class="w-3 h-3 mr-1" /> Download
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Main Content: Catalog Table -->
                <div class="lg:col-span-3">
                    <Card>
                        <CardContent class="p-0">
                            <div v-if="isLoading" class="p-8 space-y-4">
                                <div v-for="i in 5" :key="i" class="flex items-center gap-4">
                                    <Skeleton class="h-12 w-8 rounded" />
                                    <div class="flex-1 space-y-2">
                                        <Skeleton class="h-4 w-1/3" />
                                        <Skeleton class="h-3 w-1/4" />
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="books.length > 0" class="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead class="w-[80px]">Capa</TableHead>
                                            <TableHead>Título / Autor</TableHead>
                                            <TableHead class="hidden md:table-cell">ISBN</TableHead>
                                            <TableHead class="text-right">Ações</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="book in books" :key="book.id" class="group">
                                            <TableCell>
                                                <div class="h-12 w-8 rounded bg-neutral-100 dark:bg-zinc-800 overflow-hidden border border-neutral-200 dark:border-zinc-700 shadow-sm transition-transform group-hover:scale-110">
                                                    <img v-if="book.cover_url" :src="book.cover_url" class="h-full w-full object-cover" />
                                                    <div v-else class="h-full w-full flex items-center justify-center">
                                                        <BookPlus class="h-4 w-4 text-neutral-400" />
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div class="font-bold text-sm">{{ book.title }}</div>
                                                <div class="text-xs text-neutral-500">{{ book.author }}</div>
                                            </TableCell>
                                            <TableCell class="hidden md:table-cell">
                                                <code class="text-[10px] bg-neutral-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded text-neutral-600 dark:text-neutral-400">
                                                    {{ book.isbn || '—' }}
                                                </code>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <Button variant="ghost" size="sm" as-child>
                                                        <NuxtLink :to="'/books/' + book.id">
                                                            <Info class="h-4 w-4 mr-1" />
                                                            Detalhes
                                                        </NuxtLink>
                                                    </Button>
                                                    
                                                    <Button 
                                                        v-if="!book.in_shelf" 
                                                        size="sm" 
                                                        class="bg-purple-600 hover:bg-purple-500"
                                                        @click="addToShelf(book)"
                                                    >
                                                        <Plus class="h-4 w-4 mr-1" />
                                                        Estante
                                                    </Button>
                                                    <Badge v-else variant="secondary" class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20">
                                                        Na Estante
                                                    </Badge>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>

                            <div v-else class="p-20 text-center">
                                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-neutral-100 dark:bg-zinc-800 mb-4 text-neutral-400">
                                    <Search class="h-6 w-6" />
                                </div>
                                <h3 class="text-lg font-bold">Nenhum livro encontrado</h3>
                                <p class="text-neutral-500 text-sm mt-1">Tente ajustar seus filtros ou buscar externamente.</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>

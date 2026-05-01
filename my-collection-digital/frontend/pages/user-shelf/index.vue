<script setup lang="ts">
import { 
    Search, 
    Filter, 
    BookOpen, 
    ChevronRight, 
    X, 
    MoreHorizontal, 
    BookCheck, 
    BookMarked, 
    Clock, 
    AlertCircle,
    LayoutGrid,
    List,
    Plus
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '~/components/ui/card';
import { Input } from '~/components/ui/input';
import { Button } from '~/components/ui/button';
import { Badge } from '~/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '~/components/ui/table';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '~/components/ui/select';
import { Progress } from '~/components/ui/progress';
import { Skeleton } from '~/components/ui/skeleton';

useHead({
    title: 'Minha Estante - Meu Acervo Digital'
});

type UserBook = {
    id: number;
    status: 'quero_ler' | 'lendo' | 'lido' | 'abandonei';
    progress_pages: number;
    book: { id: number; title: string; author: string; page_count: number | null; cover_url: string | null };
    tags?: { id: number; name: string; color: string | null }[];
    collections?: { id: number; name: string }[];
};

const userBooks = ref<UserBook[]>([]);
const isLoading = ref(true);

type Tag = { id: number; name: string; color: string | null };
type Collection = { id: number; name: string; description: string | null };

const { apiFetch } = useApi();
const tags = ref<Tag[]>([]);
const collections = ref<Collection[]>([]);

const filterQ = ref('');
const filterStatus = ref<string>('all');
const filterTagId = ref<string>('all');
const filterCollectionId = ref<string>('all');

const loadFilters = async () => {
    const [t, c]: any = await Promise.all([
        apiFetch('/tags'),
        apiFetch('/collections'),
    ]);
    tags.value = t?.data ?? [];
    collections.value = c?.data ?? [];
};

const loadShelf = async () => {
    isLoading.value = true;
    try {
        const query: any = {};
        if (filterQ.value.trim()) query.q = filterQ.value.trim();
        if (filterStatus.value !== 'all') query.status = filterStatus.value;
        if (filterTagId.value !== 'all') query.tag_id = filterTagId.value;
        if (filterCollectionId.value !== 'all') query.collection_id = filterCollectionId.value;

        const res: any = await apiFetch('/user-shelf', { query });
        userBooks.value = res?.data ?? [];
    } finally {
        isLoading.value = false;
    }
};

const clearFilters = () => {
    filterQ.value = '';
    filterStatus.value = 'all';
    filterTagId.value = 'all';
    filterCollectionId.value = 'all';
    loadShelf();
};

onMounted(async () => {
    await loadFilters();
    await loadShelf();
});

const getStatusConfig = (status: UserBook['status']) => {
    switch (status) {
        case 'quero_ler': return { label: 'Quero Ler', color: 'bg-blue-500/10 text-blue-600 border-blue-500/20', icon: BookMarked };
        case 'lendo': return { label: 'Lendo', color: 'bg-amber-500/10 text-amber-600 border-amber-500/20', icon: Clock };
        case 'lido': return { label: 'Lido', color: 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20', icon: BookCheck };
        case 'abandonei': return { label: 'Abandonei', color: 'bg-red-500/10 text-red-600 border-red-500/20', icon: AlertCircle };
        default: return { label: status, color: 'bg-zinc-500/10 text-zinc-600 border-zinc-500/20', icon: BookOpen };
    }
};
</script>

<template>
    <div class="py-8 space-y-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight">Minha Estante</h1>
                <p class="text-neutral-500 text-sm mt-1">Gerencie seu progresso e organize sua coleção digital.</p>
            </div>
            <div class="flex items-center gap-3">
                <Button variant="outline" as-child>
                    <NuxtLink to="/collections">Coleções</NuxtLink>
                </Button>
                <Button variant="outline" as-child>
                    <NuxtLink to="/tags">Tags</NuxtLink>
                </Button>
                <Button class="bg-purple-600 hover:bg-purple-500 shadow-lg shadow-purple-500/20" as-child>
                    <NuxtLink to="/books">
                        <Plus class="h-4 w-4 mr-2" />
                        Adicionar Livro
                    </NuxtLink>
                </Button>
            </div>
        </div>

        <Card class="border-none bg-zinc-50/50 dark:bg-zinc-900/50 shadow-none">
            <CardContent class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1.5 block">Buscar na Estante</label>
                        <div class="relative">
                            <Search class="absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
                            <Input v-model="filterQ" placeholder="Título, autor..." class="pl-9" @keydown.enter="loadShelf" />
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1.5 block">Status</label>
                        <Select v-model="filterStatus">
                            <SelectTrigger>
                                <SelectValue placeholder="Todos" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem value="quero_ler">Quero Ler</SelectItem>
                                <SelectItem value="lendo">Lendo</SelectItem>
                                <SelectItem value="lido">Lido</SelectItem>
                                <SelectItem value="abandonei">Abandonei</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1.5 block">Coleção</label>
                        <Select v-model="filterCollectionId">
                            <SelectTrigger>
                                <SelectValue placeholder="Todas" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todas</SelectItem>
                                <SelectItem v-for="c in collections" :key="c.id" :value="c.id.toString()">{{ c.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button class="flex-1" @click="loadShelf">Filtrar</Button>
                        <Button variant="ghost" size="icon" @click="clearFilters">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 4" :key="i" class="flex items-center gap-4 p-4 border rounded-xl">
                <Skeleton class="h-16 w-12 rounded" />
                <div class="flex-1 space-y-2">
                    <Skeleton class="h-4 w-1/4" />
                    <Skeleton class="h-3 w-1/3" />
                </div>
                <Skeleton class="h-8 w-24" />
            </div>
        </div>

        <div v-else-if="userBooks.length > 0" class="border rounded-xl overflow-hidden bg-white dark:bg-zinc-950">
            <Table>
                <TableHeader>
                    <TableRow class="bg-zinc-50/50 dark:bg-zinc-900/50">
                        <TableHead class="w-[80px]">Livro</TableHead>
                        <TableHead>Título / Autor</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="w-[200px]">Progresso</TableHead>
                        <TableHead class="text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="userBook in userBooks" :key="userBook.id" class="group">
                        <TableCell>
                            <div class="h-14 w-10 rounded bg-zinc-100 dark:bg-zinc-800 overflow-hidden border shadow-sm transition-transform group-hover:scale-105">
                                <img v-if="userBook.book.cover_url" :src="userBook.book.cover_url" class="h-full w-full object-cover" />
                                <div v-else class="h-full w-full flex items-center justify-center">
                                    <BookOpen class="h-4 w-4 text-zinc-400" />
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="font-bold text-sm">{{ userBook.book.title }}</div>
                            <div class="text-xs text-neutral-500">{{ userBook.book.author }}</div>
                        </TableCell>
                        <TableCell>
                            <Badge variant="outline" :class="getStatusConfig(userBook.status).color">
                                <component :is="getStatusConfig(userBook.status).icon" class="h-3 w-3 mr-1" />
                                {{ getStatusConfig(userBook.status).label }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-[10px] font-bold text-neutral-500 uppercase">
                                    <span>{{ Math.round((userBook.progress_pages / (userBook.book.page_count || 1)) * 100) }}%</span>
                                    <span>{{ userBook.progress_pages }} / {{ userBook.book.page_count || '?' }}</span>
                                </div>
                                <Progress :model-value="(userBook.progress_pages / (userBook.book.page_count || 1)) * 100" class="h-1.5" />
                            </div>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Button size="sm" class="bg-emerald-600 hover:bg-emerald-500 h-8" as-child>
                                    <NuxtLink :to="'/reader/' + userBook.id">
                                        Ler
                                    </NuxtLink>
                                </Button>
                                <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                    <NuxtLink :to="'/user-shelf/' + userBook.id">
                                        <ChevronRight class="h-4 w-4" />
                                    </NuxtLink>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div v-else class="text-center py-20 border-2 border-dashed rounded-3xl">
            <div class="h-16 w-16 bg-zinc-100 dark:bg-zinc-900 rounded-full flex items-center justify-center mx-auto mb-4">
                <BookOpen class="h-8 w-8 text-neutral-400" />
            </div>
            <h3 class="text-lg font-bold">Sua estante está vazia</h3>
            <p class="text-neutral-500 text-sm max-w-xs mx-auto mt-1">Explore nosso catálogo e adicione seus livros favoritos para começar a ler.</p>
            <Button class="mt-6 bg-purple-600 hover:bg-purple-500" as-child>
                <NuxtLink to="/books">Explorar Catálogo</NuxtLink>
            </Button>
        </div>
    </div>
</template>

<script setup>
useHead({
    title: 'Minha Estante - Meu Acervo Digital'
});

// Dados mockados para demonstração enquanto a API não é conectada
const userBooks = ref([
    {
        id: 1,
        status: 'lendo',
        progress_pages: 45,
        book: {
            title: 'O Senhor dos Anéis',
            author: 'J.R.R. Tolkien',
            page_count: 1200
        }
    },
    {
        id: 2,
        status: 'quero_ler',
        progress_pages: 0,
        book: {
            title: '1984',
            author: 'George Orwell',
            page_count: 328
        }
    },
    {
        id: 3,
        status: 'lido',
        progress_pages: 250,
        book: {
            title: 'O Pequeno Príncipe',
            author: 'Antoine de Saint-Exupéry',
            page_count: 250
        }
    }
]);

const getStatusColor = (status) => {
    switch (status) {
        case 'quero_ler': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'lendo': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'lido': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'abandonei': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
    }
};

const getStatusLabel = (status) => {
    const labels = {
        'quero_ler': 'Quero Ler',
        'lendo': 'Lendo',
        'lido': 'Lido',
        'abandonei': 'Abandonei'
    };
    return labels[status] || status;
};
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Minha Estante</h1>
                <NuxtLink to="/books" class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600">
                    Explorar Catálogo
                </NuxtLink>
            </div>

            <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
                <div class="p-6">
                    <div v-if="userBooks.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Título</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Autor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Progresso</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                                <tr v-for="userBook in userBooks" :key="userBook.id" class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ userBook.book.title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-zinc-400">{{ userBook.book.author }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusColor(userBook.status)]">
                                            {{ getStatusLabel(userBook.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-zinc-400">
                                        <div class="flex items-center gap-2">
                                            <div class="w-24 bg-gray-200 rounded-full h-1.5 dark:bg-zinc-700">
                                                <div class="bg-purple-600 h-1.5 rounded-full" :style="{ width: Math.min((userBook.progress_pages / userBook.book.page_count) * 100, 100) + '%' }"></div>
                                            </div>
                                            <span>{{ userBook.progress_pages }} / {{ userBook.book.page_count }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <NuxtLink :to="'/reader/' + userBook.book.id" class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 mr-2 transition-colors">Ler Agora</NuxtLink>
                                        <NuxtLink :to="'/user-shelf/' + userBook.id" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">Detalhes</NuxtLink>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-gray-500 dark:text-zinc-400">Sua estante está vazia. Adicione livros do catálogo!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

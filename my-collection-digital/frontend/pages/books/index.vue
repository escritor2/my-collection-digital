<script setup>
useHead({
    title: 'Catálogo de Livros - Meu Acervo Digital'
});

// Dados mockados para demonstração
const books = ref([
    {
        id: 1,
        title: 'O Senhor dos Anéis',
        author: 'J.R.R. Tolkien',
        page_count: 1200
    },
    {
        id: 2,
        title: '1984',
        author: 'George Orwell',
        page_count: 328
    },
    {
        id: 3,
        title: 'O Pequeno Príncipe',
        author: 'Antoine de Saint-Exupéry',
        page_count: 250
    },
    {
        id: 4,
        title: 'Dom Casmurro',
        author: 'Machado de Assis',
        page_count: 256
    }
]);
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Catálogo de Livros</h1>
                <NuxtLink to="/books/create" class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500">
                    Adicionar Novo Livro
                </NuxtLink>
            </div>

            <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-zinc-900 border dark:border-zinc-800">
                <div class="p-6">
                    <div v-if="books.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Título</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Autor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Páginas</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                                <tr v-for="book in books" :key="book.id" class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ book.title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-zinc-400">{{ book.author }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-zinc-400">{{ book.page_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <NuxtLink :to="'/books/' + book.id" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 mr-4">Ver</NuxtLink>
                                        <button class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-4">Editar</button>
                                        <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Excluir</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-gray-500 dark:text-zinc-400">Nenhum livro encontrado no catálogo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false
});
import { BookOpen, Library, TrendingUp } from 'lucide-vue-next';

// In Nuxt, we'll handle auth state differently. 
// For now, let's assume no user is logged in for the welcome page demonstration.
const user = null;
const canRegister = true;

useHead({
  title: 'Meu Acervo Digital',
});
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col font-sans text-white antialiased selection:bg-purple-500/30"
    >
        <!-- Background Decorators -->
        <div class="fixed inset-0 z-0 bg-neutral-950">
            <!-- Animated Gradient Blobs -->
            <div
                class="absolute -top-[20%] -left-[10%] h-[500px] w-[500px] animate-pulse rounded-full bg-purple-900/20 blur-[120px]"
            />
            <div
                class="absolute -right-[10%] -bottom-[20%] h-[600px] w-[600px] rounded-full bg-blue-900/10 blur-[150px]"
            />
        </div>

        <!-- Navigation Bar -->
        <header class="relative z-10 w-full border-b border-white/5 bg-black/20 backdrop-blur-md">
            <div class="container mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/20">
                        <Library class="h-5 w-5 text-white" />
                    </div>
                    <span class="text-lg font-semibold tracking-tight">Meu Acervo Digital</span>
                </div>

                <nav class="flex items-center gap-4">
                    <NuxtLink
                        v-if="user"
                        to="/dashboard"
                        class="text-sm font-medium text-neutral-300 transition-colors hover:text-white"
                    >
                        Painel de Controle
                    </NuxtLink>
                    <template v-else>
                        <NuxtLink
                            to="/login"
                            class="text-sm font-medium text-neutral-300 transition-colors hover:text-white"
                        >
                            Entrar
                        </NuxtLink>
                        <NuxtLink
                            v-if="canRegister"
                            to="/register"
                            class="rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-white/20 hover:ring-1 hover:ring-white/30"
                        >
                            Criar Conta
                        </NuxtLink>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-4 text-center sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <!-- Hero Badge -->
                <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-sm text-neutral-300 backdrop-blur-md">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-purple-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-purple-500"></span>
                    </span>
                    Sua biblioteca pessoal na nuvem
                </div>

                <!-- Hero Headline -->
                <h1 class="mb-6 text-5xl font-extrabold tracking-tight sm:text-7xl lg:text-8xl">
                    Organize suas <br class="hidden sm:block" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">Leituras.</span>
                </h1>

                <!-- Hero Description -->
                <p class="mx-auto mb-10 max-w-2xl text-lg text-neutral-400 sm:text-xl">
                    Acompanhe seu progresso, avalie suas obras favoritas e construa o acervo digital definitivo.
                    Feito para leitores apaixonados que amam estatísticas.
                </p>

                <!-- Call To Action Buttons -->
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <NuxtLink
                        v-if="user"
                        to="/dashboard"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-8 py-4 text-sm font-semibold text-black transition-all hover:bg-neutral-200 sm:w-auto"
                    >
                        Acessar Meu Acervo
                    </NuxtLink>
                    <template v-else>
                        <NuxtLink
                            to="/register"
                            class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-8 py-4 text-sm font-semibold text-black transition-all hover:bg-neutral-200 sm:w-auto"
                        >
                            Começar Agora
                        </NuxtLink>
                        <NuxtLink
                            to="/login"
                            class="flex w-full items-center justify-center gap-2 rounded-full border border-white/10 bg-white/5 px-8 py-4 text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/10 sm:w-auto"
                        >
                            Fazer Login
                        </NuxtLink>
                    </template>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mt-24 grid grid-cols-1 gap-6 sm:grid-cols-3 max-w-5xl mx-auto w-full pb-20">
                <div class="rounded-2xl border border-white/5 bg-white/5 p-6 backdrop-blur-sm transition-all hover:bg-white/10">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500/20 text-purple-400">
                        <Library class="h-5 w-5" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold">Catálogo Pessoal</h3>
                    <p class="text-sm text-neutral-400">Adicione livros à sua estante digital, classifique por status e nunca mais perca o controle do que já leu.</p>
                </div>

                <div class="rounded-2xl border border-white/5 bg-white/5 p-6 backdrop-blur-sm transition-all hover:bg-white/10">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/20 text-blue-400">
                        <BookOpen class="h-5 w-5" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold">Sessões de Leitura</h3>
                    <p class="text-sm text-neutral-400">Cronometre seu tempo de leitura e registre quantas páginas você devorou em cada sessão.</p>
                </div>

                <div class="rounded-2xl border border-white/5 bg-white/5 p-6 backdrop-blur-sm transition-all hover:bg-white/10">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/20 text-green-400">
                        <TrendingUp class="h-5 w-5" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold">Estatísticas Reais</h3>
                    <p class="text-sm text-neutral-400">Visualize seu ritmo de leitura, métricas mensais e metas alcançadas em um painel detalhado.</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-white/5 py-8 text-center text-sm text-neutral-500 backdrop-blur-md">
            <p>&copy; {{ new Date().getFullYear() }} Meu Acervo Digital. Todos os direitos reservados.</p>
        </footer>
    </div>
</template>


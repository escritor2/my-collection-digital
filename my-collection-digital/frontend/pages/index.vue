<script setup lang="ts">
definePageMeta({
  layout: false
});
import { BookOpen, Library, TrendingUp, Shield, Zap, Sparkles, MessageSquare, Globe, Layout, ChevronRight, Check } from 'lucide-vue-next';
import { Button } from '~/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '~/components/ui/card';

const { isAuthenticated } = useAuth();
const canRegister = true;
const scrolled = ref(false);

useHead({
  title: 'Meu Acervo Digital - Sua Biblioteca Inteligente',
});

const features = [
    {
        title: 'Leitor Inteligente',
        description: 'Suporte nativo para EPUB e PDF com IA integrada para tirar dúvidas em tempo real.',
        icon: BookOpen,
        color: 'text-purple-400'
    },
    {
        title: 'Estatísticas Detalhadas',
        description: 'Visualize seu progresso de leitura com heatmaps, metas anuais e velocidade de leitura.',
        icon: TrendingUp,
        color: 'text-blue-400'
    },
    {
        title: 'IA Assistente',
        description: 'Gere resumos, quizzes e flashcards automaticamente a partir dos seus livros.',
        icon: Sparkles,
        color: 'text-amber-400'
    },
    {
        title: 'Social e Clubes',
        description: 'Crie clubes de leitura, compartilhe destaques e siga seus amigos.',
        icon: MessageSquare,
        color: 'text-emerald-400'
    },
    {
        title: 'Modo Offline',
        description: 'Continue lendo e anotando mesmo sem internet. Sincronização automática ao voltar.',
        icon: Zap,
        color: 'text-orange-400'
    },
    {
        title: 'Privacidade Total',
        description: 'Seus dados e seus livros são seus. Criptografia de ponta a ponta e exportação total.',
        icon: Shield,
        color: 'text-red-400'
    }
];

const plans = [
    {
        name: 'Gratuito',
        price: 'R$ 0',
        description: 'Para leitores casuais',
        features: ['Até 5 livros', 'Estatísticas básicas', 'Leitor EPUB/PDF', 'Modo Offline'],
        cta: 'Começar Agora',
        popular: false
    },
    {
        name: 'Pro',
        price: 'R$ 19,90',
        description: 'Para leitores vorazes',
        features: ['Livros ilimitados', 'IA Assistente Ilimitada', 'Estatísticas Avançadas', 'Clubes de Leitura', 'Temas Customizados'],
        cta: 'Assinar Pro',
        popular: true
    },
    {
        name: 'Equipes',
        price: 'R$ 49,90',
        description: 'Para escolas e grupos',
        features: ['Tudo do Pro', 'Gestão de Membros', 'Analytics de Grupo', 'API de Integração', 'Suporte Prioritário'],
        cta: 'Falar com Vendas',
        popular: false
    }
];
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
                        v-if="isAuthenticated"
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
        <main class="relative z-10 flex flex-1 flex-col px-4 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <section class="flex min-h-[80vh] flex-col items-center justify-center pt-20 pb-32 text-center">
                <div class="mx-auto max-w-4xl">
                    <!-- Hero Badge -->
                    <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-sm font-medium text-neutral-300 backdrop-blur-md transition-all hover:bg-white/10">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-purple-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-purple-500"></span>
                        </span>
                        Nova versão 2.0 com IA integrada
                    </div>

                    <!-- Hero Headline -->
                    <h1 class="mb-6 text-6xl font-extrabold tracking-tight sm:text-8xl">
                        Sua Biblioteca <br class="hidden sm:block" />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-blue-400 to-emerald-400">Inteligente.</span>
                    </h1>

                    <!-- Hero Description -->
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-neutral-400 sm:text-xl leading-relaxed">
                        Organize seus livros, acompanhe suas metas e use o poder da Inteligência Artificial para elevar sua compreensão de leitura. O acervo digital definitivo para o leitor moderno.
                    </p>

                    <!-- Call To Action Buttons -->
                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <NuxtLink to="/register">
                            <Button size="lg" class="h-14 rounded-full bg-white px-8 text-lg font-bold text-black hover:bg-neutral-200">
                                Começar Gratuitamente
                                <ChevronRight class="ml-2 h-5 w-5" />
                            </Button>
                        </NuxtLink>
                        <NuxtLink to="/login">
                            <Button variant="outline" size="lg" class="h-14 rounded-full border-white/10 bg-white/5 px-8 text-lg font-semibold text-white backdrop-blur-sm hover:bg-white/10">
                                Ver Demonstração
                            </Button>
                        </NuxtLink>
                    </div>
                </div>

                <div class="mt-20 w-full max-w-5xl rounded-2xl border border-white/10 bg-white/5 p-2 backdrop-blur-sm shadow-2xl">
                    <div class="rounded-xl border border-white/10 bg-neutral-900 overflow-hidden aspect-[16/10] relative group">
                        <div class="absolute inset-0 bg-gradient-to-tr from-purple-500/20 via-blue-500/10 to-transparent"></div>
                        <!-- Placeholder for Dashboard Preview -->
                        <div class="w-full h-full flex items-center justify-center border-b border-white/5 bg-neutral-950/50">
                            <div class="text-center space-y-4">
                                <div class="inline-flex p-4 rounded-2xl bg-white/5 ring-1 ring-white/10">
                                    <Layout class="h-12 w-12 text-purple-400" />
                                </div>
                                <h4 class="text-xl font-bold text-white/40">Visualização do Dashboard</h4>
                            </div>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                            <NuxtLink :to="isAuthenticated ? '/dashboard' : '/login'">
                                <Button variant="secondary" class="rounded-full">
                                    <Sparkles class="mr-2 h-4 w-4" />
                                    Explorar Interface
                                </Button>
                            </NuxtLink>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="py-32">
                <div class="text-center mb-20">
                    <h2 class="text-3xl font-bold sm:text-5xl mb-4">Tudo o que você precisa</h2>
                    <p class="text-neutral-400 text-lg max-w-2xl mx-auto">
                        Desenvolvemos cada ferramenta pensando na melhor experiência de leitura possível.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    <Card v-for="feature in features" :key="feature.title" class="bg-white/5 border-white/10 backdrop-blur-sm hover:bg-white/10 transition-colors">
                        <CardHeader>
                            <div :class="['mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-white/5 ring-1 ring-white/10', feature.color]">
                                <component :is="feature.icon" class="h-6 w-6" />
                            </div>
                            <CardTitle class="text-xl font-bold text-white">{{ feature.title }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-neutral-400 leading-relaxed">{{ feature.description }}</p>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <!-- Pricing Section -->
            <section id="pricing" class="py-32 bg-white/[0.02] -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-3xl font-bold sm:text-5xl mb-4">Preços Simples e Transparentes</h2>
                    <p class="text-neutral-400 text-lg max-w-2xl mx-auto">
                        Escolha o plano que melhor se adapta ao seu ritmo de leitura.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <div v-for="plan in plans" :key="plan.name" 
                        :class="['relative flex flex-col rounded-3xl border p-8 transition-all', 
                            plan.popular ? 'bg-white/10 border-purple-500/50 scale-105 z-10' : 'bg-white/5 border-white/10']">
                        
                        <div v-if="plan.popular" class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-purple-500 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white">
                            Mais Popular
                        </div>

                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-white mb-2">{{ plan.name }}</h3>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold text-white">{{ plan.price }}</span>
                                <span v-if="plan.price !== 'R$ 0'" class="text-neutral-400 text-sm">/mês</span>
                            </div>
                            <p class="mt-2 text-sm text-neutral-400">{{ plan.description }}</p>
                        </div>

                        <ul class="mb-10 flex-1 space-y-4">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-3 text-sm text-neutral-300">
                                <Check class="h-4 w-4 text-emerald-400" />
                                {{ feature }}
                            </li>
                        </ul>

                        <Button :variant="plan.popular ? 'default' : 'outline'" 
                            :class="['w-full rounded-xl h-12 font-bold transition-all', plan.popular ? 'bg-purple-600 hover:bg-purple-500' : '']">
                            {{ plan.cta }}
                        </Button>
                    </div>
                </div>
            </section>

            <!-- CTA Final -->
            <section class="py-32 text-center">
                <div class="max-w-3xl mx-auto rounded-3xl bg-gradient-to-br from-purple-600/20 to-blue-600/20 border border-white/10 p-12 backdrop-blur-md">
                    <h2 class="text-3xl font-bold sm:text-4xl mb-6">Pronto para transformar sua leitura?</h2>
                    <p class="text-neutral-300 mb-10 text-lg">
                        Junte-se a centenas de leitores que já estão usando o Meu Acervo Digital para organizar sua vida literária.
                    </p>
                    <NuxtLink to="/register">
                        <Button size="lg" class="rounded-full bg-white px-10 text-lg font-bold text-black hover:bg-neutral-200">
                            Começar Agora Gratuitamente
                        </Button>
                    </NuxtLink>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-white/5 bg-black/20 py-12 backdrop-blur-md">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="flex items-center gap-2">
                        <Library class="h-6 w-6 text-neutral-400" />
                        <span class="text-lg font-semibold tracking-tight text-neutral-200">Meu Acervo Digital</span>
                    </div>
                    <nav class="flex gap-8 text-sm text-neutral-400">
                        <a href="#" class="hover:text-white transition-colors">Termos</a>
                        <a href="#" class="hover:text-white transition-colors">Privacidade</a>
                        <a href="#" class="hover:text-white transition-colors">Suporte</a>
                        <a href="#" class="hover:text-white transition-colors">Twitter</a>
                    </nav>
                    <div class="text-sm text-neutral-500">
                        &copy; 2026 Meu Acervo Digital. Todos os direitos reservados.
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>


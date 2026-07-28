<script setup lang="ts">
definePageMeta({
  layout: false
});
import { BookOpen, Library, TrendingUp, Shield, Zap, Sparkles, MessageSquare, Layout, ChevronRight, Check, Sun, Moon } from 'lucide-vue-next';
import { Button } from '~/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '~/components/ui/card';

const { isAuthenticated } = useAuth();
const canRegister = true;
const { appearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(appearance.value === 'dark' ? 'light' : 'dark');
}

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
        features: ['Livros ilimitados', 'IA Assistente limitada', 'Estatísticas Avançadas', 'Clubes de Leitura', 'Temas Customizados'],
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
    <div class="relative flex min-h-screen flex-col font-sans text-foreground antialiased selection:bg-primary/30">
        <!-- Background -->
        <div class="fixed inset-0 z-0 bg-background" />


        <!-- Navigation Bar -->
        <header class="relative z-10 w-full border-b border-border bg-background/70 backdrop-blur-md">
            <div class="container mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 ring-1 ring-primary/20">
                        <Library class="h-5 w-5 text-primary" />
                    </div>
                    <span class="text-lg font-serif font-semibold tracking-tight text-foreground">Meu Acervo Digital</span>
                </div>


                <nav class="flex items-center gap-4">
                    <NuxtLink
                        v-if="isAuthenticated"
                        to="/dashboard"
                        class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                    >
                        Painel de Controle
                    </NuxtLink>
                    <template v-else>
                        <NuxtLink
                            to="/login"
                            class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Entrar
                        </NuxtLink>
                        <NuxtLink
                            v-if="canRegister"
                            to="/register"
                            class="rounded-full bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground transition-all hover:bg-secondary/70 hover:ring-1 hover:ring-border"
                        >
                            Criar Conta
                        </NuxtLink>
                    </template>
                    <button
                        @click="toggleTheme"
                        class="relative flex h-9 w-9 items-center justify-center rounded-full border border-border bg-secondary/50 text-foreground transition-colors hover:bg-secondary"
                        :aria-label="appearance === 'dark' ? 'Ativar modo claro' : 'Ativar modo escuro'"
                    >
                        <Sun class="h-4 w-4 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
                        <Moon class="absolute h-4 w-4 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
                    </button>
                </nav>
            </div>
        </header>


        <!-- Main Content -->
        <main class="relative z-10 flex flex-1 flex-col px-4 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <section class="relative flex min-h-[80vh] flex-col items-center justify-center overflow-hidden pt-20 pb-32 text-center">
                <!-- Assinatura visual: lombadas de estante ao fundo do hero -->
                <div class="shelf-texture pointer-events-none absolute -top-4 left-0 right-0 bottom-8 opacity-50" />


                <div class="relative mx-auto max-w-4xl">
                    <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-border bg-secondary/50 px-4 py-1.5 text-sm font-medium text-muted-foreground backdrop-blur-md transition-all hover:bg-secondary">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-30"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
                        </span>
                        Nova versão 2.0 com IA integrada
                    </div>


                    <!-- Hero Headline -->
                    <h1 class="mb-6 font-serif text-5xl font-semibold tracking-tight text-foreground sm:text-7xl">
                        Sua Biblioteca <br class="hidden sm:block" />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-blue-400 to-emerald-400">Inteligente.</span>
                    </h1>


                    <!-- Hero Description -->
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-muted-foreground sm:text-xl leading-relaxed">
                        Organize seus livros, acompanhe suas metas e use o poder da Inteligência Artificial para elevar sua compreensão de leitura. O acervo digital definitivo para o leitor moderno.
                    </p>


                    <!-- Call To Action Buttons -->
                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <NuxtLink to="/register">
                            <Button size="lg" class="h-14 rounded-full bg-primary px-8 text-lg font-bold text-primary-foreground hover:bg-primary/90">
                                Começar Gratuitamente
                                <ChevronRight class="ml-2 h-5 w-5" />
                            </Button>
                        </NuxtLink>
                        <NuxtLink to="/login">
                            <Button variant="outline" size="lg" class="h-14 rounded-full border-border bg-secondary/40 px-8 text-lg font-semibold text-foreground backdrop-blur-sm hover:bg-secondary/70">
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
                    <p class="text-muted-foreground text-lg max-w-2xl mx-auto">
                        Desenvolvemos cada ferramenta pensando na melhor experiência de leitura possível.
                    </p>
                </div>


                 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    <Card
                        v-for="feature in features"
                        :key="feature.title"
                        class="border-border bg-card/70 shadow-book transition-colors hover:bg-card"
                    >
                        <CardHeader>
                            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary ring-1 ring-primary/20">
                                <component :is="feature.icon" class="h-6 w-6" />
                            </div>
                            <CardTitle class="font-serif text-xl font-semibold text-foreground">{{ feature.title }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground leading-relaxed">{{ feature.description }}</p>
                        </CardContent>
                    </Card>
                </div>
            </section>


           <!-- Pricing Section -->
            <section id="pricing" class="py-32 bg-secondary/25 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="font-serif text-3xl font-semibold text-foreground sm:text-5xl mb-4">Preços Simples e Transparentes</h2>
                    <p class="text-muted-foreground text-lg max-w-2xl mx-auto">
                        Escolha o plano que melhor se adapta ao seu ritmo de leitura.
                    </p>
                </div>


                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <div
                        v-for="plan in plans"
                        :key="plan.name"
                        :class="['relative flex flex-col overflow-hidden rounded-3xl border pl-9 pr-8 py-8 shadow-book transition-all',
                            plan.popular ? 'bg-card border-primary/40 scale-105 z-10 page-fold' : 'bg-card/70 border-border']"
                    >
                        <!-- Lombada: aba lateral de identificação do plano -->
                        <div
                            :class="['absolute left-0 top-0 h-full w-2', plan.popular ? 'bg-primary' : 'bg-border']"
                            aria-hidden="true"
                        />


                        <div v-if="plan.popular" class="absolute top-6 left-1/2 -translate-x-1/2 rounded-full bg-primary px-4 py-1 text-xs font-bold uppercase tracking-wider text-primary-foreground">
                            Mais Popular
                        </div>


                        <div :class="['mb-8', plan.popular && 'mt-6']">
                            <h3 class="font-serif text-xl font-semibold text-foreground mb-2">{{ plan.name }}</h3>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold text-foreground">{{ plan.price }}</span>
                                <span v-if="plan.price !== 'R$ 0'" class="text-muted-foreground text-sm">/mês</span>
                            </div>
                            <p class="mt-2 text-sm text-muted-foreground">{{ plan.description }}</p>
                        </div>


                        <ul class="mb-10 flex-1 space-y-4">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-3 text-sm text-foreground/80">
                                <Check class="h-4 w-4 text-primary shrink-0" />
                                {{ feature }}
                            </li>
                        </ul>


                        <Button :variant="plan.popular ? 'default' : 'outline'"
                            :class="['w-full rounded-xl h-12 font-bold transition-all', plan.popular ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'border-border']">
                            {{ plan.cta }}
                        </Button>
                    </div>
                </div>
            </section>


            <!-- CTA Final -->
            <section class="py-32 text-center">
                <div class="max-w-3xl mx-auto rounded-3xl bg-card/70 border border-border p-12 shadow-book backdrop-blur-md">
                    <h2 class="font-serif text-3xl font-semibold text-foreground sm:text-4xl mb-6">Pronto para transformar sua leitura?</h2>
                    <p class="text-muted-foreground mb-10 text-lg">
                        Junte-se a centenas de leitores que já estão usando o Meu Acervo Digital para organizar sua vida literária.
                    </p>
                    <NuxtLink to="/register">
                        <Button size="lg" class="rounded-full bg-primary px-10 text-lg font-bold text-primary-foreground hover:bg-primary/90">
                            Começar Agora Gratuitamente
                        </Button>
                    </NuxtLink>
                </div>
            </section>
        </main>


        <!-- Footer -->
        <footer class="relative z-10 border-t border-border bg-background/70 py-12 backdrop-blur-md">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="flex items-center gap-2">
                        <Library class="h-6 w-6 text-muted-foreground" />
                        <span class="font-serif text-lg font-semibold tracking-tight text-foreground">Meu Acervo Digital</span>
                    </div>
                    <nav class="flex gap-8 text-sm text-muted-foreground">
                        <a href="#" class="hover:text-foreground transition-colors">Termos</a>
                        <a href="#" class="hover:text-foreground transition-colors">Privacidade</a>
                        <a href="#" class="hover:text-foreground transition-colors">Suporte</a>
                        <a href="#" class="hover:text-foreground transition-colors">Twitter</a>
                    </nav>
                    <div class="text-sm text-muted-foreground/70">
                        &copy; 2026 Meu Acervo Digital. Todos os direitos reservados.
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>


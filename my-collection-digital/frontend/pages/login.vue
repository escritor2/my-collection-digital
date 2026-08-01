<script setup lang ="ts">
definePageMeta({
    layout: false
});
useHead({
    title: 'Login Admin - My Digital Collection'
});

const status = ref('');
const { normalize } = useApiError();
const form = reactive({
    email: '',
    password: '',
    remember: false,
    processing: false,
    errors: {} as Record<string, string>
});


const submit = async () => {
    form.processing = true;
    try {
        const { login, fetchUser } = useAuth();
        await login({
            email: form.email,
            password: form.password,
            remember: form.remember
        });
        
        // Ensure user data is fetched before redirecting
        await fetchUser();
        
        navigateTo('/dashboard');
    } catch (error: any) {
        const err = normalize(error);
        if (err.status === 422) {
            form.errors = err.fieldErrors || {};
        } else {
            status.value = err.message;
        }
    } finally {
        form.processing = false;
    }
};
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-background text-foreground">
        <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-card shadow-book overflow-hidden sm:rounded-2xl border border-border">
            <div class="mb-8 text-center">
                <h1 class="font-serif text-3xl font-semibold text-brand">Área Restrita</h1>
                <p class="text-sm text-muted-foreground mt-2">Acesso exclusivo do Administrador</p>
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-brand">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-foreground">E-mail Administrativo</label>
                    <input
                        id="email"
                        type="email"
                        class="mt-1 block w-full bg-background border border-border text-foreground rounded-md shadow-sm focus:border-ring focus:ring-ring sm:text-sm py-2 px-3"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        aria-label="Email"
                    />
                    <div v-if="form.errors.email" class="text-destructive text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-foreground">Senha Pessoal</label>
                    <input
                        id="password"
                        type="password"
                        class="mt-1 block w-full bg-background border border-border text-foreground rounded-md shadow-sm focus:border-ring focus:ring-ring sm:text-sm py-2 px-3"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        aria-label="Senha"
                    />
                    <div v-if="form.errors.password" class="text-destructive text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" v-model="form.remember" class="rounded bg-background border-border text-primary focus:ring-ring" />
                        <span class="ms-2 text-sm text-muted-foreground">Lembrar minha sessão</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-primary-foreground bg-brand hover:bg-brand/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring focus:ring-offset-background transition disabled:opacity-50" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Entrar no Acervo
                    </button>
                </div>

                
                <div class="mt-4 text-center space-y-1">
                    <div>
                        <NuxtLink to="/register" class="text-sm text-muted-foreground hover:text-foreground transition">Ainda não tem uma conta? Registre-se!</NuxtLink>
                    </div>
                    <div>
                        <NuxtLink to="/" class="text-sm text-muted-foreground hover:text-foreground transition">Voltar para a página inicial</NuxtLink>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

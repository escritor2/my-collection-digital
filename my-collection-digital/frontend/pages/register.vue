<script setup lang="ts">
definePageMeta({
    layout: false
});
useHead({
    title: 'Criar Conta - My Digital Collection'
});

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    processing: false,
    errors: {}
});

const submit = async () => {
    form.processing = true;
    form.errors = {};

    try {
        const { register } = useAuth();

        await register({
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        });

        navigateTo('/login');
    } catch (error: any) {
        const { normalize } = useApiError();
        const err = normalize(error);

        if (err.status === 422) {
            form.errors = err.fieldErrors || {};
        } else {
            console.error(err);
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
                <h1 class="font-serif text-3xl font-semibold text-brand">Criar Conta</h1>
                <p class="text-sm text-muted-foreground mt-2">Junte-se ao nosso acervo digital</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-foreground">Nome Completo</label>
                    <input
                        id="name"
                        type="text"
                        class="mt-1 block w-full bg-background border border-border text-foreground rounded-md shadow-sm focus:border-ring focus:ring-ring sm:text-sm py-2 px-3"
                        v-model="form.name"
                        required
                        autofocus
                    />
                    <div v-if="form.errors.name" class="text-destructive text-xs mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-foreground">E-mail</label>
                    <input
                        id="email"
                        type="email"
                        class="mt-1 block w-full bg-background border border-border text-foreground rounded-md shadow-sm focus:border-ring focus:ring-ring sm:text-sm py-2 px-3"
                        v-model="form.email"
                        required
                    />
                    <div v-if="form.errors.email" class="text-destructive text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-foreground">Senha</label>
                    <input
                        id="password"
                        type="password"
                        class="mt-1 block w-full bg-background border border-border text-foreground rounded-md shadow-sm focus:border-ring focus:ring-ring sm:text-sm py-2 px-3"
                        v-model="form.password"
                        required
                    />
                    <div v-if="form.errors.password" class="text-destructive text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-foreground">Confirmar Senha</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full bg-background border border-border text-foreground rounded-md shadow-sm focus:border-ring focus:ring-ring sm:text-sm py-2 px-3"
                        v-model="form.password_confirmation"
                        required
                    />
                    <div v-if="form.errors.password_confirmation" class="text-destructive text-xs mt-1">{{ form.errors.password_confirmation }}</div>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-primary-foreground bg-brand hover:bg-brand/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring focus:ring-offset-background transition disabled:opacity-50" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Registrar
                    </button>
                </div>

                <div class="mt-4 text-center space-y-1">
                    <div>
                        <NuxtLink to="/login" class="text-sm text-muted-foreground hover:text-foreground transition">Já tem uma conta? Faça Login!</NuxtLink>
                    </div>
                    <div>
                        <NuxtLink to="/" class="text-sm text-muted-foreground hover:text-foreground transition">Voltar para a página inicial</NuxtLink>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
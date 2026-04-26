<script setup lang ="ts">
definePageMeta({
    layout: false
});
useHead({
    title: 'Login Admin - My Digital Collection'
});

const status = ref('');
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
        const { login } = useAuth();
        await login({
            email: form.email,
            password: form.password,
            remember: form.remember
        });
        navigateTo('/dashboard');
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.errors = error.response._data.errors || {};
        } else {
            status.value = 'Falha no login. Verifique suas credenciais.';
        }
    } finally {
        form.processing = false;
    }
};
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-zinc-900 text-gray-200">
        <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-zinc-800 shadow-2xl overflow-hidden sm:rounded-lg border border-zinc-700">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-400 to-emerald-600 bg-clip-text text-transparent">Área Restrita</h1>
                <p class="text-sm text-zinc-400 mt-2">Acesso exclusivo do Administrador</p>
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-400">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-300">E-mail Administrativo</label>
                    <input
                        id="email"
                        type="email"
                        class="mt-1 block w-full bg-zinc-900 border border-zinc-700 text-white rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm py-2 px-3"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <div v-if="form.errors.email" class="text-red-400 text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-300">Senha Pessoal</label>
                    <input
                        id="password"
                        type="password"
                        class="mt-1 block w-full bg-zinc-900 border border-zinc-700 text-white rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm py-2 px-3"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <div v-if="form.errors.password" class="text-red-400 text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" v-model="form.remember" class="rounded bg-zinc-900 border-zinc-700 text-emerald-500 focus:ring-emerald-500" />
                        <span class="ms-2 text-sm text-zinc-400">Lembrar minha sessão</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 focus:ring-offset-zinc-800 transition disabled:opacity-50" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Entrar no Acervo
                    </button>
                </div>
                
                <div class="mt-4 text-center">
                    <NuxtLink to="/" class="text-sm text-zinc-400 hover:text-white transition">Voltar para a página inicial</NuxtLink>
                </div>
            </form>
        </div>
    </div>
</template>

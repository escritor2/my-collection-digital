<script setup>
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
    console.log('Registering user:', form.name);
    await new Promise(resolve => setTimeout(resolve, 1000));
    form.processing = false;
    navigateTo('/dashboard');
};
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-zinc-900 text-gray-200">
        <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-zinc-800 shadow-2xl overflow-hidden sm:rounded-lg border border-zinc-700">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">Criar Conta</h1>
                <p class="text-sm text-zinc-400 mt-2">Junte-se ao nosso acervo digital</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-300">Nome Completo</label>
                    <input
                        id="name"
                        type="text"
                        class="mt-1 block w-full bg-zinc-900 border border-zinc-700 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                        v-model="form.name"
                        required
                        autofocus
                    />
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-300">E-mail</label>
                    <input
                        id="email"
                        type="email"
                        class="mt-1 block w-full bg-zinc-900 border border-zinc-700 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                        v-model="form.email"
                        required
                    />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-300">Senha</label>
                    <input
                        id="password"
                        type="password"
                        class="mt-1 block w-full bg-zinc-900 border border-zinc-700 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                        v-model="form.password"
                        required
                    />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-zinc-300">Confirmar Senha</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full bg-zinc-900 border border-zinc-700 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                        v-model="form.password_confirmation"
                        required
                    />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-zinc-800 transition disabled:opacity-50" :disabled="form.processing">
                        Registrar
                    </button>
                </div>
                
                <div class="mt-4 text-center">
                    <NuxtLink to="/login" class="text-sm text-zinc-400 hover:text-white transition">Já tem uma conta? Faça Login</NuxtLink>
                </div>
            </form>
        </div>
    </div>
</template>

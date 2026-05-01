<script setup lang="ts">
import { useAuth } from '~/composables/useAuth';
import { useApi } from '~/composables/useApi';
import { Button } from '~/components/ui/button';
import { Input } from '~/components/ui/input';
import { Label } from '~/components/ui/label';
import { Separator } from '~/components/ui/separator';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '~/components/ui/card';
import { Shield, Lock, Smartphone } from 'lucide-vue-next';
import { ref } from 'vue';

definePageMeta({
    layout: 'settings-layout',
});

const { apiFetch } = useApi();
const isLoading = ref(false);
const message = ref<{ type: 'success' | 'error', text: string } | null>(null);

const form = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const handlePasswordUpdate = async () => {
    isLoading.value = true;
    message.value = null;
    try {
        await apiFetch('/settings/password', {
            method: 'PUT',
            body: form.value,
        });
        message.value = { type: 'success', text: 'Senha atualizada com sucesso!' };
        form.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
    } catch (e: any) {
        message.value = { type: 'error', text: e.data?.message || 'Erro ao atualizar senha.' };
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-medium">Segurança</h3>
            <p class="text-sm text-muted-foreground">
                Proteja sua conta e gerencie suas credenciais.
            </p>
        </div>
        <Separator />

        <div v-if="message" :class="['p-3 rounded-md text-sm', message.type === 'success' ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-red-500/10 text-red-500 border border-red-500/20']">
            {{ message.text }}
        </div>

        <section class="space-y-8">
            <Card class="border border-zinc-200 dark:border-zinc-800 bg-transparent shadow-none">
                <CardHeader>
                    <CardTitle class="text-base flex items-center gap-2">
                        <Lock class="h-4 w-4 text-purple-600" />
                        Alterar Senha
                    </CardTitle>
                    <CardDescription>Recomendamos o uso de uma senha forte que você não use em outros lugares.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="handlePasswordUpdate" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="current_password">Senha Atual</Label>
                            <Input id="current_password" v-model="form.current_password" type="password" />
                        </div>
                        <div class="space-y-2">
                            <Label for="password">Nova Senha</Label>
                            <Input id="password" v-model="form.password" type="password" />
                        </div>
                        <div class="space-y-2">
                            <Label for="password_confirmation">Confirmar Nova Senha</Label>
                            <Input id="password_confirmation" v-model="form.password_confirmation" type="password" />
                        </div>
                        <div class="flex justify-end pt-2">
                            <Button type="submit" :disabled="isLoading" class="bg-purple-600 hover:bg-purple-700">
                                {{ isLoading ? 'Atualizando...' : 'Atualizar Senha' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card class="border border-zinc-200 dark:border-zinc-800 bg-transparent shadow-none">
                <CardHeader>
                    <CardTitle class="text-base flex items-center gap-2">
                        <Smartphone class="h-4 w-4 text-purple-600" />
                        Autenticação em Duas Etapas (2FA)
                    </CardTitle>
                    <CardDescription>Adicione uma camada extra de segurança à sua conta usando um aplicativo autenticador.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-sm font-medium">Status: <span class="text-red-500">Desativado</span></p>
                            <p class="text-xs text-muted-foreground">O 2FA não está configurado no momento.</p>
                        </div>
                        <Button variant="outline" size="sm">Configurar 2FA</Button>
                    </div>
                </CardContent>
            </Card>

            <Card class="border border-red-500/20 bg-red-500/5 shadow-none">
                <CardHeader>
                    <CardTitle class="text-base text-red-600">Zona de Perigo</CardTitle>
                    <CardDescription>Ações irreversíveis relacionadas à sua conta.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">Excluir Conta</p>
                            <p class="text-xs text-muted-foreground">Todos os seus dados serão removidos permanentemente.</p>
                        </div>
                        <Button variant="destructive" size="sm">Excluir Minha Conta</Button>
                    </div>
                </CardContent>
            </Card>
        </section>
    </div>
</template>

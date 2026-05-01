<script setup lang="ts">
import { useAuth } from '~/composables/useAuth';
import { useApi } from '~/composables/useApi';
import { Button } from '~/components/ui/button';
import { Input } from '~/components/ui/input';
import { Label } from '~/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '~/components/ui/card';
import { Separator } from '~/components/ui/separator';
import { Avatar, AvatarFallback, AvatarImage } from '~/components/ui/avatar';
import { useInitials } from '~/composables/useInitials';
import { ref, onMounted } from 'vue';

definePageMeta({
    layout: 'settings-layout',
});

const { user, fetchUser } = useAuth();
const { apiFetch } = useApi();
const initials = useInitials();

const isLoading = ref(false);
const message = ref<{ type: 'success' | 'error', text: string } | null>(null);

const form = ref({
    name: '',
    email: '',
});

onMounted(async () => {
    if (!user.value) {
        await fetchUser();
    }
    if (user.value) {
        form.value.name = user.value.name;
        form.value.email = user.value.email;
    }
});

const handleSubmit = async () => {
    isLoading.value = true;
    message.value = null;
    try {
        await apiFetch('/settings/profile', {
            method: 'PATCH',
            body: form.value,
        });
        message.value = { type: 'success', text: 'Perfil atualizado com sucesso!' };
        await fetchUser();
    } catch (e: any) {
        message.value = { type: 'error', text: e.data?.message || 'Erro ao atualizar perfil.' };
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-medium">Perfil</h3>
            <p class="text-sm text-muted-foreground">
                Como os outros verão você na plataforma.
            </p>
        </div>
        <Separator />
        
        <div v-if="message" :class="['p-3 rounded-md text-sm', message.type === 'success' ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-red-500/10 text-red-500 border border-red-500/20']">
            {{ message.text }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-8">
            <Card class="border-none shadow-none bg-transparent">
                <CardContent class="p-0 space-y-6">
                    <div class="flex items-center gap-4">
                        <Avatar class="h-20 w-20 border-2 border-purple-500/20">
                            <AvatarImage v-if="user?.avatar_url" :src="user.avatar_url" />
                            <AvatarFallback class="bg-purple-100 text-purple-600 text-xl font-bold">
                                {{ initials(user?.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <Button type="button" variant="outline" size="sm">Alterar Avatar</Button>
                            <p class="text-[10px] text-muted-foreground mt-2 uppercase font-bold tracking-widest">JPG, GIF ou PNG. Máx 2MB.</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="name">Nome Público</Label>
                        <Input id="name" v-model="form.name" placeholder="Seu nome" />
                        <p class="text-[11px] text-muted-foreground">Este é o nome que será exibido no seu perfil e contribuições.</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">E-mail</Label>
                        <Input id="email" type="email" v-model="form.email" placeholder="seu@email.com" />
                        <p class="text-[11px] text-muted-foreground">O e-mail é usado para login e notificações importantes.</p>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-end">
                <Button type="submit" :disabled="isLoading" class="bg-purple-600 hover:bg-purple-700">
                    {{ isLoading ? 'Salvando...' : 'Salvar Alterações' }}
                </Button>
            </div>
        </form>
    </div>
</template>

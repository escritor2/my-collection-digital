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
import { Loader2, X } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

definePageMeta({
    layout: 'settings-layout',
});

const { user, fetchUser } = useAuth();
const { apiFetch } = useApi();
const { getInitials } = useInitials();

const isLoading = ref(false);
const message = ref<{ type: 'success' | 'error', text: string } | null>(null);

const form = ref({
    name: '',
    email: '',
});

const avatarInput = ref<HTMLInputElement | null>(null);
const isUploadingAvatar = ref(false);
const avatarError = ref<string | null>(null);

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

const triggerAvatarPicker = () => {
    avatarInput.value?.click();
};

const onAvatarSelected = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    avatarError.value = null;

    if (file.size > 4 * 1024 * 1024) {
        avatarError.value = 'Arquivo muito grande. Máximo de 4MB.';
        input.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);

    isUploadingAvatar.value = true;
    try {
        await apiFetch('/settings/profile/avatar', {
            method: 'POST',
            body: formData,
        });
        await fetchUser();
    } catch (e: any) {
        avatarError.value = e.data?.message || e.data?.errors?.avatar?.[0] || 'Erro ao enviar a foto.';
    } finally {
        isUploadingAvatar.value = false;
        input.value = '';
    }
};

const removeAvatar = async () => {
    isUploadingAvatar.value = true;
    avatarError.value = null;
    try {
        await apiFetch('/settings/profile/avatar', { method: 'DELETE' });
        await fetchUser();
    } catch (e: any) {
        avatarError.value = e.data?.message || 'Erro ao remover a foto.';
    } finally {
        isUploadingAvatar.value = false;
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
                        <div class="relative">
                            <Avatar class="h-20 w-20 border-2 border-purple-500/20">
                                <AvatarImage v-if="user?.avatar_url" :src="user.avatar_url" />
                                <AvatarFallback class="bg-purple-100 text-purple-600 text-xl font-bold">
                                    {{ getInitials(user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <div v-if="isUploadingAvatar" class="absolute inset-0 rounded-full bg-black/40 flex items-center justify-center">
                                <Loader2 class="h-5 w-5 text-white animate-spin" />
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <input ref="avatarInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onAvatarSelected" />
                                <Button type="button" variant="outline" size="sm" :disabled="isUploadingAvatar" @click="triggerAvatarPicker">
                                    {{ user?.avatar_url ? 'Alterar foto' : 'Adicionar foto' }}
                                </Button>
                                <Button v-if="user?.avatar_url" type="button" variant="ghost" size="sm" class="text-muted-foreground gap-1" :disabled="isUploadingAvatar" @click="removeAvatar">
                                    <X class="h-3.5 w-3.5" /> Remover
                                </Button>
                            </div>
                            <p class="text-[10px] text-muted-foreground mt-2 uppercase font-bold tracking-widest">JPG, PNG ou WEBP. Máx 4MB.</p>
                            <p v-if="avatarError" class="text-xs text-red-500 mt-1">{{ avatarError }}</p>
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

<script setup>
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps({
    goal: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['saved']);
const config = useRuntimeConfig();

const isOpen = ref(false);
const loading = ref(false);
const errorMsg = ref('');

const form = ref({
    type: 'daily',
    target_value: 30,
    target_unit: 'pages'
});

watch(() => props.goal, (newGoal) => {
    if (newGoal) {
        form.value = {
            type: newGoal.type || 'daily',
            target_value: newGoal.target_value || 30,
            target_unit: newGoal.target_unit || 'pages'
        };
    }
}, { immediate: true });

async function saveGoal() {
    loading.value = true;
    errorMsg.value = '';
    
    try {
        const url = props.goal && props.goal.id 
            ? `/api/reading-goals/${props.goal.id}`
            : '/api/reading-goals';
            
        const method = props.goal && props.goal.id ? 'PUT' : 'POST';

        const { data, error } = await useFetch(url, {
            baseURL: config.public.apiBase,
            method: method,
            body: form.value,
            headers: {
                Accept: 'application/json'
            }
        });

        if (error.value) {
            errorMsg.value = error.value.data?.message || 'Erro ao salvar meta';
        } else {
            isOpen.value = false;
            emit('saved');
        }
    } catch (e) {
        errorMsg.value = 'Erro de comunicação com servidor';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger asChild>
            <Button variant="outline" size="sm">
                {{ goal ? 'Editar Meta' : 'Nova Meta' }}
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ goal ? 'Editar' : 'Criar' }} Meta de Leitura</DialogTitle>
                <DialogDescription>
                    Defina sua meta de leitura para manter um ritmo constante.
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid grid-cols-4 items-center gap-4">
                    <Label for="type" class="text-right">Frequência</Label>
                    <Select v-model="form.type" id="type" class="col-span-3">
                        <SelectTrigger class="col-span-3">
                            <SelectValue placeholder="Selecione a frequência" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="daily">Diária</SelectItem>
                            <SelectItem value="weekly">Semanal</SelectItem>
                            <SelectItem value="monthly">Mensal</SelectItem>
                            <SelectItem value="yearly">Anual</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                
                <div class="grid grid-cols-4 items-center gap-4">
                    <Label for="target_value" class="text-right">Quantidade</Label>
                    <Input id="target_value" type="number" v-model="form.target_value" class="col-span-3" min="1" />
                </div>
                
                <div class="grid grid-cols-4 items-center gap-4">
                    <Label for="target_unit" class="text-right">Unidade</Label>
                    <Select v-model="form.target_unit" id="target_unit" class="col-span-3">
                        <SelectTrigger class="col-span-3">
                            <SelectValue placeholder="Selecione a unidade" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="pages">Páginas</SelectItem>
                            <SelectItem value="minutes">Minutos</SelectItem>
                            <SelectItem value="books">Livros</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            
            <div v-if="errorMsg" class="text-sm text-red-500 mb-2">
                {{ errorMsg }}
            </div>
            
            <DialogFooter>
                <Button type="submit" @click="saveGoal" :disabled="loading">
                    {{ loading ? 'Salvando...' : 'Salvar' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
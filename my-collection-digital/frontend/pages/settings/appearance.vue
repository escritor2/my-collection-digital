<script setup lang="ts">
import { useAppearance } from '~/composables/useAppearance';
import { useAccessibilityPrefs } from '~/composables/useAccessibilityPrefs';
import { Button } from '~/components/ui/button';
import { Label } from '~/components/ui/label';
import { Separator } from '~/components/ui/separator';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '~/components/ui/card';
import { Sun, Moon, Laptop, Type, Check, Globe } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

definePageMeta({
    layout: 'settings-layout',
});

const { appearance, updateAppearance } = useAppearance();
const { dyslexiaMode, setDyslexiaMode } = useAccessibilityPrefs();
const { locale, locales, setLocale } = useI18n();

const themes = [
    { id: 'light', name: 'Claro', icon: Sun },
    { id: 'dark', name: 'Escuro', icon: Moon },
    { id: 'system', name: 'Sistema', icon: Laptop },
];
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-medium">Aparência e Idioma</h3>
            <p class="text-sm text-muted-foreground">
                Personalize como a plataforma se parece e fala com você.
            </p>
        </div>
        <Separator />

        <section class="space-y-8">
            <div class="space-y-4">
                <Label>Idioma do Sistema</Label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button
                        v-for="loc in locales"
                        :key="loc.code"
                        @click="setLocale(loc.code)"
                        :class="[
                            'relative flex items-center gap-3 p-4 rounded-xl border-2 transition-all text-left',
                            locale === loc.code 
                                ? 'border-purple-600 bg-purple-50/50 dark:bg-purple-900/10' 
                                : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700'
                        ]"
                    >
                        <Globe class="h-4 w-4 text-purple-600" />
                        <span class="text-sm font-bold">{{ loc.name }}</span>
                        <Check v-if="locale === loc.code" class="ml-auto h-4 w-4 text-purple-600" />
                    </button>
                </div>
            </div>

            <Separator />

            <div class="space-y-4">
                <Label>Tema</Label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button
                        v-for="theme in themes"
                        :key="theme.id"
                        @click="updateAppearance(theme.id as any)"
                        :class="[
                            'relative flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all text-left group',
                            appearance === theme.id 
                                ? 'border-purple-600 bg-purple-50/50 dark:bg-purple-900/10' 
                                : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700'
                        ]"
                    >
                        <div v-if="appearance === theme.id" class="absolute top-2 right-2">
                            <div class="bg-purple-600 rounded-full p-0.5">
                                <Check class="h-3 w-3 text-white" />
                            </div>
                        </div>
                        
                        <div :class="[
                            'p-3 rounded-lg',
                            appearance === theme.id ? 'bg-purple-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500'
                        ]">
                            <component :is="theme.icon" class="h-6 w-6" />
                        </div>
                        
                        <div class="text-center">
                            <p class="text-sm font-bold">{{ theme.name }}</p>
                        </div>
                    </button>
                </div>
            </div>

            <Separator />

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="space-y-0.5">
                        <Label class="text-base flex items-center gap-2">
                            <Type class="h-4 w-4 text-purple-600" />
                            Modo Dislexia
                        </Label>
                        <p class="text-sm text-muted-foreground">Usa a fonte Atkinson Hyperlegible para melhorar a legibilidade.</p>
                    </div>
                    <Button 
                        variant="outline" 
                        @click="setDyslexiaMode(!dyslexiaMode)"
                        :class="dyslexiaMode ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/10' : ''"
                    >
                        {{ dyslexiaMode ? 'Ativado' : 'Desativado' }}
                    </Button>
                </div>
            </div>

            <Card class="border border-purple-500/20 bg-gradient-to-br from-purple-500/5 to-indigo-500/5 shadow-none">
                <CardHeader>
                    <CardTitle class="text-sm font-bold uppercase tracking-widest text-purple-600">Dica de Experiência</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm leading-relaxed">
                        O Modo Dislexia foi projetado especificamente para tornar a leitura de códigos e documentações mais fluida, 
                        aumentando a distinção entre caracteres semelhantes.
                    </p>
                </CardContent>
            </Card>
        </section>
    </div>
</template>

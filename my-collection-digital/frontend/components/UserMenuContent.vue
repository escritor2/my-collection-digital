<script setup lang="ts">
import { LogOut, Settings, Sun, Moon, Laptop, Type, Check, Zap } from 'lucide-vue-next';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuPortal,
} from '~/components/ui/dropdown-menu';
import UserInfo from '~/components/UserInfo.vue';
import type { User } from '~/types';
import { computed } from 'vue';

const { dyslexiaMode, setDyslexiaMode } = useAccessibilityPrefs();
const { appearance, updateAppearance } = useAppearance();

const handleLogout = () => {
    navigateTo('/login');
};

type Props = {
    user: User | null;
};

const props = defineProps<Props>();

const nextLevelXp = 1000;
const xpProgress = computed(() => {
    if (!props.user) return 0;
    const relativeXp = props.user.xp % nextLevelXp;
    return (relativeXp / nextLevelXp) * 100;
});
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex flex-col gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
            
            <div v-if="user" class="px-2 pb-1">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] text-muted-foreground font-medium flex items-center gap-1">
                        <Zap class="h-2 w-2 text-purple-500 fill-purple-500" />
                        Próximo Nível
                    </span>
                    <span class="text-[10px] text-purple-500 font-bold">{{ user.xp % 1000 }} / 1000 XP</span>
                </div>
                <div class="h-1 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-gradient-to-r from-purple-500 to-indigo-600 transition-all duration-500"
                        :style="{ width: `${xpProgress}%` }"
                    ></div>
                </div>
            </div>
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <NuxtLink class="flex w-full cursor-pointer items-center" to="/settings/profile" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Configurações
            </NuxtLink>
        </DropdownMenuItem>
        
        <DropdownMenuSub>
            <DropdownMenuSubTrigger class="cursor-pointer">
                <Sun class="mr-2 h-4 w-4 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
                <Moon class="absolute mr-2 h-4 w-4 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
                <span>Aparência</span>
            </DropdownMenuSubTrigger>
            <DropdownMenuPortal>
                <DropdownMenuSubContent>
                    <DropdownMenuItem class="cursor-pointer" @select="updateAppearance('light')">
                        <Sun class="mr-2 h-4 w-4" />
                        <span>Claro</span>
                        <Check v-if="appearance === 'light'" class="ml-auto h-4 w-4" />
                    </DropdownMenuItem>
                    <DropdownMenuItem class="cursor-pointer" @select="updateAppearance('dark')">
                        <Moon class="mr-2 h-4 w-4" />
                        <span>Escuro</span>
                        <Check v-if="appearance === 'dark'" class="ml-auto h-4 w-4" />
                    </DropdownMenuItem>
                    <DropdownMenuItem class="cursor-pointer" @select="updateAppearance('system')">
                        <Laptop class="mr-2 h-4 w-4" />
                        <span>Sistema</span>
                        <Check v-if="appearance === 'system'" class="ml-auto h-4 w-4" />
                    </DropdownMenuItem>
                </DropdownMenuSubContent>
            </DropdownMenuPortal>
        </DropdownMenuSub>

        <DropdownMenuItem class="cursor-pointer" @select="setDyslexiaMode(!dyslexiaMode)">
            <Type class="mr-2 h-4 w-4" />
            <span>Modo Dislexia</span>
            <div :class="['ml-auto h-2 w-2 rounded-full', dyslexiaMode ? 'bg-purple-500' : 'bg-zinc-300 dark:bg-zinc-700']"></div>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem class="cursor-pointer text-red-600 focus:bg-red-50 focus:text-red-600 dark:text-red-400 dark:focus:bg-red-900/10" @select="handleLogout">
        <LogOut class="mr-2 h-4 w-4" />
        Sair
    </DropdownMenuItem>
</template>


<script setup>
import { Flame, Trophy } from 'lucide-vue-next';

defineProps({
    streak: {
        type: Object,
        default: () => ({ current_streak: 0, longest_streak: 0 })
    }
});
</script>

<template>
    <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow sm:rounded-lg border dark:border-zinc-800">
        <div class="px-4 py-5 sm:p-6 h-full flex flex-col justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Suas Ofensivas
            </h3>
            
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="flex flex-col items-center justify-center p-4 bg-orange-50 dark:bg-orange-950/20 rounded-lg border border-orange-100 dark:border-orange-900/30">
                    <Flame class="w-8 h-8 text-orange-500 mb-2" :class="{'animate-pulse': streak.current_streak > 0}" />
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ streak.current_streak }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-semibold mt-1">Atual</span>
                </div>
                
                <div class="flex flex-col items-center justify-center p-4 bg-yellow-50 dark:bg-yellow-950/20 rounded-lg border border-yellow-100 dark:border-yellow-900/30">
                    <Trophy class="w-8 h-8 text-yellow-500 mb-2" />
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ streak.longest_streak }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-semibold mt-1">Maior</span>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <p v-if="streak.current_streak > 0" class="text-sm text-gray-600 dark:text-gray-400">
                    Você leu por <span class="font-bold text-orange-500">{{ streak.current_streak }}</span> dias consecutivos!
                </p>
                <p v-else class="text-sm text-gray-500 dark:text-gray-400">
                    Leia hoje para iniciar uma ofensiva!
                </p>
            </div>
        </div>
    </div>
</template>
<script setup>
import { computed } from 'vue';

const props = defineProps({
    goal: {
        type: Object,
        default: null
    }
});

const progressWidth = computed(() => {
    if (!props.goal) return '0%';
    return `${props.goal.percentage}%`;
});

const unitLabel = computed(() => {
    if (!props.goal) return '';
    return {
        'pages': 'páginas',
        'minutes': 'minutos',
        'books': 'livros'
    }[props.goal.target_unit] || props.goal.target_unit;
});

const typeLabel = computed(() => {
    if (!props.goal) return '';
    return {
        'daily': 'Diária',
        'weekly': 'Semanal',
        'monthly': 'Mensal',
        'yearly': 'Anual'
    }[props.goal.type] || props.goal.type;
});
</script>

<template>
    <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow sm:rounded-lg border dark:border-zinc-800">
        <div class="px-4 py-5 sm:p-6 h-full flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        Meta {{ typeLabel }}
                    </h3>
                    <slot name="action"></slot>
                </div>
                
                <div v-if="goal">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        {{ goal.current_value }} de {{ goal.target_value }} {{ unitLabel }}
                    </p>
                    
                    <div class="w-full bg-gray-200 dark:bg-zinc-800 rounded-full h-2.5 mb-2 overflow-hidden">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" :style="{ width: progressWidth }"></div>
                    </div>
                    <p class="text-xs text-right text-gray-500 dark:text-gray-400">
                        {{ goal.percentage }}%
                    </p>
                </div>
                <div v-else class="text-center py-6 text-gray-500 dark:text-gray-400">
                    <p class="mb-4">Nenhuma meta definida</p>
                </div>
            </div>
        </div>
    </div>
</template>
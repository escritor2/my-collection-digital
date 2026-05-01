<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '~/components/ui/avatar';
import { useInitials } from '~/composables/useInitials';
import { Flame, Zap } from 'lucide-vue-next';
import type { User } from '~/types';

type Props = {
    user: User | null;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(
    () => props.user?.avatar && props.user?.avatar !== '',
);

const xpProgress = computed(() => {
  if (!props.user) return 0;
  const relativeXp = props.user.xp % 1000;
  return (relativeXp / 1000) * 100;
});
</script>

<template>
    <template v-if="user">
        <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
            <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />
            <AvatarFallback class="rounded-lg text-black dark:text-white">
                {{ getInitials(user.name) }}
            </AvatarFallback>
        </Avatar>

        <div class="grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-medium">{{ user.name }}</span>
            <span v-if="showEmail" class="truncate text-[10px] text-muted-foreground">{{
                user.email
            }}</span>
            <div class="flex items-center gap-1.5 mt-0.5">
                <div class="flex items-center gap-0.5 text-[10px] font-bold text-purple-600 dark:text-purple-400">
                    <Zap class="h-2.5 w-2.5 fill-current" />
                    Lvl {{ user.level }}
                </div>
                <div v-if="user.streak_days > 0" class="flex items-center gap-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-500">
                    <Flame class="h-2.5 w-2.5 fill-current" />
                    {{ user.streak_days }}d
                </div>
            </div>
        </div>
    </template>
</template>


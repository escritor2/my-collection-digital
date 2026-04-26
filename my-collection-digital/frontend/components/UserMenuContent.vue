<script setup lang="ts">
import { LogOut, Settings } from 'lucide-vue-next';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '~/components/ui/dropdown-menu';
import UserInfo from '~/components/UserInfo.vue';
import type { User } from '~/types';

const handleLogout = () => {
    navigateTo('/login');
};

type Props = {
    user: User;
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <NuxtLink class="block w-full cursor-pointer" to="/settings/profile" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </NuxtLink>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem class="cursor-pointer" @select="handleLogout">
        <LogOut class="mr-2 h-4 w-4" />
        Sair
    </DropdownMenuItem>
</template>


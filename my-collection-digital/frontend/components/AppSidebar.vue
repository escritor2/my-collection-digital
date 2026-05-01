<script setup lang="ts">
import { BookOpen, FolderGit2, LayoutGrid, Library, Tags, Folder, Search, Bell, Users } from 'lucide-vue-next';
import AppLogo from '~/components/AppLogo.vue';
import NavFooter from '~/components/NavFooter.vue';
import NavMain from '~/components/NavMain.vue';
import NavUser from '~/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '~/components/ui/sidebar';
import type { NavItem } from '~/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Minha Estante',
        href: '/user-shelf',
        icon: BookOpen,
    },
    {
        title: 'Catálogo',
        href: '/books',
        icon: Library,
    },
    {
        title: 'Busca',
        href: '/search',
        icon: Search,
    },
    {
        title: 'Tags',
        href: '/tags',
        icon: Tags,
    },
    {
        title: 'Coleções',
        href: '/collections',
        icon: Folder,
    },
    {
        title: 'Retenção',
        href: '/retention',
        icon: Bell,
    },
    {
        title: 'Social',
        href: '/social',
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <NuxtLink to="/dashboard">
                            <AppLogo />
                        </NuxtLink>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>


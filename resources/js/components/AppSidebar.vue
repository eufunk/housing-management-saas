<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Bell, Building2, Calendar, FileText, FolderOpen, LayoutGrid, Settings, UserCheck, Users, Wallet, Wrench } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import NavFooter from './NavFooter.vue';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
    {
        title: 'Immobilien',
        href: '/properties',
        icon: Building2,
        items: [
            { title: 'Übersicht', href: '/properties' },
            { title: 'Gebäude', href: '/properties/buildings' },
            { title: 'Wohnungen', href: '/properties/units' },
        ],
    },
    { title: 'Mieter', href: '/tenants', icon: Users },
    { title: 'Eigentümer', href: '/owners', icon: UserCheck },
    { title: 'Verträge', href: '/leases', icon: FileText },
    { title: 'Finanzen', href: '/payments', icon: Wallet },
    { title: 'Reparaturen', href: '/maintenance', icon: Wrench },
    { title: 'Dokumente', href: '/documents', icon: FolderOpen },
    { title: 'Termine', href: '/appointments', icon: Calendar },
    { title: 'Benachrichtigungen', href: '/notifications', icon: Bell },
];

const footerNavItems: NavItem[] = [{ title: 'Einstellungen', href: '/settings/profile', icon: Settings }];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
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

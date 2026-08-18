<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { AlertTriangle, Building2, CalendarClock, DoorOpen, TrendingDown, TrendingUp, Users, Wrench } from 'lucide-vue-next';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

// Placeholder data. Once the backend modules exist, these will come from a
// DashboardController instead of being hard-coded here.
const stats = [
    { label: 'Immobilien', value: '12', icon: Building2 },
    { label: 'Wohnungen', value: '84', icon: DoorOpen },
    { label: 'Mieter', value: '76', icon: Users },
    { label: 'Leerstand', value: '8', description: '4 Wohnungen frei' },
];

const financials = [
    { label: 'Einnahmen (Monat)', value: '48.250 €', icon: TrendingUp, tone: 'positive' as const },
    { label: 'Ausgaben (Monat)', value: '12.940 €', icon: TrendingDown, tone: 'negative' as const },
];

const alerts = [
    { label: 'Offene Reparaturen', value: 5, icon: Wrench },
    { label: 'Überfällige Zahlungen', value: 2, icon: AlertTriangle },
    { label: 'Auslaufende Mietverträge (30 Tage)', value: 3, icon: CalendarClock },
];

const recentActivity = [
    { description: 'Mietvertrag für Wohnung 3B unterschrieben', time: 'vor 2 Stunden' },
    { description: 'Reparaturauftrag "Heizung Ausfall" abgeschlossen', time: 'vor 5 Stunden' },
    { description: 'Zahlung von Max Mustermann eingegangen', time: 'gestern' },
    { description: 'Neue Immobilie "Gartenstraße 12" angelegt', time: 'vor 2 Tagen' },
];

const financialTone = computed(
    () => (tone: 'positive' | 'negative') => (tone === 'positive' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'),
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card v-for="stat in stats" :key="stat.label">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">{{ stat.label }}</CardTitle>
                        <component :is="stat.icon" v-if="stat.icon" class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stat.value }}</div>
                        <p v-if="stat.description" class="text-xs text-muted-foreground">{{ stat.description }}</p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <Card class="lg:col-span-1">
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Finanzen (dieser Monat)</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-for="item in financials" :key="item.label" class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <component :is="item.icon" class="size-4" />
                                {{ item.label }}
                            </div>
                            <span class="font-semibold" :class="financialTone(item.tone)">{{ item.value }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card class="lg:col-span-1">
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Aufmerksamkeit erforderlich</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-for="alert in alerts" :key="alert.label" class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <component :is="alert.icon" class="size-4" />
                                {{ alert.label }}
                            </div>
                            <Badge variant="secondary">{{ alert.value }}</Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card class="lg:col-span-1">
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Letzte Aktivitäten</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-3">
                            <li v-for="(activity, index) in recentActivity" :key="index" class="text-sm">
                                <p>{{ activity.description }}</p>
                                <p class="text-xs text-muted-foreground">{{ activity.time }}</p>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

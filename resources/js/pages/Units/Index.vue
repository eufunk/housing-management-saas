<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { EmptyState } from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useListSearch } from '@/composables/useListSearch';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { DoorOpen, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Building {
    id: number;
    name: string;
}

type UnitStatus = 'vacant' | 'occupied' | 'maintenance';
type UnitType = 'apartment' | 'commercial' | 'parking_space' | 'other';

interface UnitRecord {
    ulid: string;
    type: UnitType;
    unit_number: string;
    floor: number | null;
    size_sqm: string | null;
    rooms: number | null;
    status: UnitStatus;
    building: Building | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    units: {
        data: UnitRecord[];
        links: PaginationLink[];
    };
    filters: { search: string | null; status: string | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Immobilien', href: '/properties' },
    { title: 'Wohnungen', href: '/properties/units' },
];

const statusLabels: Record<UnitStatus, string> = {
    vacant: 'Leerstand',
    occupied: 'Vermietet',
    maintenance: 'In Renovierung',
};

const statusVariants: Record<UnitStatus, 'secondary' | 'success' | 'outline'> = {
    vacant: 'outline',
    occupied: 'success',
    maintenance: 'secondary',
};

const typeLabels: Record<UnitType, string> = {
    apartment: 'Wohnung',
    commercial: 'Gewerbeeinheit',
    parking_space: 'Stellplatz',
    other: 'Sonstige Einheit',
};

const statusFilterOptions = [
    { value: 'all', label: 'Alle Status' },
    { value: 'vacant', label: 'Leerstand' },
    { value: 'occupied', label: 'Vermietet' },
    { value: 'maintenance', label: 'In Renovierung' },
];

const statusFilter = ref(props.filters.status ?? 'all');

const { search, navigate } = useListSearch('units.index', props.filters.search, () => ({
    status: statusFilter.value !== 'all' ? statusFilter.value : null,
}));

watch(statusFilter, () => navigate());

const unitPendingDeletion = ref<UnitRecord | null>(null);

const confirmDelete = (unit: UnitRecord) => {
    unitPendingDeletion.value = unit;
};

const destroy = () => {
    if (!unitPendingDeletion.value) {
        return;
    }

    router.delete(route('units.destroy', [unitPendingDeletion.value.ulid]), {
        preserveScroll: true,
        onFinish: () => {
            unitPendingDeletion.value = null;
        },
    });
};
</script>

<template>
    <Head title="Wohnungen" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">Wohnungen</h1>
                <Button as-child>
                    <Link :href="route('units.create')">
                        <Plus class="size-4" />
                        Wohnung hinzufügen
                    </Link>
                </Button>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="relative max-w-sm flex-1">
                    <Search class="absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="Suche nach Nr., Gebäude…" class="pl-8" />
                </div>
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-44">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="option in statusFilterOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <EmptyState
                v-if="units.data.length === 0 && !filters.search && !filters.status"
                :icon="DoorOpen"
                title="Noch keine Wohnungen"
                description="Legen Sie eine Wohnung an und ordnen Sie sie einem Gebäude zu."
            >
                <Button as-child>
                    <Link :href="route('units.create')">Wohnung hinzufügen</Link>
                </Button>
            </EmptyState>

            <EmptyState v-else-if="units.data.length === 0" :icon="Search" title="Keine Treffer" description="Keine Wohnungen für diese Filter." />

            <template v-else>
                <div class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nr.</TableHead>
                                <TableHead>Typ</TableHead>
                                <TableHead>Gebäude</TableHead>
                                <TableHead>Etage</TableHead>
                                <TableHead>Größe</TableHead>
                                <TableHead>Zimmer</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="w-0"><span class="sr-only">Aktionen</span></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="unit in units.data" :key="unit.ulid">
                                <TableCell class="font-medium">{{ unit.unit_number }}</TableCell>
                                <TableCell>{{ typeLabels[unit.type] }}</TableCell>
                                <TableCell>{{ unit.building?.name ?? '—' }}</TableCell>
                                <TableCell>{{ unit.floor ?? '—' }}</TableCell>
                                <TableCell>{{ unit.size_sqm ? `${unit.size_sqm} m²` : '—' }}</TableCell>
                                <TableCell>{{ unit.rooms ?? '—' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="statusVariants[unit.status]">{{ statusLabels[unit.status] }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <Button as-child variant="ghost" size="sm">
                                            <Link :href="route('units.edit', [unit.ulid])">
                                                <Pencil class="size-4" />
                                                <span class="sr-only">Bearbeiten</span>
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="confirmDelete(unit)">
                                            <Trash2 class="size-4" />
                                            <span class="sr-only">Löschen</span>
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="units.links" />
            </template>
        </div>

        <Dialog :open="unitPendingDeletion !== null" @update:open="(open) => !open && (unitPendingDeletion = null)">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Wohnung löschen?</DialogTitle>
                    <DialogDescription>
                        „{{ unitPendingDeletion?.unit_number }}" wird gelöscht. Dieser Vorgang kann nicht rückgängig gemacht werden.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="secondary">Abbrechen</Button>
                    </DialogClose>
                    <Button variant="destructive" @click="destroy">Löschen</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

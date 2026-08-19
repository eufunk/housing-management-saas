<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { EmptyState } from '@/components/ui/empty-state';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { DoorOpen, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Building {
    id: number;
    name: string;
}

type UnitStatus = 'vacant' | 'occupied' | 'maintenance';

interface UnitRecord {
    ulid: string;
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

defineProps<{
    units: {
        data: UnitRecord[];
        links: PaginationLink[];
    };
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

            <EmptyState
                v-if="units.data.length === 0"
                :icon="DoorOpen"
                title="Noch keine Wohnungen"
                description="Legen Sie eine Wohnung an und ordnen Sie sie einem Gebäude zu."
            >
                <Button as-child>
                    <Link :href="route('units.create')">Wohnung hinzufügen</Link>
                </Button>
            </EmptyState>

            <template v-else>
                <div class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nr.</TableHead>
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

<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { EmptyState } from '@/components/ui/empty-state';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Building, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Property {
    id: number;
    name: string;
}

interface BuildingRecord {
    ulid: string;
    name: string;
    floors: number | null;
    property: Property | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    buildings: {
        data: BuildingRecord[];
        links: PaginationLink[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Immobilien', href: '/properties' },
    { title: 'Gebäude', href: '/properties/buildings' },
];

const buildingPendingDeletion = ref<BuildingRecord | null>(null);

const confirmDelete = (building: BuildingRecord) => {
    buildingPendingDeletion.value = building;
};

const destroy = () => {
    if (!buildingPendingDeletion.value) {
        return;
    }

    router.delete(route('buildings.destroy', [buildingPendingDeletion.value.ulid]), {
        preserveScroll: true,
        onFinish: () => {
            buildingPendingDeletion.value = null;
        },
    });
};
</script>

<template>
    <Head title="Gebäude" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">Gebäude</h1>
                <Button as-child>
                    <Link :href="route('buildings.create')">
                        <Plus class="size-4" />
                        Gebäude hinzufügen
                    </Link>
                </Button>
            </div>

            <EmptyState
                v-if="buildings.data.length === 0"
                :icon="Building"
                title="Noch keine Gebäude"
                description="Legen Sie ein Gebäude an und ordnen Sie es einer Immobilie zu."
            >
                <Button as-child>
                    <Link :href="route('buildings.create')">Gebäude hinzufügen</Link>
                </Button>
            </EmptyState>

            <template v-else>
                <div class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Immobilie</TableHead>
                                <TableHead>Stockwerke</TableHead>
                                <TableHead class="w-0"><span class="sr-only">Aktionen</span></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="building in buildings.data" :key="building.ulid">
                                <TableCell class="font-medium">{{ building.name }}</TableCell>
                                <TableCell>{{ building.property?.name ?? '—' }}</TableCell>
                                <TableCell>{{ building.floors ?? '—' }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <Button as-child variant="ghost" size="sm">
                                            <Link :href="route('buildings.edit', [building.ulid])">
                                                <Pencil class="size-4" />
                                                <span class="sr-only">Bearbeiten</span>
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="confirmDelete(building)">
                                            <Trash2 class="size-4" />
                                            <span class="sr-only">Löschen</span>
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="buildings.links" />
            </template>
        </div>

        <Dialog :open="buildingPendingDeletion !== null" @update:open="(open) => !open && (buildingPendingDeletion = null)">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Gebäude löschen?</DialogTitle>
                    <DialogDescription>
                        „{{ buildingPendingDeletion?.name }}" wird gelöscht. Dieser Vorgang kann nicht rückgängig gemacht werden.
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

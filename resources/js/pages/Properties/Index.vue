<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { EmptyState } from '@/components/ui/empty-state';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Owner {
    id: number;
    name: string;
}

interface Property {
    ulid: string;
    name: string;
    street: string;
    postal_code: string;
    city: string;
    country: string;
    owner: Owner | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    properties: {
        data: Property[];
        links: PaginationLink[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Immobilien', href: '/properties' }];

const propertyPendingDeletion = ref<Property | null>(null);

const confirmDelete = (property: Property) => {
    propertyPendingDeletion.value = property;
};

const destroy = () => {
    if (!propertyPendingDeletion.value) {
        return;
    }

    router.delete(route('properties.destroy', [propertyPendingDeletion.value.ulid]), {
        preserveScroll: true,
        onFinish: () => {
            propertyPendingDeletion.value = null;
        },
    });
};
</script>

<template>
    <Head title="Immobilien" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">Immobilien</h1>
                <Button as-child>
                    <Link :href="route('properties.create')">
                        <Plus class="size-4" />
                        Immobilie hinzufügen
                    </Link>
                </Button>
            </div>

            <EmptyState
                v-if="properties.data.length === 0"
                :icon="Building2"
                title="Noch keine Immobilien"
                description="Legen Sie Ihre erste Immobilie an, um mit der Verwaltung zu beginnen."
            >
                <Button as-child>
                    <Link :href="route('properties.create')">Immobilie hinzufügen</Link>
                </Button>
            </EmptyState>

            <template v-else>
                <div class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Adresse</TableHead>
                                <TableHead>Eigentümer</TableHead>
                                <TableHead class="w-0"><span class="sr-only">Aktionen</span></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="property in properties.data" :key="property.ulid">
                                <TableCell class="font-medium">{{ property.name }}</TableCell>
                                <TableCell>{{ property.street }}, {{ property.postal_code }} {{ property.city }}</TableCell>
                                <TableCell>{{ property.owner?.name ?? '—' }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <Button as-child variant="ghost" size="sm">
                                            <Link :href="route('properties.edit', [property.ulid])">
                                                <Pencil class="size-4" />
                                                <span class="sr-only">Bearbeiten</span>
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="confirmDelete(property)">
                                            <Trash2 class="size-4" />
                                            <span class="sr-only">Löschen</span>
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="properties.links" />
            </template>
        </div>

        <Dialog :open="propertyPendingDeletion !== null" @update:open="(open) => !open && (propertyPendingDeletion = null)">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Immobilie löschen?</DialogTitle>
                    <DialogDescription>
                        „{{ propertyPendingDeletion?.name }}" wird gelöscht. Dieser Vorgang kann nicht rückgängig gemacht werden.
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

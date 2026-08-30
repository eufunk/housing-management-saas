<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { EmptyState } from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useListSearch } from '@/composables/useListSearch';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Trash2, UserCheck } from 'lucide-vue-next';
import { ref } from 'vue';

interface OwnerRecord {
    ulid: string;
    name: string;
    email: string | null;
    phone: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    owners: {
        data: OwnerRecord[];
        links: PaginationLink[];
    };
    filters: { search: string | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Eigentümer', href: '/owners' }];

const { search } = useListSearch('owners.index', props.filters.search);

const ownerPendingDeletion = ref<OwnerRecord | null>(null);

const confirmDelete = (owner: OwnerRecord) => {
    ownerPendingDeletion.value = owner;
};

const destroy = () => {
    if (!ownerPendingDeletion.value) {
        return;
    }

    router.delete(route('owners.destroy', [ownerPendingDeletion.value.ulid]), {
        preserveScroll: true,
        onFinish: () => {
            ownerPendingDeletion.value = null;
        },
    });
};
</script>

<template>
    <Head title="Eigentümer" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">Eigentümer</h1>
                <Button as-child>
                    <Link :href="route('owners.create')">
                        <Plus class="size-4" />
                        Eigentümer hinzufügen
                    </Link>
                </Button>
            </div>

            <div class="relative max-w-sm">
                <Search class="absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="search" placeholder="Suche nach Name, E-Mail…" class="pl-8" />
            </div>

            <EmptyState
                v-if="owners.data.length === 0 && !filters.search"
                :icon="UserCheck"
                title="Noch keine Eigentümer"
                description="Legen Sie einen Eigentümer an, um ihn später Immobilien zuzuordnen."
            >
                <Button as-child>
                    <Link :href="route('owners.create')">Eigentümer hinzufügen</Link>
                </Button>
            </EmptyState>

            <EmptyState
                v-else-if="owners.data.length === 0"
                :icon="Search"
                title="Keine Treffer"
                :description="`Keine Eigentümer gefunden für „${filters.search}“.`"
            />

            <template v-else>
                <div class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>E-Mail</TableHead>
                                <TableHead>Telefon</TableHead>
                                <TableHead class="w-0"><span class="sr-only">Aktionen</span></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="owner in owners.data" :key="owner.ulid">
                                <TableCell class="font-medium">
                                    <Link :href="route('owners.show', [owner.ulid])" class="hover:underline">{{ owner.name }}</Link>
                                </TableCell>
                                <TableCell>{{ owner.email ?? '—' }}</TableCell>
                                <TableCell>{{ owner.phone ?? '—' }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <Button as-child variant="ghost" size="sm">
                                            <Link :href="route('owners.edit', [owner.ulid])">
                                                <Pencil class="size-4" />
                                                <span class="sr-only">Bearbeiten</span>
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="confirmDelete(owner)">
                                            <Trash2 class="size-4" />
                                            <span class="sr-only">Löschen</span>
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="owners.links" />
            </template>
        </div>

        <Dialog :open="ownerPendingDeletion !== null" @update:open="(open) => !open && (ownerPendingDeletion = null)">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Eigentümer löschen?</DialogTitle>
                    <DialogDescription>
                        „{{ ownerPendingDeletion?.name }}" wird gelöscht. Dieser Vorgang kann nicht rückgängig gemacht werden.
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

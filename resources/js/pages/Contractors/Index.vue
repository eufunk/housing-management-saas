<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { EmptyState } from '@/components/ui/empty-state';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, Wrench } from 'lucide-vue-next';
import { ref } from 'vue';

interface ContractorRecord {
    ulid: string;
    company_name: string;
    contact_name: string | null;
    email: string | null;
    phone: string | null;
    specialty: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    contractors: {
        data: ContractorRecord[];
        links: PaginationLink[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reparaturen', href: '/maintenance' },
    { title: 'Handwerker', href: '/contractors' },
];

const contractorPendingDeletion = ref<ContractorRecord | null>(null);

const confirmDelete = (contractor: ContractorRecord) => {
    contractorPendingDeletion.value = contractor;
};

const destroy = () => {
    if (!contractorPendingDeletion.value) {
        return;
    }

    router.delete(route('contractors.destroy', [contractorPendingDeletion.value.ulid]), {
        preserveScroll: true,
        onFinish: () => {
            contractorPendingDeletion.value = null;
        },
    });
};
</script>

<template>
    <Head title="Handwerker" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">Handwerker</h1>
                <Button as-child>
                    <Link :href="route('contractors.create')">
                        <Plus class="size-4" />
                        Handwerker hinzufügen
                    </Link>
                </Button>
            </div>

            <EmptyState
                v-if="contractors.data.length === 0"
                :icon="Wrench"
                title="Noch keine Handwerker"
                description="Legen Sie einen Handwerksbetrieb an, um ihn später Reparaturaufträgen zuzuordnen."
            >
                <Button as-child>
                    <Link :href="route('contractors.create')">Handwerker hinzufügen</Link>
                </Button>
            </EmptyState>

            <template v-else>
                <div class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Firma</TableHead>
                                <TableHead>Ansprechpartner</TableHead>
                                <TableHead>Kontakt</TableHead>
                                <TableHead>Fachgebiet</TableHead>
                                <TableHead class="w-0"><span class="sr-only">Aktionen</span></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="contractor in contractors.data" :key="contractor.ulid">
                                <TableCell class="font-medium">{{ contractor.company_name }}</TableCell>
                                <TableCell>{{ contractor.contact_name ?? '—' }}</TableCell>
                                <TableCell>{{ contractor.email ?? contractor.phone ?? '—' }}</TableCell>
                                <TableCell>{{ contractor.specialty ?? '—' }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <Button as-child variant="ghost" size="sm">
                                            <Link :href="route('contractors.edit', [contractor.ulid])">
                                                <Pencil class="size-4" />
                                                <span class="sr-only">Bearbeiten</span>
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="confirmDelete(contractor)">
                                            <Trash2 class="size-4" />
                                            <span class="sr-only">Löschen</span>
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="contractors.links" />
            </template>
        </div>

        <Dialog :open="contractorPendingDeletion !== null" @update:open="(open) => !open && (contractorPendingDeletion = null)">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Handwerker löschen?</DialogTitle>
                    <DialogDescription>
                        „{{ contractorPendingDeletion?.company_name }}" wird gelöscht. Dieser Vorgang kann nicht rückgängig gemacht werden.
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

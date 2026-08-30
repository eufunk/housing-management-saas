<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { EmptyState } from '@/components/ui/empty-state';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface TenantRecord {
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

defineProps<{
    tenants: {
        data: TenantRecord[];
        links: PaginationLink[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Mieter', href: '/tenants' }];

const tenantPendingDeletion = ref<TenantRecord | null>(null);

const confirmDelete = (tenant: TenantRecord) => {
    tenantPendingDeletion.value = tenant;
};

const destroy = () => {
    if (!tenantPendingDeletion.value) {
        return;
    }

    router.delete(route('tenants.destroy', [tenantPendingDeletion.value.ulid]), {
        preserveScroll: true,
        onFinish: () => {
            tenantPendingDeletion.value = null;
        },
    });
};
</script>

<template>
    <Head title="Mieter" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">Mieter</h1>
                <Button as-child>
                    <Link :href="route('tenants.create')">
                        <Plus class="size-4" />
                        Mieter hinzufügen
                    </Link>
                </Button>
            </div>

            <EmptyState
                v-if="tenants.data.length === 0"
                :icon="Users"
                title="Noch keine Mieter"
                description="Legen Sie einen Mieter an, um ihn später einem Mietvertrag zuzuordnen."
            >
                <Button as-child>
                    <Link :href="route('tenants.create')">Mieter hinzufügen</Link>
                </Button>
            </EmptyState>

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
                            <TableRow v-for="tenant in tenants.data" :key="tenant.ulid">
                                <TableCell class="font-medium">{{ tenant.name }}</TableCell>
                                <TableCell>{{ tenant.email ?? '—' }}</TableCell>
                                <TableCell>{{ tenant.phone ?? '—' }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <Button as-child variant="ghost" size="sm">
                                            <Link :href="route('tenants.edit', [tenant.ulid])">
                                                <Pencil class="size-4" />
                                                <span class="sr-only">Bearbeiten</span>
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="confirmDelete(tenant)">
                                            <Trash2 class="size-4" />
                                            <span class="sr-only">Löschen</span>
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="tenants.links" />
            </template>
        </div>

        <Dialog :open="tenantPendingDeletion !== null" @update:open="(open) => !open && (tenantPendingDeletion = null)">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Mieter löschen?</DialogTitle>
                    <DialogDescription>
                        „{{ tenantPendingDeletion?.name }}" wird gelöscht. Dieser Vorgang kann nicht rückgängig gemacht werden.
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

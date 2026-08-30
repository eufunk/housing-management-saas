<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Building2, FolderOpen, Pencil, Wallet } from 'lucide-vue-next';

interface Property {
    ulid: string;
    name: string;
    street: string;
    postal_code: string;
    city: string;
}

interface OwnerRecord {
    ulid: string;
    name: string;
    email: string | null;
    phone: string | null;
    properties: Property[];
}

const props = defineProps<{ owner: OwnerRecord }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Eigentümer', href: '/owners' },
    { title: props.owner.name, href: `/owners/${props.owner.ulid}` },
];
</script>

<template>
    <Head :title="owner.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">{{ owner.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ owner.email ?? 'Keine E-Mail hinterlegt' }}</p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('owners.edit', [owner.ulid])">
                        <Pencil class="size-4" />
                        Bearbeiten
                    </Link>
                </Button>
            </div>

            <Tabs default-value="overview">
                <TabsList>
                    <TabsTrigger value="overview">Übersicht</TabsTrigger>
                    <TabsTrigger value="properties">Immobilien ({{ owner.properties.length }})</TabsTrigger>
                    <TabsTrigger value="finances">Finanzinformationen</TabsTrigger>
                    <TabsTrigger value="documents">Dokumente</TabsTrigger>
                </TabsList>

                <TabsContent value="overview">
                    <div class="grid max-w-md gap-4 text-sm">
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Name</span>
                            <span>{{ owner.name }}</span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">E-Mail</span>
                            <span>{{ owner.email ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Telefon</span>
                            <span>{{ owner.phone ?? '—' }}</span>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="properties">
                    <EmptyState
                        v-if="owner.properties.length === 0"
                        :icon="Building2"
                        title="Keine Immobilien zugeordnet"
                        description="Ordnen Sie diesem Eigentümer beim Anlegen oder Bearbeiten einer Immobilie zu."
                    />
                    <div v-else class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Adresse</TableHead>
                                    <TableHead class="w-0"><span class="sr-only">Aktionen</span></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="property in owner.properties" :key="property.ulid">
                                    <TableCell class="font-medium">{{ property.name }}</TableCell>
                                    <TableCell>{{ property.street }}, {{ property.postal_code }} {{ property.city }}</TableCell>
                                    <TableCell>
                                        <Button as-child variant="ghost" size="sm">
                                            <Link :href="route('properties.show', [property.ulid])">
                                                <Pencil class="size-4" />
                                                <span class="sr-only">Ansehen</span>
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="finances">
                    <EmptyState
                        :icon="Wallet"
                        title="Finanzinformationen"
                        description="Dieses Modul ist noch nicht implementiert (Roadmap-Phase 4)."
                    />
                </TabsContent>
                <TabsContent value="documents">
                    <EmptyState :icon="FolderOpen" title="Dokumente" description="Dieses Modul ist noch nicht implementiert." />
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>

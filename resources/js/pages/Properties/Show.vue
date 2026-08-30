<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Activity, Building, FileText, FolderOpen, Pencil, UserCheck, Wallet, Wrench } from 'lucide-vue-next';

interface Owner {
    ulid: string;
    name: string;
    email: string | null;
    phone: string | null;
}

interface Unit {
    ulid: string;
    unit_number: string;
    type: string;
    floor: number | null;
    status: string;
}

interface BuildingRecord {
    ulid: string;
    name: string;
    floors: number | null;
    units_count: number;
    units: Unit[];
}

interface PropertyRecord {
    ulid: string;
    name: string;
    street: string;
    postal_code: string;
    city: string;
    country: string;
    owner: Owner | null;
    buildings: BuildingRecord[];
}

const props = defineProps<{ property: PropertyRecord }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Immobilien', href: '/properties' },
    { title: props.property.name, href: `/properties/${props.property.ulid}` },
];

const statusLabels: Record<string, string> = {
    vacant: 'Leerstand',
    occupied: 'Vermietet',
    maintenance: 'In Renovierung',
};

const typeLabels: Record<string, string> = {
    apartment: 'Wohnung',
    commercial: 'Gewerbeeinheit',
    parking_space: 'Stellplatz',
    other: 'Sonstige Einheit',
};

const allUnits = props.property.buildings.flatMap((building) => building.units.map((unit) => ({ ...unit, buildingName: building.name })));
const totalUnits = allUnits.length;
</script>

<template>
    <Head :title="property.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">{{ property.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ property.street }}, {{ property.postal_code }} {{ property.city }}</p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('properties.edit', [property.ulid])">
                        <Pencil class="size-4" />
                        Bearbeiten
                    </Link>
                </Button>
            </div>

            <Tabs default-value="overview">
                <TabsList>
                    <TabsTrigger value="overview">Übersicht</TabsTrigger>
                    <TabsTrigger value="buildings">Gebäude ({{ property.buildings.length }})</TabsTrigger>
                    <TabsTrigger value="units">Einheiten ({{ totalUnits }})</TabsTrigger>
                    <TabsTrigger value="owner">Eigentümer</TabsTrigger>
                    <TabsTrigger value="leases">Verträge</TabsTrigger>
                    <TabsTrigger value="finances">Finanzen</TabsTrigger>
                    <TabsTrigger value="maintenance">Reparaturen</TabsTrigger>
                    <TabsTrigger value="documents">Dokumente</TabsTrigger>
                    <TabsTrigger value="activity">Aktivitäten</TabsTrigger>
                </TabsList>

                <TabsContent value="overview">
                    <div class="grid max-w-md gap-4 text-sm">
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Adresse</span>
                            <span>{{ property.street }}, {{ property.postal_code }} {{ property.city }}</span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Eigentümer</span>
                            <span>{{ property.owner?.name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Gebäude</span>
                            <span>{{ property.buildings.length }}</span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Einheiten</span>
                            <span>{{ totalUnits }}</span>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="buildings">
                    <EmptyState
                        v-if="property.buildings.length === 0"
                        :icon="Building"
                        title="Noch keine Gebäude"
                        description="Legen Sie ein Gebäude an und ordnen Sie es dieser Immobilie zu."
                    >
                        <Button as-child>
                            <Link :href="route('buildings.create')">Gebäude hinzufügen</Link>
                        </Button>
                    </EmptyState>
                    <div v-else class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Stockwerke</TableHead>
                                    <TableHead>Einheiten</TableHead>
                                    <TableHead class="w-0"><span class="sr-only">Aktionen</span></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="building in property.buildings" :key="building.ulid">
                                    <TableCell class="font-medium">{{ building.name }}</TableCell>
                                    <TableCell>{{ building.floors ?? '—' }}</TableCell>
                                    <TableCell>{{ building.units_count }}</TableCell>
                                    <TableCell>
                                        <Button as-child variant="ghost" size="sm">
                                            <Link :href="route('buildings.edit', [building.ulid])">
                                                <Pencil class="size-4" />
                                                <span class="sr-only">Bearbeiten</span>
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="units">
                    <EmptyState
                        v-if="totalUnits === 0"
                        :icon="Building"
                        title="Noch keine Einheiten"
                        description="Legen Sie zunächst ein Gebäude an, dann Einheiten darin."
                    />
                    <div v-else class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nr.</TableHead>
                                    <TableHead>Typ</TableHead>
                                    <TableHead>Gebäude</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-0"><span class="sr-only">Aktionen</span></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="unit in allUnits" :key="unit.ulid">
                                    <TableCell class="font-medium">{{ unit.unit_number }}</TableCell>
                                    <TableCell>{{ typeLabels[unit.type] ?? unit.type }}</TableCell>
                                    <TableCell>{{ unit.buildingName }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{{ statusLabels[unit.status] ?? unit.status }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Button as-child variant="ghost" size="sm">
                                            <Link :href="route('units.edit', [unit.ulid])">
                                                <Pencil class="size-4" />
                                                <span class="sr-only">Bearbeiten</span>
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="owner">
                    <EmptyState
                        v-if="!property.owner"
                        :icon="UserCheck"
                        title="Kein Eigentümer zugeordnet"
                        description="Ordnen Sie beim Bearbeiten dieser Immobilie einen Eigentümer zu."
                    >
                        <Button as-child variant="outline">
                            <Link :href="route('properties.edit', [property.ulid])">Immobilie bearbeiten</Link>
                        </Button>
                    </EmptyState>
                    <div v-else class="grid max-w-md gap-4 text-sm">
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Name</span>
                            <span>{{ property.owner.name }}</span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">E-Mail</span>
                            <span>{{ property.owner.email ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Telefon</span>
                            <span>{{ property.owner.phone ?? '—' }}</span>
                        </div>
                        <Button as-child variant="outline" class="w-fit">
                            <Link :href="route('owners.show', [property.owner.ulid])">Eigentümer-Profil öffnen</Link>
                        </Button>
                    </div>
                </TabsContent>

                <TabsContent value="leases">
                    <EmptyState :icon="FileText" title="Mietverträge" description="Dieses Modul ist noch nicht implementiert (Roadmap-Phase 2)." />
                </TabsContent>
                <TabsContent value="finances">
                    <EmptyState :icon="Wallet" title="Finanzen" description="Dieses Modul ist noch nicht implementiert (Roadmap-Phase 4)." />
                </TabsContent>
                <TabsContent value="maintenance">
                    <EmptyState :icon="Wrench" title="Reparaturen" description="Dieses Modul ist noch nicht implementiert (Roadmap-Phase 3)." />
                </TabsContent>
                <TabsContent value="documents">
                    <EmptyState :icon="FolderOpen" title="Dokumente" description="Dieses Modul ist noch nicht implementiert." />
                </TabsContent>
                <TabsContent value="activity">
                    <EmptyState :icon="Activity" title="Aktivitäten" description="Noch keine Aktivitätsprotokollierung verfügbar." />
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>

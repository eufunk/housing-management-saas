<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { FileText, FolderOpen, Pencil, Wallet, Wrench } from 'lucide-vue-next';

interface TenantRecord {
    ulid: string;
    name: string;
    email: string | null;
    phone: string | null;
}

const props = defineProps<{ tenant: TenantRecord }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Mieter', href: '/tenants' },
    { title: props.tenant.name, href: `/tenants/${props.tenant.ulid}` },
];
</script>

<template>
    <Head :title="tenant.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">{{ tenant.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ tenant.email ?? 'Keine E-Mail hinterlegt' }}</p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('tenants.edit', [tenant.ulid])">
                        <Pencil class="size-4" />
                        Bearbeiten
                    </Link>
                </Button>
            </div>

            <Tabs default-value="overview">
                <TabsList>
                    <TabsTrigger value="overview">Übersicht</TabsTrigger>
                    <TabsTrigger value="lease">Mietvertrag</TabsTrigger>
                    <TabsTrigger value="payments">Zahlungen</TabsTrigger>
                    <TabsTrigger value="maintenance">Schadensmeldungen</TabsTrigger>
                    <TabsTrigger value="documents">Dokumente</TabsTrigger>
                </TabsList>

                <TabsContent value="overview">
                    <div class="grid max-w-md gap-4 text-sm">
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Name</span>
                            <span>{{ tenant.name }}</span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">E-Mail</span>
                            <span>{{ tenant.email ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="text-muted-foreground">Telefon</span>
                            <span>{{ tenant.phone ?? '—' }}</span>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="lease">
                    <EmptyState :icon="FileText" title="Mietvertrag" description="Dieses Modul ist noch nicht implementiert (Roadmap-Phase 2)." />
                </TabsContent>
                <TabsContent value="payments">
                    <EmptyState :icon="Wallet" title="Zahlungen" description="Dieses Modul ist noch nicht implementiert (Roadmap-Phase 4)." />
                </TabsContent>
                <TabsContent value="maintenance">
                    <EmptyState :icon="Wrench" title="Schadensmeldungen" description="Dieses Modul ist noch nicht implementiert (Roadmap-Phase 3)." />
                </TabsContent>
                <TabsContent value="documents">
                    <EmptyState :icon="FolderOpen" title="Dokumente" description="Dieses Modul ist noch nicht implementiert." />
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>

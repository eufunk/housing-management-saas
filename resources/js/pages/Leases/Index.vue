<script setup lang="ts">
import { EmptyState } from '@/components/ui/empty-state';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Verträge', href: '/leases' }];
</script>

<template>
    <Head title="Verträge" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <EmptyState
                :icon="FileText"
                title="Noch keine Mietverträge"
                description="Dieses Modul wird in einer der nächsten Iterationen mit echten Daten befüllt."
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { EmptyState } from '@/components/ui/empty-state';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { FolderOpen } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dokumente', href: '/documents' }];
</script>

<template>
    <Head title="Dokumente" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <EmptyState
                :icon="FolderOpen"
                title="Noch keine Dokumente"
                description="Dieses Modul wird in einer der nächsten Iterationen mit echten Daten befüllt."
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { EmptyState } from '@/components/ui/empty-state';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Benachrichtigungen', href: '/notifications' }];
</script>

<template>
    <Head title="Benachrichtigungen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <EmptyState
                :icon="Bell"
                title="Keine neuen Benachrichtigungen"
                description="Hier erscheinen künftig System- und Aktivitätsbenachrichtigungen."
            />
        </div>
    </AppLayout>
</template>

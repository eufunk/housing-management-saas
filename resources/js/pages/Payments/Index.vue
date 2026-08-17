<script setup lang="ts">
import { EmptyState } from '@/components/ui/empty-state';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Wallet } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Finanzen', href: '/payments' }];
</script>

<template>
    <Head title="Finanzen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <EmptyState
                :icon="Wallet"
                title="Noch keine Finanzdaten"
                description="Zahlungen, Rechnungen und Ausgaben werden in einer der nächsten Iterationen mit echten Daten befüllt."
            />
        </div>
    </AppLayout>
</template>

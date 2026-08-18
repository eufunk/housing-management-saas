<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{ links: PaginationLink[] }>();
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center gap-1" aria-label="Seitennavigation">
        <template v-for="(link, index) in links" :key="index">
            <Button v-if="!link.url" variant="outline" size="sm" disabled class="text-muted-foreground">
                <span v-html="link.label" />
            </Button>
            <Button v-else as-child :variant="link.active ? 'default' : 'outline'" size="sm">
                <Link :href="link.url" preserve-scroll>
                    <span v-html="link.label" />
                </Link>
            </Button>
        </template>
    </nav>
</template>

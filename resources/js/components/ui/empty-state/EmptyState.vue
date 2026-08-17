<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { LucideIcon } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';

interface Props {
    icon?: LucideIcon;
    title: string;
    description?: string;
    class?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <div :class="cn('flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center', $props.class)">
        <component :is="icon" v-if="icon" class="mb-4 size-10 text-muted-foreground" />
        <h3 class="text-lg font-semibold">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-muted-foreground">{{ description }}</p>
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>

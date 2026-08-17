<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';
import { type AlertVariants, alertVariants } from '.';

interface Props {
    variant?: AlertVariants['variant'];
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <div role="alert" :class="cn(alertVariants({ variant }), props.class)">
        <slot />
    </div>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface TenantRecord {
    ulid: string;
    name: string;
    email: string | null;
    phone: string | null;
}

const props = defineProps<{ tenant: TenantRecord }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Mieter', href: '/tenants' },
    { title: props.tenant.name, href: `/tenants/${props.tenant.ulid}/edit` },
];

const form = useForm({
    name: props.tenant.name,
    email: props.tenant.email ?? '',
    phone: props.tenant.phone ?? '',
});

const submit = () => {
    form.put(route('tenants.update', [props.tenant.ulid]));
};
</script>

<template>
    <Head :title="`Mieter bearbeiten – ${tenant.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-lg font-semibold">Mieter bearbeiten</h1>

            <form class="max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" required autofocus />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">E-Mail</Label>
                    <Input id="email" v-model="form.email" type="email" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">Telefon</Label>
                    <Input id="phone" v-model="form.phone" />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">Speichern</Button>
                    <Button as-child variant="secondary">
                        <Link :href="route('tenants.index')">Abbrechen</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface ContractorRecord {
    ulid: string;
    company_name: string;
    contact_name: string | null;
    email: string | null;
    phone: string | null;
    specialty: string | null;
}

const props = defineProps<{ contractor: ContractorRecord }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reparaturen', href: '/maintenance' },
    { title: 'Handwerker', href: '/contractors' },
    { title: props.contractor.company_name, href: `/contractors/${props.contractor.ulid}/edit` },
];

const form = useForm({
    company_name: props.contractor.company_name,
    contact_name: props.contractor.contact_name ?? '',
    email: props.contractor.email ?? '',
    phone: props.contractor.phone ?? '',
    specialty: props.contractor.specialty ?? '',
});

const submit = () => {
    form.put(route('contractors.update', [props.contractor.ulid]));
};
</script>

<template>
    <Head :title="`Handwerker bearbeiten – ${contractor.company_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-lg font-semibold">Handwerker bearbeiten</h1>

            <form class="max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="company_name">Firma</Label>
                    <Input id="company_name" v-model="form.company_name" required autofocus />
                    <InputError :message="form.errors.company_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="contact_name">Ansprechpartner</Label>
                    <Input id="contact_name" v-model="form.contact_name" />
                    <InputError :message="form.errors.contact_name" />
                </div>

                <div class="grid grid-cols-2 gap-4">
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
                </div>

                <div class="grid gap-2">
                    <Label for="specialty">Fachgebiet</Label>
                    <Input id="specialty" v-model="form.specialty" placeholder="z. B. Sanitär, Elektrik, Heizung" />
                    <InputError :message="form.errors.specialty" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">Speichern</Button>
                    <Button as-child variant="secondary">
                        <Link :href="route('contractors.index')">Abbrechen</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

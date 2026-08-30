<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Eigentümer', href: '/owners' },
    { title: 'Neu', href: '/owners/create' },
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
});

const submit = () => {
    form.post(route('owners.store'));
};
</script>

<template>
    <Head title="Eigentümer hinzufügen" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-lg font-semibold">Eigentümer hinzufügen</h1>

            <form class="max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" required autofocus placeholder="Vor- und Nachname oder Firma" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">E-Mail</Label>
                    <Input id="email" v-model="form.email" type="email" placeholder="name@beispiel.de" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">Telefon</Label>
                    <Input id="phone" v-model="form.phone" placeholder="+49 30 1234567" />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">Speichern</Button>
                    <Button as-child variant="secondary">
                        <Link :href="route('owners.index')">Abbrechen</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Owner {
    id: number;
    name: string;
}

interface Property {
    ulid: string;
    owner_id: number | null;
    name: string;
    street: string;
    postal_code: string;
    city: string;
    country: string;
}

const props = defineProps<{ property: Property; owners: Owner[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Immobilien', href: '/properties' },
    { title: props.property.name, href: `/properties/${props.property.ulid}/edit` },
];

const form = useForm({
    owner_id: props.property.owner_id ? String(props.property.owner_id) : '',
    name: props.property.name,
    street: props.property.street,
    postal_code: props.property.postal_code,
    city: props.property.city,
    country: props.property.country,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        owner_id: data.owner_id ? Number(data.owner_id) : null,
    })).put(route('properties.update', [props.property.ulid]));
};
</script>

<template>
    <Head :title="`Immobilie bearbeiten – ${property.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-lg font-semibold">Immobilie bearbeiten</h1>

            <form class="max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" required autofocus />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="street">Straße und Hausnummer</Label>
                    <Input id="street" v-model="form.street" required />
                    <InputError :message="form.errors.street" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="postal_code">PLZ</Label>
                        <Input id="postal_code" v-model="form.postal_code" required />
                        <InputError :message="form.errors.postal_code" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="city">Stadt</Label>
                        <Input id="city" v-model="form.city" required />
                        <InputError :message="form.errors.city" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="owner">Eigentümer</Label>
                    <Select v-model="form.owner_id">
                        <SelectTrigger id="owner">
                            <SelectValue placeholder="Kein Eigentümer zugeordnet" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="owner in props.owners" :key="owner.id" :value="String(owner.id)">
                                {{ owner.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.owner_id" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">Speichern</Button>
                    <Button as-child variant="secondary">
                        <Link :href="route('properties.index')">Abbrechen</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

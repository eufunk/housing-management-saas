<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Property {
    id: number;
    name: string;
}

interface BuildingRecord {
    ulid: string;
    property_id: number | null;
    name: string;
    floors: number | null;
}

const props = defineProps<{ building: BuildingRecord; properties: Property[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Immobilien', href: '/properties' },
    { title: 'Gebäude', href: '/properties/buildings' },
    { title: props.building.name, href: `/properties/buildings/${props.building.ulid}/edit` },
];

const form = useForm({
    property_id: props.building.property_id ? String(props.building.property_id) : '',
    name: props.building.name,
    floors: props.building.floors ? String(props.building.floors) : '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        property_id: data.property_id ? Number(data.property_id) : null,
        floors: data.floors ? Number(data.floors) : null,
    })).put(route('buildings.update', [props.building.ulid]));
};
</script>

<template>
    <Head :title="`Gebäude bearbeiten – ${building.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-lg font-semibold">Gebäude bearbeiten</h1>

            <form class="max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="property">Immobilie</Label>
                    <Select v-model="form.property_id">
                        <SelectTrigger id="property">
                            <SelectValue placeholder="Immobilie auswählen" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="property in props.properties" :key="property.id" :value="String(property.id)">
                                {{ property.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.property_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" required autofocus />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="floors">Stockwerke</Label>
                    <Input id="floors" v-model="form.floors" type="number" min="1" />
                    <InputError :message="form.errors.floors" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">Speichern</Button>
                    <Button as-child variant="secondary">
                        <Link :href="route('buildings.index')">Abbrechen</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

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

const props = defineProps<{ properties: Property[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Immobilien', href: '/properties' },
    { title: 'Gebäude', href: '/properties/buildings' },
    { title: 'Neu', href: '/properties/buildings/create' },
];

const form = useForm({
    property_id: '' as string,
    name: '',
    floors: '' as string,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        property_id: data.property_id ? Number(data.property_id) : null,
        floors: data.floors ? Number(data.floors) : null,
    })).post(route('buildings.store'));
};
</script>

<template>
    <Head title="Gebäude hinzufügen" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-lg font-semibold">Gebäude hinzufügen</h1>

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
                    <Input id="name" v-model="form.name" required autofocus placeholder="z. B. Haus A" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="floors">Stockwerke</Label>
                    <Input id="floors" v-model="form.floors" type="number" min="1" placeholder="z. B. 4" />
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

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Building {
    id: number;
    name: string;
}

interface UnitRecord {
    ulid: string;
    building_id: number | null;
    unit_number: string;
    floor: number | null;
    size_sqm: string | null;
    rooms: number | null;
    status: string;
}

const props = defineProps<{ unit: UnitRecord; buildings: Building[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Immobilien', href: '/properties' },
    { title: 'Wohnungen', href: '/properties/units' },
    { title: props.unit.unit_number, href: `/properties/units/${props.unit.ulid}/edit` },
];

const statusOptions = [
    { value: 'vacant', label: 'Leerstand' },
    { value: 'occupied', label: 'Vermietet' },
    { value: 'maintenance', label: 'In Renovierung' },
];

const form = useForm({
    building_id: props.unit.building_id ? String(props.unit.building_id) : '',
    unit_number: props.unit.unit_number,
    floor: props.unit.floor !== null ? String(props.unit.floor) : '',
    size_sqm: props.unit.size_sqm ?? '',
    rooms: props.unit.rooms !== null ? String(props.unit.rooms) : '',
    status: props.unit.status,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        building_id: data.building_id ? Number(data.building_id) : null,
        floor: data.floor ? Number(data.floor) : null,
        size_sqm: data.size_sqm ? Number(data.size_sqm) : null,
        rooms: data.rooms ? Number(data.rooms) : null,
    })).put(route('units.update', [props.unit.ulid]));
};
</script>

<template>
    <Head :title="`Wohnung bearbeiten – ${unit.unit_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-lg font-semibold">Wohnung bearbeiten</h1>

            <form class="max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="building">Gebäude</Label>
                    <Select v-model="form.building_id">
                        <SelectTrigger id="building">
                            <SelectValue placeholder="Gebäude auswählen" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="building in props.buildings" :key="building.id" :value="String(building.id)">
                                {{ building.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.building_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="unit_number">Wohnungsnummer</Label>
                    <Input id="unit_number" v-model="form.unit_number" required autofocus />
                    <InputError :message="form.errors.unit_number" />
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="grid gap-2">
                        <Label for="floor">Etage</Label>
                        <Input id="floor" v-model="form.floor" type="number" min="0" />
                        <InputError :message="form.errors.floor" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="size_sqm">Größe (m²)</Label>
                        <Input id="size_sqm" v-model="form.size_sqm" type="number" min="0" step="0.01" />
                        <InputError :message="form.errors.size_sqm" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="rooms">Zimmer</Label>
                        <Input id="rooms" v-model="form.rooms" type="number" min="0" />
                        <InputError :message="form.errors.rooms" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="status">Status</Label>
                    <Select v-model="form.status">
                        <SelectTrigger id="status">
                            <SelectValue placeholder="Status auswählen" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.status" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">Speichern</Button>
                    <Button as-child variant="secondary">
                        <Link :href="route('units.index')">Abbrechen</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

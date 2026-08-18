<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Building2, FileText, FolderOpen, Users, Wallet, Wrench } from 'lucide-vue-next';

const page = usePage<SharedData>();

const modules = [
    { title: 'Immobilien', description: 'Objekte, Gebäude und Wohnungen im Überblick', icon: Building2 },
    { title: 'Mieter & Eigentümer', description: 'Alle Beteiligten und ihre Verträge an einem Ort', icon: Users },
    { title: 'Verträge', description: 'Mietverträge mit Laufzeiten und Konditionen', icon: FileText },
    { title: 'Finanzen', description: 'Zahlungen, Rechnungen und Ausgaben im Blick', icon: Wallet },
    { title: 'Reparaturen', description: 'Schadensmeldungen bis zur Erledigung verfolgen', icon: Wrench },
    { title: 'Dokumente & Termine', description: 'Zentral abgelegt und jederzeit auffindbar', icon: FolderOpen },
];

const demoForm = useForm({});

const startDemo = () => {
    demoForm.post(route('demo-login'));
};
</script>

<template>
    <Head title="Willkommen" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header class="border-b border-sidebar-border/70">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
                    </div>
                    <span class="font-semibold">{{ page.props.name }}</span>
                </div>
                <nav class="flex items-center gap-2">
                    <Button as-child variant="ghost">
                        <Link :href="route('login')">Anmelden</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="route('register')">Registrieren</Link>
                    </Button>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <section class="mx-auto max-w-6xl px-6 py-20 text-center">
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Haus- und Immobilienverwaltung, professionell organisiert</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-muted-foreground">
                    {{ page.props.name }} bündelt Immobilien, Mietverträge, Finanzen und Reparaturen in einer Anwendung — für Hausverwaltungen, die
                    den Überblick behalten wollen.
                </p>
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <Button as-child size="lg">
                        <Link :href="route('register')">Kostenlos registrieren</Link>
                    </Button>
                    <Button size="lg" variant="outline" :disabled="demoForm.processing" @click="startDemo"> Demo ausprobieren </Button>
                </div>
                <p class="mt-3 text-xs text-muted-foreground">Keine Registrierung nötig — die Demo nutzt einen gemeinsamen Beispiel-Account.</p>
            </section>

            <section class="mx-auto max-w-6xl px-6 pb-20">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="module in modules" :key="module.title">
                        <CardContent class="flex items-start gap-4 pt-6">
                            <component :is="module.icon" class="mt-1 size-6 shrink-0 text-primary" />
                            <div>
                                <h3 class="font-semibold">{{ module.title }}</h3>
                                <p class="mt-1 text-sm text-muted-foreground">{{ module.description }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </section>
        </main>

        <footer class="border-t border-sidebar-border/70 py-6">
            <div class="mx-auto max-w-6xl px-6 text-center text-sm text-muted-foreground">
                &copy; {{ new Date().getFullYear() }} {{ page.props.name }}
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Building2, FileText, FolderOpen, Users, Wallet, Wrench } from 'lucide-vue-next';

const page = usePage<SharedData>();

// Each module gets its own accent colour (light/dark-aware) so the grid reads
// as a set of distinct areas rather than one grey wall — the rest of the app
// intentionally stays on the neutral shadcn theme; only this marketing page
// gets colour.
const modules = [
    {
        title: 'Immobilien',
        description: 'Objekte, Gebäude und Wohnungen im Überblick',
        icon: Building2,
        chip: 'bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400',
    },
    {
        title: 'Mieter & Eigentümer',
        description: 'Alle Beteiligten und ihre Verträge an einem Ort',
        icon: Users,
        chip: 'bg-violet-100 text-violet-600 dark:bg-violet-950/50 dark:text-violet-400',
    },
    {
        title: 'Verträge',
        description: 'Mietverträge mit Laufzeiten und Konditionen',
        icon: FileText,
        chip: 'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400',
    },
    {
        title: 'Finanzen',
        description: 'Zahlungen, Rechnungen und Ausgaben im Blick',
        icon: Wallet,
        chip: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400',
    },
    {
        title: 'Reparaturen',
        description: 'Schadensmeldungen bis zur Erledigung verfolgen',
        icon: Wrench,
        chip: 'bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400',
    },
    {
        title: 'Dokumente & Termine',
        description: 'Zentral abgelegt und jederzeit auffindbar',
        icon: FolderOpen,
        chip: 'bg-cyan-100 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-400',
    },
];

const demoForm = useForm({});

const startDemo = () => {
    demoForm.post(route('demo-login'));
};
</script>

<template>
    <Head title="Willkommen" />

    <div class="relative flex min-h-screen flex-col overflow-hidden bg-background text-foreground">
        <!-- Soft colour wash behind the hero — purely decorative, not part of the shadcn theme tokens. -->
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[32rem] overflow-hidden" aria-hidden="true">
            <div
                class="absolute left-1/2 top-[-10rem] h-[28rem] w-[42rem] -translate-x-1/2 rounded-full bg-gradient-to-br from-blue-400/30 via-violet-400/25 to-emerald-300/20 blur-3xl dark:from-blue-500/20 dark:via-violet-500/15 dark:to-emerald-400/10"
            />
        </div>

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
            <section class="mx-auto max-w-3xl px-6 py-16 text-center sm:py-20">
                <h1 class="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">
                    Digitale Betriebs- und Verwaltungsplattform für Hausverwaltungen
                </h1>
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <Button as-child size="lg" class="bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
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
                            <div :class="['flex size-11 shrink-0 items-center justify-center rounded-lg', module.chip]">
                                <component :is="module.icon" class="size-5" />
                            </div>
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

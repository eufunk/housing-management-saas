# Architektur

## Stack

| Layer      | Technologie                                                    |
| ---------- | ---------------------------------------------------------------- |
| Backend    | PHP 8.4/8.5, Laravel 12, Inertia.js (Laravel-Adapter)            |
| Frontend   | Vue 3 (`<script setup lang="ts">`), TypeScript, Tailwind CSS, Vite |
| Datenbank  | PostgreSQL 16                                                     |
| Cache/Queue| Redis (vorbereitet, Standard-Treiber vorerst `database`)          |
| Storage    | Laravel Filesystem, S3-kompatibel vorbereitet (`config/filesystems.php`) |
| Lokale Entwicklung | Nativ, empfohlen über WSL2 (kein Docker — siehe [development.md](development.md#warum-kein-docker-für-die-lokale-entwicklung)) |
| Deployment | Docker-Image (`docker/render/Dockerfile`), nur für Render — siehe [deployment.md](deployment.md) |
| Tests      | Pest 3 (PHPUnit-Unterbau)                                          |

## Warum `laravel/vue-starter-kit` statt nacktem `laravel/laravel`?

Die Aufgabenstellung fordert Vue 3 + TypeScript + Inertia + Tailwind sowie "bevorzugt offizielle
Laravel-/Vue-/Inertia-Lösungen". Laravel bietet dafür ein offizielles, von Laravel selbst
gepflegtes Starter-Kit (`laravel/vue-starter-kit`), das genau diesen Stack inklusive
Auth-Grundgerüst (Login/Register/Password-Reset/E-Mail-Verifizierung), einem
Sidebar-Application-Layout und einer wiederverwendbaren UI-Komponentenbibliothek
(shadcn-vue-artig, auf Radix-Vue/CVA-Basis) mitbringt. Das ist näher an "offizieller Lösung"
als ein selbst zusammengebautes Setup und spart Zeit, die in die eigentliche Fachlogik fließen
kann.

**Bekannte Bugs im Starter-Kit (Stand des verwendeten Release v1.0.2), die im Rahmen dieser
Grundstruktur behoben wurden:**

- `tsconfig.json` referenzierte `vue/tsx`, das es in Vue 3.5 nicht mehr gibt (→ `vue/jsx`).
- `resources/js/app.ts` erweiterte `vite/client` per `declare module`, obwohl die aktuelle
  Vite-Version `vite/client.d.ts` nicht mehr als Modul, sondern als globales Ambient-Skript
  ausliefert (→ `declare global`).
- `SharedData` (globale Inertia-Props) erfüllte die von `@inertiajs/vue3` 2.x geforderte
  Index-Signatur nicht.
- `NavMain.vue` definierte einen eigenen, inkompatiblen `NavItem`-Typ (`url` statt `href`).
- Mehrere Stellen fehlte der Type-Parameter bei `usePage<SharedData>()`.
- `tests/Pest.php` existierte, aber `pestphp/pest` war nicht in `composer.json` gelistet —
  die mitgelieferte Test-Konfiguration war faktisch tot.

Diese Korrekturen sind bewusst minimal-invasiv und lassen sich in einem Diff gegen ein frisches
`composer create-project laravel/vue-starter-kit` nachvollziehen.

## Multi-Tenancy

Siehe [multi-tenancy.md](multi-tenancy.md) für die vollständige Architekturentscheidung.
Kurzfassung: `organizations` + `organization_user`-Pivot (Rolle pro Mitgliedschaft) +
`users.current_organization_id` als aktiver Tenant-Kontext. Isolation wird auf Query-Ebene
über einen globalen Eloquent-Scope (`OrganizationScope`) erzwungen, nicht nur in Controllern.

## Backend-Konventionen

- **Controller** bleiben schlank. Business-Logik gehört in **Services** (`app/Services/`,
  wird angelegt, sobald die erste komplexe Operation es braucht) oder **Actions**
  (`app/Actions/`, für isolierte Einzeloperationen).
- **Form Requests** für Validierung, keine Validierung im Controller.
- **Policies** für Autorisierung, siehe [authorization.md](authorization.md). Kein
  Hardcoding von Berechtigungen in Controllern oder Views.
- **Events/Jobs** für alles, was asynchron oder entkoppelt ablaufen soll (z. B.
  Benachrichtigungen bei neuen Reparaturaufträgen) — wird eingeführt, sobald der erste
  konkrete Use-Case existiert.

`app/Services/` existiert aktuell noch nicht als Verzeichnis, da ein leerer Ordner ohne Inhalt
keinen Mehrwert bietet und nicht von Git getrackt wird. `app/Actions/` wurde mit der ersten
tatsächlich benötigten Klasse angelegt: `ProvisionDemoAccount` (siehe
[authentication.md](authentication.md), Abschnitt Demo-Login) — eine isolierte
Einzeloperation, die den öffentlichen Gastzugang ohne Registrierung bereitstellt.

## Frontend-Konventionen

Verzeichnisstruktur unter `resources/js/` (aus dem Starter-Kit übernommen, lowercase statt
der in der Aufgabenstellung beispielhaft groß geschriebenen Namen — das ist Vue/Nuxt-Konvention
und wird beibehalten, um konsistent mit dem offiziellen Starter-Kit zu bleiben):

```
resources/js/
├── components/       # Wiederverwendbare Komponenten
│   └── ui/           # Basis-UI-Bausteine (Button, Card, Badge, Alert, EmptyState, ...)
├── composables/       # Vue Composables (z. B. useAppearance, useInitials)
├── layouts/           # App-, Auth- und Settings-Layouts
├── lib/                # Utilities (cn/Tailwind-Merge-Helper)
├── pages/              # Inertia-Seiten, 1:1 zu Laravel-Routen
└── types/              # Globale TypeScript-Typen (SharedData, NavItem, ...)
```

`Services/` und `Stores/` (aus der Aufgabenstellung als Beispiel genannt) werden erst
eingeführt, wenn tatsächlicher Bedarf besteht (API-Client-Abstraktion bzw. globaler
State jenseits von Inertia-Props/Composables) — ansonsten wären es leere, ungenutzte
Ordner entgegen der Vorgabe "keine toten Dateien".

**Wichtige Falle bei der lowercase-`pages/`-Konvention:** `inertiajs/inertia-laravel` erwartet
in seinem eigenen Default (`config('inertia.testing.page_paths')`) noch `resources/js/Pages`
(Großbuchstabe) für den `assertInertia(...)->component(...)`-Testhelfer. Da Windows/WSL-Mounts
(`/mnt/c/...`) case-**in**sensitiv sind, fällt eine Abweichung dort nicht auf — ein
case-sensitiver Linux-CI-Runner schlägt jedoch zurecht fehl. Behoben über `config/inertia.php`
(überschreibt `page_paths` und `testing.page_paths` auf `resources/js/pages`).

## KI-Funktionen

Noch nicht implementiert. Wenn ein konkreter Anwendungsfall feststeht (z. B. automatisierte
Kategorisierung von Reparaturmeldungen), wird er als eigener Service hinter einem Interface
angebunden, damit der Provider austauschbar bleibt. Aktuell keine Abhängigkeit installiert.

## Laravel Reverb / Echtzeit

Architektonisch vorgesehen (`BROADCAST_CONNECTION` in `.env`), aber noch nicht installiert.
Aktivierung über `php artisan install:broadcasting`, sobald der erste Echtzeit-Use-Case
(z. B. Live-Update bei neuen Reparaturmeldungen) ansteht — das installiert Reverb und
publiziert `config/broadcasting.php` sowie `routes/channels.php`.

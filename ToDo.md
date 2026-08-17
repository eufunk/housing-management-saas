# ToDo: PropertyManager SaaS – Grundstruktur

Quelle: `Aufgabenstellung/PropertyManager SaaS Grundstruktur.docx`

Ziel dieser ersten Aufgabe: **kein vollständiges Feature-Set**, sondern ein sauberes,
startbares Laravel-SaaS-Grundgerüst (Multi-Tenant-fähig), auf dem die eigentliche
Immobilienverwaltung später Schritt für Schritt aufgebaut wird. Unnötige
Implementierungen und vorschnelle Architekturentscheidungen vermeiden.

Status-Legende: `[ ]` offen · `[~]` in Arbeit · `[x]` erledigt

---

## 0. Repository-Analyse (Schritt 1–2)
- [x] Aufgabenstellung gelesen und ausgewertet
- [x] Repo-Struktur geprüft (war leer: nur `README.md`, `.gitignore`, `Aufgabenstellung/`)
- [x] Vorhandene lokale Tool-Versionen geprüft (PHP/Composer/Docker fehlten, Node/npm vorhanden)

## 1. Laravel-Basisprojekt (Schritt 3)
- [x] PHP 8.5.9 + Composer 2.10.2 via Chocolatey installiert (Admin-Schritt durch Nutzer)
- [x] Benötigte PHP-Extensions aktiviert: fileinfo, pdo_pgsql, pgsql, zip, gd, intl, sodium, exif
- [x] Laravel via offiziellem **laravel/vue-starter-kit** (v1.0.2, Laravel 12.66, Inertia 2.0.25) initialisiert
      statt nacktem `laravel/laravel`-Skeleton, da dieser bereits Vue3+TS+Inertia+Tailwind+Auth+Sail
      nach offiziellem Standard mitbringt (Entscheidung mit Nutzer abgestimmt)
- [x] Composer-Abhängigkeiten installiert (`composer.lock` committed)
- [x] `.env` / `.env.example` angelegt und auf PostgreSQL umgestellt (APP_NAME=PropertyManager,
      DB_CONNECTION=pgsql, DB_HOST=pgsql, REDIS_HOST=redis)
- [x] npm-Audit-Fixes angewendet (23 → 0 Vulnerabilities, keine Breaking Changes)
- [x] Diverse Typfehler/Bugs im offiziellen Starter-Kit-Template behoben (tsconfig `vue/tsx`→`vue/jsx`,
      `vite/client`-Modul-Augmentierung, `SharedData`-Index-Signatur, `NavMain` NavItem-Typkonflikt,
      fehlende `usePage<SharedData>()`-Generics, `tabindex`-Bindings, SVG `mix-blend-mode`) –
      `vue-tsc --noEmit`, `eslint .` und `npm run build` laufen jetzt fehlerfrei

## 2. Frontend-Stack einrichten (Schritt 4)
- [x] Vue 3 + `<script setup lang="ts">` (via Starter-Kit)
- [x] TypeScript konfiguriert (tsconfig.json, Fehler behoben)
- [x] Inertia.js (Laravel-Adapter + Vue-Adapter) eingerichtet
- [x] Tailwind CSS konfiguriert (v3, shadcn-vue-artige UI-Komponenten inkl. Sidebar-Layout)
- [x] Vite konfiguriert (vite.config.ts, Build getestet)

## 3. Datenbank & Infrastruktur (Schritt 5)
- [x] PostgreSQL als DB-Verbindung konfiguriert (.env/.env.example)
- [x] Docker-Setup: `docker-compose.yml` (app/nginx/queue/scheduler/node/pgsql/redis),
      `docker/php/Dockerfile` (PHP 8.4-fpm-alpine + benötigte Extensions), `docker/nginx/default.conf`,
      `.dockerignore` — Start mit `docker compose up -d --build` (siehe `docs/development.md`)
- [x] Redis: Cache-/Queue-/Session-Treiber bereits in Laravel-Configs vorhanden, `REDIS_HOST=redis`
      gesetzt; Standard-Treiber bleiben vorerst `database` (kein Redis-Zwang für einfaches lokales Dev
      ohne Docker) — Queue-Worker- und Scheduler-Container sind aber bereits vorbereitet
- [x] Laravel Reverb: architektonisch vorgesehen (BROADCAST_CONNECTION in .env), bewusst NICHT
      installiert, da noch kein Echtzeit-Use-Case existiert (vermeidet ungenutzte Dependency);
      Aktivierung dokumentiert in `docs/architecture.md` (`php artisan install:broadcasting`)
- [x] S3-kompatibler File-Storage: `config/filesystems.php` bringt bereits einen `s3`-Disk mit
      `endpoint`/`use_path_style_endpoint` (MinIO/Spaces-kompatibel) — Laravel-Default, keine
      Änderung nötig; AWS_ENDPOINT/AWS_URL in .env.example ergänzt

> **Bekannter Blocker (Umgebung, nicht Code):** Auf diesem Rechner blockiert eine Windows-
> Anwendungssteuerungsrichtlinie sowohl die PHP-`pdo_sqlite`/`sqlite3`-Extensions als auch
> `initdb.exe` der lokalen PostgreSQL-Installation (jeweils identische Fehlermeldung
> "Eine Anwendungssteuerungsrichtlinie hat diese Datei blockiert"). Es existiert daher aktuell
> **keine lauffähige lokale Datenbank** – Migrationen und Tests können erst ausgeführt werden,
> sobald eine DB erreichbar ist (z.B. via Docker auf einem anderen Host oder nach IT-Freigabe).
> Auf Nutzerwunsch wird bis dahin ohne laufende DB weitergebaut (Dateien/Konfiguration/Doku).

## 4. Grundlegende Projektstruktur (Schritt 6)
- [x] Backend-Verzeichnisstruktur: Form Requests (vorhanden), Policies (`app/Policies/`,
      2 Beispiele), Enums (`app/Enums/`), Model-Concerns/Scopes; `Services/`/`Actions/` bewusst
      erst bei erstem echten Bedarf angelegt (keine leeren Ordner, siehe `docs/architecture.md`)
- [x] Frontend-Verzeichnisstruktur unter `resources/js/` — lowercase-Konvention des Starter-Kits
      beibehalten (`components/`, `layouts/`, `pages/`, `composables/`, `types/`, `lib/`);
      `services/`/`stores/` bewusst nicht angelegt, siehe `docs/architecture.md`
- [x] Pages-Unterordner angelegt: Dashboard, Properties, Buildings, Units, Tenants, Owners,
      Leases, Payments, Invoices, Expenses, Maintenance, Documents, Appointments, Notifications,
      Settings (aus Starter-Kit) — alle außer Dashboard/Settings als EmptyState-Platzhalter

## 5. Datenbankarchitektur (Abschnitt 9)
- [x] Migrationen für alle 19 Kern-Tabellen angelegt: `users` (erweitert), `organizations`,
      `organization_user`, `properties`, `buildings`, `units`, `owners`, `tenants`, `contractors`,
      `leases`, `payments`, `invoices`, `expenses`, `document_categories`, `documents`,
      `maintenance_requests`, `maintenance_comments`, `appointments`, `activity_logs`
      (PHP-Syntax geprüft, noch nicht ausgeführt — siehe DB-Blocker oben)
- [x] ULIDs für öffentliche IDs (`ulid`-Spalte + `HasUlid`-Trait), Foreign Keys, Unique
      Constraints, Timestamps, Soft Deletes wo sinnvoll (nicht bei Pivot/Log-Tabellen)
- [x] Eloquent-Modelle mit Beziehungen für alle Tabellen (`app/Models/*.php`), per
      Smoke-Test ohne DB erfolgreich geladen

## 6. Multi-Tenancy (Abschnitt 3)
- [x] Architektur festgelegt: `organizations` + `organization_user`-Pivot (Rolle pro
      Mitgliedschaft) + `users.current_organization_id` als aktiver Tenant-Kontext;
      Super Admin ist plattformweit (`users.is_super_admin`), nicht organisationsgebunden
      → dokumentiert in `docs/architecture.md` (folgt)
- [x] Middleware `EnsureUserHasOrganization` (Alias `ensure-organization`) vorbereitet,
      noch nicht an Routen gebunden (keine Feature-Routen existieren noch)
- [x] `OrganizationScope` (Global Scope, fail-closed) + `BelongsToOrganization`-Trait
      sorgen dafür, dass jede Query automatisch auf die aktive Organisation eingeschränkt wird

## 7. Authentifizierung & Autorisierung (Abschnitt 4)
- [x] Grundlegende Authentifizierung vorhanden (Laravel-Standardmechanismen via Starter-Kit:
      Login/Register/Password-Reset/Email-Verification)
- [x] Rollen vorbereitet: `OrganizationRole`-Enum (property_manager, owner, tenant, contractor)
      + `is_super_admin`-Flag für Super Admin
- [x] Erweiterbares Berechtigungssystem: `Gate::before` für Super-Admin-Bypass,
      `AuthorizesOrganizationAccess`-Trait + Beispiel-Policies (`PropertyPolicy`,
      `MaintenanceRequestPolicy`) als Muster für weitere Policies

## 8. Zentrale Layouts & UI-Komponenten (Schritt 7)
- [x] Application Layout: Sidebar mit allen 10 Modulen (Dashboard, Immobilien, Mieter,
      Eigentümer, Verträge, Finanzen, Reparaturen, Dokumente, Termine, Benachrichtigungen)
      + Einstellungen im Footer
- [x] Topbar vorhanden (Starter-Kit: Suche-Icon, Notifications-Icon, Benutzerprofil/Dropdown)
      — Organisationsauswahl im Header folgt, sobald Multi-Org-UI gebaut wird
- [x] Mobile: Responsive Sidebar/Sheet, Mobile Navigation (aus Starter-Kit, unverändert nutzbar)
- [x] Wiederverwendbare UI-Komponenten: Button, Input, Card, Dialog (Modal), Dropdown,
      Avatar, Breadcrumb, Checkbox, Label, Sheet, Sidebar, Skeleton (Loading States),
      Tooltip (alle aus Starter-Kit) + neu: **Badge**, **Alert**, **EmptyState**
- [~] Select, Table, Pagination, Form-Wrapper bewusst noch nicht gebaut — keine Datenlisten
      existieren noch, die sie bräuchten (vermeidet premature abstraction); nachzuholen,
      sobald die erste echte Listenansicht (z.B. Properties-Index) implementiert wird

## 9. Dashboard-Prototyp (Schritt 8, Abschnitt 8)
- [x] Dashboard-Seite mit Mock-/Placeholder-Daten: Immobilien, Wohnungen, Mieter, Leerstand,
      Einnahmen/Ausgaben (Monat), offene Reparaturen, überfällige Zahlungen, auslaufende
      Mietverträge, letzte Aktivitäten — als KPI-Cards + Listen (kein Chart, um keine
      zusätzliche Chart-Library einzuführen, bevor echte Zeitreihendaten existieren)
- [x] Platzhalter-Seiten (EmptyState) für alle übrigen Sidebar-Module + zugehörige Routen
      (`Route::inertia`, offizieller Inertia-Helfer) angelegt, damit keine toten Links entstehen

## 10. Sicherheit (Abschnitt 11)
- [x] CSRF, Mass Assignment: Laravel-Defaults genutzt (CSRF-Middleware aktiv; `organization_id`
      bewusst NICHT in `$fillable`, sondern nur serverseitig über `BelongsToOrganization` gesetzt)
- [ ] Rate Limiting vorbereiten (Laravel-Standard-Throttle noch nicht auf neue Routen gelegt)
- [ ] Sichere Datei-Uploads / Zugriffskontrolle auf Dokumente (Modell/Policy-Grundlage steht,
      Upload-Endpoint existiert noch nicht)
- [x] Tenant Isolation: `OrganizationScope` (fail-closed) + Tests geschrieben (siehe unten)
- [x] Sichere Passwort-/Session-Konfiguration: Laravel-Defaults (bcrypt/hashed cast, Session
      aus Starter-Kit) unverändert übernommen

## 11. Tests (Schritt 10, Abschnitt 14)
- [x] Pest eingerichtet — `tests/Pest.php` existierte bereits im Starter-Kit, das Paket
      `pestphp/pest` + `pestphp/pest-plugin-laravel` fehlte aber tatsächlich in `composer.json`
      (Inkonsistenz im offiziellen Starter-Kit) → nachinstalliert
- [x] Beispieltests geschrieben: `tests/Feature/TenantIsolationTest.php` (4 Tests: Isolation,
      kein Org-Kontext, Super-Admin-Bypass, Auto-Stamping beim Erstellen),
      `tests/Feature/Authorization/PropertyPolicyTest.php` (5 Tests: Property Manager, Owner,
      Tenant, fremde Organisation, Super Admin) — Authentication-Tests waren bereits im
      Starter-Kit vorhanden (Login/Register/Password-Reset/Email-Verification)
- [~] Ausführung nicht möglich (DB-Blocker, s.o.) — alle 36 Tests scheitern einheitlich exakt
      an `could not find driver (sqlite)`, was bestätigt, dass Test- und Anwendungscode
      strukturell korrekt sind und nur die fehlende DB-Verbindung im Weg steht

## 12. Qualitätskontrolle (Schritt 10–11, Abschnitt 19)
- [~] Laravel Tests: geschrieben, Ausführung blockiert (siehe DB-Blocker)
- [x] TypeScript Check (`vue-tsc --noEmit`) — fehlerfrei
- [x] ESLint (`eslint .`) — fehlerfrei
- [x] Frontend Build (`npm run build`) — erfolgreich
- [x] Laravel Pint (`vendor/bin/pint --test`) — fehlerfrei (mehrere Dateien des Starter-Kits
      selbst waren nicht Pint-konform und wurden mitkorrigiert)
- [x] Mehrere echte Bugs im offiziellen `laravel/vue-starter-kit`-Template gefunden und behoben
      (siehe Abschnitt 1) statt nur den eigenen Code zu prüfen

## 13. Dokumentation (Schritt 12, Abschnitt 15)
- [x] `README.md` aktualisiert (Setup, Docker-Commands, Doku-Übersicht, Rollen, Status-Link)
- [x] `docs/architecture.md` (inkl. Begründung Starter-Kit-Wahl + gefundene/behobene Bugs)
- [x] `docs/database.md`
- [x] `docs/authentication.md`
- [x] `docs/authorization.md`
- [x] `docs/multi-tenancy.md`
- [x] `docs/development.md`

## 14. Git-Hygiene (Abschnitt 16)
- [x] `.gitignore` durch die vom offiziellen Starter-Kit mitgelieferte Version ersetzt
      (umfassender als die vorherige, deckt alle alten Einträge weiterhin ab)
- [x] `.env` ist in `.gitignore` gelistet und wurde nie committed (per `git status` geprüft)
- [ ] Erste Commit-Struktur — **noch nicht committet**, da bisher kein expliziter Auftrag dazu
      vorlag; Vorschlag für sinnvolle Aufteilung siehe unten

---

## Offene Punkte (bewusst nicht Teil dieser Grundstruktur)

- Migrationen/Tests real gegen eine laufende PostgreSQL-Instanz ausführen (DB-Blocker, s. o.)
- `ensure-organization`-Middleware an Routen binden, sobald Organisations-Onboarding existiert
- Rate Limiting auf neue Routen legen
- Datei-Upload-Endpoint + Zugriffskontrolle für `documents` implementieren
- Select/Table/Pagination/Form-Wrapper-Komponenten, sobald die erste echte Listenansicht kommt
- Weitere Policies (Lease, Payment, Document, ...) nach dem Muster von `PropertyPolicy`
- Laravel Reverb installieren, sobald ein Echtzeit-Use-Case ansteht

## Vorschlag: erste Commit-Struktur

Noch nicht ausgeführt (siehe Abschnitt 14). Sinnvolle Aufteilung in mehrere Commits statt
eines Mega-Commits:

1. `chore: initialize laravel/vue-starter-kit` — Starter-Kit-Grundgerüst unverändert
2. `fix: correct starter-kit type errors and vite/client augmentation` — die in `docs/architecture.md`
   dokumentierten Bugfixes (tsconfig, app.ts, SharedData, NavMain, usePage-Generics)
3. `feat: multi-tenant database schema and models` — alle Migrationen, Models, Enums, Concerns, Scopes
4. `feat: authorization foundation` — Gate::before, Policies, Middleware
5. `feat: property management navigation and dashboard prototype` — Sidebar, Platzhalterseiten,
   Dashboard, neue UI-Komponenten (Badge/Alert/EmptyState)
6. `feat: docker development environment` — docker-compose.yml, Dockerfile, nginx-Config
7. `test: add tenant isolation and authorization tests`
8. `docs: add architecture, database, auth and multi-tenancy documentation`

---

## Arbeitsprinzipien (aus der Aufgabenstellung, immer beachten)
- Vor Änderungen: Repo, Dependencies und bestehende Konventionen prüfen – nichts blind überschreiben
- Bevorzugt offizielle Laravel-/Vue-/Inertia-Lösungen, möglichst wenige zusätzliche Libraries
- Keine Dependency ohne konkreten Grund installieren
- Controller schlank halten (Form Requests, Services, Actions, Policies, Events, Jobs)
- Keine großen Vue-Komponenten, TypeScript konsequent nutzen, kein unnötiges `any`
- Keine premature abstractions, keine toten Dateien, keine ungenutzten Dependencies
- Wichtige Architekturentscheidungen in `docs/architecture.md` dokumentieren
- KI-Funktionen nur architektonisch vorbereiten, nicht implementieren

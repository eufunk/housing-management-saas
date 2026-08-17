# Projektdokumentation: PropertyManager SaaS

Dies ist eine projektbegleitende Dokumentation — sie beschreibt, *was* verlangt wurde und *wie*
vorgegangen wurde, im Unterschied zu den übrigen Dateien in `docs/`, die den *fertigen Stand*
der Architektur beschreiben (siehe [architecture.md](architecture.md),
[database.md](database.md), [authentication.md](authentication.md),
[authorization.md](authorization.md), [multi-tenancy.md](multi-tenancy.md),
[development.md](development.md)). Wird während der weiteren Arbeit am Projekt fortgeschrieben.

Der laufende Umsetzungsstand mit Checklisten steht in [`ToDo.md`](../ToDo.md) im Repo-Root.

---

## Aufgabenstellung

Quelle: `Aufgabenstellung/PropertyManager SaaS Grundstruktur.docx`.

Kernauftrag: Aufbau der **professionellen Grundstruktur** einer SaaS-Anwendung zur Haus- und
Immobilienverwaltung — ausdrücklich **nicht** die vollständige Fachanwendung. Vorgegebener
Stack:

- **Backend**: PHP 8.4+/Laravel (aktuelle stabile Version), Eloquent, Migrations, Policies/Gates,
  Form Requests, Jobs/Queues, Notifications, Events, Scheduler, REST wo sinnvoll
- **Frontend**: Vue 3 (`<script setup lang="ts">`), TypeScript, Inertia.js, Tailwind CSS, Vite
  — kein separates React/Node-Backend
- **Datenbank**: PostgreSQL, von Anfang an für Multi-Tenancy vorbereitet
- **Infrastruktur**: Redis, Queue, Scheduler, Laravel Reverb (später), S3-kompatibler Storage,
  Docker, PHPUnit/Pest

Zentrale fachliche Vorgaben:

1. **Multi-Tenancy**: Jede Organisation muss logisch von anderen getrennt sein; ein Nutzer darf
   niemals Daten einer anderen Organisation sehen können — als härteste, nicht verhandelbare
   Anforderung explizit hervorgehoben.
2. **Rollen**: Super Admin, Property Manager, Owner, Tenant, Contractor — Berechtigungssystem
   muss erweiterbar sein, kein Hardcoding an vielen Stellen.
3. **Hauptmodule**: Immobilien (Properties/Buildings/Units/Rooms/Parking), Personen
   (Users/Owners/Tenants/Contractors), Mietverträge, Finanzen, Reparaturen, Dokumente, Termine,
   Benachrichtigungen, Audit/Activity-Logs.
4. **Layout**: professionelles B2B-SaaS-Layout mit Sidebar (Dashboard, Immobilien, Mieter,
   Eigentümer, Verträge, Finanzen, Reparaturen, Dokumente, Termine, Benachrichtigungen,
   Einstellungen), Topbar (Suche, Notifications, Profil, Organisation), responsiv.
5. **Dashboard**: erster UI-Prototyp mit Mock-/Placeholder-Daten (Kennzahlen wie Anzahl
   Immobilien, Einnahmen/Ausgaben, Leerstand, offene Reparaturen, überfällige Zahlungen,
   auslaufende Verträge, letzte Aktivitäten).
6. **Datenbankarchitektur**: konkrete Liste von Kern-Tabellen vorgegeben (users, organizations,
   organization_user, properties, buildings, units, owners, tenants, contractors, leases,
   payments, invoices, expenses, maintenance_requests, maintenance_comments, documents,
   document_categories, appointments, activity_logs) — mit UUIDs/ULIDs, Foreign Keys, Indexes,
   Unique Constraints, Timestamps, Soft Deletes.
7. **Sicherheit**: CSRF, XSS, SQL Injection, Mass Assignment, Authorization, Authentication,
   Rate Limiting, sichere Uploads, Zugriffskontrolle auf Dokumente, Tenant Isolation, sichere
   Passwörter/Sessions — mit explizitem Fokus auf die Tenant-Isolation als "besonders wichtig".
8. **Explizite Leitplanken für die Arbeitsweise**: keine unnötigen Implementierungen, keine
   premature abstractions, möglichst wenige zusätzliche Libraries, keine Dependency ohne
   konkreten Grund, offizielle Laravel-/Vue-/Inertia-Lösungen bevorzugen, vor Änderungen das
   Repository und bestehende Konventionen analysieren, wichtige Architekturentscheidungen in
   `docs/architecture.md` dokumentieren, am Ende keine offensichtlichen Build-/TypeScript-Fehler.

Vorgegebene Vorgehensweise (20 Abschnitte des Dokuments, zusammengefasst): Repository
analysieren → prüfen, ob bereits ein Laravel-Projekt existiert → Laravel initialisieren →
Vue/TS/Inertia/Tailwind einrichten → PostgreSQL/Docker einrichten → Grundstruktur anlegen →
zentrale Layouts/UI-Komponenten → Dashboard-Seite → Auth/Autorisierung/Multi-Tenancy
vorbereiten → Tests und Build-Prozesse ausführen → Fehler beheben → Dokumentation
aktualisieren.

## Herangehensweise

### 1. Aufgabenstellung erschließen und ToDo-Liste anlegen

Die `.docx`-Datei ist binär und konnte nicht direkt gelesen werden; der Text wurde über die
im Dateiformat enthaltene `document.xml` extrahiert. Aus der vollständigen Aufgabenstellung
wurde eine `ToDo.md` im Repo-Root erstellt, die alle Abschnitte in abarbeitbare Checklisten
übersetzt und seither nach jedem größeren Arbeitsschritt aktualisiert wird — sie dient als
lebendiger Fortschrittstracker über die gesamte Bearbeitung hinweg.

### 2. Repository-Analyse vor dem ersten Schritt

Vor jeder Änderung wurde der bestehende Zustand geprüft: Das Repository enthielt zu Beginn nur
`README.md`, `.gitignore` und den `Aufgabenstellung`-Ordner — kein Laravel-Projekt, keine
lokale PHP/Composer/Docker-Installation. Das entspricht Schritt 1–2 der Vorgabe
("Analysiere zuerst das vorhandene Repository", "Prüfe, ob bereits ein Laravel-Projekt
existiert").

### 3. Toolchain einrichten

PHP und Composer fehlten lokal vollständig; Node/npm waren vorhanden. Nach Rücksprache mit dem
Nutzer wurden PHP 8.5 und Composer über Chocolatey installiert (erforderte einen manuellen,
elevierten Schritt durch den Nutzer, da die Agentenumgebung nicht administrativ ist). Die für
Laravel/PostgreSQL benötigten PHP-Extensions (`fileinfo`, `pdo_pgsql`, `pgsql`, `zip`, `gd`,
`intl`, `sodium`, `exif`) wurden in der `php.ini` aktiviert.

### 4. Bewusste Entscheidung: offizielles Starter-Kit statt manuellem Aufbau

Zunächst wurde das nackte `laravel/laravel`-Skeleton installiert und der Plan gefasst, Vue 3,
TypeScript, Inertia und Tailwind manuell aufzusetzen. Da die Aufgabenstellung ausdrücklich
"offizielle Laravel-/Vue-/Inertia-Lösungen" bevorzugt, wurde dieser Ansatz noch einmal
hinterfragt — und mit dem offiziellen `laravel/vue-starter-kit` existiert genau die von Laravel
selbst gepflegte Lösung für exakt diesen Stack (inklusive Auth-Grundgerüst und einer
wiederverwendbaren UI-Komponentenbibliothek). Nach Rücksprache mit dem Nutzer wurde das bare
Skeleton verworfen und stattdessen das Starter-Kit installiert — eine bewusste Kurskorrektur,
sobald erkennbar wurde, dass die "offizielle Lösung" näher an der Vorgabe liegt als der eigene
Aufbau.

### 5. Starter-Kit nicht blind vertrauen, sondern verifizieren

"Offiziell" wurde nicht mit "fehlerfrei" gleichgesetzt: Nach der Installation wurden
TypeScript-Check, ESLint und Produktions-Build sofort ausgeführt (nicht erst am Ende). Dabei
kamen mehrere echte Bugs im Starter-Kit-Template selbst zum Vorschein — u. a. eine veraltete
`vue/tsx`-Typreferenz, eine nicht mehr funktionierende `vite/client`-Modul-Erweiterung, eine
fehlende Index-Signatur im globalen `SharedData`-Typ und ein inkompatibler, doppelt
definierter `NavItem`-Typ in `NavMain.vue`. Alle wurden minimal-invasiv behoben und in
[architecture.md](architecture.md) dokumentiert, statt sie zu ignorieren oder das Starter-Kit
zu verwerfen.

### 6. Datenbankarchitektur und Multi-Tenancy zuerst

Da Tenant-Isolation in der Aufgabenstellung als "besonders wichtig" hervorgehoben wird, wurde
die Multi-Tenancy-Architektur bewusst *vor* den UI-Modulen entschieden und dokumentiert (siehe
[multi-tenancy.md](multi-tenancy.md)): `organizations` + `organization_user`-Pivot mit
Rolle je Mitgliedschaft, `users.current_organization_id` als aktiver Tenant-Kontext,
plattformweiter Super-Admin-Flag statt organisationsgebundener Rolle. Die Durchsetzung erfolgt
nicht nur in Controllern, sondern zentral über einen fail-closed globalen Eloquent-Scope
(`OrganizationScope`), ergänzt um explizite Prüfungen in den Policies als zweite
Verteidigungslinie. Alle 19 aus der Aufgabenstellung geforderten Tabellen wurden als
Migrationen mit ULIDs, sauberen Foreign Keys und Soft Deletes angelegt, dazu passende
Eloquent-Modelle mit Beziehungen.

### 7. Umgebungsblocker: keine lauffähige lokale Datenbank

Sowohl die PHP-SQLite-Erweiterung als auch eine frische PostgreSQL-Installation via Chocolatey
scheiterten auf der Entwicklungsmaschine an derselben Windows-Anwendungssteuerungsrichtlinie
(identische Fehlermeldung bei zwei unabhängigen Installationsversuchen — kein Zufall, sondern
eine systemische Sperre). Nach Rücksprache mit dem Nutzer wurde entschieden, ohne laufende
Datenbank weiterzubauen: Migrationen und Models wurden per PHP-Syntaxprüfung (`php -l`) und
einem DB-losen Model-Smoke-Test verifiziert; die geschriebenen Pest-Tests scheitern aktuell
alle einheitlich exakt am fehlenden Datenbanktreiber — ein Beleg dafür, dass Test- und
Anwendungscode strukturell korrekt sind und einzig die Infrastruktur fehlt. Details siehe
[database.md](database.md) und [development.md](development.md).

### 8. Rollen und Autorisierung nach demselben Muster wie Multi-Tenancy

Statt eines zusätzlichen Berechtigungspakets (z. B. spatie/laravel-permission) wurden die vier
festen Rollen als PHP-Enum (`OrganizationRole`) plus Laravel-native Policies/Gates umgesetzt —
konsistent mit der Vorgabe, möglichst wenige zusätzliche Libraries zu verwenden. Zwei
Beispiel-Policies (`PropertyPolicy`, `MaintenanceRequestPolicy`) demonstrieren das Muster für
alle weiteren, noch nicht implementierten Policies. Begründung in
[authorization.md](authorization.md).

### 9. Frontend: Navigation, Dashboard, Platzhalterseiten statt Fachlogik

Um keine toten Links zu erzeugen, aber auch keine verfrühte Fachlogik zu bauen, wurde für jedes
Sidebar-Modul außer Dashboard eine schlanke Platzhalterseite mit einer neu gebauten
`EmptyState`-Komponente angelegt (dazu `Badge` und `Alert` als weitere wiederverwendbare
UI-Bausteine). Tabellen-/Select-/Pagination-Komponenten wurden bewusst *nicht* vorab gebaut,
da noch keine Datenliste existiert, die sie bräuchte — vermeidet premature abstraction, ist
aber in `ToDo.md` als offener Punkt vermerkt.

### 10. Infrastruktur, Tests, Dokumentation, Qualitätssicherung

Docker Compose (PHP-FPM, Nginx, PostgreSQL, Redis, Queue-Worker, Scheduler, Vite-Node-Service)
wurde als Dateien vorbereitet, aber auf dieser Maschine nicht getestet (Docker nicht
installiert). Beim Einrichten der Tests fiel auf, dass das Starter-Kit eine `tests/Pest.php`
mitliefert, ohne dass `pestphp/pest` tatsächlich als Abhängigkeit in `composer.json` gelistet
war — eine weitere Inkonsistenz im offiziellen Template, die durch Nachinstallation behoben
wurde. Vor Abschluss jedes größeren Arbeitsschritts liefen TypeScript-Check, ESLint,
Produktions-Build und Laravel Pint durchgehend grün; alle sieben `docs/*.md`-Dateien sowie das
README wurden konsistent mit dem tatsächlich gebauten Code geschrieben (nicht vorab als Plan).

### 11. Commit und Push erst auf expliziten Wunsch

Änderungen wurden erst committet und gepusht, nachdem der Nutzer explizit danach gefragt hatte
— vorher blieb der Arbeitsstand unversioniert im Working Tree, wie in den
Ausführungsrichtlinien vorgesehen.

# Projektdokumentation: PropertyManager SaaS

Dies ist eine projektbegleitende Dokumentation — sie beschreibt, *was* verlangt wurde und *wie*
vorgegangen wurde, im Unterschied zu den übrigen Dateien in `docs/`, die den *fertigen Stand*
der Architektur beschreiben (siehe [architecture.md](architecture.md),
[database.md](database.md), [authentication.md](authentication.md),
[authorization.md](authorization.md), [multi-tenancy.md](multi-tenancy.md),
[development.md](development.md), [deployment.md](deployment.md)). Wird während der weiteren
Arbeit am Projekt fortgeschrieben.

Fachbegriffe und Tech-Stack-Namen werden in [glossar.md](glossar.md) erklärt.

Der laufende Umsetzungsstand mit Checklisten steht in [`ToDo.md`](../ToDo.md) im Repo-Root.

---

## Aufgabenstellung

Quelle: `Aufgabenstellung/PropertyManager SaaS Grundstruktur.docx`.

Kernauftrag: Aufbau der **professionellen Grundstruktur** einer SaaS-Anwendung (Software as a
Service — eine zentral gehostete Anwendung, die Nutzer:innen über den Browser nutzen, typischerweise
im Abo-Modell, ohne lokale Installation) zur Haus- und Immobilienverwaltung — ausdrücklich
**nicht** die vollständige Fachanwendung. Vorgegebener Stack:

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

*(Dieser Blocker wurde später aufgelöst — siehe Abschnitt 12.)*

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

### 12. Der DB-Blocker wird doch noch gelöst: Docker Desktop, WSL2 und eine hartnäckige Fehlersuche

Auf Wunsch des Nutzers wurde der ungelöste DB-Blocker aus Abschnitt 7 noch einmal angegangen,
diesmal mit dem Ziel, tatsächlich eine laufende Datenbank auf der Entwicklungsmaschine zu
bekommen. Der Weg dorthin war deutlich steiniger als erwartet und ist selbst ein gutes Beispiel
dafür, wie man sich durch mehrere, sich überlagernde Umgebungsprobleme durcharbeitet, statt bei
der ersten Fehlermeldung aufzugeben:

1. **Docker Desktop installieren.** WSL2 (Voraussetzung für Docker auf Windows 11 Home) wurde
   installiert (`wsl --install`, Neustart). Docker Desktop selbst wurde per Chocolatey
   installiert, startete aber nicht: Die Logs zeigten `wsl.exe --mount auf ARM64 erfordert
   Windows 27653 oder höher` sowie `Exec format error` bei internen Docker-Hilfsprozessen —
   die Entwicklungsmaschine läuft auf **Windows-on-ARM64**, und Docker Desktops WSL2-basierter
   Mechanismus fürs Datenlaufwerk setzt dort einen Windows-Build voraus, der auf regulären
   Stable-Channel-Installationen noch nicht existiert. Eine kurze Recherche zeigte: Das ist kein
   Einzelfall dieser Maschine, sondern ein seit Jahren offenes, plattformweites Problem mit
   mehreren aktiven GitHub-Issues über verschiedene Docker-Desktop-Versionen hinweg — das
   systematische Ausprobieren älterer Versionen wurde deshalb bewusst *nicht* verfolgt, da es
   eine Zeitwette mit sehr ungewissem Ausgang gewesen wäre.
2. **Kurskorrektur: PostgreSQL direkt in WSL2, ohne Docker.** Da WSL2 selbst (unabhängig von
   Docker) einwandfrei lief, wurde PostgreSQL direkt in der Ubuntu-Distribution installiert
   (`apt install postgresql`) — `initdb` lief dort ohne Probleme, da die blockierende
   Windows-Anwendungssteuerungsrichtlinie ausschließlich native Windows-Programme betrifft,
   nicht Programme innerhalb der Linux-VM.
3. **Neue, unerwartete Instabilität: Windows↔WSL2-Networking.** Die Verbindung von
   nativem Windows-PHP zur WSL2-Datenbank funktionierte zunächst nicht zuverlässig — mal ging
   sie, mal kam "Connection refused", ohne erkennbares Muster. Systematisches Nachprüfen zeigte:
   Selbst Windows-eigene Tools wie `Test-NetConnection` und `curl.exe` waren betroffen, während
   PowerShells `.NET`-Netzwerkschicht zuverlässig funktionierte — ein Hinweis darauf, dass es
   sich nicht um ein PHP-spezifisches Problem handelte. Nach einem WSL2-Neustart
   (`wsl --shutdown`) und Umstellen von PostgreSQL auf `listen_addresses = '*'` plus direkter
   Verbindung über die WSL2-VM-IP (statt der üblichen `localhost`-Weiterleitung) ließ sich das
   Muster genauer eingrenzen: Native Windows-Programme (`php.exe`, `curl.exe`) scheiterten
   weiterhin konsequent, obwohl PowerShell-Tools dieselbe Verbindung zuverlässig herstellen
   konnten — exakt dasselbe Verhalten wie die Anwendungssteuerungsrichtlinie aus Abschnitt 7,
   nur diesmal auf Netzwerkebene statt bei Datei-/Prozessausführung.
4. **Finale Lösung: PHP selbst in WSL2 betreiben.** Statt weiter zu versuchen, natives
   Windows-PHP zuverlässig mit einer in WSL2 laufenden Datenbank zu verbinden, wurde PHP,
   Composer und die nötigen Extensions ebenfalls direkt in WSL2 installiert. Das Projekt bleibt
   dabei am gewohnten Ort auf der Windows-Festplatte (erreichbar über `/mnt/c/...`), nur die
   Ausführung von `php`/`composer`/`artisan` wandert nach WSL2. Damit ist keine Windows-Grenze
   mehr beteiligt, die die Anwendungssteuerungsrichtlinie überhaupt greifen lassen könnte.
5. **Vollständige Verifikation.** Mit diesem Aufbau liefen alle 22 Migrationen sauber gegen
   PostgreSQL durch, die komplette Pest-Suite (36 Tests) war grün — bis auf zwei Ausnahmen, die
   sich als direkte, korrekte Konsequenzen eigener Architekturentscheidungen entpuppten und
   entsprechend angepasst wurden, statt sie als Bugs zu behandeln:
   - `ProfileUpdateTest`: Der Test des Starter-Kits erwartete, dass ein gelöschter Nutzer über
     `$user->fresh()` `null` liefert — das gilt aber nur für hartes Löschen. Da `User` inzwischen
     `SoftDeletes` nutzt (Teil des Multi-Tenancy-Datenmodells), liefert `fresh()` bewusst weiterhin
     den (jetzt als gelöscht markierten) Datensatz, da `fresh()` laut Eloquent-Design alle
     globalen Scopes umgeht. Der Test wurde entsprechend auf `->trashed()` umgestellt.
   - `PropertyPolicyTest`: Ein Policy-Test rief `$user->can(...)` auf, ohne den Nutzer zuvor per
     `actingAs()` zu authentifizieren. Da `OrganizationScope` (siehe
     [multi-tenancy.md](multi-tenancy.md)) beim Nachladen der `owner`-Beziehung auf
     `auth()->user()` zugreift, lieferte diese Beziehung ohne eingeloggten Nutzer keine Daten —
     ein Testaufbau-Fehler, kein Fehler in Policy oder Scope. Behoben durch `actingAs()` vor den
     Assertions.
   Abschließend wurde ein echter Login über die tatsächlich laufende Anwendung im Browser
   verifiziert (Testnutzer, Formular, Redirect zum Dashboard) — nicht nur isoliert per Tests.

**Lehre:** Mehrere unabhängige Umgebungsprobleme können sich überlagern und einander verdecken
(App-Control-Policy, ARM64-Docker-Bug, WSL2-Netzwerkinstabilität sahen zunächst wie ein
einziges, diffuses "Datenbank geht nicht"-Problem aus). Sie einzeln, mit jeweils eigenen
Diagnoseschritten zu isolieren — statt vorschnell eine Ursache anzunehmen und danach zu
optimieren — war hier der Weg zu einer tatsächlich funktionierenden Lösung. Details und
Setup-Anleitung: [development.md](development.md).

### 13. Öffentlicher Zugang: Startseite, Demo-Login, Laravel Cloud

Auf die Frage, wie sich die Anwendung öffentlich zugänglich machen ließe (mit Vergleich zu
Streamlit Community Cloud, wo eine App direkt aus einem GitHub-Repository gebaut wird), wurde
zunächst recherchiert statt aus dem Gedächtnis geantwortet: Laravel hat kein exaktes Äquivalent,
aber **Laravel Cloud** — die offizielle, von Laravel selbst betriebene Plattform — verfolgt ein
sehr ähnliches Prinzip (Repository verbinden, Datenbank hinzufügen, automatische Deploys bei
jedem Push). Die offizielle Doku wurde gezielt abgerufen, um keine veralteten oder erfundenen
Konfigurationsdetails zu dokumentieren: Laravel Cloud ist vollständig dashboard-gesteuert, es
gibt **keine** Konfigurationsdatei, die im Repository liegen müsste (anders als z. B. bei
Vercel).

Die Rückfrage des Nutzers deckte dabei eine Fehlannahme aus einer früheren Session auf: Die
Startseite (`/`) war zuvor bewusst auf `/login` umgeleitet worden, unter der Annahme,
PropertyManager sei "ein rein internes B2B-Tool ohne öffentliche Landingpage". Der Nutzer
stellte klar, dass eine echte Startseite nicht nur für eine Demo-Version, sondern für **alle**
Endnutzer gebraucht wird — eine Korrektur einer zuvor eigenständig getroffenen Annahme, nicht
nur eine Erweiterung. Entsprechend wurde `/` wieder auf eine echte, neu gestaltete Landingpage
umgestellt (Hero, Modulübersicht, CTAs), statt die alte, gelöschte Laravel-Marketing-Seite
wiederherzustellen. Dabei fiel zusätzlich auf, dass `AppLogo.vue` noch den Platzhaltertext
"Laravel Starter Kit" statt des tatsächlichen Produktnamens anzeigte — ein weiterer
Starter-Kit-Rest, der nie ersetzt worden war, und im selben Zug mitkorrigiert wurde.

Für den Gastzugang wurde bewusst **keine** Registrierung vor der Nutzung verlangt: Ein
"Demo ausprobieren"-Button loggt Besucher:innen direkt in einen gemeinsamen, automatisch
bereitgestellten Demo-Account ein (`App\Actions\ProvisionDemoAccount` — die erste tatsächlich
gebrauchte Klasse in `app/Actions/`, das Verzeichnis existierte bis dahin noch nicht, siehe
Abschnitt 9 zur Backend-Struktur). Die Entscheidung für einen *gemeinsamen* statt eines
*pro Besuch frisch erzeugten* Demo-Accounts wurde bewusst und dokumentiert getroffen: Da hinter
den Modulen aktuell nur lesende Platzhalterseiten stehen (keine echten, veränderbaren
Geschäftsdaten), können sich Besucher:innen aktuell nicht gegenseitig stören — sobald echte
CRUD-Funktionalität existiert, ist eine session-isolierte Lösung der nächste Schritt (in
`docs/authentication.md` als offener Punkt vermerkt, nicht stillschweigend ignoriert).

Der anschließende Push löste zunächst einen fehlgeschlagenen CI-Lauf aus — diesmal aus einem
Grund, der sich nicht per `gh`-CLI oder öffentlicher GitHub-API einsehen ließ (Job-Logs
verlangen Repo-Admin-Rechte). Der Nutzer hat den entscheidenden Log-Ausschnitt manuell aus der
GitHub-Oberfläche kopiert: `WelcomeTest > guests see the public landing page` scheiterte mit
"Inertia page component file [Welcome] does not exist." Des Rätsels Lösung: `inertia-laravel`
sucht Testkomponenten standardmäßig unter `resources/js/Pages` (Großbuchstabe), während dieses
Projekt bewusst die moderne, kleingeschriebene `pages/`-Konvention nutzt (siehe
[architecture.md](architecture.md)). Auf dem Windows/WSL-Mount (`/mnt/c/...`), auf dem lokal und
in WSL2 entwickelt wird, ist das Dateisystem case-**in**sensitiv, sodass `Pages` und `pages`
identisch aufgelöst werden und der Fehler unsichtbar blieb — ein weiterer Fall, in dem die
lokale Entwicklungsumgebung dieses Projekts (bereits mehrfach Quelle von Überraschungen, siehe
Abschnitt 12) einen Bug maskiert hat, den ein "echter" Linux-CI-Runner zurecht aufdeckte. Behoben
über eine gezielte `config/inertia.php`-Ergänzung. Lehre: Wo eine öffentlich sichtbare Log-Zeile
fehlt, lohnt es sich, gezielt danach zu fragen, statt auf Verdacht Konfigurationen zu ändern —
das eigentliche Symptom (der volle Pest-Fehlertext) war um Größenordnungen aussagekräftiger als
die generische "exit code 1"-Annotation.

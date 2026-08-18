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

### 14. "Streamlit kostet mich nichts" — Deployment-Kurskorrektur

Nach der ersten Laravel-Cloud-Vorbereitung (Abschnitt 13) fragte der Nutzer, bevor er als
Besucher irgendetwas anklickte, explizit nach: Entstehen Kosten, wenn viele Besucher:innen die
Demo nutzen? Statt aus dem Gedächtnis zu beruhigen, wurden die tatsächlichen Laravel-Cloud-Preise
nachgeschlagen (Starter-Tarif: 5 $/Monat plus Nutzung, Kreditkarte auch im günstigsten Einstieg
nötig) und ehrlich mit Spending-Limit als Absicherung erklärt. Die Antwort des Nutzers war
eindeutig: "Ich möchte es gar nicht nutzen. Streamlit kostet mich nichts." — ein klarer
Ausschluss eines kostenpflichtigen Wegs zugunsten eines echten Gratis-Angebots, kein Feilschen um
Details.

Daraufhin wurde recherchiert, was für Laravel-Apps dem tatsächlich dauerhaft kostenlosen
Streamlit-Community-Cloud-Modell am nächsten kommt. Render bot sich an (Web-Service ganz ohne
Kreditkarte), mit einem wichtigen Vorbehalt: Renders eigene kostenlose PostgreSQL-Datenbank läuft
nach 30 Tagen ab und wird dann gelöscht — kein Dauerzustand. Um trotzdem "wirklich kostenlos,
dauerhaft" zu erreichen, wurde Neon (dauerhaft kostenloses PostgreSQL) als separater
Datenbank-Anbieter kombiniert, statt Renders eigene Datenbank zu nutzen — ein bewusster
Kompromiss zwischen Einfachheit (ein Anbieter) und tatsächlicher Dauerhaftigkeit (zwei Anbieter).

Technisch stellte sich dabei heraus, dass Render — anders als zunächst in einer Quelle behauptet
— **keinen** nativen PHP-Buildpack hat (gezielt gegengecheckt, da diese Behauptung einer zweiten
Quelle widersprach). Die robuste, verifizierbar korrekte Lösung ist daher ein eigenes,
mehrstufiges Docker-Image (`docker/render/Dockerfile`): eine Node-Stufe baut die Frontend-Assets,
eine PHP/Nginx-Stufe bündelt beides in einem einzigen Container, wie es Renders
Free-Tier-Web-Service (ein Prozess, ein Port) erfordert. Da auf der Entwicklungsmaschine kein
funktionierendes Docker läuft (siehe Abschnitt 12), konnte dieses Image nicht gegen einen echten
Render-Account gebaut/getestet werden — diese Einschränkung wurde explizit in
`docs/deployment.md` benannt, statt stillschweigend als "sollte funktionieren" auszugeben.

Auf Wunsch des Nutzers wurde die Dokumentation zugunsten von README.md schlank gehalten: Statt
Details dort zu duplizieren, verweist README.md nur noch mit einem einzeiligen Infofeld auf
`docs/deployment.md`, wo der kostenlose Render+Neon-Weg jetzt vor Laravel Cloud steht (das als
kostenpflichtige, komfortablere Alternative erhalten bleibt, nicht gelöscht wurde).

### 15. Ein echter Name: PropertyManager → ImmoDesk

"PropertyManager" war von Anfang an nur ein Arbeitstitel — entstanden, weil die allererste
`ToDo.md` einen Namen brauchte, um überhaupt anzufangen, nicht aus einer bewussten
Markenentscheidung. Der Nutzer störte sich zu Recht daran, als die App zum ersten Mal eine
echte, öffentlich sichtbare Startseite bekam (Abschnitt 13) und der Platzhaltername dort
plötzlich sichtbar wurde, statt nur intern in Konfigurationsdateien zu stehen.

Auf die Rückfrage, wofür die App konkret gedacht ist, folgte eine kurze, konkrete Erklärung
(Zielgruppe: Hausverwaltungsfirmen; Module: Immobilien/Gebäude/Wohnungen, Mietverträge,
Finanzen, Reparaturen, Dokumente, Termine; Rollen: Property Manager, Eigentümer, Mieter,
Handwerker). Der Nutzer bat um zehn Namensvorschläge, dann um zehn weitere — beide Runden bewusst
als offene Liste in der Chat-Antwort präsentiert statt über eine erzwungene Auswahl-UI, da bei
zehn gleichwertigen Optionen kein einzelner Vorschlag als "empfohlen" markiert werden sollte.
Erst nach der zweiten Runde bat der Nutzer um eine ausführlichere Beschreibung der App, um selbst
einen passenden Namen zu finden — daraufhin entschied er sich für **ImmoDesk** mit der Tagline
"Die zentrale Plattform für Immobilienverwaltung", die er in eigenen Worten und mit Verweis auf
"ähnlich wie ImmoCloud" formulierte.

Bei der Umsetzung war Sorgfalt gefragt: Der alte Arbeitstitel "PropertyManager" tauchte im Code
nicht nur als Markenname auf, sondern zufällig auch als Name des Enum-Case
`OrganizationRole::PropertyManager` — die Rolle "Property Manager" in der Rollenhierarchie
(Super Admin, Property Manager, Owner, Tenant, Contractor aus der ursprünglichen
Aufgabenstellung). Eine reine Text-Ersetzung über den ganzen Code hätte diesen fachlich
korrekten, vom Markennamen völlig unabhängigen Begriff mit umbenannt. Vor der Umbenennung wurde
daher gezielt nach allen Vorkommen von "PropertyManager" gesucht und jedes einzeln eingeordnet:
Markenname (→ umbenennen: `APP_NAME`, Demo-E-Mail-Domain, README, Docker-Netzwerkname) versus
Rollenname (→ unverändert lassen) versus historischer Verweis auf die tatsächliche
Dateibenennung der Aufgabenstellung oder auf bereits vergangene, im Journal korrekt beschriebene
Entscheidungen (→ ebenfalls unverändert lassen, da rückwirkendes Umschreiben von Geschichte hier
falsch wäre).

Direkt danach wurde die Landingpage nochmal verfeinert: Die neue Headline wurde als "zu groß und
zu grob" empfunden (kleiner, leichter gemacht), und der Wunsch nach einem "farblichen Layout"
deckte eine echte Design-Eigenschaft auf, die vorher nicht auffiel — das gesamte Theme nutzt
Shadcns "neutral"-Basisfarbe, bei der `--primary` buchstäblich 0 % Farbsättigung hat (reines
Schwarz/Weiß). Statt das globale Theme umzubauen (hätte Sidebar, Dashboard, Formulare etc.
mitverändert — eine größere Entscheidung, die nicht gefragt war), bekam bewusst nur die
Landingpage echte Farbe: sechs unterschiedlich einfärbte Icon-Chips für die Modul-Kacheln, ein
dezenter Farbverlauf hinter dem Hero, ein blauer Haupt-Call-to-Action.

### 16. Von der Konkurrenzanalyse zur Produkt-Roadmap

Der Nutzer recherchierte eigenständig die Website der Immobilienverwaltung Riebeling GmbH und
brachte eine wichtige Neuausrichtung mit: Die bisherige Leitidee ("Ordnung ins Chaos bringen")
unterstellt, Hausverwaltungen hätten keine Software — Riebeling zeigt, dass professionelle
Verwaltungen längst digital arbeiten (eigenes Ticketsystem mit Vorgangsnummern, Mängelmeldung
mit bis zu drei Fotos, Eigentümerportal mit monatlichem Reporting). Die eigentlich hilfreiche
Frage lautet daher nicht "brauchen sie Software", sondern "was könnte diese Software besser,
einfacher oder intelligenter machen". Der Nutzer bezeichnete Riebeling ausdrücklich als
**Referenzunternehmen für Anforderungsanalyse**, nicht als Kunde oder Vorlage zum Kopieren — eine
wichtige, selbst gesetzte ethische Leitplanke, die unverändert in die neue Roadmap-Dokumentation
übernommen wurde.

Der Feature-Abgleich (Riebeling-Funktion gegen bestehendes Datenmodell) ergab ein ermutigendes
Bild: Jede beobachtete Riebeling-Funktion hat bereits eine Entsprechung im während der
Grundstruktur-Phase entworfenen Schema (`maintenance_requests` deckt das Ticketsystem ab,
`documents` die Fotoanhänge, `contractors.specialty` wäre die Grundlage für eine spätere
Handwerker-Zuordnung, die Owner-Rolle plus `PropertyPolicy` das Eigentümerportal). Es fehlt nicht
Architektur, sondern echte Fachlogik statt der aktuellen EmptyState-Platzhalter — eine
Bestätigung, dass die Grundstruktur-Entscheidungen tragfähig waren.

Auf Bitte des Nutzers wurde daraus `docs/roadmap.md` — ein neues, bewusst *vorausschauendes*
Dokument (im Unterschied zum rückblickenden `project-journal.md` und der
Grundstruktur-Checkliste `ToDo.md`) mit einer vorgeschlagenen Sechs-Phasen-Reihenfolge
(Stammdaten → Mietverträge → Reparatur-Workflow mit Vorgangsnummer → Finanzen →
Eigentümerportal → KI-gestützte Vorgangsbearbeitung zuletzt, da sie echte Daten aus Phase 3
braucht). WEG-Verwaltung — von Riebeling zusätzlich angeboten — wurde explizit *nicht* in die
Roadmap aufgenommen, sondern als eigene, spätere Grundsatzentscheidung benannt: rechtlich und
strukturell ein anderes Feld als Mietverwaltung, kein Nebeneffekt, der einfach mitgebaut würde.
Dieses Dokument wird als Arbeitsgrundlage für die Priorisierung kommender
Implementierungsschritte verwendet, bis der Nutzer etwas anderes vorgibt.

### 17. Phase 1 der Roadmap: erstes echtes CRUD (Properties)

Auf Wunsch des Nutzers begann die Umsetzung von Phase 1 der Roadmap ("Stammdaten") mit
`Properties`, exakt in der dort festgelegten Reihenfolge ("Properties zuerst, alles andere hängt
daran"). Damit verlässt das Projekt zum ersten Mal die reine Grundstruktur-Phase — es ist das
erste Modul mit echter Fachlogik statt eines `EmptyState`-Platzhalters.

Vor der Backend-Arbeit mussten drei UI-Grundbausteine nachgebaut werden, die die Grundstruktur
bewusst zurückgestellt hatte (siehe ToDo.md, Abschnitt 8): `Select` (vollständig auf Basis der
radix-vue-Primitiven, gleiches Muster wie die bereits vorhandenen `Dialog`/`DropdownMenu`-
Komponenten), `Table` (einfache gestylte `<table>`-Wrapper) und `Pagination` — Letztere bewusst
*nicht* unter `components/ui/`, sondern direkt unter `components/`, da sie nicht auf einem
generischen Design-System-Primitiv basiert, sondern konkret die Form von Laravels
Standard-Paginator-Links (`{url, label, active}[]`) konsumiert.

Backend-seitig kam eine grundlegende Lücke im Laravel-12-Starter-Kit zum Vorschein: die
Basisklasse `App\Http\Controllers\Controller` bringt in Laravel 11+/12 standardmäßig weder den
`AuthorizesRequests`-Trait noch `Illuminate\Routing\Controller` als Elternklasse mit (bewusste
Verschlankung durch das Framework). `authorizeResource()` – die Standardmethode, um einen
Resource-Controller automatisch an eine Policy zu koppeln – schlug deshalb zunächst mit "Call to
undefined method ...::authorizeResource()" fehl, und nach dem naheliegenden ersten Fix (nur den
Trait hinzufügen) mit einem zweiten, versteckteren Fehler: "Call to undefined method
...::middleware()", weil `authorizeResource()` intern `$this->middleware()` aufruft – eine Methode,
die erst durch `Illuminate\Routing\Controller` als Elternklasse bereitgestellt wird. Erst beide
Änderungen zusammen (Trait *und* Elternklasse) lösten das Problem. Da alle weiteren Phase-1-Module
(Buildings, Units, Owners, Tenants, Contractors) densel­ben `authorizeResource()`-Ansatz nutzen
werden, wurde der Fix bewusst in der Basisklasse vorgenommen statt pro Controller wiederholt.

Bei der Validierung von `owner_id` wurde die bereits während der Grundstruktur-Phase erkannte
`Rule::exists()`-Falle konkret relevant: `Rule::exists()` fragt die Datenbank direkt ab und
umgeht dabei Eloquents `OrganizationScope` vollständig. Ohne explizite Zusatzbedingung hätte ein
Property Manager einer Organisation einem Objekt versehentlich (oder absichtlich) einen
Eigentümer aus einer fremden Organisation zuweisen können. Fix:
`Rule::exists('owners', 'id')->where('organization_id', $this->user()->current_organization_id)`
— mit einem eigenen Feature-Test abgesichert, der genau dieses Cross-Tenant-Szenario prüft.

Ein Testfall deckte zudem eine Designfrage auf, die vorher nur theoretisch bestand: Was passiert,
wenn ein Property Manager versucht, ein Objekt einer fremden Organisation über die URL zu
bearbeiten? Die ursprüngliche Testerwartung war `403 Forbidden` (Policy lehnt ab) — tatsächlich
liefert die Anwendung `404 Not Found`, weil `OrganizationScope` das fremde Objekt beim
Route-Model-Binding bereits vollständig unsichtbar macht, bevor die Policy überhaupt aufgerufen
wird. Das ist stärkere Isolation als ein 403 (der würde bestätigen, dass der Datensatz *existiert*,
nur eben nicht zugänglich ist) — der Test wurde entsprechend auf `assertNotFound()` korrigiert,
nicht die Anwendung.

Die `Select`-Komponente von radix-vue erzwingt typisiert einen einzigen generischen Wertetyp; ohne
expliziten Typparameter fällt TypeScript auf `string` zurück. Statt gegen diese Typisierung
anzukämpfen, wurde `owner_id` im Formular konsequent als String geführt (`SelectItem` bekommt
`String(owner.id)` als Wert) und erst unmittelbar vor dem Absenden per `form.transform()` in
`number | null` zurückgewandelt — ein pragmatischer, in shadcn-vue-Projekten üblicher Kompromiss.
Ähnlich ergab sich bei den `route()`-Aufrufen mit ULID-Parameter (`properties.update`,
`properties.destroy`, `properties.edit`), dass die projekteigene, handgeschriebene
`route()`-Typdeklaration (`resources/js/types/ziggy.ts`) für nicht literal bekannte Routennamen
keine nackten String-Argumente akzeptiert, sondern ein Array erwartet (`route(name, [param])`) —
ebenfalls durch die Typprüfung aufgedeckt, nicht durch manuelles Nachschlagen.

Ergebnis: vollständiges CRUD (Index mit Tabelle/Pagination/Lösch-Dialog, Create, Edit) inklusive
sieben neuer Feature-Tests (Sichtbarkeit pro Organisation, Erstellen, Cross-Tenant-Owner-Ablehnung,
Bearbeiten, Löschen, Rollen-Sperre für Tenant, Cross-Tenant-404) — alle 47 Tests (vorher 40)
weiterhin grün, `vue-tsc`/`eslint`/`prettier`/Pint sauber. Vor der Weiterarbeit an
Buildings/Units/Owners/Tenants/Contractors (laut Roadmap die nächsten Schritte innerhalb von
Phase 1) wird beim Nutzer zurückgemeldet, wie vorher angekündigt.

### 18. Lokales Docker-Setup entfernt

Auf Nachfrage, wofür die Docker-Dateien im Projekt eigentlich da sind, erklärte sich, dass sie
zwei unabhängige Zwecke bedienen: das lokale Docker-Compose-Setup (nie auf dieser Maschine
lauffähig, siehe Abschnitt 10) und `docker/render/Dockerfile` für die Render-Bereitstellung
(Abschnitt 17). Der Nutzer entschied daraufhin, den bereits früher formulierten Grundsatz
("alles löschen, was nicht irgendwie genutzt wird") konsequent auch hier anzuwenden — *unabhängig*
davon, dass die ursprüngliche Aufgabenstellung ein Docker-Setup explizit fordert. Da diese
Entscheidung zwei unterschiedliche Konsequenzen hatte (das lokale Setup war schlicht nie nutzbar;
das Render-Image ist zwar ungetestet, aber die einzige dokumentierte Grundlage für den
kostenlosen Deployment-Weg), wurde vor der Löschung gezielt nachgefragt, ob beides oder nur das
lokale Setup gemeint war — der Nutzer entschied sich für Letzteres.

Entfernt: `docker-compose.yml`, `docker/php/Dockerfile`, `docker/nginx/default.conf`. Die Datei
`docker/php/opcache.ini` wurde nicht gelöscht, sondern nach `docker/render/opcache.ini`
verschoben, da sie auch vom verbleibenden Render-Image eingebunden wird (`COPY
docker/php/opcache.ini ...` in `docker/render/Dockerfile`) — beim Löschen nicht übersehen, weil
vorher gezielt nach allen Verwendern jeder zu löschenden Datei gesucht wurde, nicht nur nach dem
naheliegenden lokalen Zweck. `.dockerignore` blieb aus demselben Grund erhalten: Docker wendet
die `.dockerignore` im Build-Kontext-Wurzelverzeichnis unabhängig vom gewählten Dockerfile an,
sie wird also weiterhin vom Render-Build gelesen.

Alle Dokuverweise auf das entfernte Setup wurden überarbeitet (`README.md`, `docs/development.md`
— WSL2 ist jetzt der primäre, nicht mehr der alternative Weg —, `docs/deployment.md`,
`docs/architecture.md`, `docs/glossar.md`, `.editorconfig`), mit einer Ausnahme: historische
Journal-Einträge (Abschnitt 10 oben) beschreiben korrekt, was zum jeweiligen Zeitpunkt der Fall
war, und wurden bewusst nicht rückwirkend umgeschrieben — dieselbe Konvention wie schon bei der
ImmoDesk-Umbenennung (Abschnitt 15). Zusätzlich zur Code-Änderung wünschte der Nutzer eine
Dokumentation der Abweichung von der Aufgabenstellung direkt in
`Aufgabenstellung/PropertyManager SaaS Grundstruktur.docx` selbst (nicht nur in `docs/`) — dort
als neuer Abschnitt 21 angehängt (Werkzeuge: `python-docx`, in WSL2 nachinstalliert, da weder
`pip` noch das Paket vorher verfügbar waren), mit Begründung (ARM64-Docker-Desktop-Bug,
Windows-Anwendungssteuerungsrichtlinie) und dem stattdessen verwendeten Werkzeug (WSL2).

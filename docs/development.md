# Entwicklung

## Voraussetzungen

- PHP 8.4+, Composer, Node 22+, PostgreSQL 16
- **Kein Docker** für die lokale Entwicklung — siehe
  [Warum kein Docker für die lokale Entwicklung?](#warum-kein-docker-für-die-lokale-entwicklung)
  weiter unten. Die App läuft nativ, empfohlen über **WSL2** (Windows) bzw. direkt nativ
  (macOS/Linux). Ein Docker-Image existiert weiterhin, aber nur für die öffentliche
  Bereitstellung auf Render — siehe [deployment.md](deployment.md).

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# PostgreSQL-Zugangsdaten in .env anpassen (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
php artisan migrate
npm run build   # oder: npm run dev (für HMR während der Entwicklung)
php artisan serve
```

Unter Windows: siehe [PHP + PostgreSQL nativ in WSL2](#php--postgresql-nativ-in-wsl2) für die
genauen Schritte (einmalige WSL2-Einrichtung, PostgreSQL-Installation, Netzwerktücken).

## Qualitätssicherung vor jedem Commit

```bash
npx vue-tsc --noEmit     # TypeScript
npx eslint .              # Lint
npm run build              # Produktions-Build
php vendor/bin/pint --test  # PHP Code-Style
php artisan test              # Pest-Tests
```

## PHP + PostgreSQL nativ in WSL2

Empfohlener Weg unter Windows: Die Backend-Toolchain läuft komplett innerhalb von WSL2, nicht
unter nativem Windows. Das ist eine vollwertige, getestete lokale Entwicklungsumgebung — nicht
Docker-basiert, siehe [Warum kein Docker für die lokale
Entwicklung?](#warum-kein-docker-für-die-lokale-entwicklung) unten für den Hintergrund.

**Einmalige Einrichtung** (PowerShell **als Administrator** für die ersten zwei Befehle):

```powershell
wsl --install                     # installiert WSL2 + Ubuntu, danach Neustart nötig
```

Nach dem Neustart, wieder in einer (diesmal nicht-elevierten) PowerShell bzw. direkt in Ubuntu:

```bash
sudo apt-get update
sudo apt-get install -y postgresql postgresql-contrib \
    php-cli php-pgsql php-sqlite3 php-mbstring php-xml php-curl php-zip php-bcmath php-gd php-intl \
    composer

sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD 'secret';"
sudo -u postgres psql -c "CREATE DATABASE housing_management_saas;"
```

PostgreSQL startet danach automatisch mit jedem WSL-Start (systemd-Service, standardmäßig
aktiviert). Node/npm können wahlweise ebenfalls in WSL laufen oder — unproblematisch, da ohne
DB-Zugriff — weiterhin unter Windows (`npm run dev` für Vite/HMR).

**Projekt einrichten** (Befehle innerhalb von WSL, Projekt über `/mnt/c/...` erreichbar):

```bash
cd "/mnt/c/Users/<dein-windows-benutzer>/OneDrive/Skills_Update/housing-management-saas"
composer install
php artisan key:generate
```

`.env` anpassen — PHP und PostgreSQL laufen beide in **derselben** WSL2-VM, daher reicht die
normale Loopback-Adresse:

```dotenv
DB_HOST=127.0.0.1
```

```bash
php artisan migrate
php artisan test               # sollte vollständig grün sein
php artisan serve --host=0.0.0.0 --port=8000
```

Die App ist danach unter `http://127.0.0.1:8000` **im Windows-Browser** erreichbar (WSL2 leitet
eingehende Verbindungen von Windows automatisch weiter).

**Testnutzer anlegen** (einmalig, für manuelles Durchklicken der App):

```bash
php artisan tinker
```
```php
$org = \App\Models\Organization::create(['name' => 'Demo Hausverwaltung', 'slug' => 'demo']);
$user = \App\Models\User::create([
    'name' => 'Demo Manager', 'email' => 'demo@example.com',
    'password' => bcrypt('password'), 'email_verified_at' => now(),
    'current_organization_id' => $org->id,
]);
$org->users()->attach($user, ['role' => \App\Enums\OrganizationRole::PropertyManager->value]);
```

### Bekannte WSL2-Netzwerktücke

Verbindungen **von Windows nach WSL2** (z. B. Windows-natives `php.exe` → Postgres in WSL) können
nach einer Docker-Desktop-Installation/-Deinstallation oder nach dem Schlafmodus instabil werden
— Windows-Tools zeigen den Port dann fälschlich als offen, native Programme wie `php.exe`
scheitern trotzdem mit "Connection refused". Abhilfe: WSL2 komplett neu starten.

```powershell
wsl --shutdown
wsl -d Ubuntu -- echo "wieder da"
```

Dieses Problem betrifft **nicht** den empfohlenen Aufbau oben (PHP läuft dort selbst in WSL2 und
spricht mit Postgres über den Linux-eigenen Netzwerkstack, ganz ohne die Windows-Grenze zu
queren) — es trat nur während der Fehlersuche auf, als versucht wurde, natives Windows-PHP mit
einer in WSL2 laufenden Datenbank zu verbinden.

## Warum kein Docker für die lokale Entwicklung?

Die ursprüngliche Aufgabenstellung sah ein Docker-Compose-Setup für die lokale Entwicklung vor
(`docker-compose.yml`, `docker/php/Dockerfile`, `docker/nginx/`). Das wurde aus dem Projekt
entfernt, da es auf der tatsächlichen Entwicklungsmaschine nie lauffähig war — Details dazu sind
zusätzlich in `Aufgabenstellung/PropertyManager SaaS Grundstruktur.docx` (Abschnitt 21)
festgehalten. Der Windows-Rechner, auf dem dieses Grundgerüst erstellt wurde, läuft auf
**ARM64**. Zwei unabhängige Blocker wurden hier gefunden:

1. **Eine Windows-Anwendungssteuerungsrichtlinie** blockiert die Ausführung mancher neu
   installierter nativer Binaries (u. a. PHP-SQLite-Erweiterungen, PostgreSQLs `initdb.exe`)
   sowie zeitweise auch ausgehende Netzwerkverbindungen einzelner nativer Programme
   (`php.exe`, `curl.exe`) — betrifft ausschließlich unter **nativem Windows** laufende
   Programme.
2. **Docker Desktop startet auf diesem ARM64-Windows-Build nicht**: Es benötigt für sein
   Datenlaufwerk `wsl.exe --mount`, was laut Fehlermeldung Windows-Build 27653+ voraussetzt
   (installiert: 26200) — ein seit Jahren offenes, plattformweites Problem
   (siehe z. B. [docker/for-win#14110](https://github.com/docker/for-win/issues/14110),
   [#14689](https://github.com/docker/for-win/issues/14689),
   [#14821](https://github.com/docker/for-win/issues/14821)), keine Eigenheit dieser Maschine.

**Lösung:** PHP, Composer und PostgreSQL laufen komplett innerhalb von WSL2/Ubuntu (siehe oben)
— das umgeht Blocker 1 (kein natives Windows-Binary beteiligt) und macht Blocker 2 gegenstandslos
(kein Docker nötig). Damit laufen `php artisan migrate` und `php artisan test` vollständig
erfolgreich, inklusive eines echten End-to-End-Logins im Browser.

Ein Docker-Image existiert im Projekt trotzdem noch: `docker/render/Dockerfile` — das dient
aber ausschließlich der öffentlichen Bereitstellung auf Render (siehe
[deployment.md](deployment.md)), nicht der lokalen Entwicklung.

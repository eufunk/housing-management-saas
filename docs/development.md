# Entwicklung

## Voraussetzungen

- Docker Desktop (empfohlen — startet PHP, Nginx, PostgreSQL, Redis und Node in einem Schritt)
- Alternativ lokal: PHP 8.4+, Composer, Node 22+, PostgreSQL 16, Redis
- Auf **Windows-on-ARM64** funktioniert Docker Desktop derzeit nicht (siehe unten) — dort
  PHP + PostgreSQL nativ in WSL2 installieren, siehe
  [Alternative: PHP + PostgreSQL nativ in WSL2](#alternative-php--postgresql-nativ-in-wsl2-ohne-docker)

## Setup mit Docker (empfohlen)

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Die Anwendung läuft danach unter `http://localhost:8080`, der Vite-Dev-Server (HMR) unter
`http://localhost:5173`.

Nützliche Befehle:

```bash
docker compose exec app php artisan tinker      # REPL
docker compose exec app php artisan test        # Tests (Pest)
docker compose exec app vendor/bin/pint         # Code-Style prüfen/fixen
docker compose logs -f app                       # Logs
docker compose down                                # Stoppen
```

Enthaltene Services (`docker-compose.yml`):

| Service     | Zweck                                              |
| ------------ | ---------------------------------------------------- |
| `app`         | PHP-FPM (Laravel)                                      |
| `nginx`         | Webserver, Port `8080` → `80`                            |
| `queue`           | `php artisan queue:work`                                   |
| `scheduler`         | `php artisan schedule:work`                                  |
| `node`                | Vite-Dev-Server, Port `5173`                                    |
| `pgsql`                 | PostgreSQL 16, Port `5432`                                        |
| `redis`                   | Redis 7, Port `6379`                                                |

## Setup ohne Docker

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

## Qualitätssicherung vor jedem Commit

```bash
npx vue-tsc --noEmit     # TypeScript
npx eslint .              # Lint
npm run build              # Produktions-Build
php vendor/bin/pint --test  # PHP Code-Style
php artisan test              # Pest-Tests
```

## Alternative: PHP + PostgreSQL nativ in WSL2 (ohne Docker)

Auf **Windows-on-ARM64**-Rechnern kann Docker Desktop aktuell nicht starten (siehe Abschnitt
unten) — die Backend-Toolchain läuft dort stattdessen komplett innerhalb von WSL2, nicht unter
nativem Windows. Das ist kein Fallback zweiter Klasse, sondern eine vollwertige, getestete
lokale Entwicklungsumgebung; sie eignet sich auch für alle, die lieber ganz ohne Docker
arbeiten.

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

`.env` anpassen — anders als beim Docker-Setup (`DB_HOST=pgsql`) läuft hier alles in **derselben**
WSL2-VM, daher reicht die normale Loopback-Adresse:

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

## Bekannter Zustand dieser Umgebung (Entwicklungsmaschine)

Der Windows-Rechner, auf dem dieses Grundgerüst erstellt wurde, läuft auf **ARM64**. Zwei
unabhängige Blocker wurden hier gefunden und gelöst bzw. umgangen:

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
erfolgreich (22 Migrationen, 36/36 Tests), inklusive eines echten End-to-End-Logins im Browser.

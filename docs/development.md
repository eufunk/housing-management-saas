# Entwicklung

## Voraussetzungen

- Docker Desktop (empfohlen — startet PHP, Nginx, PostgreSQL, Redis und Node in einem Schritt)
- Alternativ lokal: PHP 8.4+, Composer, Node 22+, PostgreSQL 16, Redis

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

## Bekannter Zustand dieser Umgebung (Entwicklungsmaschine)

Auf dem Windows-Rechner, auf dem dieses Grundgerüst erstellt wurde, ist Docker nicht
installiert und eine Anwendungssteuerungsrichtlinie blockiert sowohl die PHP-SQLite-
Erweiterungen als auch die lokale PostgreSQL-Installation (`initdb.exe`). Dadurch konnten
Migrationen und Tests dort nicht gegen eine echte Datenbank ausgeführt werden — PHP-Syntax,
TypeScript, ESLint, Frontend-Build und Pint laufen jedoch vollständig fehlerfrei. Auf einer
Maschine mit funktionierendem Docker (oder freigegebener lokaler PostgreSQL-Installation)
sollten `php artisan migrate` und `php artisan test` ohne weitere Anpassungen funktionieren.

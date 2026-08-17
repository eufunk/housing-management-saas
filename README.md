# PropertyManager

Modernes SaaS-Grundgerüst für professionelle Haus- und Immobilienverwaltung — Laravel 12,
Vue 3 + TypeScript (Inertia.js), Tailwind CSS, PostgreSQL. Multi-Tenant-fähig von Grund auf.

Dies ist die **Grundstruktur**: Architektur, Datenbankschema, Multi-Tenancy, Rollen/Policies,
zentrale Layouts/UI-Komponenten und ein Dashboard-Prototyp mit Mock-Daten. Die eigentliche
Fachlogik (Immobilien-, Mietvertrags-, Zahlungsverwaltung etc.) wird darauf aufbauend
schrittweise implementiert.

## Stack

- **Backend**: PHP 8.4+, Laravel 12, Inertia.js
- **Frontend**: Vue 3 (`<script setup lang="ts">`), TypeScript, Tailwind CSS, Vite
- **Datenbank**: PostgreSQL 16
- **Infrastruktur**: Redis, Docker Compose, S3-kompatibler Storage (vorbereitet)
- **Tests**: Pest 3

## Schnellstart

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

App: `http://localhost:8080` · Vite-Dev-Server: `http://localhost:5173`

Ohne Docker sowie weitere Befehle (Tests, Code-Style, ...): siehe
[docs/development.md](docs/development.md).

## Dokumentation

| Dokument                                             | Inhalt                                          |
| ------------------------------------------------------- | -------------------------------------------------- |
| [docs/architecture.md](docs/architecture.md)               | Stack-Entscheidungen, Backend-/Frontend-Konventionen |
| [docs/database.md](docs/database.md)                         | Schema, Konventionen, Tabellenübersicht                |
| [docs/authentication.md](docs/authentication.md)               | Auth-Flow                                                |
| [docs/authorization.md](docs/authorization.md)                   | Rollen, Policies, Gates                                    |
| [docs/multi-tenancy.md](docs/multi-tenancy.md)                     | Mandantenmodell und Isolationsstrategie                       |
| [docs/development.md](docs/development.md)                           | Setup, Docker-Befehle, QA-Checks                                 |
| [docs/project-journal.md](docs/project-journal.md)                     | Aufgabenstellung und Herangehensweise (Projektdoku)               |

## Rollen

Super Admin (plattformweit) · Property Manager · Owner · Tenant · Contractor
(organisationsgebunden) — siehe [docs/authorization.md](docs/authorization.md).

## Projektstatus

Siehe [ToDo.md](ToDo.md) für den aktuellen Umsetzungsstand der Grundstruktur.

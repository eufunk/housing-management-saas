# ImmoDesk

**Digitale Betriebs- und Verwaltungsplattform für Hausverwaltungen.** Für die laufende
Verwaltung bereits vermieteter/verkaufter Objekte gedacht (Mietverträge, Zahlungen, Reparaturen,
Dokumente) — nicht für Makler bzw. die Vermittlung neuer Miet-/Kaufinteressenten. Laravel 12,
Vue 3 + TypeScript (Inertia.js), Tailwind CSS, PostgreSQL. Multi-Tenant-fähig von Grund auf.

Dies ist die **Grundstruktur**: Architektur, Datenbankschema, Multi-Tenancy, Rollen/Policies,
zentrale Layouts/UI-Komponenten und ein Dashboard-Prototyp mit Mock-Daten. Die eigentliche
Fachlogik (Immobilien-, Mietvertrags-, Zahlungsverwaltung etc.) wird darauf aufbauend
schrittweise implementiert.

## Stack

- **Backend**: PHP 8.4+, Laravel 12, Inertia.js
- **Frontend**: Vue 3 (`<script setup lang="ts">`), TypeScript, Tailwind CSS, Vite
- **Datenbank**: PostgreSQL 16
- **Infrastruktur**: Redis (vorbereitet), S3-kompatibler Storage (vorbereitet)
- **Tests**: Pest 3

## Schnellstart

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# PostgreSQL-Zugangsdaten in .env anpassen
php artisan migrate
npm run build
php artisan serve
```

App: `http://localhost:8000` · für Hot-Reload während der Entwicklung: `npm run dev`

**Unter Windows** empfohlen über WSL2 (native Windows-Installation ist ebenfalls möglich, aber
ungetestet) — genaue Schritte siehe
[docs/development.md](docs/development.md#php--postgresql-nativ-in-wsl2).

Kein Docker für die lokale Entwicklung nötig (Hintergrund dazu sowie weitere Befehle wie Tests,
Code-Style, ...: siehe [docs/development.md](docs/development.md)). Ein Docker-Image existiert
weiterhin ausschließlich für die öffentliche Bereitstellung auf Render, siehe unten.

> **Öffentliche Bereitstellung / Gastzugang:** siehe [docs/deployment.md](docs/deployment.md).

## Dokumentation

| Dokument                                             | Inhalt                                          |
| ------------------------------------------------------- | -------------------------------------------------- |
| [docs/architecture.md](docs/architecture.md)               | Stack-Entscheidungen, Backend-/Frontend-Konventionen |
| [docs/database.md](docs/database.md)                         | Schema, Konventionen, Tabellenübersicht                |
| [docs/authentication.md](docs/authentication.md)               | Auth-Flow, Demo-/Gastzugang                              |
| [docs/authorization.md](docs/authorization.md)                   | Rollen, Policies, Gates                                    |
| [docs/multi-tenancy.md](docs/multi-tenancy.md)                     | Mandantenmodell und Isolationsstrategie                       |
| [docs/development.md](docs/development.md)                           | Setup (WSL2), QA-Checks                                          |
| [docs/deployment.md](docs/deployment.md)                                | Öffentliche Bereitstellung (Render/Neon, Laravel Cloud), Gastzugang    |
| [docs/project-journal.md](docs/project-journal.md)                        | Aufgabenstellung und Herangehensweise (Projektdoku)                  |
| [docs/glossar.md](docs/glossar.md)                                           | Glossar: Tech-Stack und Fachbegriffe erklärt                           |
| [docs/roadmap.md](docs/roadmap.md)                                              | Produkt-Roadmap: priorisierte nächste Implementierungsschritte            |
| [docs/status.md](docs/status.md)                                                    | Status-Snapshot gegenüber dem ursprünglichen 17-Phasen-Plan                  |
| [docs/testing/](docs/testing/)                                                     | Manuelle UI-Testfälle pro Modul (z. B. Immobilien)                          |

## Rollen

Super Admin (plattformweit) · Property Manager · Owner · Tenant · Contractor
(organisationsgebunden) — siehe [docs/authorization.md](docs/authorization.md). Öffentliche
Besucher:innen können zusätzlich ohne Konto den Demo-Zugang nutzen (Rolle Property Manager im
gemeinsamen Demo-Account).

## Projektstatus

Siehe [ToDo.md](ToDo.md) für den Umsetzungsstand der Grundstruktur und
[docs/status.md](docs/status.md) für den aktuellen Fachlogik-Fortschritt gegenüber dem
ursprünglichen 17-Phasen-Plan.

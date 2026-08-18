# Datenbank

PostgreSQL 16. Verbindung wird über `.env` konfiguriert (`DB_CONNECTION=pgsql`), siehe
[development.md](development.md) für lokales Setup.

## Konventionen

- **Primärschlüssel**: `id` (Auto-Increment `bigint`), intern für Foreign Keys/Joins.
- **Öffentlicher Identifikator**: zusätzliche `ulid`-Spalte (unique, via
  `App\Models\Concerns\HasUlid`), gedacht für URLs/APIs, damit niemals fortlaufende
  interne IDs (und damit indirekt Datenmengen anderer Organisationen) über die URL erraten
  werden können. `HasUlid::getRouteKeyName()` sorgt dafür, dass implizites Route-Model-Binding
  automatisch die `ulid`-Spalte statt `id` verwendet.
- **Foreign Keys**: `constrained()->cascadeOnDelete()` für "gehört zwingend zu"-Beziehungen
  (z. B. `unit → building`), `nullOnDelete()` für optionale Referenzen (z. B.
  `property.owner_id`).
- **Timestamps**: `created_at`/`updated_at` auf allen Tabellen außer `organization_user`
  (Pivot, nur Zeitpunkt der Mitgliedschaft relevant) und `activity_logs`
  (nur `created_at`, Logs werden nicht nachträglich geändert).
- **Soft Deletes**: auf allen fachlichen Tabellen mit eigenem Lebenszyklus (Properties, Leases,
  Payments, ...). **Nicht** auf `organization_user` (Pivot — Mitgliedschaft wird hart entfernt)
  und `maintenance_comments`/`activity_logs` (unveränderliche Log-Einträge).
- **Unique Constraints**: u. a. `organizations.slug`, `(organization_id, user_id)` auf
  `organization_user`, `(building_id, unit_number)` auf `units`, `invoices.invoice_number`.

## Tabellenübersicht

| Tabelle                | Zweck                                                              |
| ------------------------ | -------------------------------------------------------------------- |
| `users`                   | Nutzerkonten (plattformweit, s. `is_super_admin`)                     |
| `organizations`            | Mandanten (Hausverwaltungsfirmen)                                     |
| `organization_user`         | Mitgliedschaft + Rolle (Pivot)                                         |
| `properties`                 | Immobilien                                                              |
| `buildings`                   | Gebäude innerhalb einer Immobilie                                       |
| `units`                        | Wohnungen innerhalb eines Gebäudes                                       |
| `owners`                         | Eigentümer (optional mit User-Account verknüpft)                          |
| `tenants`                          | Mieter (optional mit User-Account verknüpft)                                |
| `contractors`                        | Dienstleister (optional mit User-Account verknüpft)                           |
| `leases`                               | Mietverträge                                                                     |
| `payments`                                | Zahlungen zu einem Mietvertrag                                                     |
| `invoices`                                  | Rechnungen (Immobilie und/oder Mietvertrag)                                          |
| `expenses`                                    | Ausgaben (Immobilie und/oder Gebäude)                                                  |
| `document_categories`                            | Dokumentkategorien                                                                       |
| `documents`                                        | Dokumente, polymorph anhängbar (`documentable`)                                            |
| `maintenance_requests`                                | Reparaturaufträge                                                                             |
| `maintenance_comments`                                  | Kommentare zu Reparaturaufträgen                                                                |
| `appointments`                                             | Termine, polymorph verknüpfbar (`appointmentable`)                                               |
| `activity_logs`                                              | Audit-Log, polymorph (`subject`)                                                                   |

Vollständige Spaltenlisten: `database/migrations/`. Beziehungen: `app/Models/*.php`.

## Migrationen ausführen

```bash
php artisan migrate
```

Verifiziert: alle 22 Migrationen laufen sauber gegen eine echte PostgreSQL-16-Instanz durch,
ebenso die vollständige Test-Suite (`php artisan test`, 36/36 grün) und ein realer
End-to-End-Login. Auf Windows-on-ARM64-Rechnern, wo weder Docker noch eine native
PostgreSQL-Installation funktionieren (siehe [development.md](development.md)), laufen PHP und
PostgreSQL dafür innerhalb von WSL2 statt unter nativem Windows.

# Multi-Tenancy

## Modell

```
Organization
  └─┬─ organization_user (Pivot: role je Mitgliedschaft)
    └── User

Organization
  ├── Property → Building → Unit
  ├── Owner, Tenant, Contractor
  ├── Lease → Payment, Invoice
  ├── Expense
  ├── MaintenanceRequest → MaintenanceComment
  ├── Document, DocumentCategory
  ├── Appointment
  └── ActivityLog
```

- **`organizations`**: eine Zeile pro Mandant (Hausverwaltungsfirma).
- **`organization_user`**: Many-to-Many zwischen `users` und `organizations` mit einer
  `role`-Spalte (`property_manager` | `owner` | `tenant` | `contractor`,
  siehe `App\Enums\OrganizationRole`). Ein Nutzer kann Mitglied mehrerer Organisationen sein
  — z. B. ein Eigentümer, der Wohnungen bei zwei verschiedenen Verwaltungen hat, oder ein
  Contractor, der für mehrere Hausverwaltungen arbeitet.
- **`users.current_organization_id`**: die aktuell aktive Organisation im Kontext einer
  Session. Bestimmt, welche Daten ein Nutzer sieht, wenn er in mehreren Organisationen
  Mitglied ist. Ein UI zum Wechseln der aktiven Organisation existiert noch nicht.
- **`users.is_super_admin`**: Plattformweite Rolle, **nicht** an eine Organisation gebunden.
  Super Admins verwalten die gesamte SaaS-Plattform, siehe [authorization.md](authorization.md).

Jede fachliche Tabelle (`properties`, `leases`, `documents`, ...) trägt eine eigene
`organization_id`-Spalte. Es gibt bewusst **keine** implizite Ableitung der Organisation über
Umwege (z. B. über die Beziehungskette `unit → building → property → organization`), damit
jede Query direkt und ohne Join isoliert werden kann.

## Durchsetzung der Isolation

Isolation passiert **nicht** nur in Controllern, sondern zentral auf Query-Ebene:

1. **`App\Models\Concerns\BelongsToOrganization`** (Trait): wird von jedem organisationsgebundenen
   Model genutzt. Registriert `App\Models\Scopes\OrganizationScope` als globalen Scope und
   stempelt `organization_id` beim Erstellen automatisch mit der aktiven Organisation des
   eingeloggten Nutzers — **`organization_id` ist bewusst nicht in `$fillable`**, damit es
   niemals über Mass Assignment von außen gesetzt werden kann.
2. **`App\Models\Scopes\OrganizationScope`**: schränkt *jede* Query (auch Relationen,
   `find()`, etc.) auf `organization_id = aktuelle Organisation des Nutzers` ein.
   - **Fail-closed**: Ist kein Nutzer authentifiziert oder hat der Nutzer keine aktive
     Organisation, liefert die Query **keine** Zeilen zurück (`organization_id = 0`,
     nie ein realer Wert) — nie "alle Zeilen" als unsicherer Default.
   - Super Admins (`is_super_admin = true`) umgehen den Scope, um plattformweit
     administrieren zu können.
   - Im Konsolenkontext (Artisan-Commands, Seeder, Migrationen) ist der Scope inaktiv,
     damit z. B. Seeder organisationsübergreifend Daten anlegen können.
3. **Policies** (siehe [authorization.md](authorization.md)) prüfen zusätzlich explizit
   `$model->organization_id === $user->current_organization_id`, obwohl der Scope das Modell
   eigentlich schon gefiltert haben sollte. Grund: Route-Model-Binding, gecachte Instanzen oder
   eager-geladene Relationen können dem Code ein Modell in die Hand geben, das *vor*
   Anwendung des Scopes geladen wurde — Policies sind die zweite, unabhängige Verteidigungslinie.

## Middleware

`App\Http\Middleware\EnsureUserHasOrganization` (Alias `ensure-organization`) ist vorbereitet,
aber noch **nicht** an Routen gebunden: Es gibt noch keinen Onboarding-Flow, der einem neuen
Nutzer automatisch eine Organisation zuweist, daher würde die Middleware aktuell jede
Kernseite für neu registrierte Nutzer blockieren. Sie wird aktiviert, sobald
Organisations-Onboarding existiert.

## Bewusste Vereinfachung für diese Grundstruktur

- `properties.owner_id` ist eine einfache Fremdschlüssel-Spalte (ein Eigentümer pro
  Immobilie). Bei Bedarf für Miteigentümerschaft kann später eine `property_owner`-Pivot-
  Tabelle ergänzt werden, ohne dass bestehende Daten migriert werden müssen (`owner_id` bliebe
  als "Haupteigentümer" bestehen).

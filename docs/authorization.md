# Rollen & Autorisierung

## Rollen

| Rolle             | Geltungsbereich          | Definiert in                                  |
| ------------------ | ------------------------ | ---------------------------------------------- |
| Super Admin         | Plattformweit             | `users.is_super_admin` (boolean)                |
| Property Manager    | Pro Organisation           | `organization_user.role = 'property_manager'`   |
| Owner                | Pro Organisation           | `organization_user.role = 'owner'`               |
| Tenant                | Pro Organisation           | `organization_user.role = 'tenant'`               |
| Contractor            | Pro Organisation           | `organization_user.role = 'contractor'`           |

Die vier organisationsgebundenen Rollen sind in `App\Enums\OrganizationRole` als
String-Backed-Enum definiert. Super Admin ist bewusst **kein** Wert dieses Enums, da er nicht
an eine Organisationsmitgliedschaft gebunden ist, sondern die Plattform als Ganzes verwaltet.

## Warum kein Rollen-/Berechtigungspaket (z. B. spatie/laravel-permission)?

Die Aufgabenstellung fordert, möglichst wenige zusätzliche Libraries zu verwenden und keine
Dependency ohne konkreten Grund zu installieren. Mit vier festen, einfachen Rollen pro
Organisation und Laravels eingebauten Policies/Gates lässt sich das Berechtigungssystem
vollständig ohne Zusatzpaket abbilden — inklusive Erweiterbarkeit (neue Policy-Methoden,
neue Enum-Werte). Sollte das Modell später deutlich komplexer werden (z. B. granulare,
pro-Nutzer konfigurierbare Permissions statt fester Rollen), ist das der Punkt, an dem ein
dediziertes Paket sinnvoll wird.

## Bausteine

- **`Gate::before`** (`app/Providers/AppServiceProvider.php`): Super Admins bestehen jede
  Autorisierungsprüfung automatisch. Das ersetzt keine Tenant-Isolation (die passiert über
  `OrganizationScope`, siehe [multi-tenancy.md](multi-tenancy.md)) — es erlaubt Super Admins
  lediglich, Policy-Checks für Aktionen zu bestehen, die sie plattformweit ausführen dürfen.
- **`App\Policies\Concerns\AuthorizesOrganizationAccess`** (Trait): stellt zwei
  Hilfsmethoden für alle Policies bereit:
  - `belongsToUserOrganization($user, $model)` — prüft explizit die Organisationszugehörigkeit
    des Modells (zweite Verteidigungslinie neben dem globalen Scope).
  - `hasRole($user, OrganizationRole $role)` — prüft die Rolle des Nutzers in seiner aktuellen
    Organisation.
- **Beispiel-Policies** als Muster für weitere:
  - `App\Policies\PropertyPolicy` — Property Manager haben vollen Zugriff, Owner nur Lesezugriff
    auf eigene Immobilien, Tenants/Contractors keinen Zugriff.
  - `App\Policies\MaintenanceRequestPolicy` — zeigt eine Drei-Rollen-Interaktion: Property
    Manager verwalten alles, Tenants sehen nur eigene Meldungen, Contractors nur ihnen
    zugewiesene (und dürfen dort nur Status/Notizen ändern, nicht löschen oder neu zuweisen).

Policies werden von Laravel automatisch per Namenskonvention erkannt
(`App\Models\Property` → `App\Policies\PropertyPolicy`), es ist keine manuelle Registrierung
nötig.

## Weitere Policies

Für die übrigen Modelle (Lease, Payment, Document, ...) existieren noch keine Policies — sie
folgen dem exakt gleichen Muster wie `PropertyPolicy`/`MaintenanceRequestPolicy` und werden
angelegt, sobald die zugehörigen Controller/Aktionen implementiert werden. Das vermeidet
Policy-Leichen ohne aufrufenden Code.

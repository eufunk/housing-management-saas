# Test Use Cases: Eigentümer (Owners)

Manuelle UI-Testfälle für das Eigentümer-Modul (`/owners`). Ergänzt die automatisierten Tests in
`tests/Feature/Owners/OwnerCrudTest.php` und `tests/Feature/Authorization/OwnerPolicyTest.php`.

## Voraussetzungen

- Anwendung lokal erreichbar (siehe `docs/development.md`)
- Zugang über **Demo-Login** (Startseite → „Demo ausprobieren", Rolle Property Manager) oder
  ein eigener Account
- Der Demo-Account ist bereits mit 5 Eigentümern vorbefüllt
  (`php artisan db:seed --class=DemoPropertySeeder`)
- Für die Rollen-/Mandantentrennungsfälle wird ein **zweiter** Test-Account mit anderer Rolle
  bzw. anderer Organisation benötigt

## UC-01 — Eigentümerliste ansehen

**Schritte:** Sidebar → „Eigentümer".

**Erwartet:** Tabelle mit Name, E-Mail, Telefon, z. B. „Sabine Hoffmann".

## UC-02 — Leere Liste (Empty State)

| | |
|---|---|
| Voraussetzung | Eigener Account/eigene Organisation ohne Eigentümer |

**Erwartet:** Hinweis „Noch keine Eigentümer" mit Button „Eigentümer hinzufügen".

## UC-03 — Eigentümer anlegen

**Schritte:** „Eigentümer hinzufügen" → Name (Pflicht), E-Mail/Telefon (optional) ausfüllen →
Speichern.

**Erwartet:** Neuer Eintrag erscheint in der Liste. Anschließend im Immobilien-Formular
(„Immobilie hinzufügen"/bearbeiten) im Eigentümer-Dropdown auswählbar.

## UC-04 — Validierung: ungültige E-Mail

**Schritte:** Eigentümer mit E-Mail „nicht-gueltig" anlegen.

**Erwartet:** Fehlermeldung am E-Mail-Feld, kein neuer Eintrag. Name allein ohne E-Mail/Telefon
ist dagegen gültig (beide optional).

## UC-05 — Eigentümer bearbeiten

**Schritte:** Stift-Icon klicken, Daten ändern, speichern.

**Erwartet:** Änderungen erscheinen in der Liste und im Eigentümer-Dropdown der
Immobilien-Formulare.

## UC-06 — Eigentümer löschen

**Schritte:** Papierkorb-Icon klicken, Löschung bestätigen.

**Erwartet:** Eintrag verschwindet aus der Liste. Immobilien, die diesem Eigentümer zugeordnet
waren, zeigen danach „—" in der Eigentümer-Spalte (Fremdschlüssel ist `nullOnDelete`).

## UC-07 — Rollenbeschränkung (Tenant/Contractor)

**Schritte:** Mit einem Tenant- oder Contractor-Account `/owners/create` direkt aufrufen.

**Erwartet:** Zugriff verweigert (403).

## UC-08 — Mandantentrennung

**Schritte:** Mit Account A die ULID eines eigenen Eigentümers aus der Bearbeiten-URL merken,
mit Account B (andere Organisation) dieselbe URL aufrufen.

**Erwartet:** 404 — `OrganizationScope` macht den fremden Eigentümer vor der Policy-Prüfung
bereits unsichtbar.

# Test Use Cases: Mieter (Tenants)

Manuelle UI-Testfälle für das Mieter-Modul (`/tenants`). Ergänzt die automatisierten Tests in
`tests/Feature/Tenants/TenantCrudTest.php` und `tests/Feature/Authorization/TenantPolicyTest.php`.

## Voraussetzungen

- Anwendung lokal erreichbar (siehe `docs/development.md`)
- Zugang über **Demo-Login** (Startseite → „Demo ausprobieren", Rolle Property Manager) oder
  ein eigener Account
- Für die Rollen-/Mandantentrennungsfälle wird ein **zweiter** Test-Account mit anderer Rolle
  bzw. anderer Organisation benötigt

## UC-01 — Mieterliste ansehen

**Schritte:** Sidebar → „Mieter".

**Erwartet:** Tabelle mit Name, E-Mail, Telefon (anfangs leer, da der Demo-Datensatz keine
Mieter vorbefüllt).

## UC-02 — Leere Liste (Empty State)

**Erwartet:** Hinweis „Noch keine Mieter" mit Button „Mieter hinzufügen" — im Demo-Account
direkt beim ersten Aufruf sichtbar.

## UC-03 — Mieter anlegen

**Schritte:** „Mieter hinzufügen" → Name (Pflicht), E-Mail/Telefon (optional) ausfüllen →
Speichern.

**Erwartet:** Neuer Eintrag erscheint in der Liste.

## UC-04 — Validierung: ungültige E-Mail

**Schritte:** Mieter mit E-Mail „nicht-gueltig" anlegen.

**Erwartet:** Fehlermeldung am E-Mail-Feld, kein neuer Eintrag.

## UC-05 — Mieter bearbeiten

**Schritte:** Stift-Icon klicken, Daten ändern, speichern.

**Erwartet:** Änderungen erscheinen in der Liste.

## UC-06 — Mieter löschen

**Schritte:** Papierkorb-Icon klicken, Löschung bestätigen.

**Erwartet:** Eintrag verschwindet aus der Liste.

## UC-07 — Rollenbeschränkung (Owner/Contractor)

**Schritte:** Mit einem Owner- oder Contractor-Account `/tenants/create` direkt aufrufen.

**Erwartet:** Zugriff verweigert (403).

## UC-08 — Mandantentrennung

**Schritte:** Mit Account A die ULID eines eigenen Mieters aus der Bearbeiten-URL merken, mit
Account B (andere Organisation) dieselbe URL aufrufen.

**Erwartet:** 404 — `OrganizationScope` macht den fremden Mieter vor der Policy-Prüfung bereits
unsichtbar.

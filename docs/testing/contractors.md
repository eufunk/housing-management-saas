# Test Use Cases: Handwerker (Contractors)

Manuelle UI-Testfälle für das Handwerker-Modul (`/contractors`, erreichbar über Sidebar
„Reparaturen" → „Handwerker"). Ergänzt die automatisierten Tests in
`tests/Feature/Contractors/ContractorCrudTest.php` und
`tests/Feature/Authorization/ContractorPolicyTest.php`.

## Voraussetzungen

- Anwendung lokal erreichbar (siehe `docs/development.md`)
- Zugang über **Demo-Login** (Startseite → „Demo ausprobieren", Rolle Property Manager) oder
  ein eigener Account
- Für die Rollen-/Mandantentrennungsfälle wird ein **zweiter** Test-Account mit anderer Rolle
  bzw. anderer Organisation benötigt

## UC-01 — Handwerkerliste ansehen

**Schritte:** Sidebar → „Reparaturen" aufklappen → „Handwerker".

**Erwartet:** Tabelle mit Firma, Ansprechpartner, Kontakt, Fachgebiet (anfangs leer, da der
Demo-Datensatz keine Handwerker vorbefüllt).

## UC-02 — Leere Liste (Empty State)

**Erwartet:** Hinweis „Noch keine Handwerker" mit Button „Handwerker hinzufügen".

## UC-03 — Handwerker anlegen

**Schritte:** „Handwerker hinzufügen" → Firma (Pflicht), Ansprechpartner/E-Mail/Telefon/
Fachgebiet (optional) ausfüllen → Speichern.

**Erwartet:** Neuer Eintrag erscheint in der Liste.

## UC-04 — Validierung: ungültige E-Mail

**Schritte:** Handwerker mit E-Mail „nicht-gueltig" anlegen.

**Erwartet:** Fehlermeldung am E-Mail-Feld, kein neuer Eintrag. Firma allein ohne weitere Angaben
ist dagegen gültig.

## UC-05 — Handwerker bearbeiten

**Schritte:** Stift-Icon klicken, Daten ändern, speichern.

**Erwartet:** Änderungen erscheinen in der Liste.

## UC-06 — Handwerker löschen

**Schritte:** Papierkorb-Icon klicken, Löschung bestätigen.

**Erwartet:** Eintrag verschwindet aus der Liste.

## UC-07 — Rollenbeschränkung (Owner/Tenant)

**Schritte:** Mit einem Owner- oder Tenant-Account `/contractors/create` direkt aufrufen.

**Erwartet:** Zugriff verweigert (403).

## UC-08 — Mandantentrennung

**Schritte:** Mit Account A die ULID eines eigenen Handwerkers aus der Bearbeiten-URL merken,
mit Account B (andere Organisation) dieselbe URL aufrufen.

**Erwartet:** 404 — `OrganizationScope` macht den fremden Handwerker vor der Policy-Prüfung
bereits unsichtbar.

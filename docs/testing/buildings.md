# Test Use Cases: Gebäude (Buildings)

Manuelle UI-Testfälle für das Gebäude-Modul (`/properties/buildings`). Ergänzt die
automatisierten Tests in `tests/Feature/Buildings/BuildingCrudTest.php` und
`tests/Feature/Authorization/BuildingPolicyTest.php`.

## Voraussetzungen

- Anwendung lokal erreichbar (siehe `docs/development.md`)
- Zugang über **Demo-Login** (Startseite → „Demo ausprobieren", Rolle Property Manager) oder
  ein eigener Account
- Der Demo-Account ist mit 20 Immobilien und 9 Gebäuden vorbefüllt
  (`php artisan db:seed --class=DemoPropertySeeder`)
- Für die Rollen-/Mandantentrennungsfälle wird ein **zweiter** Test-Account mit anderer Rolle
  bzw. anderer Organisation benötigt

## UC-01 — Gebäudeliste ansehen

**Schritte**
1. Einloggen (Demo-Login)
2. Sidebar: „Immobilien" aufklappen → „Gebäude" auswählen

**Erwartet:** Tabelle mit Spalten Name, Immobilie, Stockwerke erscheint, z. B. „Haus A" /
„Wohnanlage Sonnenhof" / 5 Stockwerke.

## UC-02 — Leere Liste (Empty State)

| | |
|---|---|
| Voraussetzung | Eigener Account/eigene Organisation ohne Gebäude |

**Schritte:** Zu „Gebäude" navigieren.

**Erwartet:** Hinweis „Noch keine Gebäude" mit Button „Gebäude hinzufügen".

## UC-03 — Gebäude anlegen

**Schritte**
1. „Gebäude hinzufügen" klicken
2. Immobilie im Dropdown auswählen, Namen und optional Stockwerke ausfüllen
3. Speichern

**Erwartet:** Rückkehr zur Liste, neues Gebäude erscheint mit der gewählten Immobilie in der
entsprechenden Spalte.

## UC-04 — Validierung: Immobilie und Name fehlen

**Schritte:** Formular ohne Auswahl/Eingabe abschicken.

**Erwartet:** Fehlermeldungen unter Immobilie-Dropdown und Name-Feld, kein neuer Eintrag.

## UC-05 — Gebäude bearbeiten

**Schritte:** Ein Gebäude über das Stift-Icon bearbeiten, Namen ändern, speichern.

**Erwartet:** Formular war vorausgefüllt (inkl. korrekt vorausgewählter Immobilie); Liste zeigt
danach den geänderten Namen.

## UC-06 — Gebäude löschen

**Schritte:** Papierkorb-Icon klicken, im Dialog erst „Abbrechen" (Eintrag bleibt), dann erneut
und „Löschen" bestätigen.

**Erwartet:** Nach Bestätigung verschwindet das Gebäude aus der Liste.

## UC-07 — Rollenbeschränkung (Tenant/Contractor)

**Schritte:** Mit einem Tenant- oder Contractor-Account `/properties/buildings/create` direkt
aufrufen.

**Erwartet:** Zugriff verweigert (403).

## UC-08 — Mandantentrennung

**Schritte:** Mit Account A die ULID eines eigenen Gebäudes aus der Bearbeiten-URL merken, mit
Account B (andere Organisation) dieselbe URL aufrufen.

**Erwartet:** 404 — `OrganizationScope` macht das fremde Gebäude vor der Policy-Prüfung bereits
unsichtbar.

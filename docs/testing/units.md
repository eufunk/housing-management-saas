# Test Use Cases: Wohnungen (Units)

Manuelle UI-Testfälle für das Wohnungen-Modul (`/properties/units`). Ergänzt die automatisierten
Tests in `tests/Feature/Units/UnitCrudTest.php` und `tests/Feature/Authorization/UnitPolicyTest.php`.

## Voraussetzungen

- Anwendung lokal erreichbar (siehe `docs/development.md`)
- Zugang über **Demo-Login** (Startseite → „Demo ausprobieren", Rolle Property Manager) oder
  ein eigener Account
- Der Demo-Account ist mit 9 Gebäuden und 25 Wohnungen vorbefüllt
  (`php artisan db:seed --class=DemoPropertySeeder`) — Status-Mix: 14 vermietet, 8 Leerstand,
  3 in Renovierung, also alle drei Status-Badges im Demo-Datensatz vertreten

## UC-01 — Wohnungsliste mit Daten ansehen

**Schritte**
1. Einloggen (Demo-Login)
2. Sidebar: „Immobilien" aufklappen → „Wohnungen" auswählen

**Erwartet:** Tabelle mit Nr., Gebäude, Etage, Größe, Zimmer, Status. Da mehr als 15 Wohnungen
existieren, ist eine Pagination-Leiste sichtbar. Der Status wird als farbiges Badge angezeigt
(Leerstand = outline, Vermietet = grün, In Renovierung = grau/sekundär).

## UC-02 — Leere Liste (Empty State)

| | |
|---|---|
| Voraussetzung | Eigener Account/eigene Organisation ohne Wohnungen |

**Erwartet:** Hinweis „Noch keine Wohnungen" mit Button „Wohnung hinzufügen".

## UC-03 — Wohnung anlegen

**Schritte**
1. „Wohnung hinzufügen" klicken
2. Gebäude auswählen, Wohnungsnummer eingeben, optional Etage/Größe/Zimmer, Status wählen
3. Speichern

**Erwartet:** Neue Wohnung erscheint in der Liste mit korrektem Status-Badge.

## UC-04 — Validierung: doppelte Wohnungsnummer im selben Gebäude

**Schritte:** Eine Wohnung mit einer im gewählten Gebäude bereits vergebenen Nummer anlegen
(z. B. „101" in „Haus A", falls dort schon vorhanden).

**Erwartet:** Fehlermeldung am Nummernfeld, kein neuer Eintrag. Dieselbe Nummer in einem
*anderen* Gebäude ist dagegen erlaubt (Eindeutigkeit gilt nur pro Gebäude).

## UC-05 — Wohnung bearbeiten (Status ändern)

**Schritte:** Eine Wohnung mit Status „Leerstand" bearbeiten, Status auf „Vermietet" ändern,
speichern.

**Erwartet:** Badge in der Liste wechselt von outline auf grün.

## UC-06 — Wohnung löschen

**Schritte:** Papierkorb-Icon klicken, Löschung im Dialog bestätigen.

**Erwartet:** Wohnung verschwindet aus der Liste.

## UC-07 — Rollenbeschränkung (Tenant/Contractor)

**Schritte:** Mit einem Tenant- oder Contractor-Account `/properties/units/create` direkt
aufrufen.

**Erwartet:** Zugriff verweigert (403).

## UC-08 — Mandantentrennung

**Schritte:** Mit Account A die ULID einer eigenen Wohnung aus der Bearbeiten-URL merken, mit
Account B (andere Organisation) dieselbe URL aufrufen.

**Erwartet:** 404 — `OrganizationScope` macht die fremde Wohnung vor der Policy-Prüfung bereits
unsichtbar.

# Test Use Cases: Immobilien (Properties)

Manuelle UI-Testfälle für das Immobilien-Modul (`/properties`). Ergänzt die automatisierten
Tests in `tests/Feature/Properties/PropertyCrudTest.php` — dort wird die Logik (Autorisierung,
Mandantentrennung, Validierung) bereits gegen echte HTTP-Requests geprüft; diese Datei prüft
denselben Funktionsumfang zusätzlich manuell über die tatsächliche Benutzeroberfläche im
Browser, inklusive visueller/interaktiver Aspekte (Dropdown-Verhalten, Bestätigungsdialog,
Pagination-Optik), die automatisierte Tests nicht abdecken.

Weitere Module (Owners, Tenants, Contractors, ...) bekommen eigene Dateien in diesem Ordner,
sobald ihr CRUD implementiert ist — siehe bereits [buildings.md](buildings.md) und
[units.md](units.md).

## Voraussetzungen

- Anwendung lokal erreichbar (siehe `docs/development.md`)
- Zugang über **Demo-Login** (Startseite → „Demo ausprobieren", Rolle Property Manager) oder
  ein eigener Account
- Für UC-08 und UC-09 ist der Demo-Account bereits mit 20 Beispiel-Immobilien vorbefüllt
  (`php artisan db:seed --class=DemoPropertySeeder`)
- Für UC-02 und UC-10 wird ein **zweiter** Test-Account mit anderer Rolle bzw. anderer
  Organisation benötigt, da der Demo-Account fest auf Property Manager/eine Organisation
  eingestellt ist

---

## UC-01 — Immobilienliste mit Daten ansehen

| | |
|---|---|
| Rolle | Property Manager (Demo-Login) |
| Voraussetzung | Demo-Daten geseedet (20 Immobilien) |

**Schritte**
1. Einloggen (Demo-Login)
2. In der Sidebar auf „Immobilien" klicken

**Erwartet:** Tabelle mit Spalten Name, Adresse, Eigentümer erscheint. Erste Seite zeigt 15
Zeilen (alphabetisch nach Name sortiert), darunter eine Pagination-Leiste. Bei einem Teil der
Zeilen steht in der Spalte „Eigentümer" ein Name (z. B. „Sabine Hoffmann"), bei anderen ein „—"
(kein Eigentümer zugeordnet).

## UC-02 — Leere Liste (Empty State)

| | |
|---|---|
| Rolle | Property Manager |
| Voraussetzung | Eigener Account/eigene Organisation **ohne** Immobilien (nicht der vorbefüllte Demo-Account) |

**Schritte**
1. Einloggen mit einem frischen Account ohne bestehende Immobilien
2. Zu „Immobilien" navigieren

**Erwartet:** Statt einer Tabelle erscheint ein zentrierter Hinweis („Noch keine Immobilien")
mit Icon, Beschreibungstext und einem Button „Immobilie hinzufügen".

## UC-03 — Neue Immobilie ohne Eigentümer anlegen

| | |
|---|---|
| Rolle | Property Manager |

**Schritte**
1. Auf „Immobilie hinzufügen" klicken
2. Name, Straße/Hausnummer, PLZ, Stadt ausfüllen; Eigentümer-Dropdown unverändert lassen
   („Kein Eigentümer zugeordnet")
3. „Speichern" klicken

**Erwartet:** Rückkehr zur Liste, neue Immobilie erscheint mit „—" in der Eigentümer-Spalte.

## UC-04 — Neue Immobilie mit Eigentümer anlegen

| | |
|---|---|
| Rolle | Property Manager |
| Voraussetzung | Mindestens ein Eigentümer existiert (im Demo-Account: 5 vorhanden) |

**Schritte**
1. „Immobilie hinzufügen" klicken, Pflichtfelder ausfüllen
2. Eigentümer-Dropdown öffnen, einen Eintrag auswählen (z. B. „Nordlicht Immobilien GbR")
3. Speichern

**Erwartet:** Neue Immobilie erscheint in der Liste mit dem gewählten Namen in der
Eigentümer-Spalte.

## UC-05 — Validierung: Pflichtfelder leer

| | |
|---|---|
| Rolle | Property Manager |

**Schritte**
1. „Immobilie hinzufügen" klicken
2. Ohne etwas auszufüllen direkt „Speichern" klicken

**Erwartet:** Formular wird nicht abgeschickt, unter jedem Pflichtfeld (Name, Straße, PLZ,
Stadt) erscheint eine Fehlermeldung. Es entsteht kein neuer Eintrag in der Liste.

## UC-06 — Immobilie bearbeiten

| | |
|---|---|
| Rolle | Property Manager |

**Schritte**
1. In der Liste bei einer beliebigen Immobilie auf das Stift-Icon klicken
2. Namen ändern (z. B. Zusatz „ (renoviert)" anhängen)
3. Speichern

**Erwartet:** Formular war mit den bisherigen Werten vorausgefüllt. Nach dem Speichern zeigt
die Liste den geänderten Namen.

## UC-07 — Eigentümer nachträglich entfernen

| | |
|---|---|
| Rolle | Property Manager |
| Voraussetzung | Eine Immobilie mit zugewiesenem Eigentümer (z. B. „Wohnanlage Sonnenhof") |

**Schritte**
1. Diese Immobilie bearbeiten
2. Eigentümer-Dropdown auf „Kein Eigentümer zugeordnet" zurücksetzen
3. Speichern

**Erwartet:** Eigentümer-Spalte zeigt in der Liste danach „—".

## UC-08 — Immobilie löschen

| | |
|---|---|
| Rolle | Property Manager |

**Schritte**
1. Papierkorb-Icon bei einer Immobilie klicken
2. Im Dialog „Abbrechen" klicken → Eintrag muss weiterhin in der Liste stehen
3. Papierkorb-Icon erneut klicken, diesmal „Löschen" bestätigen

**Erwartet:** Nach Schritt 2 keine Änderung. Nach Schritt 3 verschwindet der Eintrag aus der
Liste (Soft Delete in der Datenbank, für die UI nicht mehr sichtbar).

## UC-09 — Pagination

| | |
|---|---|
| Rolle | Property Manager |
| Voraussetzung | Mehr als 15 Immobilien (im Demo-Account mit 20 Einträgen gegeben) |

**Schritte**
1. Zur Immobilienliste navigieren
2. Am Ende der ersten Seite auf „2" bzw. „Weiter" klicken

**Erwartet:** Zweite Seite zeigt die restlichen (alphabetisch folgenden) Immobilien, aktuelle
Seite ist in der Pagination-Leiste hervorgehoben. Zurück-Navigation funktioniert ebenso.

## UC-10 — Rollenbeschränkung (Tenant/Contractor)

| | |
|---|---|
| Rolle | Tenant oder Contractor |
| Voraussetzung | Zweiter Test-Account mit einer dieser Rollen in einer Organisation mit Immobilien |

**Schritte**
1. Mit einem Tenant- oder Contractor-Account einloggen
2. Versuchen, `/properties` oder `/properties/create` direkt über die URL aufzurufen

**Erwartet:** Zugriff wird verweigert (403), kein „Immobilien"-Link in der Sidebar für diese
Rollen sichtbar (sofern die Navigation rollenbasiert gefiltert ist).

## UC-11 — Mandantentrennung

| | |
|---|---|
| Rolle | Property Manager |
| Voraussetzung | Zwei Accounts in zwei unterschiedlichen Organisationen, beide mit eigenen Immobilien |

**Schritte**
1. Mit Account A einloggen, sich die ULID einer eigenen Immobilie aus der URL der
   Bearbeiten-Seite merken (`/properties/{ulid}/edit`)
2. Ausloggen, mit Account B (andere Organisation) einloggen
3. Die gemerkte URL direkt aufrufen

**Erwartet:** 404 (nicht 403) — die Immobilie ist für Account B nicht auffindbar, nicht nur
nicht bearbeitbar. Auch in Account B's eigener Liste taucht Account A's Immobilie nicht auf.

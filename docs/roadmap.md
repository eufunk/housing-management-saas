# Produkt-Roadmap

Dieses Dokument ist **vorausschauend** (was als Nächstes sinnvoll ist), im Unterschied zu
[project-journal.md](project-journal.md) (was bisher passiert ist) und [`ToDo.md`](../ToDo.md)
(Checkliste der abgeschlossenen Grundstruktur-Phase). Es dient als Arbeitsgrundlage für die
Priorisierung der nächsten Implementierungsschritte, nachdem die Grundstruktur
(Datenbank, Rollen, Sicherheit, Layout, Login/Demo) steht, aber alle Fachmodule noch
EmptyState-Platzhalter sind.

## Auslöser: Wettbewerbsrecherche statt Annahmen

Die ursprüngliche Leitidee ("Ordnung ins Chaos der Verwaltung bringen") ist richtig, aber zu
unspezifisch — professionelle Hausverwaltungen haben in der Regel bereits digitale Systeme. Die
eigentliche Frage lautet daher: **Was kann diese Software besser, einfacher oder intelligenter
machen als bestehende Hausverwaltungssoftware?**

Als Grundlage dafür wurde die öffentliche Website der Immobilienverwaltung Riebeling GmbH
analysiert (32 Objekte, 4.073 betreute Einheiten, laut eigener Angabe "100 % digital",
Eigentümerportal). **Wichtig:** Riebeling ist hier ein **Referenzunternehmen für
Anforderungsanalyse**, nicht ein Kunde, den es zu kopieren gilt, und keine Vorlage für Design
oder Inhalte — es geht um die Frage "Welche Informationen und Prozesse müsste unsere Software
abbilden, wenn ich morgen bei einer solchen Hausverwaltung anfinge?", nicht um Nachbau einer
fremden Website.

## Feature-Abgleich

| Bei Riebeling beobachtet          | Entsprechung in diesem Projekt                          | Status                       |
| ----------------------------------- | ---------------------------------------------------------- | ------------------------------- |
| Immobilienverwaltung                  | `properties` / `buildings` / `units`                          | **CRUD fertig** (alle drei)     |
| Mieterverwaltung                        | `tenants`                                                        | Datenmodell fertig, kein CRUD       |
| Mietverträge                              | `leases`                                                           | Datenmodell fertig, kein CRUD         |
| Schadens-/Mängelmeldung mit Fotos            | `maintenance_requests` + `documents` (polymorph)                    | Datenmodell fertig, kein CRUD           |
| Vorgangsnummer/Ticketsystem                    | `maintenance_requests.ulid`                                            | Vorhanden, aber nicht als lesbare Nummer formatiert |
| Handwerker/Objektbetreuung                        | `contractors` (inkl. `specialty`-Feld)                                    | Datenmodell fertig, kein CRUD               |
| Dokumente                                            | `documents` / `document_categories`                                          | Datenmodell fertig, kein CRUD                 |
| Eigentümerverwaltung                                    | `owners`                                                                        | Datenmodell fertig, kein CRUD                   |
| Finanzinformationen                                        | `payments` / `invoices` / `expenses`                                              | Datenmodell fertig, kein CRUD                     |
| Eigentümer-Reporting                                          | Dashboard (aktuell nur Mock-Daten)                                                  | Noch nicht begonnen                                 |
| Benachrichtigungen                                               | `activity_logs`, Sidebar-Modul "Benachrichtigungen"                                    | Datenmodell/Platzhalter vorhanden, keine Logik        |
| Rollen (Manager/Eigentümer/Mieter/Handwerker)                       | `OrganizationRole` + Policies                                                          | **Fertig** (Grundstruktur)                              |
| Eigentümerportal                                                       | Owner-Rolle + Policy-Muster (`PropertyPolicy`)                                            | Rollen-Grundlage fertig, keine Portal-Ansicht             |
| WEG-Verwaltung / Sondereigentumsverwaltung / Facility Mgmt.               | —                                                                                            | Nicht abgedeckt, bewusste Grundsatzentscheidung (s. u.)     |

**Fazit:** Die Kernentität-Abdeckung ist bereits vollständig durchdacht (jedes beobachtete
Riebeling-Feature hat eine Entsprechung im Datenmodell) — es fehlt nicht Architektur, sondern
**echte Fachlogik statt Platzhalter**. Das bestätigt: Die Grundstruktur-Phase hat die richtigen
Weichen gestellt.

## Vorgeschlagene Phasenreihenfolge

Vorschlag zur Priorisierung der nächsten Implementierungsschritte — jede Phase baut auf der
vorherigen auf und liefert für sich einen nutzbaren Zwischenstand, statt in Breite alle Module
gleichzeitig halbfertig zu bauen.

### Phase 1 — Stammdaten (Fundament für alles Weitere)
Echtes CRUD für `Properties` → `Buildings` → `Units`, `Owners`, `Tenants`, `Contractors`.
Ohne diese Basis lässt sich kein anderes Modul sinnvoll befüllen oder testen. Reihenfolge
innerhalb der Phase: Properties zuerst (alles hängt daran) — **fertig** —, dann Buildings/Units
— **fertig**, siehe `docs/project-journal.md` Abschnitt 17/19 —, dann Personen
(Owners/Tenants/Contractors) parallel, noch offen.

### Phase 2 — Mietverträge
Echtes CRUD für `Leases`, verknüpft mit Units und Tenants. Voraussetzung für Phase 3 und 4
(Zahlungen und Reparaturmeldungen hängen an einer Unit/einem Mietverhältnis).

### Phase 3 — Reparatur-Workflow (stärkste Riebeling-Parallele, hoher Nutzwert)
Der von Riebeling beobachtete Vorgang `Mieter → Meldung → Vorgangsnummer → Zuständigkeit →
Handwerker → Lösung` wird 1:1 auf `maintenance_requests` + `maintenance_comments` +
`documents` umgesetzt:
- Mieter-Formular zur Selbstmeldung (Titel, Beschreibung, **bis zu 3 Fotos** — exakt Riebelings
  Grenze, ein bewusst übernommenes, bewährtes UX-Detail)
- Automatisch generierte, lesbare Vorgangsnummer (z. B. `T-2026-0001`, organisationsweit
  fortlaufend — zusätzlich zur bereits vorhandenen `ulid`, die technisch, nicht
  menschenlesbar ist)
- Statuspipeline (offen → zugewiesen → in Bearbeitung → erledigt → geschlossen), inkl.
  Sichtbarkeit für Mieter (eigene Meldungen) und Handwerker (zugewiesene Aufträge) gemäß der
  bereits bestehenden `MaintenanceRequestPolicy`
- Kommentarverlauf über `maintenance_comments`

### Phase 4 — Finanzen
Echtes CRUD für `Payments`, `Invoices`, `Expenses`, verknüpft mit Leases/Properties.
Voraussetzung für Phase 5.

### Phase 5 — Eigentümerportal & Reporting
Eine dedizierte, lesende Ansicht für die Owner-Rolle: eigene Immobilien, zugehörige Dokumente,
**monatliche Finanzauswertung** (Einnahmen/Ausgaben je Objekt) — der von Riebeling explizit als
Differenzierungsmerkmal genannte Baustein ("100 % digital", Eigentümerportal). Technisch eine
Erweiterung des bestehenden Policy-Musters (`PropertyPolicy`s Owner-Zweig existiert schon),
kein neues Berechtigungskonzept nötig.

### Phase 6 — KI-gestützte Vorgangsbearbeitung (Differenzierung, bewusst zuletzt)
Erst sinnvoll, sobald Phase 3 mit echten Daten läuft. Vorschlag für den Umfang, angelehnt an die
diskutierte Vorgangskette:
- Kategorie- und Prioritätsvorschlag beim Anlegen einer Meldung (auf Basis von Titel/Beschreibung)
- Handwerker-Vorschlag auf Basis des vorhandenen `specialty`-Felds bei `Contractor` (kein neues
  Datenmodell nötig, nur neue Logik darüber)
- **Grundprinzip**: Vorschlag statt Automatismus — ein Mensch bestätigt jede KI-Empfehlung,
  nichts wird ohne Bestätigung zugewiesen oder verändert
- Technisch bereits vorbereitet: `docs/architecture.md` sieht einen austauschbaren
  KI-Provider hinter einem Interface vor; diese Phase füllt das erstmals mit echter
  Implementierung, keine neue Architekturentscheidung nötig

## Bewusst zurückgestellte Grundsatzentscheidungen

- **WEG-Verwaltung / Sondereigentumsverwaltung / Facility Management**: Riebeling bietet das
  zusätzlich zur Mietverwaltung an. Rechtlich und strukturell ein anderes Feld
  (Eigentümerversammlungen, Wirtschaftsplan, Hausgeld, Beschlussfassung) — **kein** impliziter
  Bestandteil der aktuellen Roadmap. Erfordert eine eigene, spätere Entscheidung mit eigener
  Datenmodell-Erweiterung, nicht ein Nebeneffekt von Phase 1–6.
- **Automatisierungsgrad der KI-Funktionen** (Phase 6): Der genaue Umfang (z. B. ob auch
  Terminvorschläge oder automatische Rechnungszuordnung an die KI-Kette angehängt werden) ist
  hier bewusst offengehalten und wird erst entschieden, wenn Phase 3/4 echte Daten liefern, an
  denen sich das beurteilen lässt.

## Wie dieses Dokument genutzt wird

Dient als Arbeitsgrundlage für die Priorisierung kommender Implementierungsschritte (Phase 1
zuerst, danach der Reihe nach) — Abweichungen von der Reihenfolge erfolgen nur auf ausdrücklichen
Wunsch. Wird aktualisiert, wenn sich Priorisierung oder Umfang einzelner Phasen ändern.

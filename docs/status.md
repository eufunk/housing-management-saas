# Projektstatus gegenüber Prompts.docx

Dieses Dokument beantwortet eine wiederkehrende Frage direkt: **Wo stehen wir gerade, gemessen
am ursprünglichen 17-Phasen-Plan in `Aufgabenstellung/Prompts.docx`?** Es ist ein
**Status-Snapshot**, kein Ablaufplan — dafür siehe [roadmap.md](roadmap.md) (unsere eigene,
später und unabhängig von `Prompts.docx` entstandene Priorisierung) und
[project-journal.md](project-journal.md) (chronologische Erzählung, warum was wann entschieden
wurde). Wird aktualisiert, wenn sich der Stand wesentlich ändert — Stand hier: **30.08.2026**
(zuletzt aktualisiert: Phase 06/07-Lücken größtenteils nachgeholt).

## Wichtigster Befund

`Prompts.docx` wurde **strukturell nie als Ablaufplan verwendet**. Phase 01 dort sieht eine
reine Planungsrunde vor (nur Dokumente erstellen — `product-overview.md`, `requirements.md`,
`use-cases.md`, `business-processes.md`, `docs/decisions/` — noch **kein** Code). Tatsächlich
gestartet wurde mit einer anderen, direkteren Aufgabenstellung
(`Aufgabenstellung/PropertyManager SaaS Grundstruktur.docx`), die Produktrahmen und sofortigen
Vollaufbau des Grundgerüsts in einem Schritt kombinierte. `docs/roadmap.md` entstand ebenfalls
erst später und aus einer Konkurrenzanalyse (Immobilienverwaltung Riebeling GmbH), nicht aus
dieser Prompt-Vorlage. Die vier oben genannten Planungsdokumente existieren dadurch nicht in
dieser Form.

## Phasenstatus

| Phase | Status | Kernabweichungen |
|---|---|---|
| 01 – Produktanalyse | ⚠️ anders durchlaufen | Kein reiner Planungsschritt, siehe oben |
| 02 – Systemarchitektur | ✅ weitgehend erledigt | Kein `docs/decisions/`-ADR-Ordner |
| 03 – Datenbank & ER-Modell | ✅ weitgehend erledigt | `rooms`, `parking_spaces` fehlen; `maintenance_attachments` über `documents` gelöst |
| 04 – Auth/Multi-Tenancy/Rollen | ✅ fertig | Kaum Abweichung |
| 05 – Frontend-Grundstruktur | ✅ größtenteils fertig | Organization Switcher, Textarea, ConfirmDialog, generischer Form-Wrapper fehlen |
| 06 – Immobilienverwaltung | ✅ weitgehend erledigt | Suche/Filter, Property-Detailseite (Tabs) und Einheiten-Typ nachgeholt; `rooms`/`parking_spaces` als eigene Tabellen bleiben offen (Phase 03) |
| 07 – Eigentümer & Mieter | ✅ weitgehend erledigt | Show-Seiten für Owners/Tenants nachgeholt; Dokumente-/Finanzansicht für Eigentümer bleiben Platzhalter (hängen an Phase 09/11) |
| 08 – Mietverträge | ⬜ nicht begonnen | — |
| 09 – Finanzen | ⬜ nicht begonnen | — |
| 10 – Reparaturen & Handwerker | ⬜ nicht begonnen | Handwerker-*Stammdaten* existieren, Workflow (Tickets/Zuweisung) nicht |
| 11 – Dokumentenverwaltung | ⬜ nicht begonnen | — |
| 12 – Dashboard & Reporting | ⬜ nicht begonnen | Nur Mock-Daten-Prototyp aus Phase 05 |
| 13 – Notifications | ⬜ nicht begonnen | — |
| 14 – Qualität/Security/Tests | 🟡 laufend, nicht als Phase | Tests/QA laufen durchgehend mit, aber nie als eigene Abschlussphase |
| 15 – KI-Funktionen | ⬜ nicht begonnen | Bewusst zuletzt (auch in `roadmap.md` so vorgesehen) |
| 16 – SaaS & Billing | ⬜ nicht begonnen | — |
| 17 – Production Readiness | ⬜ nicht begonnen | — |

**Kurz:** Wir stehen am Übergang zwischen Phase 07 und 08 dieser Vorlage. Die zuvor offenen
Lücken in Phase 06/07 (Show-Seiten, Suche/Filter, Einheiten-Typ) sind nachgeholt; verbleibende
Lücken dort (Eigentümer-Dokumente/-Finanzen, `rooms`/`parking_spaces`) hängen an Modulen, die
erst in späteren Phasen entstehen.

## Wo die Details stehen

Jede Abweichung aus der Tabelle ist zusätzlich **direkt an der jeweiligen Stelle in
`Aufgabenstellung/Prompts.docx`** als Anmerkung eingefügt (markiert mit „🔎 Anmerkung (Claude,
Stand …)"), mit ausführlicherer Begründung als hier. Nachgeholte Punkte haben dort zusätzlich
eine „✅ Nachtrag"-Anmerkung direkt darunter, statt die ursprüngliche Anmerkung zu löschen oder
umzuschreiben. Diese Datei ist die kompakte Übersicht, die docx die Detailbegründung im
Originalkontext des jeweiligen Prompts.

## Verhältnis zu roadmap.md

`roadmap.md` und dieses Dokument nutzen **unterschiedliche Phaseneinteilungen**, die nicht
1:1 aufeinander abbilden:

- `Prompts.docx`/dieses Dokument: 17 Phasen aus der ursprünglichen Prompt-Vorlage
- `roadmap.md`: 6 Phasen aus eigener, späterer Priorisierung (Phase 1 „Stammdaten" davon
  bereits abgeschlossen — entspricht inhaltlich in etwa den Phasen 06–07 hier)

Für die Priorisierung der *nächsten* Schritte ist `roadmap.md` maßgeblich, nicht die
Reihenfolge in `Prompts.docx`.

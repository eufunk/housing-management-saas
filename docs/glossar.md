# Glossar

Erklärt die im Projekt verwendeten Technologien und Fachbegriffe für alle, die mit dem
Stack noch nicht vertraut sind. Sortiert nach Themenbereich; siehe
[architecture.md](architecture.md) für die konkreten Entscheidungen in diesem Projekt.

## Architekturbegriffe

**SaaS (Software as a Service)**
Eine zentral gehostete Anwendung, die Nutzer:innen über den Browser verwenden — typischerweise
im Abo-Modell, ohne lokale Installation. Der Anbieter kümmert sich um Betrieb, Updates und
Wartung; alle Kund:innen nutzen dieselbe Anwendung (siehe **Multi-Tenancy**).

**Toolchain**
Die Gesamtheit der Werkzeuge, die zum Entwickeln, Bauen und Ausführen eines Projekts nötig sind
— hier z. B. PHP, Composer, Node.js und npm sowie Docker. Wenn im Projekt von "Toolchain
einrichten" die Rede ist, ist damit die Installation und Konfiguration all dieser Werkzeuge auf
der Entwicklungsmaschine gemeint, nicht der Anwendungscode selbst.

**Multi-Tenancy (Mandantenfähigkeit)**
Mehrere Kund:innen ("Mandanten"/"Tenants" — hier: Hausverwaltungsfirmen) teilen sich dieselbe
Anwendung und Datenbank, ihre Daten sind aber logisch strikt voneinander getrennt. In diesem
Projekt heißt ein Mandant "Organisation" (`Organization`); jede Datenzeile trägt eine
`organization_id`, und ein globaler Datenbank-Scope sorgt dafür, dass nie Daten einer fremden
Organisation sichtbar werden. Details: [multi-tenancy.md](multi-tenancy.md).

**MVC (Model-View-Controller)**
Ein verbreitetes Architekturmuster, das Anwendungscode in drei Rollen aufteilt: **Model**
(Daten und Geschäftslogik), **View** (Darstellung), **Controller** (nimmt Anfragen entgegen,
steuert den Ablauf). Laravel folgt diesem Muster; die "View" wird hier allerdings nicht in
klassischen Laravel-Templates gerendert, sondern von Vue-Komponenten übernommen (siehe
**Inertia.js**).

**ORM (Object-Relational Mapping)**
Ein Werkzeug, das Datenbanktabellen als Programmier-Objekte abbildet, damit man mit
gewöhnlichem Code statt mit rohem SQL arbeitet. Laravels ORM heißt **Eloquent** (siehe unten).

**REST / RESTful API**
Eine verbreitete Konvention, um Web-Schnittstellen (APIs) zu gestalten: Ressourcen (z. B.
"eine Immobilie") werden über feste URLs angesprochen, Standard-HTTP-Methoden (GET, POST, PUT,
DELETE) beschreiben die Aktion. Die Aufgabenstellung fordert REST "wo sinnvoll" — für die
Inertia-basierten Seiten dieses Projekts wird meist kein separates REST-API gebraucht, das
kann aber später für z. B. eine mobile App ergänzt werden.

**Policy / Gate**
Laravel-Konzepte zur **Autorisierung**: Eine *Policy* bündelt die Zugriffsregeln für ein
bestimmtes Modell (z. B. "wer darf eine Immobilie bearbeiten?"), ein *Gate* ist eine einzelne,
freistehende Berechtigungsregel (in diesem Projekt z. B. der Super-Admin-Bypass). Siehe
[authorization.md](authorization.md).

**Middleware**
Code, der zwischen einer eingehenden Anfrage und der eigentlichen Seiten-/Controller-Logik
läuft — z. B. um zu prüfen, ob jemand eingeloggt ist, oder (in diesem Projekt vorbereitet) ob
ein Nutzer einer Organisation zugeordnet ist.

**Global Scope**
Eine Regel, die Laravel automatisch an *jede* Datenbankabfrage eines Modells anhängt. In diesem
Projekt sorgt der `OrganizationScope` dafür, dass jede Abfrage automatisch nach der aktiven
Organisation gefiltert wird, ohne dass das in jedem einzelnen Controller wiederholt werden
muss — die zentrale technische Grundlage der Tenant-Isolation.

**Soft Delete**
Statt eine Datenbankzeile beim "Löschen" wirklich zu entfernen, wird nur ein
`deleted_at`-Zeitstempel gesetzt. Die Zeile bleibt erhalten (z. B. für Historie/Audit), taucht
aber in normalen Abfragen nicht mehr auf.

**ULID (Universally Unique Lexicographically Sortable Identifier)**
Ein zufälliger, weltweit eindeutiger Identifikator (ähnlich einer UUID), der zusätzlich
zeitlich sortierbar ist. In diesem Projekt bekommt jede Tabelle neben der internen,
fortlaufenden `id` (für schnelle interne Verknüpfungen) eine öffentliche `ulid`-Spalte, damit
über URLs/APIs niemals erratbare, fortlaufende interne IDs preisgegeben werden.

## Backend

**PHP**
Die Programmiersprache, in der das Backend geschrieben ist. Läuft auf dem Server und
verarbeitet Anfragen, bevor eine Antwort an den Browser geschickt wird.

**Laravel**
Ein PHP-Framework — ein umfangreiches Regelwerk aus Werkzeugen und Konventionen, das den Bau
von Webanwendungen beschleunigt (Datenbankzugriff, Routing, Authentifizierung, Validierung
u. v. m. sind bereits eingebaut, statt alles selbst schreiben zu müssen). Bildet das komplette
Backend dieses Projekts.

**Composer**
Der Paketmanager für PHP: verwaltet und installiert Laravel selbst sowie alle weiteren
PHP-Bibliotheken, die das Projekt braucht (definiert in `composer.json`).

**Eloquent**
Laravels eingebautes ORM (siehe oben). Jede Datenbanktabelle bekommt eine passende PHP-Klasse
("Model", z. B. `Property`), über die man mit der Tabelle arbeitet, ohne SQL zu schreiben.

**Migrations (Migrationen)**
Versionierte PHP-Dateien, die Änderungen am Datenbankschema beschreiben (z. B. "lege Tabelle
`properties` an"). Werden nacheinander ausgeführt und sorgen dafür, dass jede
Entwicklungsumgebung denselben Datenbankaufbau bekommt, ohne SQL-Dateien manuell abzugleichen.

**Artisan**
Laravels Kommandozeilen-Werkzeug (`php artisan ...`) — z. B. für das Ausführen von Migrationen,
Erzeugen von Dateigerüsten oder Starten des Entwicklungsservers.

**Form Request**
Eine eigene Laravel-Klasse, die die Validierungsregeln für ein einzelnes Formular/eine Anfrage
bündelt, statt sie im Controller zu vermischen — hält Controller schlank.

**Job / Queue**
Eine *Queue* (Warteschlange) lässt zeitintensive Aufgaben (z. B. E-Mail-Versand) im Hintergrund
statt während der Anfrage selbst abarbeiten. Ein *Job* ist eine einzelne, in die Warteschlange
eingereihte Aufgabe.

**Event / Listener**
Ein *Event* wird ausgelöst, wenn im System etwas Bestimmtes passiert (z. B. "Mietvertrag
unterschrieben"); ein oder mehrere *Listener* reagieren darauf (z. B. "sende Bestätigungsmail"),
ohne dass der auslösende Code diese Folgeaktionen selbst kennen muss.

**Scheduler**
Laravels eingebauter Zeitplaner für wiederkehrende Aufgaben (z. B. "prüfe täglich um 8 Uhr auf
auslaufende Mietverträge"), vergleichbar mit einem Cronjob, aber im Anwendungscode definiert.

**Notification**
Laravels einheitliche Schnittstelle, um Nutzer:innen über verschiedene Kanäle zu benachrichtigen
(E-Mail, Datenbank/In-App, später ggf. SMS/Push), ohne für jeden Kanal separaten Code zu
schreiben.

## Frontend

**Vue 3**
Ein JavaScript-Framework für interaktive Benutzeroberflächen im Browser. Zerlegt die
Oberfläche in wiederverwendbare **Komponenten** (z. B. eine Sidebar, ein Button).

**`<script setup lang="ts">`**
Die moderne Vue-3-Schreibweise für Komponenten: kompakter Code, direkte Nutzung von
**TypeScript** ohne zusätzliche Umwege.

**TypeScript**
Eine Erweiterung von JavaScript um ein **Typsystem**: Variablen/Funktionen bekommen feste
Datentypen, wodurch viele Fehler schon beim Schreiben des Codes (statt erst beim Ausführen im
Browser) auffallen.

**Inertia.js**
Das Bindeglied zwischen Laravel-Backend und Vue-Frontend. Erlaubt es, eine Single-Page-App mit
Vue zu bauen, während Routing und Datenbereitstellung weiterhin ganz normal in Laravel
passieren — ohne ein separates REST-/GraphQL-API zwischen beiden aufbauen zu müssen.

**Tailwind CSS**
Ein CSS-Framework, das Design über kleine, kombinierbare Klassennamen direkt im HTML/Vue-Code
umsetzt (z. B. `class="rounded-lg p-4"`), statt eigene CSS-Dateien für jede Komponente zu
pflegen.

**Vite**
Das Build-Werkzeug, das den Frontend-Code (Vue, TypeScript, CSS) für den Browser
zusammenbaut und während der Entwicklung Änderungen sofort im Browser sichtbar macht
(Hot Module Replacement).

**Radix-Vue / class-variance-authority (CVA) / tailwind-merge**
Hilfsbibliotheken hinter den wiederverwendbaren UI-Komponenten (Button, Dialog, Dropdown, ...):
Radix-Vue liefert barrierefreie, ungestylte Basis-Verhalten (z. B. Tastatursteuerung in einem
Dialog), CVA verwaltet die verschiedenen visuellen Varianten einer Komponente (z. B.
`variant="destructive"` bei einem Button), tailwind-merge löst Konflikte, wenn mehrere
Tailwind-Klassen dieselbe CSS-Eigenschaft setzen.

**Ziggy**
Macht die in Laravel definierten Routennamen (z. B. `dashboard`) auch im Vue/TypeScript-Code
nutzbar (`route('dashboard')`), statt URLs doppelt zu pflegen.

## Datenbank & Infrastruktur

**PostgreSQL**
Das relationale Datenbanksystem, in dem alle Daten gespeichert werden.

**Redis**
Ein sehr schneller Key-Value-Speicher (Daten im Arbeitsspeicher). Wird typischerweise für
Caching, Warteschlangen (Queues) und Sessions genutzt — in diesem Projekt architektonisch
vorbereitet, aber noch nicht zwingend aktiviert (siehe [architecture.md](architecture.md)).

**Laravel Reverb**
Laravels eigener WebSocket-Server für Echtzeitfunktionen (z. B. eine Benachrichtigung, die
sofort ohne Neuladen der Seite erscheint). In diesem Projekt vorbereitet, aber noch nicht
installiert, da noch kein konkreter Echtzeit-Anwendungsfall existiert.

**S3 / S3-kompatibler Storage**
"S3" ist Amazons Cloud-Speicherdienst für Dateien; "S3-kompatibel" bedeutet, dass auch andere
Anbieter (oder selbst gehostete Lösungen wie MinIO) dieselbe Schnittstelle anbieten. Laravel
kann darüber z. B. hochgeladene Dokumente speichern, statt sie auf dem eigenen Server
abzulegen.

**Docker / Docker Compose**
Docker verpackt eine Anwendung mitsamt allen Abhängigkeiten in einen isolierten **Container**,
der auf jedem Rechner gleich läuft. Docker Compose startet mehrere zusammengehörige Container
(hier: PHP, Nginx, PostgreSQL, Redis, Node) mit einem einzigen Befehl.

**WSL2 (Windows Subsystem for Linux, Version 2)**
Eine echte, leichtgewichtige Linux-Umgebung, die direkt unter Windows läuft (technisch eine
kleine virtuelle Maschine mit eigenem Linux-Kernel) — man kann darin z. B. Ubuntu installieren
und ganz normale Linux-Programme wie `apt install` ausführen, ohne einen separaten Rechner oder
eine klassische VM zu brauchen. Auf der Entwicklungsmaschine dieses Projekts läuft die gesamte
PHP-/PostgreSQL-Toolchain innerhalb von WSL2, weil Docker Desktop dort (Windows auf ARM64)
nicht funktioniert — siehe [development.md](development.md).

**Nginx**
Ein Webserver, der eingehende Anfragen entgegennimmt und PHP-Anfragen an PHP-FPM weiterreicht.

**PHP-FPM**
Der Prozess, der PHP-Code tatsächlich ausführt und die Ergebnisse an den Webserver (Nginx)
zurückgibt.

## Tools & Qualitätssicherung

**Pest / PHPUnit**
Test-Frameworks für PHP. PHPUnit ist die etablierte Grundlage, Pest baut darauf auf und bietet
eine kompaktere, lesbarere Schreibweise für Tests (in diesem Projekt verwendet).

**Laravel Pint**
Ein Code-Style-Werkzeug für PHP: formatiert Code automatisch nach einem einheitlichen Standard
(Einrückung, Leerzeichen, Reihenfolge von Imports, ...), damit der ganze Code gleich aussieht,
egal wer ihn geschrieben hat.

**ESLint**
Das Pendant zu Pint für JavaScript/TypeScript/Vue: findet stilistische und potenzielle
Logikfehler im Frontend-Code.

**npm**
Der Paketmanager für JavaScript — verwaltet und installiert alle Frontend-Bibliotheken
(Vue, Tailwind, Vite, ...), definiert in `package.json`.

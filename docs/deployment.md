# Deployment

Zwei Wege sind vorbereitet: ein **kostenloser** (Render + Neon, kein Zahlungsmittel nötig — die
hier empfohlene Option) und ein **nutzungsbasiert kostenpflichtiger** (Laravel Cloud, braucht ein
Zahlungsmittel, dafür komfortabler). Beide bringen die öffentliche Startseite inkl.
[Demo-/Gastzugang](authentication.md#demo-login-gastzugang) live.

## Empfohlen: Render (App) + Neon (Datenbank) — kostenlos

Kommt dem Prinzip von Streamlit Community Cloud am nächsten: kein Zahlungsmittel nötig, echte
Kostenfreiheit statt nur "kostenlose Testphase". Zwei Anbieter, weil Renders eigene kostenlose
PostgreSQL-Datenbank nach 30 Tagen abläuft und gelöscht wird — Neons kostenlose Datenbank läuft
dagegen dauerhaft weiter.

**Kompromisse gegenüber Laravel Cloud** (bewusst in Kauf genommen für Kostenfreiheit):
- Die App "schläft" nach 15 Minuten Inaktivität ein; die erste Anfrage danach dauert bis zu einer
  Minute (Render "kaltstartet" den Container neu).
- Kein natives PHP-Buildpack bei Render — läuft daher über ein selbst geschriebenes,
  **ungetestetes** Docker-Image (`docker/render/Dockerfile`, siehe unten). Sollte funktionieren,
  wurde aber nie gegen einen echten Render-Account verifiziert (kein Docker auf der
  Entwicklungsmaschine lauffähig, siehe [development.md](development.md)) — beim ersten
  Deploy-Versuch ggf. Fehlermeldungen im Render-Dashboard mit dieser Doku abgleichen.
- Migrationen laufen bei jedem Container-Start neu (statt in einem separaten Release-Schritt) —
  unproblematisch, da `migrate --force` idempotent ist, aber ein kleiner Zeitverlust bei jedem
  Aufwachen aus dem Ruhezustand.

### Schritt 1 — Neon-Datenbank anlegen

1. Auf [neon.tech](https://neon.tech) kostenlos registrieren (kein Zahlungsmittel nötig).
2. Neues Projekt anlegen, PostgreSQL-Version 16 oder 17 wählen.
3. Verbindungsdaten notieren (Host, Port, Datenbankname, Nutzername, Passwort — Neon zeigt diese
   einzeln an, zusätzlich zu einer kombinierten Connection-URL).

### Schritt 2 — Render Web Service anlegen

1. Auf [render.com](https://render.com) kostenlos registrieren (kein Zahlungsmittel nötig).
2. "New" → "Web Service" → GitHub-Repository `housing-management-saas` verbinden.
3. **Environment**: "Docker" auswählen (nicht "Node"/"Python" — Render hat kein natives
   PHP-Buildpack).
4. **Dockerfile Path**: `docker/render/Dockerfile`
5. **Instance Type**: "Free" auswählen.
6. Umgebungsvariablen setzen (orientiert an `.env.example`):
   ```
   APP_NAME=PropertyManager
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=<lokal per "php artisan key:generate --show" erzeugen und hier eintragen>
   APP_URL=<wird von Render nach dem ersten Deploy als https://xxx.onrender.com angezeigt>
   DB_CONNECTION=pgsql
   DB_HOST=<von Neon>
   DB_PORT=<von Neon, meist 5432>
   DB_DATABASE=<von Neon>
   DB_USERNAME=<von Neon>
   DB_PASSWORD=<von Neon>
   SESSION_DRIVER=database
   QUEUE_CONNECTION=database
   CACHE_STORE=database
   FILESYSTEM_DISK=local
   MAIL_MAILER=log
   ```
   `APP_KEY` **nicht** bei jedem Deploy neu generieren (würde bestehende Sessions/verschlüsselte
   Daten ungültig machen) — einmal lokal erzeugen, dauerhaft als Umgebungsvariable setzen.
7. "Create Web Service" — Render baut das Docker-Image (Node-Stage für die Frontend-Assets,
   PHP/Nginx-Stage für die Anwendung) und deployt automatisch.

### Automatische Deploys

Render deployt standardmäßig bei jedem Push auf den verbundenen Branch neu (wie Laravel Clouds
"Push to Deploy") — kein zusätzlicher GitHub-Actions-Workflow nötig.

## Alternative: Laravel Cloud — nutzungsbasiert kostenpflichtig

[Laravel Cloud](https://cloud.laravel.com) ist die offizielle, von Laravel selbst betriebene
Hosting-Plattform — komfortabler (kein eigenes Docker-Image nötig, kein Kaltstart-Warten,
komplett dashboard-gesteuert), aber **ein Zahlungsmittel ist auch im günstigsten Tarif
erforderlich** (Starter: 5 $/Monat Grundgebühr, erster Monat kostenlos, danach nutzungsbasiert
plus Grundgebühr — siehe [Pricing](https://laravel.com/cloud/pricing)). Gegen unerwartet hohe
Rechnungen (z. B. bei plötzlich vielen Besucher:innen) lässt sich im Dashboard ein
**Spending Limit** setzen: Bei Erreichen pausiert der Compute automatisch, statt dass eine
Rechnung eskaliert.

### Voraussetzungen (bereits erfüllt)

- Laravel-Version ≥ minimal unterstützter Version (wir nutzen 12.66, weit über dem Minimum)
- PHP-Version-Constraint in `composer.json` kompatibel (`^8.2`)
- Kein Code mit hartcodierten Dateisystempfaden oder direkten Shell-Aufrufen, der die
  Portabilität einschränken würde
- Standard-Healthcheck-Route `/up` vorhanden (Laravel-Default)

### Setup-Schritte

1. Repository nach GitHub pushen (bereits erledigt).
2. Auf [cloud.laravel.com](https://cloud.laravel.com) registrieren.
3. "New Application" → Repository `housing-management-saas` auswählen.
4. Datenbank-Ressource hinzufügen: "Create and connect a database" → **PostgreSQL**
   (Serverless Postgres empfiehlt sich für geringe/unregelmäßige Last, da es ebenfalls
   hibernieren kann — passend für eine Demo-/Grundstruktur-Anwendung).
5. Optional: **Redis**-Ressource hinzufügen, sobald Cache/Queue/Sessions tatsächlich auf Redis
   umgestellt werden (aktuell laufen sie auf dem `database`-Treiber, siehe
   [architecture.md](architecture.md) — funktioniert auch ohne Redis-Ressource).
6. Build-/Deploy-Befehle im Dashboard prüfen bzw. setzen (Laravel Cloud erkennt Standard-Laravel-
   Apps automatisch; bei Bedarf explizit setzen):
   ```
   Build:  composer install --no-dev --optimize-autoloader && npm ci && npm run build
   Deploy: php artisan migrate --force
   ```
7. Umgebungsvariablen im Dashboard setzen — orientiert an `.env.example`
   (`APP_KEY` generiert Laravel Cloud i. d. R. automatisch beim ersten Deploy;
   `DB_*`-Variablen werden beim Verbinden der Datenbank-Ressource automatisch injiziert).
8. **Spending Limit setzen** (Settings → Billing) — empfohlen, z. B. 10–15 $/Monat als
   Sicherheitsnetz gegen unerwartet hohen Traffic.
9. "Save and Deploy".

### Queue-Worker und Scheduler

Für Hintergrundjobs (`QUEUE_CONNECTION=database`/`redis`) und den Scheduler
(`php artisan schedule:run`) lassen sich in Laravel Cloud zusätzliche, separat skalierbare
"Compute"-Prozesse im Dashboard definieren (analog zu den `queue`-/`scheduler`-Services in
`docker-compose.yml` für die lokale Entwicklung) — nicht Teil dieser Grundstruktur, da noch
keine Jobs existieren, die eine Queue tatsächlich brauchen.

### Health-Check

Laravel Cloud nutzt die Standard-Route `/up` (aus `bootstrap/app.php`,
`->withRouting(health: '/up')`) als Healthcheck — bereits vorhanden, keine Änderung nötig.

## Gastzugang / Demo-Modus

Für öffentlichen Zugang ohne Registrierung gibt es einen **Demo-Login**: Der Button
"Demo ausprobieren" auf der Startseite (`/`) loggt Besucher:innen ohne Passwort in einen
gemeinsamen, automatisch bereitgestellten Demo-Account ein (`App\Actions\ProvisionDemoAccount`,
siehe [authentication.md](authentication.md)). Kein zusätzliches Deployment-Setup nötig — der
Demo-Account wird beim ersten Klick automatisch angelegt.

## Andere Optionen (nicht vorbereitet, aber kompatibel)

Das vorhandene `docker-compose.yml`/`docker/php/Dockerfile`-Setup (siehe
[development.md](development.md)) funktioniert grundsätzlich auch als Basis für weitere
Docker-fähige PaaS-Anbieter wie **Railway** — falls weder Render noch Laravel Cloud passen.

# Deployment

## Laravel Cloud

[Laravel Cloud](https://cloud.laravel.com) ist die offizielle, von Laravel selbst betriebene
Hosting-Plattform — das Äquivalent zu "Streamlit Community Cloud" für Laravel-Apps: Repository
verbinden, Datenbank hinzufügen, fertig. Es ist **komplett dashboard-gesteuert**: Es gibt keine
zusätzliche Konfigurationsdatei, die im Repository liegen müsste — Build-Befehle, Deploy-Befehle
und Umgebungsvariablen werden alle über das Laravel-Cloud-Dashboard eingestellt, nicht über eine
Datei wie `vercel.json` oder `render.yaml`.

### Voraussetzungen (bereits erfüllt)

- Laravel-Version ≥ minimal unterstützter Version (wir nutzen 12.66, weit über dem Minimum)
- PHP-Version-Constraint in `composer.json` kompatibel (`^8.2`)
- Kein Code mit hartcodierten Dateisystempfaden oder direkten Shell-Aufrufen, der die
  Portabilität einschränken würde
- Standard-Healthcheck-Route `/up` vorhanden (Laravel-Default)

### Setup-Schritte

1. Repository nach GitHub pushen (bereits erledigt).
2. Auf [cloud.laravel.com](https://cloud.laravel.com) registrieren (Kreditkarte erforderlich,
   auch im kostengünstigen Pay-as-you-go-"Starter"-Tarif — es wird nur die tatsächliche Nutzung
   berechnet, mit Hibernation nach konfigurierbarer Inaktivität, um Kosten gering zu halten).
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
8. "Save and Deploy" — Laravel Cloud baut ein Docker-Image mit der gewählten PHP-Version,
   installiert Abhängigkeiten, baut die Frontend-Assets und stellt die App unter einer
   automatisch generierten Domain bereit (eigene Domain später verbindbar).

### Automatische Deploys

**Push to Deploy ist standardmäßig aktiviert**: Jeder Push auf den konfigurierten Branch
(typischerweise `main`) löst automatisch ein neues Deployment aus — kein zusätzlicher
GitHub-Actions-Workflow nötig. Alternativ lässt sich ein "Deploy Hook" (eine URL, die per POST
angestoßen wird) für eine CI/CD-Pipeline aktivieren, falls Deploys erst nach erfolgreicher CI
ausgelöst werden sollen (Settings → Deployments).

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

Das vorhandene `docker-compose.yml`/`docker/`-Setup (siehe [development.md](development.md))
funktioniert grundsätzlich auch als Basis für Docker-fähige PaaS-Anbieter wie **Railway** oder
**Render** (beide unterstützen Deploy aus einem Dockerfile plus verwaltete PostgreSQL-Instanz,
mit kostenlosem Einstiegstarif) — falls Laravel Cloud aus irgendeinem Grund nicht passt.

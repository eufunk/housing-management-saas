# Authentifizierung

Kommt unverändert aus dem offiziellen `laravel/vue-starter-kit` (Laravel-Standardmechanismen,
kein Zusatzpaket):

- **Login / Logout** — `App\Http\Controllers\Auth\AuthenticatedSessionController`
- **Registrierung** — `App\Http\Controllers\Auth\RegisteredUserController`
- **Passwort vergessen / zurücksetzen** — `PasswordResetLinkController`, `NewPasswordController`
- **E-Mail-Verifizierung** — `EmailVerificationPromptController`, `VerifyEmailController`,
  `EmailVerificationNotificationController`
- **Passwort erneut bestätigen** (für sensible Aktionen) — `ConfirmablePasswordController`

Session-basierte Authentifizierung (Laravel-Default, `SESSION_DRIVER=database`), Passwörter
werden über den `hashed`-Cast automatisch mit Bcrypt gehasht (`BCRYPT_ROUNDS=12` in `.env`).

## Erweiterung: Registrierung und Organisationszuordnung

Der Standard-Registrierungs-Flow legt aktuell nur einen `User` an, ohne ihn einer Organisation
zuzuordnen (`current_organization_id` bleibt `null`). Das ist bewusst nicht Teil dieser
Grundstruktur — sobald ein Organisations-Onboarding existiert (z. B. "Organisation anlegen"
oder "Einladung annehmen" nach der Registrierung), wird `RegisteredUserController` bzw. ein
neuer Onboarding-Controller den Nutzer in `organization_user` eintragen und
`current_organization_id` setzen. Bis dahin sind neu registrierte Nutzer zwar authentifiziert,
sehen aber mangels Organisation keine organisationsgebundenen Daten (siehe
[multi-tenancy.md](multi-tenancy.md), Fail-Closed-Verhalten von `OrganizationScope`).

Tests für den bestehenden Auth-Flow liegen unter `tests/Feature/Auth/`.

## Demo-Login (Gastzugang)

Für öffentlichen Zugang ohne Registrierung gibt es zusätzlich `App\Http\Controllers\Auth\
DemoLoginController` (`POST /demo-login`, Route-Name `demo-login`, `guest`-Middleware +
`throttle:10,1`). Er loggt Besucher:innen ohne Passwortabfrage in einen **einzigen, gemeinsam
genutzten** Demo-Account ein, den `App\Actions\ProvisionDemoAccount` bei Bedarf automatisch
anlegt (Organisation "Demo Hausverwaltung", Nutzer `demo@immodesk.app`, Rolle
`property_manager`) — idempotent: Existiert der Account bereits, wird er wiederverwendet;
wurde er zwischenzeitlich gelöscht, wird er beim nächsten Klick automatisch neu angelegt
("self-healing").

**Bewusste Vereinfachung**: Der Demo-Account wird **nicht** pro Besuch neu/isoliert erzeugt,
sondern von allen Gästen geteilt. Das ist unkritisch, solange keine echten,
veränderbaren Geschäftsdaten hinter den Modulen stehen (aktuell nur EmptyState-Platzhalter,
siehe [architecture.md](architecture.md)) — sobald echte CRUD-Funktionalität für die
demonstrierten Module existiert, sollte auf eine Sitzung-isolierte Demo-Bereitstellung
(z. B. pro Besuch ein frischer, nach Ablauf automatisch bereinigter Account) umgestellt werden.

Der Button "Demo ausprobieren" befindet sich auf der öffentlichen Startseite
(`resources/js/pages/Welcome.vue`).

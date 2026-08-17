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

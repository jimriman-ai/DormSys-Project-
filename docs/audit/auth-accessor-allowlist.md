# Auth Accessor Allowlist (Wave 1 / HD-W1-Q1 Option B)

**Status:** ACTIVE for Wave 1 · **T3 Guardrail:** PARKED until Lead opens T3  
**Marker:** `WAVE1-AUTH-ALLOWLIST`

## Rule (HD-W1-Q1)

Production code may use **only** `auth('identity')`, **except** the allowlisted entries below.

Forbidden everywhere else: `Auth::user()`, `Auth::guard(...)`, bare `auth()`, `$request->user()`.

## Allowlisted production paths

| File | Symbols | Justification |
|------|---------|---------------|
| `app/Infrastructure/Auth/SessionAuthenticator.php` | `Auth::attempt`, `Auth::user`, `Auth::logout` | Auth Foundation password login on default/`web` + `App\Models\User`. `UserModel::getAuthPassword()` throws — cannot use `auth('identity')` without behavior change. Paired with `LoginUserAction` / `LogoutUserAction`. |
| `app/Infrastructure/Auth/SessionAuthUserResolver.php` | `Auth::user` | Must resolve the **same** default/`web` session as `SessionAuthenticator`. Moving to `auth('identity')` alone returns null after web login (breaks `GetCurrentAuthUserAction`). |

## Explicitly NOT allowlisted (out of Wave 1 / frozen-adjacent)

| File | Note |
|------|------|
| `app/Application/Auth/EstablishApiSessionFromCredentialLoginAction.php` | `Auth::guard('api'|'identity')` — dual-session / **DBT-3** surface. DEBT-DISCOVERY; do not “fix” under Wave 1. |
| `app/Http/Controllers/Web/AuthSessionController.php` | Same dual logout pattern — DBT-3 adjacent. |
| `app/Http/Controllers/ApiAuthSessionController.php` | Same. |

## T2 / T3 dependency

- **T2:** DONE — bare `auth()` in `tests/Feature/Auth/**` replaced with `auth('web')` (Auth Foundation session; matches T1 allowlist guard).
- **T3:** DONE — PHPStan rule `App\Rules\PHPStan\NoBareAuthCallRule` registered in `phpstan.neon`. Allowlist = SessionAuthenticator + SessionAuthUserResolver only.

## Sunset

Remove allowlist entries only after Lead HD unifies Auth Foundation credential login onto `auth('identity')` (may require `users` table HD and/or identity password support — currently forbidden).

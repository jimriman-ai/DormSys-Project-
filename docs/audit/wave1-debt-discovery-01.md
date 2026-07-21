# DEBT-DISCOVERY-01 — Completion Wave 1 (Auth remediation)

**Registered:** 2026-07-21 · **Source wave:** Completion Wave 1  
**Rule:** Log newly discovered out-of-scope violations; **do not fix** in Wave 1.  
**Parent handoff:** `docs/audit/completion-wave1-handoff.md`

---

## DEBT-W1-01 — Dual-guard Auth facade (DBT-3 adjacent)

| Field | Value |
|-------|--------|
| Status | **OPEN** — registered; not remediated |
| Classification | FROZEN execution restriction (**DBT-3** / `auth:api`) |
| Evidence | `app/Application/Auth/EstablishApiSessionFromCredentialLoginAction.php` L24–25 (`Auth::guard('api'|'identity')->loginUsingId`) |
| | `app/Http/Controllers/Web/AuthSessionController.php` L63–64 |
| | `app/Http/Controllers/ApiAuthSessionController.php` L60–61 |
| Why not Wave 1 | HD-W1-Q1 forbids `Auth::guard(...)`; fixing requires dual-session / DBT-3 migrate — **STOP-F** without Lead unfreeze |
| Wave 2 | **EXCLUDE** unless Lead explicitly scopes DBT-3 |
| Closure | `UNFREEZE DBT-3` + dedicated WP; then replace with `auth('identity')`-only design per HD |

---

## DEBT-W1-02 — `auth('api')` helper on employee records HTTP

| Field | Value |
|-------|--------|
| Status | **OPEN** — registered |
| Classification | DBT-3 / mixed-guard surface |
| Evidence | `app/Http/Controllers/EmployeeRecordController.php` — `auth('api')->user()` |
| Note | Named helper (not bare `auth()`); T3 PHPStan rule does **not** fail this today |
| Wave 2 | **EXCLUDE** from dormitory isolation wave |
| Closure | Same guard consolidation HD as DBT-3 / employee_records |

---

## DEBT-W1-03 — Auth Foundation allowlist (accepted temporary)

| Field | Value |
|-------|--------|
| Status | **ACCEPTED** under HD-W1-Q1 Option B |
| Classification | ACCEPTED DEBT (sunset = credential unify HD) |
| Evidence | `SessionAuthenticator` / `SessionAuthUserResolver`; `docs/audit/auth-accessor-allowlist.md` |
| Constraint | `UserModel::getAuthPassword()` throws — cannot move attempt to `auth('identity')` without behavior/HD |
| Wave 2 | Do not silently remove allowlist |
| Closure | Lead HD: identity password strategy **or** retire Auth Foundation web stack; plus `users` table HD |

---

## DEBT-W1-04 — Guardrail coverage gap (`Auth::guard`)

| Field | Value |
|-------|--------|
| Status | **OPEN** — tooling |
| Classification | TOOLING / QUALITY DEBT |
| Evidence | `NoBareAuthCallRule` forbids `Auth::{user,attempt,logout}` only; `Auth::guard(*)` not flagged |
| Wave 2 | Optional; not module-isolation |
| Closure | Lead authorize rule expansion + allowlist entries for DEBT-W1-01 until DBT-3 closed |

---

## DEBT-W1-05 — Full-suite known failures (baseline Option A)

| Field | Value |
|-------|--------|
| Status | **DOCUMENTED** — not Wave 1 blockers |
| Classification | OUT-OF-SCOPE clusters |
| Evidence | `docs/audit/wave1-baseline-known-fail.md` — 43 failed / 1912 passed (2026-07-21 Sail run) |
| Clusters | Lottery (HD-02 FROZEN); Request `InvalidRequestTransitionException`; Architecture WF/module inventory |
| Wave 2 | Do not treat as Wave 2 Auth/isolation acceptance criteria unless Lead says so |
| Closure | Per-cluster WPs; Lottery only after HD-02 unfreeze |

---

## DEBT-W1-06 — Laravel `users` table integer PK

| Field | Value |
|-------|--------|
| Status | **PENDING HD** |
| Classification | Schema — DO NOT TOUCH |
| Evidence | HD-W1-Q2; `database/migrations/0001_01_01_000000_create_users_table.php` |
| Wave 2 | **Forbidden** without separate HD |
| Closure | Dedicated HD (not HD-W1-Q3 inventory) |

---

## DEBT-W1-07 — `$request->user()` / `$request->user('api')` (HD-W1-Q1)

| Field | Value |
|-------|--------|
| Status | **OPEN** — registered |
| Classification | Auth accessor debt (not bare `auth()`, not covered by T3 rule today) |
| Evidence | `app/Modules/Audit/Presentation/Http/Middleware/ResolveAuditPrincipalMiddleware.php` L19 — `$request->user()` |
| | `app/Modules/Request/Presentation/Http/Middleware/EnforceSessionMutationPrincipalMiddleware.php` L25 — `$request->user('api')` |
| Wave 2 | **EXCLUDE** from dormitory isolation unless Lead scopes auth middleware |
| Closure | Replace with `auth('identity')` (or explicit guard policy) under dedicated Auth WP; DBT-3 if `api` |

---

## Explicit non-debts (Wave 1 closed correctly)

| Item | Disposition |
|------|-------------|
| Bare `auth()` in `tests/Feature/Auth` | Fixed T2 → `auth('web')` |
| Bare `auth()` / `Auth::user|attempt|logout` in `app/` outside allowlist | None remaining (T3 verified) |
| Domain UUID PKs via `HasUuid` | Already v7 — inventory only |

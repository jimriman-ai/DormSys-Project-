# L3 Spec — employee-auth-ui

**Lifecycle status:** **L9-pending (L8-COMPLETE)** — W-01…W-08 CLOSED (`work-breakdown.md`); F-W07-04 Wave 1 **COMPLETED** (**F-W07-04-D3**) does not reopen L6/L7/L8 for closed boundary items.

## §1 Scope

- Login UI for dormitory-manager employees using guard `identity`.
- **OUT OF SCOPE:** password reset, registration, remember-me, API tokens.

## §2 Auth Mechanism

- **Guard:** `auth:identity` (explicit on all F2 routes; default guard stays `web` — closes G-4).
- **Provider:** `identity` → `app/Modules/Identity/Infrastructure/Persistence/Models/UserModel.php` (canonical path per Lead Confirmation A).
- **Login executed via** `EstablishApiSessionFromCredentialLoginAction` (`execute(string $email): bool` — session bridging only).
- Credential verification is handled by `LoginUserAction` → `SessionAuthenticator::login()` → `Auth::attempt()`. `EstablishApiSessionFromCredentialLoginAction` performs session bridging only and must not be called before successful verification (C-5).
- **NO password broker (W-03 / Option A):** `UserModel.getAuthPassword()` stays as-is (`LogicException`). G-1, G-2, G-3 are closed as **NOT-APPLICABLE** for F2 (credential/token flow, not password flow).
- **Guard pinning:** because UserModel `$guard_name = ['web', 'identity']`, all permission/role checks in F2 must pin guard `identity` explicitly (see G-8).

## §3 W-03 Resolution Record

- **Decision:** no password broker required.
- **Rationale:** F2 auth flow is credential/token-based via `EstablishApiSessionFromCredentialLoginAction`; no password reset in scope.
- **Reference:** W-03 Option A — no password broker (Lead-confirmed for this phase). Login via `EstablishApiSessionFromCredentialLoginAction`.

## §4 W-01 Work Item Spec

*(W-01/W-06 implementation evidence accepted under Lead Option B — Governance Reconciliation. Further F2/UI/Auth features remain frozen unless newly authorized. Boundary W-07/W-08 **CLOSED** — lifecycle **L9-pending (L8-COMPLETE)**; see work-breakdown.md.)*

- **Livewire component:** `EmployeeLogin` — landed at `app/Modules/Auth/Presentation/Livewire/EmployeeLogin.php`
- **View:** `resources/views/livewire/auth/employee-login.blade.php`
- **Route:** `GET /employee/login` named `employee.login` (guest-accessible under `guest:api`; Livewire `wire:submit` — no separate POST route required)
- **On success:** invoke `EstablishApiSessionFromCredentialLoginAction`, then redirect to `route('requests.index')`.
- **Protected routes:** middleware `auth:identity`.
- **Existing dormitory-admin `auth:identity` routes:** untouched, out of scope (Lead Confirmation B). Evidence: `routes/web.php` (`prefix('dormitory-admin')` + `middleware(['auth:identity'])`).
- **Sequencing:** W-01 (Login Livewire) and W-06 (IdentityRoleGuard → Shared Kernel) are **PARALLEL** execution. W-01 has no hard-sequence dependency on W-06. W-06 landed at `app/Shared/Auth/IdentityRoleGuard.php`.

## §5 Constraints

- No changes to `UserModel`, `config/auth.php`, migrations, or existing routes (in this L3 docs step / F2 constraint for those artifacts until separately authorized).
- Any future FK work must use `restrictOnDelete()`.

### C-5
C-5: EstablishApiSessionFromCredentialLoginAction MUST only be called after LoginUserAction has successfully validated credentials.
**Enforcement:** L7 security review + L8 test asserting the Livewire component never invokes the action on failed `LoginUserAction` result.

### G-8: Guard pinning — W-01 must explicitly use `auth:identity` for login
and must not bypass `LoginUserAction` before calling `EstablishApiSessionFromCredentialLoginAction`.
**Status:** CLOSED (Lead decision, 1405/04/24). Mitigated by C-5 for invocation order; guard pin remains mandatory for F2 routes.

## §6 Open Items

### OI-1 — Action signature deviation
**Status:** CLOSED — NO DEVIATION (Lead decision, 1405/04/24)
**Resolution:** `EstablishApiSessionFromCredentialLoginAction::execute(string $email): bool`
is intentionally credential-free. Credential verification is performed upstream by
`LoginUserAction → SessionAuthenticator::login() → Auth::attempt()`. The action's sole
responsibility is post-verification session bridging onto the `api` and `identity` guards.
The original spec assumption of a `credential` parameter was incorrect; the code is canonical.

### OI-2 — Post-login redirect target
**Status:** RESOLVED (Lead decision, 1405/04/24)
**Target:** `route('requests.index')` — consistent with existing `AuthSessionController`
landing pattern. Subject to revision only if product defines a feature-specific
post-login destination for `employee-auth-ui`.
**Middleware note:** Landing route `requests.index` is protected by `auth:api` (dual-bound guard).
W-01 Livewire component must redirect post-login to this route.

## §7 Authorization Reference

`docs/product/product-authorization-employee-auth-ui.md`

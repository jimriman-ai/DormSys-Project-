# W-07 Security Review — Scope Checklist

> **Status:** APPROVED WITH AMEND (Lead, 1405/04/24) — execution authorized.  
> **Amendments applied:** A-1 (auth config + dual models in §B), A-2 (throttle + logout/invalidation in §D), A-3 (password-broker negative-check in §E).  
> **NOT AUTHORIZED:** code fixes, W-08 execution, Spec04 / Dormitory Auth packet.  
> **Feature:** `employee-auth-ui` (F2)  
> **Prerequisite:** W-02 CLOSED (DGAP-07 Decision A).

---

## In-scope surfaces

| # | Surface | Evidence path | Review intent |
|---|---------|---------------|---------------|
| S-1 | `EmployeeLogin` Livewire | `app/Modules/Auth/Presentation/Livewire/EmployeeLogin.php` | Credential → bridge order, failure paths, redirect |
| S-2 | Guest login view / layout | `resources/views/livewire/auth/employee-login.blade.php`; `resources/views/components/layouts/guest.blade.php` | No secret leakage; form posts only to Livewire action |
| S-3 | Route `GET /employee/login` (`employee.login`) | `routes/web.php` under `guest:api` | Guest middleware; no auth bypass on login entry |
| S-4 | `IdentityRoleGuard` (Shared Kernel) | `app/Shared/Auth/IdentityRoleGuard.php` | Identity guard role check only |
| S-5 | Session / multi-guard flow | `LoginUserAction` → `EstablishApiSessionFromCredentialLoginAction`; `LogoutUserAction`; regenerate | C-5; api + identity binding |
| S-6 | Auth config + dual principals **(A-1)** | `config/auth.php`; `app/Models/User.php`; `UserModel.php` | Guard/provider duality `web`/`api`/`identity` vs L3 `auth:identity` / `auth:api` |

**Out of scope:** Spec04 Auth; BL-B1-01; inventing password broker (W-03 NO ACTION — negative check only); Eloquent UserModel↔Employee (DGAP-07 A); W-08 tests; any code change.

---

## Checklist (execution)

### A. Call-chain & C-5

- [x] A1–A4 — see report

### B. Route & middleware (+ A-1)

- [x] B1–B3 + config/auth.php / User / UserModel surfaces

### C. Guards & IdentityRoleGuard

- [x] C1–C3 — see report

### D. Session / credentials hygiene (+ A-2)

- [x] D1–D3 + rate-limit / throttle on `/employee/login` + logout / session invalidation completeness

### E. DGAP-07 / W-03 negative checks (+ A-3)

- [x] E1–E2 + no password-broker exposure for F2

---

## Approval gate (closed)

| Field | Value |
|-------|-------|
| Lead approves scope? | **YES with AMEND** |
| Amendments | A-1, A-2, A-3 |
| Authorized to execute review? | **YES** (findings only; no code) |

**Report:** [w07-security-review-report.md](./w07-security-review-report.md)

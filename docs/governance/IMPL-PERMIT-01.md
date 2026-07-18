# IMPL-PERMIT-01 — Spec04 Implementation Manifest

| Field | Value |
|-------|-------|
| **Permit ID** | **IMPL-PERMIT-01** |
| **Status** | **IMPLEMENTATION AUTHORIZED (LIMITED SCOPE)** — **APPROVED** by Lead; L5/L6 Auth Gate **OPEN** for §2 scope only |
| **Surface(s)** | `employee-request-self-service` + `department-request-approver-console` (Stage-1 only) |
| **Feature Contract** | `docs/ui/contracts/requests/employee-request-self-service.feature-contract.yaml` (`1.0.0-READY`) |
| **Auth Packet** | `docs/specs/spec04-auth-packet.md` (`0.5.0-GOVERNANCE-ACCEPTED`) |
| **IMP-Q Pack** | `docs/governance/spec04-imp-q-human-decision-pack.md` (DECISIONS RECORDED) |
| **Issued** | 1405/04/27 \| 2026-07-18 |
| **Approved-On** | 1405/04/27 \| 2026-07-18 |
| **Decision-Owner** | Lead |

> This manifest is the **only** authorization for Spec04-related development listed below.  
> Work outside **Authorizations** is forbidden.  
> Architecture and settled governance decisions must not be reopened.

---

## 1. Purpose

Authorize **strictly bounded** implementation for Spec04 employee request self-service and Stage-1 Approver Console, aligned to recorded IMP-Q decisions and the READY Feature Contract — without expanding into unrelated modules, frozen contracts, or unsettled gaps (DGAP-03, SGAP-05, Workflow redesign).

---

## 2. Authorizations (THE ONLY ALLOWED ACTIONS)

The following are the **sole** permitted implementation actions under IMPL-PERMIT-01:

### 2.1 Migration — Stage-1 snapshot column (IMP-Q-02 A)

| Item | Authorization |
|------|----------------|
| Table | `requests` |
| Column | `assigned_stage1_approver_identity_id` (UUID, nullable until populated at submit) |
| Semantics | Persist Stage-1 assigned approver identity at request submit (OQ-AUTH-03 B / DGAP-06 V1) |
| FK | **Authorized:** foreign key to `identity_users.id` with **`restrictOnDelete()`** |
| Rollback | Migration must include rollback (drop column / drop FK as appropriate) |
| Out of scope | Any other schema change; cross-module FKs beyond this authorized column |

> **Note:** This permit’s FK + `restrictOnDelete()` is the binding migration rule for IMPL-PERMIT-01 execution. Do not invent additional columns or tables under this permit.

### 2.2 Routes — Dual URL boundaries (IMP-Q-01 A)

| Surface | Path prefix | Named routes | Middleware (conceptual) |
|---------|-------------|--------------|-------------------------|
| Employee self-service | `/employee/requests/*` | `employee.requests.*` | `auth:identity` + `identity.role:employee` (+ V1 as applicable) |
| Stage-1 Approver Console | `/approvals/stage1/*` | `approvals.stage1.*` | `auth:identity` + `identity.role:DeptMgr` (+ V1 as applicable) |

**Allowed:** Register these route groups and wire Livewire/HTTP entry points needed for authorized UI/logic only.  
**Forbidden:** Embedding approve/reject under `/employee/requests/*`; using dormitory-manager roles for Stage-1.

### 2.3 Roles — IdentityRoleSeeder (IMP-Q-06 A)

| Item | Authorization |
|------|----------------|
| Seeder | Extend `database/seeders/IdentityRoleSeeder.php` |
| Roles | Ensure Spatie roles **`employee`** and **`DeptMgr`** exist with **`guard_name = identity`** |
| Forbidden | Creating these roles on `web` guard; reusing `dormitory-manager` / `dormitory-unit-manager` as Stage-1 |

### 2.4 Logic — Application Actions (IMP-Q-04 B)

| Item | Authorization |
|------|----------------|
| Pattern | Livewire (or authorized presentation) → **Application Actions** (no UI-owned auth) |
| Employee | Request **creation** and **cancellation** Application Action paths (build/wire/complete as needed for IMP-Q-05: List, Show, Create, Cancel) |
| Snapshot write | On submit/create path, persist `assigned_stage1_approver_identity_id` per org-chart resolution chain |
| Stage-1 console | Application Actions for Stage-1 **approve** / **reject** invoked only from Approver Console surface |
| Forbidden | Presentation-layer authorization decisions; client-supplied `approverId` spoofing; employee-surface approve/reject |

### 2.5 UI — Approver Console Stage-1 (IMP-Q-07 A)

| Item | Authorization |
|------|----------------|
| Surface | Separate Approver Console for Stage-1 approvals (`department-request-approver-console`) |
| Ops | Stage-1 approve / reject UI bound to Application Actions (IMP-Q-04 B) |
| Employee UI | List, Show, Create, Cancel only (IMP-Q-05 B) — **no** approval controls |
| Contracts | Successor artifacts only (IMP-Q-03 B); may add new console Feature/contract artifacts — **do not edit frozen** request-list / request-show contracts |

---

## 3. Strict Boundaries (Non-negotiable)

1. **No architecture changes** — Modular Monolith / Clean Architecture / DDD Lite layering remains as-is; Domain must not import outer layers.
2. **No reopening of decision boundaries** — DGAP-05, DGAP-06, OQ-AUTH-01/02/03/05, and IMP-Q-01…07 selections are fixed.
3. **Successor contracts only** — Do **not** modify existing frozen/approved Feature Contracts or implementation locks for legacy request-list / request-show.
4. **No unrelated GAP work** — Do not resolve DGAP-03, SGAP-05, Workflow engine redesign, or lottery governance under this permit.
5. **No scope creep** — No edit-draft, family flows, multi-type expansion, or dormitory-admin structure PEP under this permit.
6. **Audit** — State transitions that exist must continue to emit audit via established AuditService patterns where applicable to Request mutations.

---

## 4. Explicitly NOT authorized by this permit

- Broad L5 “open season” beyond the routes/roles/middleware needed for §2
- Broad L6 beyond the listed Application Actions, migration, seeder, and UI surfaces
- Changes to Dual-Principal / IdentityRoleGuard architecture
- Password broker / credential topology changes
- Cross-module Eloquent queries or FKs other than the snapshot FK in §2.1
- Any work on frozen contract files listed as successor-untouchable

---

## 5. Definition of Done (for work under this permit)

1. Snapshot migration applied with rollback; column + FK `restrictOnDelete` as authorized.
2. Route groups `/employee/requests/*` and `/approvals/stage1/*` registered with identity role gates.
3. `employee` and `DeptMgr` identity roles seedable via IdentityRoleSeeder.
4. Employee List / Show / Create / Cancel operable without approve/reject on employee UI.
5. Approver Console Stage-1 approve/reject via Application Actions only.
6. PHPStan level 8 and Pint clean for touched code; tests covering authorized paths.
7. Frozen request-list / request-show artifacts unchanged.

---

## 6. Lead Implementation Signature

| Field | Value |
|-------|-------|
| Lead name | Lead |
| Signature | **APPROVED** (FINAL LEAD AUDIT & AUTHORIZATION) |
| Date (Jalali) | 1405/04/27 |
| Date (Gregorian) | 2026-07-18 |
| Scope confirmation | ☑ I authorize **only** §2 Authorizations under IMPL-PERMIT-01 |
| Audit result | **PASSED** — governance and implementation decisions reconciled |

**Coding under this permit is authorized for §2 only.** Prefix commits / significant blocks with `[PERMIT-ID: IMPL-PERMIT-01]`.

---

## 7. L5 / L6 Auth Gate

| Gate | Status | Notes |
|------|--------|-------|
| **L5 Auth gate** | ☑ **OPEN** | Opened by Lead for IMPL-PERMIT-01 §2.2 routes/roles/middleware scope only |
| **L6 Impl gate** | ☑ **OPEN** | Opened by Lead for IMPL-PERMIT-01 §2 implementation scope only |
| Lead L5 sign-off | Lead — FINAL LEAD AUDIT & AUTHORIZATION | Date: 2026-07-18 |
| Lead L6 sign-off | Lead — FINAL LEAD AUDIT & AUTHORIZATION | Date: 2026-07-18 |

> Work outside §2 remains **NOT AUTHORIZED**.

---

## Document control

| Field | Value |
|-------|-------|
| Path | `docs/governance/IMPL-PERMIT-01.md` |
| Supersedes | Prior “implementation NOT AUTHORIZED” blanket for the **limited scope in §2 only**, upon Lead Implementation Signature |
| Outside §2 | Remains **NOT AUTHORIZED** |

**Scope finalized for Lead Audit / Signature.**  
**No code, migration files, or routes were generated by this documentation task.**

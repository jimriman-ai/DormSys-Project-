# WP-REQ-01 — Scope Document

| Field | Value |
|-------|--------|
| Work Package | **WP-REQ-01** |
| Protocol | GOV-PP-01 |
| Lifecycle | **L2 (SCOPED)** → awaiting Lead authorization to **L3 (Execution)** |
| Domain | Requests (Domain-First Priority #1) |
| Authority | OQ-REQ-02 Option A — ACCEPTED (Lead L0); IMPLEMENTATION AUTHORIZED to open |
| Effective | 1405/04/29 \| 2026-07-20 |
| Spec target | Spec05 `research.md` **R-03**; `spec.md` FR-003/FR-004 pattern; `data-model.md` (no Identity FK); `plan.md` Constraints |
| Status | **SCOPED** — awaiting Lead L3 authorization |

> **NON-AUTHORITY until L3.** This document defines execution boundary for Lead review. No implementation until Lead authorizes L3.

---

## 1. BOUNDARY DECLARATION

### 1.1 Aggregate root(s) in scope

| Aggregate / artifact | In scope? | Notes |
|----------------------|-----------|--------|
| `Request` (root) | **Yes** — schema only | Column `assigned_stage1_approver_identity_id` remains; **FK constraint removed** |
| `RequestApproval` | No behavior change | Already UUID `approver_id` without Identity FK (`data-model.md`) |
| `DependentSnapshot` / members / mission | Out | No Identity FK |
| Identity module tables / models | **Out** | No writes; no Eloquent import into Request |

### 1.2 Identity resolution — OUTSIDE Request boundary

| Concern | Owner | WP-REQ-01 rule |
|---------|--------|----------------|
| Resolve active Stage-1 approver identity UUID | Integrations + Shared (`Stage1ApproverIdentityReadBridge`, `IdentityRoleGuard`) | **Do not move into Request Domain/Application** |
| Actor principal at HTTP/Livewire | Presentation (`Authenticatable` / `getAuthIdentifier()`) | Unchanged; not redesigned in this WP |
| Persist / filter by UUID string | Request Infra (`RequestRepository::listPendingStage1`) | Keep string UUID; **no** `belongsTo(UserModel)` |
| Enforce role (`dormitory-manager`) | Shared / Presentation boundary | Unchanged |

### 1.3 Explicit exclusions

| Domain / concern | Exclusion |
|------------------|-----------|
| **Reporting** | No WP tasks, no contracts, no migrations |
| **Lottery** | No WP tasks (LotteryRegistration type behavior untouched) |
| **Workflow** | Remains deferred (CD-010); no engine work |
| **Dormitory FK normalization (OQ-DORM-04)** | **SEQUENTIAL** — `WP-DORM-04` blocked until WP-REQ-01 **CLOSED** |
| **OQ-REQ-11** (Stage-1 snapshot scope) | **DEFERRED** — do not expand snapshot to other create paths |
| **OQ-REQ-12** (auth:api vs auth:identity) | **DEFERRED** to WP-REQ-07 |
| **D-G03-FORM / assigned sites** | Out — WP-REQ-04 |
| Dependent live source stub | Out — WP-REQ-03 |
| Stages 2–4 product UI | Out — WP-REQ-06 (optional) |

---

## 2. GAP LIST (evidence-grounded)

### 2.1 Current vs Spec05 target

| Aspect | Current state (evidence) | Spec05 target |
|--------|--------------------------|---------------|
| `employee_id` / `dormitory_id` | UUID, no FK | FR-003 / FR-004 / R-03 — UUID, no FK |
| `approver_id` on approvals | UUID, no FK | Spec US3 / data-model — UUID, no FK |
| `assigned_stage1_approver_identity_id` | UUID **plus** FK → `identity_users` (`restrictOnDelete`) | R-03 / plan Constraints: **no FK to `identity_*`**; UUID reference only |
| Migration permit comment | IMPL-PERMIT-01 authorized FK | Superseded by OQ-REQ-02 Option A |
| Module README | States no cross-module FK | Align narrative after drop |
| Eloquent `belongsTo` Identity | **Absent** in Request module (grep clean) | Keep absent |

### 2.2 Gaps in this WP

| ID | Description | Spec / ledger reference | Severity | Disposition |
|----|-------------|---------------------------|----------|-------------|
| **GAP-REQ-01-A** | Drop DB foreign key on `requests.assigned_stage1_approver_identity_id` → `identity_users.id` | Spec05 `research.md` R-03; `plan.md` Constraints; OQ-REQ-02 Option A | **HIGH** (boundary violation vs canonical spec) | **IN SCOPE** |
| **GAP-REQ-01-B** | Retain nullable UUID column + index; ensure app/repo mapping unchanged | `data-model.md` UUID-ref pattern; OQ-REQ-02 “retain UUID” | HIGH (data integrity) | **IN SCOPE** |
| **GAP-REQ-01-C** | New forward migration (do not rewrite historical migration in place on deployed DBs) | Constitution / migration rollback DoD | MEDIUM | **IN SCOPE** |
| **GAP-REQ-01-D** | Update Request README / permit comment trail to Option A (docs in module) | README L31–48 vs R-03 | LOW | **IN SCOPE** (module docs only) |
| **GAP-REQ-01-E** | Architecture / forbidden-import / ModuleBoundary remain green (Request must not gain Identity imports) | `boundary-rules.md`; DEC-ARCH-POLICY-01 precedent | HIGH (gate) | **IN SCOPE** (verify-only) |
| **GAP-REQ-11** | Stage-1 snapshot only on Personal create | OQ-REQ-11 OPEN | — | **DEFERRED** |
| **GAP-REQ-12** | Dual `auth:api` vs `auth:identity` routes | OQ-REQ-12 → WP-REQ-07 | — | **DEFERRED** |

No newly invented gaps beyond the above without DEBT-DISCOVERY-01 logging at L3.

---

## 3. MIGRATION IMPACT (Option A)

### 3.1 FK → DROP plan

1. Add **new** migration under `database/migrations/modules/request/` (dated after `2026_07_18_000001_…`).
2. `up()`:
   - `dropForeign` on `assigned_stage1_approver_identity_id` (driver-safe name resolution for PostgreSQL).
   - **Do not** drop column.
   - **Do not** drop index (retain for Stage-1 queue).
3. `down()`:
   - Re-add FK → `identity_users.id` with `restrictOnDelete` (rollback path only; not the canonical end-state).
4. **Do not** edit `2026_07_18_000001_…` body for environments that already applied it (history preservation). Optional comment-only annotation in that file is Lead-optional at L3; prefer new migration + README.

### 3.2 UUID retention / integrity strategy

| Rule | Detail |
|------|--------|
| Column | `assigned_stage1_approver_identity_id` `uuid` nullable — **kept** |
| Index | Kept for `listPendingStage1` filter |
| Referential integrity | Application / port responsibility (resolve via Identity outside Request); DB no longer enforces FK |
| Orphan UUIDs | Acceptable under R-03 (same as `employee_id`); Stage-1 assign path remains fail-closed at Application when no manager (`NoStage1ApproverAvailableException`) |
| Writes to Identity | **Forbidden** in this WP |

### 3.3 Ledger table — READ-ONLY

| Artifact | WP-REQ-01 rule |
|----------|----------------|
| `docs/governance/open-decisions.md` | **READ-ONLY** during L3 execution — **no writes** from this WP |
| Exception table row OQ-REQ-02 | Already SUPERSEDED / Option A recorded (pre-L3) |
| OQ-DORM-04 sequencing | Confirm only in delivery report; do not alter |

---

## 4. TEST STRATEGY

### 4.1 Freeze-track gate

| Gate | Requirement |
|------|-------------|
| **FULL SUITE** | Mandatory before WP-REQ-01 **CLOSED** (same bar as WP-DEBT-05): `php artisan test` → **0 failed** |
| Targeted first | Request Feature/Unit + Architecture (`RequestConsumerBoundaryTest`, ForbiddenImports / ModuleBoundary if Request Infra touched) |
| Evidence | Capture summary line in DELIVERY CONFIRMATION (no Git/SHA) |

### 4.2 Per-module isolation

| Rule | Detail |
|------|--------|
| No Dormitory coupling | Do not modify Dormitory migrations, models, or `user_id` FKs (OQ-DORM-04 / WP-DORM-04) |
| No Identity schema change | Do not alter `identity_users` |
| Request-only file set | Migration(s) under `modules/request/`; optional Request README; no Presentation/Application logic change unless L3 finds compile break (unexpected — flag DEBT) |

### 4.3 Rollback path

1. Migration `down()` restores FK (emergency only).
2. Prefer forward-fix if post-deploy data assumes no FK.
3. Session Handoff at WP close must state: applied migration name + full-suite summary (no SHA).

---

## 5. DEPENDENCY GATE

| Dependent | Status | Gate |
|-----------|--------|------|
| **WP-DORM-04** | SEQUENTIAL | **BLOCKED** until WP-REQ-01 status = **CLOSED** |
| WP-REQ-02… | Unrelated | May proceed only under separate Lead auth; must not reopen FK |
| OQ-REQ-11 / OQ-REQ-12 | OPEN | No implementation block on WP-REQ-01; must not be “fixed” here |

```text
WP-REQ-01 (this) ──CLOSED──► WP-DORM-04 (OQ-DORM-04) authorized to open
```

---

## 6. PROPOSED L3 TASK SEQUENCE (preview — not authorized until Lead)

| Step | Action |
|------|--------|
| T0 | STOP: porcelain clean; confirm OQ-REQ-02 Option A still in ledger (read-only) |
| T1 | Add drop-FK migration; retain column + index |
| T2 | Module README / permit narrative align to R-03 |
| T3 | Targeted Request + architecture tests |
| T4 | Full suite — 0 failed |
| T5 | DELIVERY CONFIRMATION + Session Handoff; mark WP-REQ-01 CLOSED (ledger write **only if Lead authorizes** a closeout append — default: Lead updates ledger) |

---

## 7. CONSTRAINTS (binding)

- No Git/SHA references in delivery artifacts — use **DELIVERY CONFIRMATION** only.
- Manual commit only (L4) — agent does not commit.
- Session Handoff mandatory at WP close.
- Any newly found debt → **DEBT-DISCOVERY-01** protocol (record; do not silently expand scope).
- Ledger `open-decisions.md` **READ-ONLY** during execution.

---

## 8. DONE WHEN (L3 acceptance criteria)

1. `requests.assigned_stage1_approver_identity_id` has **no** FK to `identity_users`.
2. Column + index still present; Stage-1 create/list/approve paths still pass targeted tests.
3. Full suite: **0 failed**.
4. No Dormitory / Lottery / Reporting / Workflow file changes.
5. WP-DORM-04 remains blocked in narrative until CLOSED recorded.
6. OQ-REQ-11 / OQ-REQ-12 untouched.

---

## 9. L0 REVIEW CHECKLIST

- [ ] Boundary declaration accepted
- [ ] Gap list accepted (deferred flags honored)
- [ ] Migration DROP plan accepted
- [ ] Full-suite gate accepted
- [ ] WP-DORM-04 sequential gate confirmed
- [ ] Authorize L3 Execution (yes / hold / amend)

**On Lead “Authorize L3”:** status → `WP-REQ-01 READY-FOR-L3` / `IN-PROGRESS` per Lead wording.

---

_End of WP-REQ-01 Scope Document — L2 SCOPED_

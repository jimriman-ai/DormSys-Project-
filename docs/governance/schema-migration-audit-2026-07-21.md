# Schema / Migration Read-Only Audit Report

**Mode:** READ-ONLY — no code, migration, or schema changes  
**Date:** 1405/04/30 \| 2026-07-21  
**Environment audited:** Sail PostgreSQL 17.10 · database `laravel` · Host `pgsql`  
**Sources:** `database/migrations/**`, live `pg_catalog` / `\d+`, domain modules, `docs/governance/open-decisions.md`, `docs/governance/project-state.md` (NON-AUTHORITY mirror), specs references via ledger  

---

## 0. Executive verdict

| Area | Verdict |
|------|---------|
| Migration ↔ DB sync | **ALIGNED** — all listed migrations `Ran` (50); Workflow tables present (batch 3) |
| Core business tables | **PRESENT** for active modules (incl. Workflow WP-WF-03) |
| Cross-module FK policy | **Mostly intentional soft UUID**; known exceptions registered |
| Production seed readiness | **EMPTY DB data** — roles/settings/users = 0 without seeder run |
| Blocking schema hole for WP-WF | **None observed** for WF-00…05 |
| Next schema WP | **WP-DORM-04** (HOLD) — OQ-DORM-04 Identity FK exception, not missing tables |

---

## 1. Migration inventory vs live schema

### 1.1 Counts

| Metric | Value |
|--------|-------|
| Migration files under `database/migrations/` | 50 PHP migrations (root framework + Spatie activity/telescope + all modules) |
| `migrate:status` | **50 Ran** (batches 1–3) |
| Live tables (`db:show`) | **56** (includes framework: users, cache, jobs, sessions, telescope, activity_log, migrations) |
| Workflow tables | `workflow_request_approval_instances`, `workflow_request_approval_step_executions` — **exist** |

### 1.2 Module business tables (observed live)

Identity · Employee · Request (+ children) · Workflow · Dormitory (+ structure + assignments) · Allocation · CheckIn · Lottery · Voucher · Notification · Audit · Reporting · System(`settings`)

**No orphan migration file** pending apply on this DB.  
**No missing Workflow table** relative to WP-WF-03 design.

### 1.3 Framework / tool tables (not domain)

`users` (bigint PK, web auth), `sessions`, `cache*`, `jobs*`, `failed_jobs`, `password_reset_tokens`, `activity_log`, `telescope_*`, Spatie `roles`/`permissions` (bigint PKs)

---

## 2. Foreign keys — classification

### 2.1 Intra-module FKs (expected / healthy)

| Table | FK | Target | Notes |
|-------|-----|--------|-------|
| Request children | `request_id` | `requests` | CASCADE — OK (same module) |
| Dormitory structure | building→dormitory, floor→building, room→floor, bed→room | hierarchy | RESTRICT |
| Allocation items | `allocation_id` | `allocations` | CASCADE |
| Workflow steps | `workflow_instance_id` | instances | CASCADE — OK (same module) |
| Employee deps / dept links | employee/dept internal | intra Employee | OK |
| Lottery program links | registration/result/snapshot → program | intra Lottery | OK |
| Spatie pivot | role/permission FKs | package | OK |

### 2.2 Cross-module / soft UUID (by design or ledger)

| Reference | DB FK? | Authority / note |
|-----------|--------|------------------|
| `requests.employee_id` → Employee | **No** | Soft UUID (modular boundary) |
| `requests.dormitory_id` → Dormitory | **No** | Soft UUID |
| `requests.assigned_stage1_approver_identity_id` → Identity | **No** | OQ-REQ-02 CLOSED — FK **dropped** (WP-REQ-01); column+index retained |
| `workflow_*.request_id` → Request | **No** | WP-WF-03 intentional soft UUID |
| `workflow_*.stage1/actor_identity_id` → Identity | **No** | Soft UUID |
| `allocations.person_id` / `bed_id` / sources | **No** | Soft UUID + exclusion on person+daterange |
| `check_in_records.allocation_id` / `operator_id` | **No** | Soft UUID; **no** open-checkin unique index |
| Lottery `request_id` / `employee_id` | **No** | Soft UUID (lottery→request/employee) |
| Voucher sibling UUIDs | **No** | **GAP-PREUI-07 ACCEPTED TENSION** (soft UUID by design) |
| `employee_employees.identity_id` → Identity | **No FK** (UNIQUE only) | **DGAP-07** UUID value-ref sufficient |
| Notification `recipient_employee_id` | **No** | Soft UUID |

### 2.3 Registered FK exceptions (ledger)

| ID | Live observation | Status |
|----|------------------|--------|
| **OQ-DORM-04** | `dormitory_assignments` / manager / unit-manager tables **HAVE** FK `user_id` → `identity_users` (+ dormitory/room FKs) | **Temporary exception**; remediation = **WP-DORM-04 HOLD** (not authorized now) |
| **OQ-REQ-02** | Stage-1 identity FK **absent** on `requests` | **CLOSED** — matches Option A |

### 2.4 Potentially “problematic” (observe, not auto-fix)

1. **`request_approvals.approver_id`** — UUID, **no FK** to Identity (soft). Acceptable under modular rules; orphan approver IDs possible.  
2. **Dual identity surfaces:** `users` (bigint) vs `identity_users` (uuid) — documented Auth debt (DBT-3 / dual-session); schema coexistence is intentional transitional.  
3. **OQ-DORM-04 cluster** — Identity FKs on Dormitory assignment tables contradict “no cross-module FK” constitution ideal; **explicitly accepted until WP-DORM-04**.

---

## 3. UUID / UUIDv7 status

| Layer | Observation |
|-------|-------------|
| Domain/business PK columns | Almost all **`uuid`** type |
| App generation | `HasUuid` → **`Uuid::uuid7()`** on create when id null |
| Workflow Domain VOs | Also generate **uuid7** |
| Spatie `roles`/`permissions` | **bigint** identity (package default) — not business entity PKs |
| Laravel `users` | **bigint** — web credential store, not Identity aggregate |
| `activity_log.id` | **bigint** — Spatie package |
| DB default on uuid PK | No `gen_random_uuid()` default — IDs assigned in app (OK) |

**Verdict:** Business modules are UUID-oriented with **v7 at application layer**. No migration required solely for v7. DB cannot enforce v7 version bits without custom check (none present — optional hardening, not a current WP).

---

## 4. Indexes & constraints — gaps vs domain expectations

### 4.1 Present and aligned

| Constraint | Evidence |
|------------|----------|
| Allocation overlap exclusion | `EXCLUDE … gist (person_id, date_range) WHERE status=active` ✅ Spec/Allocation |
| Workflow one running per request | Partial unique ✅ WP-WF-03 |
| Workflow one pending step | Partial unique ✅ WP-WF-03 |
| Notification dedup | UNIQUE `(correlation_id, recipient, type)` ✅ Spec09 |
| Audit correlation unique | ✅ |
| Dormitory assignment active unique | Partial unique `(user_id, dormitory_id) WHERE revoked_at IS NULL` ✅ |
| Settings key unique | ✅ |

### 4.2 Missing / weak (observed gaps — not auto-authorized to fix)

| Gap | Why it matters | Suggested owner |
|-----|----------------|-----------------|
| **`check_in_records`**: no index on `allocation_id`; no partial unique “one open check-in” (`checked_out_at IS NULL`) | Domain `findOpenByAllocationId`; race can create two open stays | CheckIn / future schema WP |
| **`allocations.bed_id`**: no FK and no index called out beyond PK | Lookups by bed softer | Allocation (optional) |
| **Cross-module orphan risk** | Soft UUID refs can dangle | Accepted by architecture; integrity via Application |
| **Status enums** | Stored as `varchar` — no DB CHECK enums | Domain/Spatie own validity; optional CHECK later |
| **`request_approvals`**: no UNIQUE(request_id, stage, decision…) | Append-only history allows multiple rows by design | OK for SoT history |

---

## 5. Incomplete / missing tables

Relative to **active implemented modules** and **WP-WF-00…05**:

| Question | Answer |
|----------|--------|
| Missing Workflow tables? | **No** |
| Missing settings table? | **No** (DG-SETTINGS-01 / WP-DEBT-04 delivered) |
| Missing dormitory assignment tables? | **No** |
| Second Workflow definition engine tables? | **Out of scope** (OD-1 — Request Approval only) |
| Separate CheckOut module tables? | **N/A** — Spec07 CLOSED under CheckIn |

**Incomplete columns:** none material for current WPs beyond optional hardening above.

---

## 6. Seeds & initial data (critical for runtime)

### 6.1 Live DB content (this audit)

| Object | Count |
|-------|------:|
| `settings` | **0** |
| `roles` | **0** |
| `identity_users` | **0** |
| `users` (web) | **0** |
| Business domain rows | **0** (empty greenfield) |

### 6.2 Seeders present in repo

| Seeder | Called by `DatabaseSeeder`? | Purpose |
|--------|----------------------------|---------|
| `IdentityRoleSeeder` | **Yes** | Identity + web roles (dormitory-manager, employee, HRMgr, …) |
| `DevelopmentUserSeeder` | **No** (manual/dev) | Dev accounts |
| Settings / auto-approval keys | **None** | — |

### 6.3 Required initial data (operational)

| Data | Needed for | Status if DB empty |
|------|------------|--------------------|
| Spatie identity roles (`dormitory-manager`, `employee`, `dormitory-unit-manager`, …) | Stage-1 gate, auth | **Must seed** (`IdentityRoleSeeder`) |
| Web roles (`HRMgr`, `Administrator`, …) | Legacy/web surfaces | Seeded by same seeder when run |
| `settings` keys `request.approval.auto.*` (×4) | Auto-approval chain | **Missing seeder** — defaults to “off” via null read (behavior OK but ops must insert for auto-on) |
| At least one active `dormitory-manager` identity (+ Employee link for notifications C1) | Stage-1 + WP-WF-05 pending/submitted notify | Dev seeder / manual |
| Dormitory sites + structure | Request create / allocation | Manual or future seed |
| Employee↔dormitory assignments | Form site scoping (WP-REQ-04) | Manual |

**Note:** Empty `settings` is **not a schema defect**; it is a **data/ops gap**. Application treats missing keys as auto-approval disabled.

---

## 7. Spec / domain / ledger alignment (summary)

| Source | Schema alignment |
|--------|------------------|
| Spec05 Request | Tables + append-only approvals + soft employee/dormitory refs; Stage-1 column without Identity FK ✅ |
| Spec / Allocation overlap | Exclusion constraint ✅ |
| Spec09 Notification | `notification_logs` + dedup unique ✅; type enum in PHP (incl. `request_approval_pending`) — **no DB enum migration needed** |
| WP-WF-03 design | Instances/steps + soft `request_id` + partial uniques ✅ |
| HD-WF-01 / CD-010 | Ownership split reflected (RequestApproval table + Workflow audit tables) ✅ |
| OQ-DORM-04 | Identity FKs on dorm assignment tables = known exception → WP-DORM-04 HOLD |
| GAP-PREUI-07 | Voucher soft UUID ✅ matched |
| DGAP-07 | Employee.identity_id no Eloquent/DB FK ✅ |

---

## 8. Active WP impact

| WP | Schema implication |
|----|-------------------|
| WP-WF-00…05 | Schema for Workflow **delivered & applied**; no further WF migrations required for current lock |
| WP-DORM-04 | **HOLD** — expected to touch Identity FK posture on dormitory `*_assignments` (execution-only when authorized) |
| Lottery / Reporting | Frozen/not schema-blocked by this audit; tables already present |
| Settings contract | Table exists; **seed keys** still ops concern |

---

## 9. Priority findings list (actionable, not authorized)

### P0 — Ops / bootstrap (not migration)

1. Run `IdentityRoleSeeder` (or `db:seed`) before any real auth/UI path.  
2. Insert or seed `request.approval.auto.*` if auto-approval desired.  
3. Create Stage-1 manager Identity + Employee (for WP-WF-05 notify path).

### P1 — Schema hardening candidates (need Lead WP)

1. CheckIn: index(+optional partial unique) on open `allocation_id`.  
2. WP-DORM-04: resolve OQ-DORM-04 Identity FK cluster when sequenced.  

### P2 — Accepted tensions (do not “fix” casually)

1. Soft UUID everywhere cross-module.  
2. Voucher without intra FKs (GAP-PREUI-07).  
3. Spatie bigint role IDs.  
4. Dual `users` / `identity_users`.

### P3 — Non-gaps

1. No missing Workflow migrations on this DB.  
2. No pending unapplied migration files observed.  
3. RequestApproval remains physical SoT table; Workflow tables are orchestration audit only.

---

## 10. Method evidence

Commands used (read-only):

- `bash ./vendor/bin/sail artisan db:show --counts`
- `bash ./vendor/bin/sail artisan migrate:status --no-interaction`
- `bash ./vendor/bin/sail exec pgsql psql -U sail -d laravel -c "…"` (constraints, indexes, column types, `\d+`, counts)
- File inventory of `database/migrations/**/*.php`
- Ledger: `docs/governance/open-decisions.md` (OQ-REQ-02, OQ-DORM-04, GAP-PREUI-07, HD-WF-01, DG-SETTINGS-01)

**No files in application code were modified for this audit body** (session mirror `project-state.md` updated per standing governance rule only).

---

## 11. Suggested Lead next prompts

> ACCEPT schema audit — then either `db:seed` ops checklist, or `GO — WP-DORM-04` when OQ-DORM-04 remediation is authorized (STOP-3 before any dorm FK migration).

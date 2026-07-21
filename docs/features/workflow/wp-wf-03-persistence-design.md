# WP-WF-03 — Persistence Design (STOP-3 Proposal)

**WP:** WP-WF-03  
**Status:** **STOP-3 ACCEPTED — implemented** (migrations + repository delivered under Lead GO)  
**Authority:** HD-WF-01 · CD-010-A1 · L3 OD-1…OD-4 · WP-WF-02 Domain/Application  
**Date:** 1405/04/30 \| 2026-07-21  

---

## §0 Scope lock

| In scope (after STOP-3 GO) | Out of scope (this WP) |
|----------------------------|-------------------------|
| Eloquent models under `Infrastructure/Persistence` | Creating/running migrations **until** Lead approves this STOP-3 |
| `RequestApprovalWorkflowRepositoryContract` implementation | Request cutover / dual-run (WP-WF-04) |
| Module-local FK: steps → instances | UI, notifications (WP-WF-05) |
| Provider bind for repository only | WP-DORM-04 |
| Feature tests with schema **after** migration GO | Generic workflow engine tables |

**Ports deferred (not WP-WF-03):**

| Port | Defer to | Reason |
|------|----------|--------|
| `RequestApprovalCommandPort` | WP-WF-04 | Cutover writer to Request |
| `StageRoleAuthorizationPort` | WP-WF-04 | Identity/Spatie binding at cutover |
| `RequestApprovalAutoSettingsPort` | WP-WF-04 (or thin System adapter if Lead prefers earlier) | Settings live path unused until engine is live |

---

## §1 Persistence design

### 1.1 Aggregate

**Root:** `RequestApprovalWorkflowInstance`  
**Children:** `WorkflowStepExecution[]` (owned; loaded/saved with root)

Mapper pattern (mirror Allocation/CheckIn):

- Infrastructure Eloquent models ≠ Domain entities  
- Repository: `save` upserts root + child steps by UUID; `find*` reconstitutes Domain via constructor  

### 1.2 Save semantics

| Operation | Behavior |
|-----------|----------|
| `save` | Upsert instance row; upsert each step by `id`; do **not** delete historical completed steps |
| `findById` | Instance + steps ordered by `activated_at` asc, then `id` |
| `findRunningByRequestId` | `request_id` + `status = running`; expect 0..1 row |

Steps are **mutable orchestration audit rows** (status advances in place), not append-only product history. Canonical append-only history remains `request_approvals` (Request module).

### 1.3 Cross-module references

| Column | Target | Constraint |
|--------|--------|------------|
| `request_id` | Request module UUID | **Value reference only — no FK** (INV cross-module FK ban; L3 §9 rollback) |
| `stage1_approver_identity_id` | Identity user UUID | **No FK** |
| `actor_identity_id` | Identity user UUID | **No FK** |
| `workflow_instance_id` (steps) | Same-module instance | **FK** allowed (cascade delete) |

### 1.4 Indexes / integrity (PostgreSQL)

1. **Partial unique:** one running instance per request  
   `UNIQUE (request_id) WHERE status = 'running'`
2. **Partial unique:** at most one pending step per instance  
   `UNIQUE (workflow_instance_id) WHERE status = 'pending'`
3. Index `(request_id, status)` for lookups  
4. Index `(workflow_instance_id, activated_at)` for step order  

No dual-run columns. No columns added to `requests`.

---

## §2 Database schema proposal

### 2.1 Table: `workflow_request_approval_instances`

| Column | Type | Notes |
|--------|------|-------|
| `id` | `uuid` PK | Domain `WorkflowInstanceId` |
| `request_id` | `uuid` NOT NULL | Soft ref |
| `status` | `varchar(32)` NOT NULL | `pending\|running\|completed\|rejected\|cancelled` |
| `stage1_approver_identity_id` | `uuid` NULL | Stage-1 snapshot |
| `current_stage` | `varchar(32)` NULL | `department_manager\|hr\|dormitory_manager\|dormitory_unit` |
| `started_at` | `timestamptz` NOT NULL | UTC |
| `completed_at` | `timestamptz` NULL | |
| `created_at` / `updated_at` | `timestamptz` | Laravel timestamps |

**Note:** Domain `start()` sets status `running` immediately; `pending` reserved for future pre-start if needed — column still accepts enum values.

### 2.2 Table: `workflow_request_approval_step_executions`

| Column | Type | Notes |
|--------|------|-------|
| `id` | `uuid` PK | Domain `WorkflowStepId` |
| `workflow_instance_id` | `uuid` NOT NULL | FK → instances ON DELETE CASCADE |
| `stage` | `varchar(32)` NOT NULL | Same stage vocabulary as Request `ApprovalStage` |
| `status` | `varchar(32)` NOT NULL | `pending\|approved\|rejected\|skipped_auto\|cancelled` |
| `actor_identity_id` | `uuid` NULL | Human or system actor |
| `reason` | `text` NULL | Reject reason |
| `activated_at` | `timestamptz` NOT NULL | |
| `completed_at` | `timestamptz` NULL | |
| `created_at` / `updated_at` | `timestamptz` | |

### 2.3 Proposed migration path (files — **not created until GO**)

```
database/migrations/modules/workflow/
  2026_07_21_000001_create_workflow_request_approval_instances_table.php
  2026_07_21_000002_create_workflow_request_approval_step_executions_table.php
```

Provider already loads: `database_path('migrations/modules/workflow')` via `WorkflowServiceProvider`.

### 2.4 Rollback

`down()` drops step table then instance table. No `requests` schema touch → dual-run flag OFF (WP-WF-04) does not require workflow table rollback for product path.

---

## §3 Planned implementation files (post-STOP-3 GO)

```
app/Modules/Workflow/Infrastructure/Persistence/Models/
  RequestApprovalWorkflowInstanceModel.php
  RequestApprovalWorkflowStepExecutionModel.php

app/Modules/Workflow/Infrastructure/Repositories/
  EloquentRequestApprovalWorkflowRepository.php

app/Modules/Workflow/Infrastructure/Providers/WorkflowServiceProvider.php
  → bind RequestApprovalWorkflowRepositoryContract

tests/Unit|Feature/Modules/Workflow/
  EloquentRequestApprovalWorkflowRepositoryTest.php (or Feature with migrate)
```

**Hygiene (optional same WP):** remove misleading `Domain/Models/.gitkeep` (Eloquent must not live in Domain).

---

## §4 Dependency impact

| Consumer | Impact |
|----------|--------|
| WP-WF-02 Actions | Unchanged; gain real repository bind |
| Request module | **None** until WP-WF-04 |
| Identity / Settings | **None** in WP-WF-03 (ports unbound or null in tests only) |
| Notification | None |
| WP-DORM-04 | None |

---

## §5 Exclusions confirmation

- [x] Migrations created only for approved Workflow tables (Lead STOP-3 ACCEPTED)
- [x] No Request module changes / cutover / dual-run
- [x] No UI / notifications / WP-DORM-04
- [x] No generic workflow_definitions / multi-engine tables (OD-1)
- [x] `request_id` soft UUID — no FK to `requests`

---

## §6 Lead approval checklist (STOP-3)

- [ ] Table names accepted (`workflow_request_approval_*`)  
- [ ] Soft UUID `request_id` (no cross-module FK) accepted  
- [ ] Partial unique indexes accepted  
- [ ] WP-WF-03 = repository + models + migrations only; other ports deferred to WP-WF-04  
- [ ] GO to create migrations + implement repository  

**Suggested Lead reply:**
> `STOP-3 ACCEPTED — WP-WF-03` GO migrations + repository only

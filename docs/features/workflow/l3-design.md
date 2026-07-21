# L3 Design — Workflow Module (Request Approval Orchestration)

**WP:** WP-WF-01  
**Status:** **L3 ACCEPTED** — Lead GO + Decision Lock (1405/04/30 \| 2026-07-21)  
**Authority:** HD-WF-01 Option B · CD-010-A1 · WP-WF-00 CLOSED  
**Canonical path:** `docs/features/workflow/l3-design.md`  
**Scope:** Design only — **no** domain/infra code, **no** migrations in this WP  

---

## §0.1 Decision Lock (Lead — L3 ACCEPTED)

| ID | Locked decision |
|----|-----------------|
| **OD-1** | Workflow scope = **Request Approval Workflow only** for v1. Keep future extensibility possible; **no Generic Workflow Engine** in current scope. |
| **OD-2** | Stage → Role mapping is **fixed and policy-controlled** for v1. **No admin-configurable** stage mapping. Approved mapping: **S1=`dormitory-manager`**, **S2=`HR`**, **S3=`dormitory-manager`**, **S4=`dormitory-unit`**. |
| **OD-3** | Workflow step log = **internal orchestration audit only**. Product-visible history = **`RequestApproval`** (canonical SoT). |
| **OD-4** | Migration = **phased cutover** with **optional dual-run**. Dual-run code **only in WP-WF-04**. **No dual-run code now.** |

---

## §0 Boundary statement

**Workflow** owns **transition rules**, **chain definition**, **routing**, and **orchestration** for the **first authorized instance**: the Request four-stage approval chain.

**Request** retains ownership of:

- Request lifecycle **status** (Spatie state machine)
- `RequestApproval` entity (append-only)
- **Canonical product-visible approval history**

**Workflow** may persist an **orchestration audit** (step execution log). That log is **non-canonical** for product UI/history (HD-WF-01 history policy).

**Out of WP-WF-01:** PHP implementation, schema, dual-run flag wiring, Notification delivery code, WP-DORM-04, Lottery/Reporting, second workflow instance.

---

## §1 Preconditions (locked)

| ID | Precondition | Source |
|----|--------------|--------|
| P1 | Workflow ACTIVATED for Request orchestration | CD-010-A1 |
| P2 | Ownership split retained | CD-010 |
| P3 | First instance = Request approval; second deferred non-blocking | HD-WF-01 |
| P4 | History: `RequestApproval` SoT; Workflow log = audit only | HD-WF-01 |
| P5 | Cutover = phased; optional dual-run; final switch STOP/GO | HD-WF-01 → WP-WF-04 |
| P6 | Sequence WP-WF-00…05 before WP-DORM-04 | HD-WF-01 |
| P7 | Spec05 remains SPEC05_CLOSED; OA-05-02 “when Workflow activates” path applies | `specs/005-request-management/spec.md` |
| P8 | Entrypoint Rule + Auth Layer Separation | D-ENTRYPOINT-RULE; constitution |

---

## §2 Design goals

1. Move **orchestration** out of Request Application (`ApprovalStageResolver` routing ownership conceptually → Workflow Definition/Engine).
2. Keep Request status machine and `RequestApproval` append semantics **behaviorally equivalent** for employees/approvers.
3. Define clear **ports/events** so WP-WF-02…05 can implement without reopening HD-WF-01.
4. Preserve Stage-1 console UX contract; later WPs rewire behind Workflow without inventing WP-FORM-01-style bypasses.

---

## §3 Stages (chain vocabulary)

Reuse existing Request `ApprovalStage` string values (no rename in v1):

| Order | Stage ID (`ApprovalStage`) | Request status while awaiting | Next status on Approve |
|------:|----------------------------|-------------------------------|------------------------|
| 1 | `department_manager` | `PendingDepartmentManager` | `PendingHR` |
| 2 | `hr` | `PendingHR` | `PendingDormitoryManager` |
| 3 | `dormitory_manager` | `PendingDormitoryManager` | `PendingDormitoryUnit` |
| 4 | `dormitory_unit` | `PendingDormitoryUnit` | `Approved` |

**Entry:** `Draft` → submit → `Submitted` → enter stage 1 (`PendingDepartmentManager`) — submit remains Request-owned; Workflow **starts instance** on submit (or on first pending status — see §10 open design choice OD-1).

**OA-05-00:** Stage name ≠ auth role. Stage `department_manager` uses identity role `dormitory-manager` (DGAP-13).

---

## §4 Stage–role–permission matrix

Guard target for Workflow v1 orchestration principals: **`identity`** (DASH D3 / Auth Layer Separation).

### OD-2 locked mapping (v1 — fixed, not admin-configurable)

| Stage order | Stage ID (`ApprovalStage`) | Identity role (policy-controlled) |
|------------:|----------------------------|-----------------------------------|
| 1 | `department_manager` | `dormitory-manager` |
| 2 | `hr` | `HR` |
| 3 | `dormitory_manager` | `dormitory-manager` |
| 4 | `dormitory_unit` | `dormitory-unit` |

> **Note:** Role strings above are the Lead-locked policy names for v1. Spatie/`IdentityRoleSeeder` may use kebab/alias variants (`dormitory-unit-manager`, `HRMgr` on web, etc.). WP-WF-02/04 must bind policy names to concrete Spatie roles without introducing admin-configurable mapping UI.

| Stage ID | Stage-1 special rule | Approve | Reject | List pending | Auto-approve actor |
|----------|----------------------|---------|--------|--------------|--------------------|
| `department_manager` | Snapshot `assigned_stage1_approver_identity_id` must match actor (Stage-1 console) | Yes | Yes | Yes (own queue) | System actor when settings flag on |
| `hr` | Role `HR` only (no snapshot) | Yes | Yes | Yes | System |
| `dormitory_manager` | Role `dormitory-manager` (same role string as Stage 1; queue not Stage-1 snapshot unless later policy says so) | Yes | Yes | Yes | System |
| `dormitory_unit` | Role `dormitory-unit` | Yes | Yes | Yes | System |

| Actor | Submit own request | Cancel (early states only) | View own history | Orchestration admin |
|-------|--------------------|----------------------------|------------------|---------------------|
| `employee` | Yes | Yes (policy: draft/submitted only — Spec05) | Yes | No |
| System (auto-approval) | No | No | No | Executes skip/approve per settings |
| Workflow engine | No direct UI | N/A | Writes audit log only | Advances steps |

**Permissions (logical — names illustrative for WP-WF-02):**

| Permission key (proposed) | Meaning |
|---------------------------|---------|
| `workflow.request_approval.act:{stage}` | Approve/reject at stage |
| `workflow.request_approval.queue:{stage}` | List pending at stage |
| `request.submit` / `request.cancel_own` | Remain Request-owned |

---

## §5 Statuses

### 5.1 Request lifecycle (canonical — Request module)

Unchanged vocabulary from current `RequestState` graph:

| Status | Owner | Workflow concern |
|--------|-------|------------------|
| `Draft` | Request | None |
| `Submitted` | Request | Trigger start instance (OD-1) |
| `PendingDepartmentManager` | Request | Active step 1 |
| `PendingHR` | Request | Active step 2 |
| `PendingDormitoryManager` | Request | Active step 3 |
| `PendingDormitoryUnit` | Request | Active step 4 |
| `Approved` | Request | Instance completed (success) |
| `Rejected` | Request | Instance completed (failure) |
| `Cancelled` | Request | Instance cancelled if started |

Post-approval Allocation/CheckIn statuses remain Spec07 — **out of Workflow v1**.

### 5.2 Workflow instance / step (orchestration audit — non-canonical)

| Entity | Status values (proposed) |
|--------|--------------------------|
| **WorkflowInstance** | `Pending`, `Running`, `Completed`, `Rejected`, `Cancelled` |
| **WorkflowStepExecution** | `Pending`, `Approved`, `Rejected`, `SkippedAuto`, `Cancelled` |

Product UIs that today show approval history continue to read **`RequestApproval`**, not step executions.

---

## §6 Approve / reject / auto-approval rules

### 6.1 Approve (human)

1. Actor authenticated on `identity` (target) with stage-authorized role/permission.
2. Instance has exactly one **active** step matching Request’s current pending status.
3. Stage-1: actor identity id equals `assigned_stage1_approver_identity_id` (retain current gate).
4. Effects (atomic from product view):
   - Append `RequestApproval` (`decision=approved`, stage, approver, `decided_at`).
   - Advance Request status via existing transition table (§3).
   - Append Workflow step execution `Approved` (audit).
   - Emit events (§8).
5. If auto-approval enabled for **subsequent** stages, engine may chain without new human action (parity with `ApproveRequestStageAction::applyAutoApprovalChain`).

### 6.2 Reject (human)

1. Same auth as approve for current stage.
2. **Reason required** (non-empty).
3. Effects:
   - Append `RequestApproval` (`decision=rejected`, reason).
   - Request → `Rejected`.
   - Instance → `Rejected`; active step → `Rejected`.
   - Emit `RequestRejected` + notification intent.
4. No further stages run.

### 6.3 Auto-approval

| Settings key (existing) | Stage |
|-------------------------|-------|
| `request.approval.auto.department_manager` | `department_manager` |
| `request.approval.auto.hr` | `hr` |
| `request.approval.auto.dormitory_manager` | `dormitory_manager` |
| `request.approval.auto.dormitory_unit` | `dormitory_unit` |

Rules:

- Read via System `SettingsReadContract` (not raw `DB::table` in new Workflow code).
- When true on stage entry (or after prior approve), system approves with **SystemActor** identity (parity with current behavior).
- Still appends **`RequestApproval`** row (canonical history).
- Step execution recorded as `SkippedAuto` or `Approved` with system actor — L3 preference: **`SkippedAuto`** for audit clarity; product history still shows approved decision on `RequestApproval`.

### 6.4 Illegal transitions

- Approve/reject when Request not in a pending-approval status → reject (domain error).
- Skip stages without auto flag → forbidden.
- Mutate/delete `RequestApproval` → forbidden (append-only).
- Workflow must not write Request status except through Request Application ports/commands (CD-010).

---

## §7 History policy (locked)

| Store | Visibility | Purpose |
|-------|------------|---------|
| **`RequestApproval`** | Product UI, APIs, employee/approver history | **Canonical SoT** |
| **Workflow step execution log** | Ops/debug/audit tooling (optional later UI) | Orchestration audit only |

WP-WF-05 verifies parity: every human/auto decision that advances or rejects produces matching `RequestApproval` row.

---

## §8 Event contract table

### 8.1 Existing Request events (retain)

| Event | Emitter (today) | Workflow role |
|-------|-----------------|---------------|
| `RequestSubmitted` | Request | **Start** WorkflowInstance (or OD-1 variant) |
| `RequestApprovalRecorded` | Request | Mirror/confirm step; may become dual-emitted during dual-run |
| `RequestApproved` | Request | Complete instance success |
| `RequestRejected` | Request | Complete instance failure |

### 8.2 Proposed Workflow ports / commands (Request ← Workflow)

| Contract / command (proposed) | Direction | Payload (minimal) | Effect on Request |
|------------------------------|-----------|-------------------|-------------------|
| `StartRequestApprovalWorkflow` | Request→Workflow or on event | `requestId`, `requestType`, `stage1ApproverIdentityId?` | Create instance + step 1 |
| `RecordStageDecision` | Workflow→Request Application | `requestId`, `stage`, `decision`, `approverId`, `reason?` | Append `RequestApproval` + status transition |
| `ApplyAutoApprovalIfConfigured` | Workflow internal → Request command | `requestId`, `stage` | Same as approve with system actor |

### 8.3 Proposed Workflow domain events (orchestration audit)

| Event | When | Consumers |
|-------|------|-----------|
| `WorkflowInstanceStarted` | Instance created | Audit; optional notify approver queue |
| `WorkflowStepActivated` | Step becomes pending | Notification (pending approval) |
| `WorkflowStepCompleted` | Step approved / skipped-auto | Internal advance |
| `WorkflowInstanceCompleted` | Final approve | Align with `RequestApproved` |
| `WorkflowInstanceRejected` | Reject | Align with `RequestRejected` |

### 8.4 Notification intents

| Trigger | NotificationType (existing / proposed) | Recipient |
|---------|----------------------------------------|-----------|
| Request submitted | `request_submitted` (exists) | Employee (confirm) |
| Step activated (human) | **PROPOSED** `request_approval_pending` *(new in WP-WF-05)* | Stage queue / Stage-1 assignee |
| Final approved | `request_approved` (exists) | Employee |
| Rejected | `request_rejected` (exists) | Employee |
| Auto-approved stage | Optional silent or same as recorded — **default: no extra employee notify per intermediate auto** | — |

Notification delivery remains **Notification module** ownership; Workflow only emits intents/events.

---

## §9 Dual-run note (WP-WF-04)

| Phase | Behavior |
|-------|----------|
| **Pre–WP-WF-04** | Inline Request orchestration remains live (current production path). |
| **WP-WF-04 dual-run (optional flag)** | Flag ON: Workflow engine computes next step **and** Request inline path may still execute; compare outcomes in tests/logs. Product writes to `RequestApproval` once (single writer — prefer Workflow→Request command only when flag selects Workflow as primary). |
| **Final switch** | Requires explicit Lead **STOP/GO**. After GO: Request Application approve/reject actions become thin adapters calling Workflow; `ApprovalStageResolver` routing ownership deprecated/removed in a follow-up cleanup task inside WP-WF-04 scope. |
| **Rollback** | Flag OFF restores inline path without schema rollback (no Workflow-required columns on `requests` for v1 if instance keyed by `requestId` UUID only). |

**STOP-3:** Any Workflow tables require Lead migration approval in WP-WF-03 — not in WP-WF-01.

---

## §10 Open design choices — **LOCKED** (see §0.1)

| ID | Status | Locked value |
|----|--------|--------------|
| OD-1 | **LOCKED** | Request Approval Workflow only; no Generic Engine in v1 |
| OD-2 | **LOCKED** | Fixed stage→role map (S1/S3=`dormitory-manager`, S2=`HR`, S4=`dormitory-unit`) |
| OD-3 | **LOCKED** | Step log = internal audit; `RequestApproval` = product SoT |
| OD-4 | **LOCKED** | Phased cutover; dual-run only in WP-WF-04; no dual-run code before then |

Remaining implementation binding (not policy): concrete Spatie role/guard resolution for `HR` / `dormitory-unit` strings → WP-WF-02 Application auth adapter (no admin UI).

---

## §11 Acceptance scenarios (L3)

| ID | Scenario | Expected |
|----|----------|----------|
| AS-01 | Submit personal request | Request → pending stage 1; WorkflowInstance `Running`; step 1 `Pending`; `RequestSubmitted` (+ optional notify) |
| AS-02 | Stage-1 approve by assigned `dormitory-manager` | `RequestApproval` approved row; status → `PendingHR`; step 1 completed; step 2 activated |
| AS-03 | Stage-1 reject with reason | `RequestApproval` rejected; Request `Rejected`; instance rejected; employee `request_rejected` |
| AS-04 | Stage-1 approve by wrong identity | Denied; no `RequestApproval` row; no status change |
| AS-05 | Auto-approval enabled for HR | After stage-1 approve, HR stage auto-completes with `RequestApproval` + system actor; employee not flooded with intermediate notifies |
| AS-06 | Final unit approve | Request `Approved`; `RequestApproved` event; instance completed; `request_approved` notify |
| AS-07 | History UI | Shows only `RequestApproval` chronology; Workflow step log not required on employee UI |
| AS-08 | Cancel in early state | Request cancelled; instance cancelled if started; no further steps |
| AS-09 | Dual-run flag OFF | Inline path unchanged (pre-cutover safety) |
| AS-10 | Final SWITCH GO | Approve/reject entrypoints route via Workflow; behavioral parity with AS-02…06 |

---

## §12 Downstream WP mapping

| WP | Consumes from this L3 |
|----|----------------------|
| WP-WF-02 | §§3–7 domain model + ports |
| WP-WF-03 | Persistence for Instance/Step (STOP-3) |
| WP-WF-04 | §9 dual-run + cutover; OD-2 binding |
| WP-WF-05 | §8.4 notifications + history parity AS-07 |

---

## §13 Non-goals / exclusions

- Second workflow instance (Allocation/CheckIn/etc.)
- Changing `ApprovalStage` string vocabulary
- Softening Stage-1 snapshot assignment model
- Editing `open-decisions.md` / Spec05 body in this WP (optional REGISTER sync later)
- WP-DORM-04, Lottery settings, PREUI-06/07/17

---

## §14 L3 acceptance checklist (Lead)

- [x] Stages/roles/status maps accepted (Decision Lock OD-2)
- [x] Approve/reject/auto rules accepted
- [x] History SoT = `RequestApproval` confirmed (OD-3)
- [x] Event + notification tables sufficient for WP-WF-02
- [x] Dual-run / STOP-GO note accepted; dual-run deferred to WP-WF-04 (OD-4)
- [x] OD-1…OD-4 **LOCKED** (Request-only scope; fixed roles; no generic engine)

**Lead reply recorded:** `L3 ACCEPTED — WP-WF-01` + Decision Lock (1405/04/30 \| 2026-07-21).

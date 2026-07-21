# Wave 3 — STOP-3A / STOP-3B Discovery (State Machine & Session Handoff)

**Command:** Wave 3 — State Machine & Session Handoff (discovery only)  
**Date:** 2026-07-21  
**Prior:** Wave 2 COMPLETE (`927ee6a`)  
**Frozen (untouched):** Lottery (HD-02), Reporting (HD-03), DBT-3  
**Gate:** **HARD STOP** — await Lead approval before any implementation

---

## Path correction (critical)

| Prompt path | Repo reality |
|-------------|--------------|
| `app/Domain/Dormitory/` | **Does not exist** (0 files) |
| Canonical Dormitory Domain | `app/Modules/Dormitory/Domain/` |
| Prompt deliverable `…/DormitoryStudentRequestStateMachine.php` under Dormitory | **Conflicts** with CD-010 / HD-WF-01 ownership: **Request owns approval/lifecycle state**; Dormitory owns physical resource/occupancy |

Agent will **not** invent `app/Domain/Dormitory/` without Lead disposition.

---

# STOP-3A — State Inventory

## A1. Dormitory module (`app/Modules/Dormitory/Domain/`)

### Enums / status fields

| Artifact | Values | Role |
|----------|--------|------|
| `Enums/ResourceStatus` | `available`, `unavailable`, `maintenance`, `inactive` | Operational availability of site/building/floor/room/bed |
| `Enums/PhysicalOccupancyState` | `vacant`, `reserved`, `occupied` | Allocation-time bed inventory markers (Spec04); Spec07 owns check-in presence separately |

### Entities holding status

| Entity | Fields | Transition logic |
|--------|--------|------------------|
| `Dormitory`, `Building`, `Floor`, `Room` | `ResourceStatus $status` | `changeStatus()` — mostly unconstrained set (Room/Dormitory) |
| `Bed` | `ResourceStatus` + `PhysicalOccupancyState` | **Yes** — `changeStatus`, `reserve`, `applyOccupyMarker`, `releaseInventoryMarker`, `startOccupancy`, `endOccupancy` with `InvalidResourceStateTransition` / `InvalidOccupancyTransition` |

### Migrations (dormitory)

| Table | Columns | DB CHECK |
|-------|---------|----------|
| `dormitories`, `dormitory_buildings`, `dormitory_floors`, `dormitory_rooms` | `status` string(32) | `available\|unavailable\|maintenance\|inactive` |
| `dormitory_beds` | `status` + `physical_occupancy_state` | status same; occupancy `vacant\|reserved\|occupied` (after 2026_07_12 alter) |
| Manager/unit assignment tables | no request lifecycle status | — |

### Gaps (Dormitory vs “student request”)

- **No** student/employee request lifecycle in Dormitory Domain.
- Existing “state machine” here is **physical inventory**, not request approval.
- Building a `DormitoryStudentRequestStateMachine` inside Dormitory would be a **new aggregate in the wrong module**.

---

## A2. Request module (actual employee/student request lifecycle)

### Spatie Model States (`app/Modules/Request/Domain/States/`)

| State `$name` | Class |
|---------------|--------|
| `draft` | `DraftState` |
| `submitted` | `SubmittedState` |
| `pending_department_manager` | `PendingDepartmentManagerState` |
| `pending_hr` | `PendingHRState` |
| `pending_dormitory_manager` | `PendingDormitoryManagerState` |
| `pending_dormitory_unit` | `PendingDormitoryUnitState` |
| `approved` | `ApprovedState` |
| `rejected` | `RejectedState` |
| `cancelled` | `CancelledState` |

**Transition matrix** (canonical in `RequestState::config()`):

```
draft → submitted | cancelled
submitted → pending_department_manager | cancelled
pending_department_manager → pending_hr | rejected
pending_hr → pending_dormitory_manager | rejected
pending_dormitory_manager → pending_dormitory_unit | rejected
pending_dormitory_unit → approved | rejected
```

Terminal: `approved` | `rejected` | `cancelled` (`isTerminal()`).

Supporting: `ApprovalStageResolver`, domain events (`RequestSubmitted`, `RequestApproved`, `RequestRejected`, `RequestCancelled`, `RequestApprovalRecorded`), `InvalidRequestTransitionException`.

### Migration

| Table | Column | Notes |
|-------|--------|-------|
| `requests` | `status` string(64) | **No** CHECK constraint listing allowed values; Spatie owns vocabulary |

### Post-approval gap (OA-05-03) — **not implemented as states**

Architecture / spec07 expect: `Approved` → `WaitingForAllocation` → `Allocated` (| `AllocationFailed`) → CheckIn/Out.

| Item | Status |
|------|--------|
| Spatie states for WaitingForAllocation / Allocated / AllocationFailed / CheckedIn / CheckedOut | **Absent** |
| `RequestLifecycleCommandPort` (Allocation → Request) | **Exists** |
| `RequestLifecycleCommandAdapter` | **No-op stub** (empty methods) |
| Spec05 OA-05-01 | Explicitly terminal at `Approved` until spec07 handoff |

---

## A3. Workflow module (orchestration — not product-visible request status)

| Enum / table | Values / notes |
|--------------|----------------|
| `WorkflowInstanceStatus` | `pending`, `running`, `completed`, `rejected`, `cancelled` |
| `RequestApprovalWorkflowStage` | `department_manager` → `hr` → `dormitory_manager` → `dormitory_unit` |
| Step status | migrations + domain step enums |
| Ownership (CD-010-A1) | Workflow = rules/routing; **Request** = product-visible state + `RequestApproval` history |

---

## A4. Session Handoff (protocol, not a state machine)

Observed meaning in governance (`docs/governance/wp-req-01-scope.md` and WP closeouts):

- Mandatory **docs delivery** at WP/Wave close: DELIVERY CONFIRMATION + Session Handoff (applied migrations, suite summary, **no SHA** where policy forbids).
- Distinct from `RequestLifecycleCommandPort` “lifecycle handoff” (OA-05-03 runtime port).

Wave 3 objective “Session Handoff protocol” → treat as **governance artifact + closeout checklist**, unless Lead redefines as code.

---

## STOP-3A summary

| Question | Answer |
|----------|--------|
| Current states in Dormitory Domain | ResourceStatus + PhysicalOccupancyState only |
| Existing transition logic (Dormitory) | Bed occupancy/status methods |
| Student request lifecycle location | **Request** Spatie `RequestState` (already exists) |
| Primary gap if “full lifecycle” intended | OA-05-03 post-approval states + real `RequestLifecycleCommandPort` adapter |
| Prompt path `app/Domain/Dormitory/…StateMachine` | **Invalid / ownership conflict** without new HD |

```
STOP-3A GATE — HARD STOP
Await Lead approval before design/impl.
```

---

# STOP-3B — Open decisions blocking State Machine design?

**Sources:** `docs/governance/open-decisions.md`, `docs/audit/wave1-baseline-known-fail.md`

## Do frozen items block SM design?

| ID | Status | Blocks Wave 3 SM? |
|----|--------|-------------------|
| HD-02 Lottery | FROZEN | **No** if Lottery untouched |
| HD-03 Reporting | OUT-OF-F3 | **No** if Reporting untouched |
| DBT-3 / DEBT-W1-01/02 | Hard STOP | **No** if no `auth:api` / dual-guard work |

## Decisions that **constrain** design (must respect)

| ID | Constraint |
|----|------------|
| **CD-010-A1 / HD-WF-01** | Request owns product-visible **state** + history; Workflow owns transition **rules**/orchestration. New SM must not relocate ownership into Dormitory. |
| **WP-WF-04 STOP/GO** | Approval cutover / dual-run still a gated sequence item — rewriting approval transitions without Lead scope risks colliding with Workflow cutover. |
| **GAP-PREUI-17** | Spatie Domain `@phpstan-extends State<\…Model>` is REGISTER-ONLY analysis debt — extending Spatie inherits that tension (acceptable unless Lead forbids). |
| **OA-05-03 / UD-10** | Post-approval payload/states historically deferred; implementing them is a **scope expansion** needing explicit Lead authorize (not silently invent). |
| **OQ-DORM-04 / WP-DORM-04** | HOLD — not SM, but do not touch assignment FK / Dormitory schema under that WP. |

## Baseline known-fail relevance (`wave1-baseline-known-fail.md`)

| Cluster | Relevance to Wave 3 |
|---------|---------------------|
| Request transition / `InvalidRequestTransitionException` | **Signal** of Request/WF cutover debt — design must account for existing Spatie matrix; not a ledger “block design” row, but a **risk**. |
| Lottery Feature | Out of scope (HD-02) |
| Architecture / Module boundary | May fail if new files violate module inventory — place under correct module |

## Verdict (STOP-3B)

| Question | Answer |
|----------|--------|
| Any OPEN decision that **forbids** designing a request lifecycle SM? | **No absolute forbid** |
| Any decision that **blocks placing it under Dormitory / `app/Domain/Dormitory`**? | **Yes — ownership** (CD-010-A1) |
| Safe to proceed to greenfield `DormitoryStudentRequestStateMachine` as written? | **No** without Lead disposition on **module home + scope** |
| Recommended Lead choices | See options below |

```
STOP-3B GATE — HARD STOP
Await Lead approval before design/impl.
```

---

# Lead disposition options (PROPOSED — not executed)

| Option | Scope | Home | Notes |
|--------|-------|------|-------|
| **W3-A** | Docs-only: inventory + Session Handoff checklist; no new SM class | — | Lowest risk; formalize Wave 3 as discovery/closeout |
| **W3-B** | Extend **Request** Spatie matrix for OA-05-03 post-approval states + wire `RequestLifecycleCommandAdapter` | `app/Modules/Request/Domain/States/` | Aligns with ownership; touches Request + Allocation adapter |
| **W3-C** | Pure Domain service wrapping existing Request transitions (facade), no new states | `app/Modules/Request/Domain/Services/` | Avoids duplicate SM; may not meet “new StateMachine” deliverable |
| **W3-D** | New `DormitoryStudentRequestStateMachine` under Dormitory / `app/Domain/…` | Prompt path | **Requires new HD** overriding CD-010 ownership — **not recommended** |

**Session Handoff deliverable (any option):** template under `docs/audit/` or governance, used at Wave 3 close — independent of SM code.

---

## Agent status

Wave 3 **ACTIVE** for discovery only · **SUSPENDED for implementation** until Lead issues e.g. `APPROVE STOP-3A` + `APPROVE STOP-3B` + disposition **W3-A|B|C|D** (or refined scope).

**Not created (post-approval only):**  
`DormitoryStudentRequestStateMachine.php`, Domain Events pack, `wave3-state-machine-design.md` (design), ledger Wave 3 COMPLETE row.

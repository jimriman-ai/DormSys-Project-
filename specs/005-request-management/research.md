# Research: Request Management (spec05)

**Date**: 2026-06-23 | **Plan**: [plan.md](./plan.md)

Consolidates OA-05-xx decisions from [spec.md](./spec.md) and resolves Phase 1 modeling unknowns before `data-model.md` and `contracts/`.

**Governance:** Phase 1 design authorized — [`handoff/spec05-planning-authorization.md`](../../.specify/docs/handoff/spec05-planning-authorization.md). Implementation complete — T001–T052 per [`handoff/spec05-implementation-authorization.md`](../../.specify/docs/handoff/spec05-implementation-authorization.md).

---

## R-01 — UUID primary key version

**Decision:** UUID **v7** via spec01 `HasUuid` (`Ramsey\Uuid\Uuid::uuid7()`).

**Rationale:** Kernel standard; consistent with Identity, Employee, Dormitory modules.

---

## R-02 — Request code format

**Decision:** Human-readable code pattern **`REQ-{YYYYMMDD}-{seq}`** where:

- `YYYYMMDD` = UTC calendar date of **first persistence** (draft create)
- `{seq}` = 4-digit zero-padded daily sequence per platform (`0001`–`9999`)

**Example:** `REQ-20260623-0001`

**Uniqueness:** Unique index on `requests.code`.

**Rationale:** Matches discovery document pattern; UTC storage aligns with constitution (Jalali at presentation only).

**Alternatives considered:** Jalali date in code — rejected for Wave 1 (presentation concern).

---

## R-03 — Cross-module reference policy

**Decision:** `employee_id`, `dormitory_id`, `approver_id`, `member_employee_id` stored as **UUID columns without FK** to other modules' tables (AP-04, CD-012 pattern).

**Rationale:** Modular monolith boundary; same as Employee `identity_id`.

---

## R-04 — Pending request definition (BR-01 / `PendingRequestReadPort`)

**Decision:** `hasPendingRequest(employeeId)` returns `true` when the employee has **at least one** request in a **non-terminal pre-approval lifecycle state**:

| Counts as pending | Does not count |
| ----------------- | -------------- |
| `Draft` | `Approved` |
| `Submitted` | `Rejected` |
| `PendingDepartmentManager` | `Cancelled` |
| `PendingHR` | Post-approval states (spec07) |
| `PendingDormitoryManager` | |
| `PendingDormitoryUnit` | |

**Rationale:** Prevents duplicate in-flight applications including unpublished drafts (organizational policy: one open application per employee). Aligns BR-01 "no active pending request."

**OA-05-09:** Port exposes **only** this boolean query — no mutations.

**Open for design review:** Whether `Draft` should block (see § Unresolved). **Recommendation:** include `Draft` — documented as **R-04 default**.

---

## R-05 — Eligibility enforcement split (CD-013)

**Decision:**

| Rule | Owner | Enforced at |
| ---- | ----- | ----------- |
| Active employee | Employee (`EmployeeEligibilityContract`) | Submit |
| No active allocation | Employee (via `ActiveAllocationReadPort` stub → spec07) | Submit |
| No pending request | Employee (via `PendingRequestReadPort` ← Request adapter) | Submit |
| Check-in not in past | **Request** (`SubmitRequestAction`) | Submit |
| Check-out after check-in | **Request** | Submit |
| Type-specific rules (BR-04, FamilyDirect dependents) | **Request** | Submit |

**Failure mode (R-013-01):** If `EmployeeEligibilityContract` throws or upstream unavailable → **fail closed** (reject submit). No cache in Wave 1A.

---

## R-06 — Dormitory reference validation (OA-05-07)

**Decision:**

| Phase | Behavior |
| ----- | -------- |
| Wave 1A (spec04 not implemented) | `NullDormitoryReadAdapter::siteExists` returns `true` for well-formed UUID; optional strict mode in tests |
| When spec04 live | `DormitoryReadContract::siteExists(dormitoryId)` at submit — reject if false |

**Rationale:** spec05 catalog dependency does not include spec04; stub unblocks Wave 1A.

---

## R-07 — Request lifecycle state machine (OA-05-01)

**Decision:** Implement approval-phase states only using **`spatie/laravel-model-states`**. State column on `requests.status` (string-backed enum).

**Terminal states (spec05):** `Approved`, `Rejected`, `Cancelled`

**Deferred states (spec07):** `WaitingForAllocation`, `Allocated`, `AllocationFailed`, `CheckedIn`, `CheckedOut` — **not** in spec05 migrations or state classes.

### Transition matrix (normative — approval phase)

| From | Action | To | Guard |
| ---- | ------ | -- | ----- |
| `Draft` | `submit` | `Submitted` | Eligibility + dates + type rules |
| `Submitted` | `enterApproval` | `PendingDepartmentManager` | Same transaction as submit or immediate follow-up |
| `Draft` | `cancel` | `Cancelled` | Submitter |
| `Submitted` | `cancel` | `Cancelled` | Submitter |
| `PendingDepartmentManager` | `approve` | `PendingHR` | Authorized approver or auto-approval |
| `PendingDepartmentManager` | `reject` | `Rejected` | Authorized approver + reason |
| `PendingHR` | `approve` | `PendingDormitoryManager` | — |
| `PendingHR` | `reject` | `Rejected` | — |
| `PendingDormitoryManager` | `approve` | `PendingDormitoryUnit` | — |
| `PendingDormitoryManager` | `reject` | `Rejected` | — |
| `PendingDormitoryUnit` | `approve` | `Approved` | Final stage |
| `PendingDormitoryUnit` | `reject` | `Rejected` | — |
| Any pending stage | `cancel` | — | **Rejected** (`InvalidRequestTransitionException`) |
| `Approved` \| `Rejected` \| `Cancelled` | any | — | **Rejected** (terminal) |

**Note:** `Submitted` may be **ephemeral** — implementation may transition `Draft → PendingDepartmentManager` in one atomic submit while still emitting `RequestSubmitted` event. Column value `submitted` allowed for audit trail if multi-step; design review may collapse to direct pending entry.

Canonical reference: [data-model.md](./data-model.md) § State machine.

---

## R-08 — Approval records (CD-010)

**Decision:** `RequestApproval` rows are **append-only**. No UPDATE/DELETE on `request_approvals` (mirror audit append-only spirit).

| Field | Purpose |
| ----- | ------- |
| `stage` | `DepartmentManager`, `HR`, `DormitoryManager`, `DormitoryUnit` |
| `decision` | `Approved`, `Rejected` |
| `approver_id` | Identity user UUID — **no FK** |
| `reason` | Required on reject; optional on approve |
| `decided_at` | UTC timestamp |

**Auto-approval (OA-05-02):** When settings flag true for stage, system inserts `RequestApproval` with `approver_id = system` sentinel UUID (`00000000-0000-7000-8000-000000000001` or dedicated `SystemActorId` VO) and advances state.

---

## R-09 — Auto-approval settings keys (AP-08)

**Decision:** Keys in `settings` table (string key / json value):

| Key | Type | Default |
| --- | ---- | ------- |
| `request.approval.auto.department_manager` | `bool` | `false` |
| `request.approval.auto.hr` | `bool` | `false` |
| `request.approval.auto.dormitory_manager` | `bool` | `false` |
| `request.approval.auto.dormitory_unit` | `bool` | `false` |

**Rationale:** AP-08; constitution four-stage chain configurable per stage.

---

## R-10 — Dependent snapshots (CD-009 / OA-05-04)

**Decision:** FamilyDirect requests persist **`request_dependent_snapshots`** child rows at submit (immutable thereafter).

| Field | Notes |
| ----- | ----- |
| `source_dependent_id` | Optional UUID copied from Employee supplier — **no FK** |
| `first_name`, `last_name` | Required snapshot |
| `relationship` | string enum TBD — see unresolved |
| `national_code` | Optional string |
| `captured_at` | UTC timestamp at submit |

**No** `dependent_id` FK to `employee_dependents`.

**Capture timing:** Snapshots **frozen at submit**; draft edits may refresh snapshot draft rows until submit.

**Blocked:** Live Employee Dependent supplier until spec03 US3 — Wave 1B uses fixture/stub `DependentSnapshotSourcePort` internal to tests.

---

## R-11 — Mission members (BR-04 / OA-05-05)

**Decision:** `RequestMember` rows for `type = Mission` only.

| Field | Notes |
| ----- | ----- |
| `employee_id` | UUID reference to Employee — **no FK** |
| `is_leader` | Exactly one `true` per request |
| Member count | 2–20 inclusive at submit |

**Eligibility:** BR-01 evaluated for **submitting employee** (`requests.employee_id`) only — not per member in spec05.

**Mission metadata:** Separate 1:1 `request_mission_details` (document URL, description).

---

## R-12 — Request types

**Decision:** Enum `RequestType`: `Personal`, `FamilyDirect`, `Mission`, `LotteryRegistration`.

| Type | Required children at submit |
| ---- | --------------------------- |
| `Personal` | None |
| `FamilyDirect` | ≥1 dependent snapshot |
| `Mission` | 2–20 members + mission details |
| `LotteryRegistration` | None (same shape as Personal; consumed by spec06) |

---

## R-13 — Domain events (Workflow-ready / CD-010)

**Decision:** Wave 1 events use spec01 **`BaseEvent` + `EVENT_NAME` / `VERSION`** (mirror spec03 `EmployeeCreated`). No kernel event registry in Wave 1.

| Event | When |
| ----- | ---- |
| `RequestSubmitted` | After successful submit |
| `RequestApprovalRecorded` | After each approval/reject row |
| `RequestApproved` | Terminal approve (`Approved`) |
| `RequestRejected` | Terminal reject |
| `RequestCancelled` | Cancel from Draft/Submitted |

Workflow (deferred) subscribes later — Request ownership unchanged.

---

## R-14 — Intra-module FK policy

**Decision:** FK constraints **allowed** within Request tables:

```text
requests
  ← request_approvals.request_id
  ← request_members.request_id
  ← request_dependent_snapshots.request_id
  ← request_mission_details.request_id (1:1)
```

**Cross-module FK:** prohibited.

---

## R-15 — Approver authorization (presentation concern)

**Decision:** Stage → Spatie permission mapping documented for implementation; **not** enforced in Domain layer.

| Stage | Permission (planned) |
| ----- | -------------------- |
| `DepartmentManager` | `request.approve.department` |
| `HR` | `request.approve.hr` |
| `DormitoryManager` | `request.approve.dormitory` |
| `DormitoryUnit` | `request.approve.dormitory_unit` |

**Open for design review:** Exact permission slugs may align with spec02 role seeds — confirm at implementation authorization.

---

## R-16 — `PendingRequestReadPort` adapter placement

**Decision:** Class `PendingRequestReadAdapter` in `app/Modules/Request/Infrastructure/Adapters/` implements `App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort`.

**Registration:** Bound in `RequestServiceProvider` (or shared provider bootstrap) — **replaces** Employee `NullPendingRequestReadAdapter` when spec05 ships.

**OA-05-09 normative rule:**

```text
PendingRequestReadPort is a read-only pull contract.
It exposes Request status information required for eligibility checks only.
It MUST NOT expose Request commands or lifecycle mutation operations.
Request remains the sole owner of Request lifecycle state.
```

Canonical reference: [contracts/employee-request-boundary.md](./contracts/employee-request-boundary.md).

---

## Deferred (documentation only)

| Topic | Status |
| ----- | ------ |
| Post-approval states | spec07 (OA-05-03) |
| Workflow engine orchestration | Deferred (CD-010) |
| Lottery rules / draw | spec06 (CD-011) |
| Allocation overlap | spec07 (CD-014) |
| Check-in/out on Request state | OQ-06 / spec07 |
| Central AuditService | spec10 |
| Livewire UI | Post-MVP |

---

## Unresolved — requires Design Review

| ID | Question | Recommendation | Impact |
| -- | -------- | -------------- | ------ |
| DR-05-01 | Should `Draft` count toward `hasPendingRequest`? | **Yes** (R-04) | BR-01 strictness |
| DR-05-02 | Persist `Submitted` as visible state or collapse on submit? | **Persist** for audit; auto-advance in same transaction | UX / reporting |
| DR-05-03 | `relationship` enum values for dependent snapshots | Mirror Employee US3 when live; provisional: `spouse`, `child`, `parent`, `other` | data-model |
| DR-05-04 | System actor UUID for auto-approval `approver_id` | Dedicated `SystemActorId` constant in Shared kernel | audit consistency |
| DR-05-05 | Validate `member_employee_id` exists via `EmployeeReadContract` at Mission submit? | **Yes** when contract available; stub in Wave 1C tests | data integrity |
| DR-05-06 | `LotteryRegistration` requires open lottery program? | **No** in spec05 — spec06 gates program linkage | scope boundary |

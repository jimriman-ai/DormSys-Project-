# Feature Specification: Request Management (spec05)

**Feature Branch**: `005-request-management`

**Created**: 2026-06-23

**Status**: **Planning — artifacts ready for review** (planning authorization not recorded; implementation not authorized)

**Catalog**: spec05 — **Planned** (`spec-catalog.md` v1.0.5)

**Depends on**: spec01 Foundation (Approved), spec02 Identity & Access (**Frozen — Wave 1A**), spec03 Employee Context (**Wave 1A + 1B complete**; US3 Dependent on hold)

**Optional reference (no catalog dependency)**: spec04 Accommodation Resource (**Design approved** — `dormitory_id` as immutable UUID reference only; no Dormitory module required for spec05 planning)

**Input**: Establish the **Request** bounded context: accommodation request submission, request lifecycle state, request-type variations, and request-level approval state/history — as upstream supplier for Lottery (spec06) and Allocation (spec07) — while respecting **CD-010** (Request owns approval state; Workflow deferred), **CD-013** (Request enforces eligibility; Employee computes), and **CD-009** (Dependent ∈ Employee; Request holds snapshots only).

**Normative boundaries**: [`../../.specify/docs/catalog-decisions.md`](../../.specify/docs/catalog-decisions.md) **CD-009**, **CD-010**, **CD-013**; [`../../.specify/docs/context-map.md`](../../.specify/docs/context-map.md) Request row, **R2** (Employee → Request), **R3** (Request ↔ Workflow), **R4** (Request → Lottery), **R6** (Request → Allocation).

**Upstream supplier contracts (existing)**:

- [`../003-employee-context/contracts/employee-eligibility-service.md`](../003-employee-context/contracts/employee-eligibility-service.md) (CD-013)
- [`../002-identity-access/contracts/identity-read-service.md`](../002-identity-access/contracts/identity-read-service.md) (approver validation — optional Wave 1)

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Personal Accommodation Request (Priority: P1)

As an employee, I need to create and submit a **Personal** accommodation request with intended stay dates and target dormitory reference so that my housing need enters the official approval pipeline.

**Why this priority**: Personal requests are the simplest path to validate BR-01 enforcement, request persistence, and lifecycle entry — foundation for all other request types.

**Independent Test**: Create draft Personal request → submit → verify status becomes `Submitted` and enters first approval pending state — using Employee eligibility contract stub/fixture only; no Allocation or Lottery modules.

**Acceptance Scenarios**:

1. **Given** an eligible employee, **When** a Personal request draft is created with valid future check-in and check-out dates, **Then** the request is persisted in `Draft` with a stable request code
2. **Given** a draft Personal request, **When** the employee submits, **Then** `EmployeeEligibilityContract` is invoked and BR-01 date rules are enforced at submission
3. **Given** an ineligible employee (e.g., inactive per eligibility contract), **When** submission is attempted, **Then** submission is rejected with stable reason codes — no state transition to approval pipeline
4. **Given** check-in date in the past or check-out not after check-in, **When** submission is attempted, **Then** submission is rejected (BR-01 date subset owned by Request enforcement)
5. **Given** a submitted request, **When** inspected, **Then** `employee_id` and `dormitory_id` are stored as immutable UUID references **without FK** to Employee or Dormitory tables

---

### User Story 2 - Request Lifecycle & Cancellation (Priority: P1)

As an employee or administrator, I need the request to follow a governed lifecycle with explicit states and limited cancellation rules so that downstream modules can rely on consistent request status.

**Why this priority**: Constitution AP-05 mandates explicit state machines for Request; Allocation and Lottery consume approved/eligible states.

**Independent Test**: Drive a request through `Draft` → approval-pending states → `Approved` or `Rejected`; attempt cancellation only in permitted states — without Workflow module or Allocation.

**Acceptance Scenarios**:

1. **Given** a draft request, **When** submitted, **Then** lifecycle transitions follow the constitution Request state machine through approval stages (OA-05-01)
2. **Given** a request in `Draft` or `Submitted`, **When** the submitting employee cancels, **Then** status becomes `Cancelled`
3. **Given** a request past `Submitted` in approval pipeline, **When** cancellation is attempted by employee, **Then** operation is rejected (policy per discovery — cancel only early states)
4. **Given** any non-terminal state, **When** an approver rejects with reason, **Then** status becomes `Rejected` and reason is recorded
5. **Given** a request reaches `Approved`, **When** inspected by a downstream stub consumer, **Then** request is readable as allocation-ready input (R6) — physical allocation not performed in spec05

---

### User Story 3 - Four-Stage Approval Records (Priority: P2)

As a department manager, HR manager, dormitory manager, or dormitory unit manager, I need approval decisions recorded per stage so that the organization has auditable approval history tied to the request.

**Why this priority**: CD-010 assigns `RequestApproval` ownership to Request; four-stage chain is constitution-defined before Workflow activation.

**Independent Test**: Progress request through DeptMgr → HR → DormMgr → DormUnitMgr approval stages with `RequestApproval` rows — inline routing rules in Request (Workflow deferred).

**Acceptance Scenarios**:

1. **Given** a request in `PendingDepartmentManager`, **When** department manager approves, **Then** a `RequestApproval` record is appended and status advances to `PendingHR`
2. **Given** each approval stage, **When** approved, **Then** `approver_id`, `stage`, `decision`, and `decided_at` are persisted (approver as UUID reference — no Identity FK)
3. **Given** configured auto-approval for a stage (OA-05-02), **When** request enters that stage, **Then** system may auto-approve per settings without Workflow engine
4. **Given** any stage, **When** rejected, **Then** approval history includes rejection reason and request becomes `Rejected`
5. **Given** final dormitory unit approval, **When** completed, **Then** request becomes `Approved` — not yet `WaitingForAllocation` until spec07 handoff (OA-05-03)

---

### User Story 4 - FamilyDirect Request with Dependent Snapshots (Priority: P2)

As an employee with registered dependents, I need to submit a **FamilyDirect** request that captures dependent participants at submission time so that family accommodation rules can be enforced downstream without Request owning dependent lifecycle.

**Why this priority**: BR-03 applies to FamilyDirect; CD-009 requires snapshots/references, not Dependent aggregate ownership.

**Independent Test**: Submit FamilyDirect request with dependent snapshot lines sourced from Employee read contract — verify Request stores snapshot payload, not `employee_dependents` ownership.

**Acceptance Scenarios**:

1. **Given** an employee with dependents (via Employee supplier), **When** a FamilyDirect draft is created, **Then** selected dependents are captured as **immutable snapshots** on the request (OA-05-04)
2. **Given** a FamilyDirect request, **When** submitted, **Then** request `type` is `FamilyDirect` and standard eligibility rules apply (BR-01)
3. **Given** a FamilyDirect request without at least one dependent snapshot, **When** submission is attempted, **Then** submission is rejected
4. **Given** a dependent updated in Employee after submission, **When** request is read, **Then** snapshot values remain unchanged (point-in-time capture)

**Dependency note**: Requires spec03 **US3 Dependent** delivery or documented test fixtures until Employee Dependent supplier is live.

---

### User Story 5 - Mission (Group) Request (Priority: P3)

As an organizational coordinator, I need to submit a **Mission** request with multiple members and a designated leader so that group maintenance housing can be processed under BR-04.

**Why this priority**: Mission requests introduce `RequestMember` and group constraints — needed before spec07 multi-room allocation patterns.

**Independent Test**: Create Mission request with 2–20 members and leader → submit → verify member list persisted — without Allocation distribution logic.

**Acceptance Scenarios**:

1. **Given** a Mission draft, **When** members are added with a designated `group_leader` among them, **Then** `RequestMember` records are persisted under the request
2. **Given** fewer than 2 or more than 20 members, **When** submission is attempted, **Then** submission is rejected (BR-04)
3. **Given** no designated leader or leader not in member set, **When** submission is attempted, **Then** submission is rejected
4. **Given** a valid Mission request, **When** submitted, **Then** eligibility is evaluated for the submitting employee (leader/coordinator policy per OA-05-05)

---

### User Story 6 - LotteryRegistration Request Type (Priority: P3)

As an employee, I need to register a **LotteryRegistration** request type so that participation in lottery programs can reference a governed request record.

**Why this priority**: Constitution lists `LotteryRegistration` as a request type; Lottery module (spec06) consumes approved registrations.

**Independent Test**: Create and submit LotteryRegistration request → verify type and lifecycle through approval — without running lottery draw (spec06).

**Acceptance Scenarios**:

1. **Given** an eligible employee, **When** a LotteryRegistration request is submitted for a dormitory reference, **Then** request is persisted with `type = LotteryRegistration`
2. **Given** an approved LotteryRegistration request, **When** a downstream Lottery stub queries by request id, **Then** request metadata is available via supplier read contract (OA-05-06)
3. **Given** lottery program rules, **When** evaluated in spec05, **Then** spec05 does **not** implement lottery scoring, draw, or results (CD-011 → spec06)

---

### Edge Cases

- Duplicate pending request for same employee? (BR-01 — `pending_request_exists` via eligibility port; Request must register pending state for port when implemented.)
- Submit while Employee eligibility service unavailable? (R-013-01 — document failure mode: block submission; mitigation deferred to plan.)
- Unknown `employee_id` or `dormitory_id` UUID? (Reject at submission or validate via supplier contracts — policy in plan.)
- Approver lacks role for stage? (Authorization via spec02 roles — enforcement in Presentation/Application; detail in plan.)
- Auto-approval enabled for all stages? (Allowed per settings — still emits `RequestApproval` audit rows.)
- Request approved but Allocation never happens? (`WaitingForAllocation` / `AllocationFailed` — **spec07**; document handoff only.)
- Check-in / check-out states on Request? (**Out of scope** — BR-13 / OQ-06; Request may transition to post-allocation states only when spec07 activates.)
- Cross-module query of `employee_*` or `dormitory_*` tables? (Forbidden — architecture boundary.)
- Workflow engine activation mid-flight? (CD-010 — Request retains state ownership; Workflow subscribes to events when activated.)

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST maintain **Request** as root aggregate of the Request bounded context
- **FR-002**: System MUST support request types: **Personal**, **FamilyDirect**, **Mission**, **LotteryRegistration** (constitution domain vocabulary)
- **FR-003**: System MUST store **`employee_id`** as immutable UUID reference to Employee — **no FK** to `employee_*` tables (CD-012 pattern)
- **FR-004**: System MUST store **`dormitory_id`** (target site) as immutable UUID reference — **no FK** to `dormitory_*` tables
- **FR-005**: System MUST enforce **BR-01** at submission: invoke **`EmployeeEligibilityContract`** (CD-013) plus Request-owned date rules (check-in not past; check-out after check-in)
- **FR-006**: System MUST implement **explicit Request lifecycle state machine** per constitution AP-05 (OA-05-01)
- **FR-007**: System MUST own **`RequestApproval`** entity, approval **state**, and **history** (CD-010)
- **FR-008**: System MUST implement **four-stage approval chain** inline in Request while Workflow is deferred: DepartmentManager → HR → DormitoryManager → DormitoryUnit (OA-05-02)
- **FR-009**: System MUST emit **domain events** on material state transitions for future Workflow subscription (CD-010 integration pattern)
- **FR-010**: System MUST capture **dependent snapshots** on FamilyDirect requests — not own Dependent lifecycle (CD-009, OA-05-04)
- **FR-011**: System MUST enforce **BR-04** for Mission requests (2–20 members, designated leader in member set)
- **FR-012**: System MUST persist **`RequestMember`** records for Mission requests
- **FR-013**: System MUST generate stable **request codes** (e.g., `REQ-YYMMDD-NNNN` pattern per discovery — exact format in plan)
- **FR-014**: System MUST expose **read-only supplier queries** for downstream contexts (Lottery, Allocation, Reporting) without cross-module Eloquent
- **FR-015**: System MUST record audit-relevant Request lifecycle actions (constitution AP-06)
- **FR-016**: System MUST use **UUID** identifiers for all Request entities (spec01 kernel)
- **FR-017**: System MUST support **cancellation** in `Draft` and `Submitted` only unless policy extended in plan

### Explicitly Out of Scope (spec05)

- **FR-EX-001**: Lottery programs, scoring, draw execution, results (**Lottery** — spec06; CD-011)
- **FR-EX-002**: Room/bed assignment, overlap detection, allocation lifecycle (**Allocation** — spec07; CD-014)
- **FR-EX-003**: Physical dormitory catalog management (**Dormitory** — spec04)
- **FR-EX-004**: Check-in / check-out operational transitions (**OQ-06** / spec07)
- **FR-EX-005**: Voucher issuance (**Voucher** — spec08)
- **FR-EX-006**: Workflow engine module activation and orchestration (**deferred** — CD-010; Request owns state Wave 1)
- **FR-EX-007**: Employee, Department, Dependent aggregate management (**spec03** — consumer only)
- **FR-EX-008**: Identity account lifecycle (**spec02** — consumer only for approver validation)
- **FR-EX-009**: Post-approval states `WaitingForAllocation`, `Allocated`, `CheckedIn`, `CheckedOut`, `AllocationFailed` implementation (**spec07** handoff — OA-05-03)
- **FR-EX-010**: Livewire employee/approver UI (deferred to post-MVP presentation wave)

### Key Entities

- **Request**: Accommodation application; type, stay dates, status, submitting employee reference, target dormitory reference, request code.
- **RequestApproval**: Append-only approval decision per stage; approver reference, stage, decision, reason, timestamp.
- **RequestMember**: Participant in a Mission request; employee or person reference per plan; leader flag.
- **DependentSnapshot** (value / embedded): Point-in-time dependent data on FamilyDirect requests — not the Employee Dependent aggregate.
- **MissionDetails** (optional child): Mission document reference and description — owned by Request aggregate scope.

---

## Success Criteria *(mandatory)*

- **SC-001**: Personal request can be drafted, submitted, and driven through four approval stages to `Approved` without Allocation, Lottery, or Workflow modules
- **SC-002**: Ineligible employees are blocked at submission with stable reason codes from `EmployeeEligibilityContract` (CD-013)
- **SC-003**: FamilyDirect requests store dependent snapshots that do not mutate when Employee dependents change later (CD-009)
- **SC-004**: Mission requests reject invalid group sizes and missing leaders (BR-04)
- **SC-005**: `RequestApproval` history is complete and append-only for each decision
- **SC-006**: Architecture boundary test passes — no cross-module persistence imports between Request and Employee/Dormitory/Allocation/Lottery Infrastructure
- **SC-007**: Domain events emitted on submit and approval transitions for documented event names (Workflow-ready)
- **SC-008**: PHPStan L8 zero errors for Request module paths when implemented (DoD)

---

## Assumptions & Recorded Decisions

### OA-05-01 — Request state machine scope (DECIDED)

**Decision:** spec05 implements the full **approval-phase** state machine:

```text
Draft → Submitted → PendingDepartmentManager → PendingHR
     → PendingDormitoryManager → PendingDormitoryUnit → Approved
     → Rejected | Cancelled
```

**Deferred to spec07:** `WaitingForAllocation`, `Allocated`, `AllocationFailed`, `CheckedIn`, `CheckedOut`.

**Rationale:** Keeps spec05 bounded to request + approval ownership; Allocation drives post-approval transitions per R6.

### OA-05-02 — Approval routing without Workflow (DECIDED — CD-010 Wave 1)

**Decision:** While Workflow is **deferred**, Request module owns **inline transition rules** for the four-stage chain. Auto-approval per stage reads from `settings` table (AP-08) when configured.

**When Workflow activates:** Request continues to own `RequestApproval` state; Workflow subscribes to Request domain events and may supply transition commands — boundary unchanged (CD-010).

### OA-05-03 — Post-approval handoff (RECORDED ASSUMPTION)

**Assumption:** `Approved` is the terminal success state for spec05 MVP. Transition to `WaitingForAllocation` is triggered by spec07 Allocation consumer or explicit handoff action — not implemented in spec05 Wave 1.

### OA-05-04 — Dependent snapshot model (DECIDED — CD-009)

**Decision:** FamilyDirect requests store **immutable snapshot rows** (name, relationship, national identifier fields per plan) captured from Employee supplier at draft finalize/submit. No `dependent_id` FK to `employee_dependents`.

**Blocked by:** spec03 US3 (Dependent) — spec05 planning proceeds; implementation wave for FamilyDirect follows US3 authorization or uses contract stubs in tests.

### OA-05-05 — Mission eligibility subject (DECIDED)

**Decision:** BR-01 eligibility is evaluated for the **submitting employee** (request owner / coordinator). Per-member allocation eligibility is **spec07** concern.

### OA-05-06 — Downstream supplier contract (RECORDED ASSUMPTION)

**Assumption:** `RequestReadContract` (name finalized in plan) exposes approved request summaries for Lottery and Allocation. Exact DTO fields deferred to `plan.md` / `contracts/`.

### OA-05-07 — Dormitory reference validation (RECORDED ASSUMPTION)

**Assumption:** `dormitory_id` is validated via **`DormitoryReadContract`** when spec04 is implemented; until then, stub adapter accepts any well-formed UUID in tests. spec05 does not require spec04 implementation per catalog dependencies.

### OA-05-08 — Pending request port feedback (DECIDED — CD-013 completion)

**Decision:** When spec05 implements Request persistence, Employee module **`PendingRequestReadPort`** stub is replaced with real adapter reading Request supplier — closes BR-01 `pending_request_exists` loop. Coordinated change across spec03 adapter + spec05 (documented in plan).

---

## Dependency Summary (spec01–spec04)

| Spec | Relationship | Usage in spec05 |
| ---- | ------------ | --------------- |
| **spec01** | Platform foundation | Module scaffold, UUID kernel, migrations pattern, architecture tests |
| **spec02** | Upstream supplier | Authenticated actor; optional `IdentityUserReadContract` for approver existence; RBAC roles for stage authorization |
| **spec03** | Upstream supplier | `employee_id` reference; **`EmployeeEligibilityContract`** (CD-013); Department context for routing (future); Dependent read for FamilyDirect (US3 hold) |
| **spec04** | Optional validator | `dormitory_id` reference; **`DormitoryReadContract`** when available — **not** a catalog hard dependency |

**Downstream consumers (out of scope for implementation):** spec06 Lottery (R4), spec07 Allocation (R6).

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `spec-catalog.md` spec05 | Request Management; Planned |
| `handoff/spec04-planning-authorization.md` | spec05 not authorized for implementation |
| `handoff/spec03-post-mvp-authorization.md` | US3/US4 hold — affects FamilyDirect timing |
| CD-009 | Dependent snapshots only |
| CD-010 | RequestApproval ownership; Workflow deferred |
| CD-013 | Eligibility enforce vs compute split |
| BR-01, BR-04 | Eligibility and group size |
| R-013-01 | Employee downtime blocks submission — mitigate in plan |
| AP-05 | State machine mandatory |
| AP-08 | Auto-approval settings |

---

## Consumer Language Guard (authoring)

- ✅ Request **owns** request lifecycle state and `RequestApproval` history
- ✅ Request **enforces** BR-01 at submission; Employee **computes** eligibility
- ✅ Request **snapshots** dependents; Employee **owns** Dependent aggregate
- ✅ Lottery **owns** draw rules (spec06); Request **supplies** registration request records
- ❌ Request storing authoritative allocation or bed assignment
- ❌ FK from `request_*` to `employee_*`, `dormitory_*`, `allocation_*`, `identity_*`
- ❌ Cross-module Eloquent queries
- ❌ Workflow engine implementation in spec05

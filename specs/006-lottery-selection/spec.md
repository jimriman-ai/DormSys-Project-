# Feature Specification: Lottery Selection (spec06)

**Feature Branch**: `006-lottery-selection`

**Created**: 2026-06-30

**Status**: `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` (Spec06-local composite; see GDR)

| Layer (Spec06-local) | Value |
| -------------------- | ----- |
| Planning | Complete |
| Implementation | Complete |
| Governance | Open |
| Documentation / mirrors | ALIGNED |
| Authority | `AUTHORITY_NOT_AVAILABLE` (Documented Exception — Option B) |
| Lifecycle posture | `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` |

> **Note:** Implementation is complete as per discovery evidence. Governance remains OPEN due to `AUTHORITY_NOT_AVAILABLE` for the Lottery Selection domain. This is a documented exception (Option B) as of 2026-07-12.

**Catalog**: spec06 — see `spec-catalog.md` (Status `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN`; not “Planned” alone; not Fully Closed)

**Governance decision / evolution**: [`.specify/docs/decision/spec06-regularization-decision.md`](../../.specify/docs/decision/spec06-regularization-decision.md) — authoritative for future Spec06 regularization context

**Regularization plan**: [`.specify/docs/plans/spec06-regularization-plan.md`](../../.specify/docs/plans/spec06-regularization-plan.md)

**Execution authorization**: [`.specify/docs/handoff/spec06-regularization-execution-authorization.md`](../../.specify/docs/handoff/spec06-regularization-execution-authorization.md) (`GRANTED` — limited documentary alignment)

**New implementation**: Not authorized by documentary regularization; Governance remains Open.

**Depends on**: spec01 Foundation; spec05 Request Management (`RequestReadContract` supplier)

**Optional reference**: spec04 Accommodation Resource (`dormitory_id` UUID validation via stub until Dormitory supplier is live)

**Input**: Establish the **Lottery** bounded context: lottery programs, participant registrations, deterministic scoring, draw execution, and result production — as upstream supplier for Allocation (spec07) per **CD-011** and **context-map.md** R4 (Request → Lottery), R5 (Lottery → Allocation).

**Normative boundaries**: [`../../.specify/docs/catalog-decisions.md`](../../.specify/docs/catalog-decisions.md) **CD-011**; [`../../.specify/docs/context-map.md`](../../.specify/docs/context-map.md) Lottery row, **R4**, **R5**.

**Upstream supplier contracts (existing)**:

- [`../005-request-management/contracts/request-read-service.md`](../005-request-management/contracts/request-read-service.md) — approved `lottery_registration` requests

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Lottery Program Lifecycle (Priority: P1)

As a lottery operator, I need to create and progress lottery programs through a governed lifecycle so that registration windows and draws run only when the program is in a valid state.

**Why this priority**: All registrations and draws attach to a program aggregate; constitution defines explicit Lottery Program state machine (AP-05).

**Independent Test**: Create program in `Draft` → progress through `RegistrationOpen` → `RegistrationClosed` → `Locked` → `Drawn` → `Completed` using domain state transitions only — without Allocation or Voucher modules.

**Acceptance Scenarios**:

1. **Given** no program exists, **When** an operator creates a program with dormitory reference, registration window, and capacity, **Then** program persists in `Draft`
2. **Given** a `Draft` program, **When** published for registration, **Then** status becomes `RegistrationOpen` per constitution lottery program states
3. **Given** `RegistrationOpen`, **When** registration deadline passes or operator closes registration, **Then** status becomes `RegistrationClosed`
4. **Given** `RegistrationClosed`, **When** program is locked for draw, **Then** status becomes `Locked` and eligible registration snapshot is frozen (immutable)
5. **Given** `Locked`, **When** draw completes successfully, **Then** status becomes `Drawn` then `Completed` with persisted results
6. **Given** any non-terminal state, **When** operator cancels with reason, **Then** status becomes `Cancelled` and no further transitions are permitted

---

### User Story 2 - Participant Registration (Priority: P1)

As an employee with an approved lottery registration request, I need to enroll in an open lottery program so that my participation is recorded for scoring and draw.

**Why this priority**: Registrations are the input to scoring and draw; Request supplies approved requests via read contract (R4).

**Independent Test**: With approved `lottery_registration` request fixture → enroll in open program → verify `LotteryRegistration` persisted — without running draw.

**Acceptance Scenarios**:

1. **Given** an open program and an approved lottery registration request, **When** enrollment is submitted, **Then** a `LotteryRegistration` links `request_id`, `employee_id`, and `program_id` as UUID references without cross-module FK
2. **Given** a closed or locked program, **When** enrollment is attempted, **Then** operation is rejected
3. **Given** duplicate enrollment for same request in same program, **When** second enrollment is attempted, **Then** operation is rejected
4. **Given** enrollment, **When** Request read contract reports request no longer approved, **Then** enrollment validation policy applies at lock time (re-validate at lock — OA-06-01)
5. **Given** enrollment, **When** inspected, **Then** Lottery does not mutate Request lifecycle (read-only consumer)

---

### User Story 3 - Deterministic Scoring (Priority: P1)

As the system, I need to compute a reproducible weighted score per registration at lock time so that lottery ranking is auditable and deterministic.

**Why this priority**: Constitution mandates transparent, reproducible lottery; formula loaded from `settings` — never hardcoded (BR scoring discipline).

**Independent Test**: Lock program with fixed seed and known registrations → compute scores → re-run with same inputs → identical ranking order.

**Acceptance Scenarios**:

1. **Given** locked program with `random_seed` and scoring config from settings, **When** scores are calculated, **Then** each registration receives `weighted_score` per configured formula
2. **Given** same seed, config, and registration set, **When** scoring runs twice, **Then** scores and rank order are identical
3. **Given** employee win history, **When** penalty rules apply per config, **Then** score reflects penalty without manual override
4. **Given** scoring config missing or invalid, **When** lock is attempted, **Then** operation fails with stable error — draw cannot proceed

---

### User Story 4 - Draw Execution & Results (Priority: P2)

As a lottery operator, I need to execute the draw and persist winners and reserves so that Allocation can consume proposed outcomes.

**Why this priority**: CD-011 assigns result production to Lottery; R5 defines downstream handoff to Allocation (contract stub in spec06).

**Independent Test**: Run draw on locked program → persist `LotteryResult` rows (winner/reserve ranks) → expose via read contract stub — without Allocation module.

**Acceptance Scenarios**:

1. **Given** a `Locked` program with scored registrations, **When** draw job runs, **Then** winners are selected up to program capacity and results persisted
2. **Given** draw job, **When** executed twice with same locked snapshot, **Then** outcome is idempotent (no duplicate results — AP-07)
3. **Given** completed draw, **When** results queried, **Then** each result includes `registration_id`, `rank`, `outcome` (winner/reserve), and `program_id`
4. **Given** internal dormitory program, **When** draw completes, **Then** proposed allocation payload is emitted via stub port/event only (spec07 implements consumption)
5. **Given** draw failure mid-transaction, **When** job retries, **Then** database state remains consistent (transactional draw)

---

### User Story 5 - Auto-Lock & Background Jobs (Priority: P3)

As the system, I need scheduled lock and draw jobs so that registration deadlines are enforced without manual intervention.

**Why this priority**: Constitution lists `AutoLockLotteryJob` and `ExecuteLotteryDrawJob` as primary background jobs.

**Independent Test**: Schedule auto-lock past deadline → verify program transitions to `Locked`; queue draw job → verify results — using test queue driver.

**Acceptance Scenarios**:

1. **Given** program past registration end with `RegistrationClosed`, **When** auto-lock job runs, **Then** program becomes `Locked` and snapshot taken
2. **Given** `Locked` program ready for draw, **When** execute draw job dispatched, **Then** draw runs asynchronously and program reaches `Completed`
3. **Given** job failure, **When** retried, **Then** idempotent behavior prevents duplicate results

---

### Edge Cases

- Program capacity exceeds eligible registrations? (All eligible become winners; no error)
- Program capacity zero? (Reject at program creation)
- Request deleted or cancelled after enrollment? (Handled at lock re-validation — exclude or fail per OA-06-01)
- Concurrent enrollments at capacity? (First-wins or waitlist — document in plan; default: no waitlist at enrollment, capacity enforced at draw)
- External dormitory program? (Results only; no bed assignment — voucher path deferred to spec08)
- Cross-module Eloquent on `request_*` or `employee_*`? (Forbidden — use `RequestReadContract` only)
- Scoring settings changed after lock? (Ignored — snapshot uses config at lock time)

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST maintain **LotteryProgram** as root aggregate of the Lottery bounded context (CD-011)
- **FR-002**: System MUST maintain **LotteryRegistration** for participant enrollment per program
- **FR-003**: System MUST maintain **LotteryResult** for draw outcomes (winner/reserve)
- **FR-004**: System MUST implement **Lottery Program state machine** per constitution AP-05 (`Draft` through `Completed` / `Cancelled`)
- **FR-005**: System MUST store `request_id`, `employee_id`, `dormitory_id` as immutable UUID references — **no FK** to Request, Employee, or Dormitory tables
- **FR-006**: System MUST consume approved lottery requests via **`RequestReadContract`** only (R4) — no cross-module Eloquent
- **FR-007**: System MUST compute **deterministic weighted scores** using formula from `settings` and `random_seed` at lock (never hardcode formula)
- **FR-008**: System MUST freeze **eligible registration snapshot** at lock before scoring/draw
- **FR-009**: System MUST execute draw inside a **database transaction** with **idempotent** job semantics (AP-07)
- **FR-010**: System MUST emit **proposed allocation** data via stub contract/event for Allocation consumer (R5) — no Allocation implementation in spec06
- **FR-011**: System MUST emit **domain events** on material program and result transitions for audit (AP-06)
- **FR-012**: System MUST reject enrollment when program is not in `RegistrationOpen`
- **FR-013**: System MUST persist draw audit metadata (`random_seed`, scoring config version, actor) on result records

### Key Entities

- **LotteryProgram** — rules, schedule, capacity, dormitory reference, lifecycle state
- **LotteryRegistration** — enrollment linking program + request + employee
- **LotteryResult** — outcome row per registration (rank, outcome type)
- **EligibleSnapshot** — immutable capture at lock (constitution vocabulary)
- **ScoringConfig** — resolved settings payload at lock time

---

## Success Criteria *(mandatory)*

- **SC-001**: Operator can create a program and progress it through all lifecycle states in under 5 minutes in a test environment
- **SC-002**: 100% of draw re-runs with identical locked inputs produce identical winner ordering
- **SC-003**: Enrollment for an approved request completes in under 2 seconds at p95 in test environment
- **SC-004**: Draw job for 1,000 registrations completes in under 60 seconds in test environment
- **SC-005**: Zero cross-module Eloquent queries from Lottery to Request, Employee, or Dormitory tables (verified by architecture test)

---

## Assumptions

- **OA-06-01**: Lottery re-validates request approval status at lock time (CD-011 open question resolved by default: re-validate in Lottery)
- **OA-06-02**: `base_lottery_score` and department priority consumed via Employee read port stub until dedicated Employee lottery port exists
- **OA-06-03**: Dormitory capacity validation uses optional read stub until spec04 supplier is live
- **OA-06-04**: UI (Livewire) for operator flows deferred; Application actions + tests deliver MVP
- **OA-06-05**: Voucher generation for external dormitories deferred to spec08

---

## Out of Scope (spec06)

- Allocation assignment execution (spec07)
- Voucher issuance (spec08)
- Request lifecycle changes (spec05)
- Workflow engine
- Livewire operator UI (follow-on)
- Physical bed tracking (spec04 / Dormitory)

---

## Governance & Evolution Notes

**Authority:** Recorded in [`.specify/docs/decision/spec06-regularization-decision.md`](../../.specify/docs/decision/spec06-regularization-decision.md) (Decisions 1–4). This note does not create Nomination, Design Approval, Implementation Authorization, or terminal closure.

### Documented exception (Option B)

`tasks.md` Complete (T001–T055) and the Lottery module footprint are **acknowledged as delivery evidence**. Spec06 is **regularized via documented exception (Option B) due to pre-governance implementation** — implementation ahead of a map-backed Nomination → Design Approval → Implementation Authorization chain.

This regularization is **documentary/governance alignment only**. It does **not**:

- invent or backdate a Spec06 IA/DA/Nomination handoff chain
- claim `AUTHORITY_CONFIRMED` or reconstruct authority from SPEC06-C06 (remains `UNKNOWN` unless later amended)
- claim Full Closure / `FULLY_CLOSED` (Governance remains **Open**)

**Authority statement:** Spec06-named map-backed authority is **`AUTHORITY_NOT_AVAILABLE` (Documented Exception)**.

**Evidence pointers:** `.specify/governance/wave-02-conflict-register.md` (SPEC06-C01…C07); `.specify/governance/records/spec06-validation-record.md`; `.specify/governance/reports/spec06-transition-gate-record.md`; `.specify/docs/plans/spec06-regularization-plan.md`.

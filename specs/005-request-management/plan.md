# Implementation Plan: Request Management (spec05)

**Branch**: `005-request-management` | **Date**: 2026-06-23 | **Spec**: [spec.md](./spec.md)

**Input**: Request bounded context — accommodation request types, lifecycle state machine (approval phase), BR-01 enforcement via Employee supplier, four-stage approval records (CD-010), dependent snapshots (CD-009), supplier read API for Lottery/Allocation.

**Governance**: **Planning review checkpoint — PASS** — planning authorization **not** recorded in catalog. Implementation **not** authorized. spec04 remains frozen.

---

## Pre-Plan Validation

| Source | Check | Result |
| ------ | ----- | ------ |
| [spec.md](./spec.md) | US1–US6, FR-001–FR-017, OA-05-01–OA-05-09 | ✅ Complete |
| [spec-catalog.md](../../.specify/docs/spec-catalog.md) | spec05 Planned; depends spec01–spec03 | ✅ Aligned |
| [context-map.md](../../.specify/docs/context-map.md) | Request row; R2, R3, R4, R6 | ✅ Aligned |
| [CD-009](../../.specify/docs/catalog-decisions.md) | Dependent snapshots; not ownership | ✅ Plan respects Employee ownership |
| [CD-010](../../.specify/docs/catalog-decisions.md) | RequestApproval in Request; Workflow deferred | ✅ Inline routing Wave 1 |
| [CD-013](../../.specify/docs/catalog-decisions.md) | Request enforces; Employee computes | ✅ Uses `EmployeeEligibilityContract` |
| spec03 handoff | US1+US2 complete; US3/US4 hold | ✅ Noted — FamilyDirect blocked or stubbed |
| spec04 status | Design approved; implementation hold | ✅ `dormitory_id` UUID + optional read contract stub |
| Module scaffold | `app/Modules/Request/` (spec01) | ✅ Four-layer paths exist; no business code |

---

## Summary

Implement the **Request** module as the accommodation application aggregate for employees: **Personal**, **FamilyDirect**, **Mission**, and **LotteryRegistration** types; **spatie/laravel-model-states** lifecycle through **Approved**; append-only **`RequestApproval`** history; **BR-01** enforcement at submit via **`EmployeeEligibilityContract`** plus local date validation.

Wave 1 delivery (when implementation authorized): draft/submit actions, approval progression actions, **inline four-stage routing** (Workflow deferred), **`RequestReadContract`** supplier surface, **`PendingRequestReadPort` adapter** feeding Employee eligibility loop, boundary architecture test, audit via `RecordsActivity` + domain events. **No** Lottery, Allocation, Dormitory implementation, Workflow engine, CheckIn/CheckOut, or Livewire UI.

**MVP boundary (planning):** US1–US3 (Personal + lifecycle + approvals) required for first implementation wave; US4–US6 phased per dependency gates below.

---

## Technical Context

| Dimension | Value |
| --------- | ------- |
| **Language/Version** | PHP 8.4; Laravel 13 |
| **Primary Dependencies** | spec01 kernel; `spatie/laravel-model-states`; spec03 `EmployeeEligibilityContract` |
| **UUID strategy** | UUID v7 via `HasUuid` on all `request_*` tables |
| **Storage** | PostgreSQL 17 — migrations under `database/migrations/modules/request/` |
| **Testing** | Pest PHP 4; unit (Domain/States), feature (actions + contracts), architecture (boundary) |
| **Target Platform** | Laravel Sail |
| **Performance Goals** | Request list/submit < 500ms p95 (constitution 10.1) |
| **Constraints** | No cross-module Eloquent; no FK to `employee_*`, `dormitory_*`, `allocation_*`, `identity_*`; Persian RTL UI deferred |
| **Scale/Scope** | 4 request types; 1 eligibility consumer; 1 supplier read contract; state machine ~12 approval-phase states |

---

## Constitution Check

*GATE: Must pass before implementation. Re-check after Phase 1 design artifacts.*

| Principle | Compliance | Notes |
| --------- | ------------ | ----- |
| AP-01 Technology Stack | ✅ PASS | Laravel 13, PostgreSQL 17, Pest |
| AP-01 Presentation | ⚠️ DEFERRED | Livewire request forms post-MVP |
| AP-02 Modular Monolith | ✅ PASS | `app/Modules/Request/` four layers |
| AP-03 Clean Architecture | ✅ PASS | Domain pure PHP; Eloquent in Infrastructure |
| AP-04 Shared DB / Module Ownership | ✅ PASS | `request_*` owned by Request; UUID refs without cross-module FK |
| AP-05 State Machines | ✅ PASS | `spatie/laravel-model-states` for Request lifecycle |
| AP-06 Audit Everything | ⚠️ CONDITIONAL | `RecordsActivity` + approval events; central `AuditService` deferred |
| AP-07 Background Processing | ⬜ N/A | Synchronous submit/approve Wave 1 |
| AP-08 Configuration | ✅ PASS | Auto-approval flags per stage in `settings` |
| 10.7 Localization | ⚠️ DEFERRED | Persian RTL UI deferred |
| 10.4 DoD | ✅ PASS | PHPStan L8, Pint, Pest — planned in future tasks |
| CD-009 | ✅ PASS | Snapshots only |
| CD-010 | ✅ PASS | RequestApproval in Request; no Workflow code |
| CD-013 | ✅ PASS | Enforce at submit via contract |
| BR-01, BR-04 | ✅ PASS | Eligibility + group size |

**Post-design re-check**: Deferred to completion of `data-model.md` and `contracts/` (Phase 1 design — not created in this step).

---

## Dependency Analysis (spec01–spec04)

### Hard dependencies (catalog)

| Spec | Artifact | Request usage | Status |
| ---- | -------- | ------------- | ------ |
| **spec01** | Module scaffold, `BaseModel`, `HasUuid`, `BaseEvent`, arch tests | Foundation | ✅ Approved |
| **spec02** | Auth, RBAC, `IdentityUserReadContract` | Approver actor; optional approver UUID validation | ✅ Frozen Wave 1A |
| **spec03** | `EmployeeEligibilityContract`, `EmployeeId`, employee existence | BR-01 compute at submit | ✅ Contract defined; US1+US2 live |
| **spec03** | `EmployeeReadContract` / Dependent supplier | FamilyDirect snapshots (US4) | ⏸ US3 on hold — stub or defer wave |
| **spec03** | `PendingRequestReadPort` | BR-01 `pending_request_exists` | 🔄 Stub today — **spec05 delivers real adapter** |

### Soft dependency (not in catalog)

| Spec | Artifact | Request usage | Status |
| ---- | -------- | ------------- | ------ |
| **spec04** | `DormitoryReadContract` | Validate `dormitory_id` at submit | 🔒 Design approved; stub until spec04 impl |

### Downstream (Request as supplier)

| Spec | Relationship | Needs from Request |
| ---- | ------------ | ------------------ |
| **spec06** Lottery | R4 consumer | Approved `LotteryRegistration` requests |
| **spec07** Allocation | R6 consumer | Approved requests → allocation proposals |
| **spec09** Notification | Event consumer | Submit/approve/reject events (future) |
| **spec10** Audit | Event/hook consumer | Critical transitions |
| **spec11** Reporting | Read projection | Request summaries |

### Dependency chain (no cycles)

```text
spec01
  └── spec02 (Identity)
        └── spec03 (Employee) ──EmployeeEligibilityContract──► spec05 (Request)
              └── optional: spec04 (Dormitory read stub)
spec05 ──RequestReadContract──► spec06, spec07 (future)
```

---

## Domain Boundaries

### Owned by Request (spec05)

| Concern | Entities / artifacts |
| ------- | -------------------- |
| Request aggregate | `Request` — type, dates, status, codes, employee/dormitory UUID refs |
| Approval history | `RequestApproval` — append-only per stage decision |
| Group membership | `RequestMember` — Mission participants |
| Family capture | `DependentSnapshot` rows (embedded child or separate table — Phase 1 design) |
| Mission metadata | `MissionDetails` — document URL, description (optional child) |
| Lifecycle | State machine classes under `Domain/States/` |
| Eligibility enforcement | Submit validator orchestrating `EmployeeEligibilityContract` + date rules |
| Approval routing (Wave 1) | Inline stage progression + auto-approval from `settings` |
| Supplier read API | `RequestReadContract` |

### Owned elsewhere (explicitly out of scope)

| Concern | Owner | spec05 interaction |
| ------- | ----- | ------------------ |
| Employee profile, Department, Dependent lifecycle | **Employee** (spec03) | UUID + eligibility contract + snapshot source |
| User accounts, roles | **Identity** (spec02) | AuthZ for approver actions |
| Dormitory catalog | **Dormitory** (spec04) | `dormitory_id` UUID; optional read validation |
| Assignment, overlap | **Allocation** (spec07) | Consumes approved requests — no implementation |
| Lottery draw, scoring | **Lottery** (spec06) | Consumes registration requests |
| Approval orchestration engine | **Workflow** (deferred) | Event subscription only — not built |
| Check-in/out | **CheckIn/CheckOut** (OQ-06) | Post-allocation states deferred |

### CD-010 integration model (R3 — deferred engine)

```
Request (spec05)  ──domain events──►  Workflow [deferred]
     │ owns RequestApproval state
     │ inline routing Wave 1
     └──────────────────────────────────►  Lottery / Allocation (read contracts)
```

---

## MVP Boundary (implementation waves)

| Wave | User stories | Scope | Gate |
| ---- | ------------ | ----- | ---- |
| **Wave 1A** | US1, US2, US3 | Personal request; lifecycle; four-stage approvals to `Approved` | Employee eligibility live; Identity auth |
| **Wave 1B** | US4 | FamilyDirect + dependent snapshots | **spec03 US3 authorized** or approved stub strategy |
| **Wave 1C** | US5, US6 | Mission + LotteryRegistration types | US1–US3 stable |
| **Post-MVP** | — | `WaitingForAllocation`+ states, Livewire UI, Workflow handoff | spec07 planning |

**Planning recommendation:** Authorize implementation Wave 1A first; do not block spec05 planning on spec04 implementation.

---

## Implementation Phases (documentation — tasks deferred)

### Phase A — Setup

- Wire `RequestServiceProvider` migrations path `database/migrations/modules/request/`
- `RequestPresentationServiceProvider` for Artisan commands
- Module README with CD-009/010/013 boundaries

### Phase B — Foundational

- Value objects: `RequestId`, `RequestCode`
- Enums: `RequestType`, `ApprovalStage`, `ApprovalDecision`
- Domain exceptions: `RequestNotFoundException`, `InvalidRequestTransitionException`, `RequestNotEligibleException`, `InvalidGroupRequestException`
- Migrations: `requests`, `request_approvals`, `request_members`, `request_dependent_snapshots` (order in Phase 1 `data-model.md`)
- Base `Request` entity + `RequestModel` + repository

### Phase C — US1 Personal submit

- `CreatePersonalRequestAction`, `SubmitRequestAction`
- BR-01: `EmployeeEligibilityContract` + date validation
- State: `Draft` → `Submitted` → first pending stage

### Phase D — US2 Lifecycle

- Full state classes per OA-05-01
- `CancelRequestAction` (Draft/Submitted only)
- Domain events: `RequestSubmitted`, `RequestCancelled`, `RequestRejected`

### Phase E — US3 Approvals

- `ApproveRequestStageAction`, `RejectRequestAction`
- `RequestApproval` persistence (append-only)
- Auto-approval reader from `settings`
- Terminal `Approved` state

### Phase F — US4–US6 (phased)

- FamilyDirect snapshot capture action
- Mission member validation (BR-04)
- LotteryRegistration type flagging

### Phase G — Supplier contracts

- `RequestReadContract` + DTOs
- `PendingRequestReadAdapter` in Infrastructure — implements Employee port
- Feature tests for supplier surface

### Phase H — Polish

- `RequestConsumerBoundaryTest` (BT-R05)
- PHPStan, Pint, quickstart scenarios
- Event `EVENT_NAME`/`VERSION` per module pattern (R-11 analog)

### Phase I — Presentation (deferred)

- Livewire create/list/approve components

---

## State Machine (approval phase — normative)

Aligned with [spec.md](./spec.md) OA-05-01 and constitution AP-05 (truncated for spec05):

```text
                    ┌─────────────┐
                    │   Draft     │
                    └──────┬──────┘
                           │ submit (eligible)
                           ▼
                    ┌─────────────┐
         cancel ◄───│  Submitted  │───► PendingDepartmentManager
                    └─────────────┘              │
                                                 ▼
                                          PendingHR
                                                 │
                                                 ▼
                                    PendingDormitoryManager
                                                 │
                                                 ▼
                                    PendingDormitoryUnit
                                                 │
                                                 ▼
                                           Approved  (spec05 terminal)
                                                 
Any approval stage ──reject──► Rejected
Draft/Submitted ──cancel──► Cancelled
```

**Deferred transitions (spec07):** `Approved` → `WaitingForAllocation` → `Allocated` → `CheckedIn` → `CheckedOut`; `AllocationFailed`.

State classes live in `app/Modules/Request/Domain/States/` — transitions **only** via Application Actions, never Livewire/controllers.

---

## Cross-Module Contracts (outline — detail in Phase 1)

| Direction | Contract | Purpose |
| --------- | -------- | ------- |
| Inbound | `EmployeeEligibilityContract` (spec03) | BR-01 compute |
| Inbound | `IdentityUserReadContract` (spec02) | Optional approver validation |
| Inbound | `DormitoryReadContract` (spec04) | Optional dormitory existence |
| Outbound | `RequestReadContract` (spec05) | Lottery/Allocation reads |
| Outbound | `PendingRequestReadPort` adapter | Employee eligibility loop |

Phase 1 design will produce `contracts/request-read-service.md`, `contracts/employee-request-boundary.md`, `contracts/request-eligibility-enforcement.md`.

### PendingRequestReadPort — read-only constraint (normative)

**Ownership pattern:**

```text
Employee Context                    Request Context
─────────────────                   ───────────────
owns: PendingRequestReadPort   ←── implements adapter (read-only)
      (interface)                     owns: Request lifecycle state
```

**Normative rule (OA-05-09):**

```text
PendingRequestReadPort is a read-only pull contract.
It exposes Request status information required for eligibility checks only.
It MUST NOT expose Request commands or lifecycle mutation operations.
Request remains the sole owner of Request lifecycle state.
```

| Allowed | Prohibited |
| ------- | ---------- |
| `hasPendingRequest(EmployeeId): bool` | Any command or state mutation on Request |
| Eligibility fact: "does employee X have a pending request?" | `CancelRequest`, `ApproveRequest`, `ChangeRequestState`, etc. |
| Adapter in Request `Infrastructure/Adapters/` | Employee Infrastructure writing to `request_*` tables |

**Architecture test (future):** BT-R09 — `PendingRequestReadPort` implementation exposes no methods beyond the port interface; Employee module does not import Request Application mutation services.

---

## Test Strategy (boundary tests)

| ID | Assertion |
| -- | --------- |
| BT-R01 | Eligible employee → Personal submit succeeds |
| BT-R02 | Ineligible employee → submit rejected with reason codes |
| BT-R03 | Four-stage approval → `Approved` with 4 `RequestApproval` rows |
| BT-R04 | Reject at any stage → `Rejected` with reason |
| BT-R05 | Architecture — no Employee/Dormitory/Allocation/Lottery Infrastructure imports in Request |
| BT-R06 | FamilyDirect snapshot immutable after Employee dependent update |
| BT-R07 | Mission with 1 or 21 members → rejected (BR-04) |
| BT-R08 | `PendingRequestReadPort` returns true when open request exists |
| BT-R09 | `PendingRequestReadPort` adapter is read-only — no Request command/mutation surface (OA-05-09) |

---

## Risk Mitigations (from catalog)

| Risk | Mitigation in spec05 plan |
| ---- | ------------------------- |
| R-013-01 Employee downtime | Fail closed on submit; no cache in Wave 1A; document circuit breaker in research.md |
| R-013-02 CD-013 reopen | Keep eligibility logic in Employee; Request only orchestrates |
| spec03 US3 hold | Wave 1B gate for FamilyDirect; Wave 1A does not require Dependent |

---

## Module Structure (documentation)

```text
app/Modules/Request/
├── Domain/
│   ├── Entities/Request.php, RequestApproval.php, RequestMember.php
│   ├── ValueObjects/RequestId.php, RequestCode.php
│   ├── Enums/RequestType.php, ApprovalStage.php, ApprovalDecision.php
│   ├── States/RequestState.php, DraftState.php, PendingDepartmentManagerState.php, ...
│   ├── Events/RequestSubmitted.php, RequestApproved.php, RequestRejected.php, ...
│   └── Exceptions/...
├── Application/
│   ├── Contracts/RequestReadContract.php
│   ├── DTOs/RequestSummaryDTO.php, RequestApprovalDTO.php
│   └── Services/
│       ├── CreatePersonalRequestAction.php
│       ├── SubmitRequestAction.php
│       ├── ApproveRequestStageAction.php
│       ├── RejectRequestAction.php
│       └── RequestReadService.php
├── Infrastructure/
│   ├── Persistence/Models/RequestModel.php, RequestApprovalModel.php, ...
│   ├── Repositories/...
│   ├── Adapters/
│   │   ├── PendingRequestReadAdapter.php      # implements Employee port
│   │   └── NullDormitoryReadAdapter.php       # until spec04
│   └── Providers/RequestServiceProvider.php
└── Presentation/
    └── Console/...                             # request:* dev commands

database/migrations/modules/request/
├── *_create_requests_table.php
├── *_create_request_approvals_table.php
├── *_create_request_members_table.php
└── *_create_request_dependent_snapshots_table.php

specs/005-request-management/
├── spec.md                                     # ✅ complete
├── plan.md                                     # this file
├── research.md                                 # ⏳ after planning authorization
├── data-model.md                               # ⏳ after planning authorization
├── contracts/                                  # ⏳ after planning authorization
├── quickstart.md                               # ⏳ after planning authorization
└── tasks.md                                    # 🔒 after planning authorization + design review
```

---

## Phase 1 Design Artifacts

| Artifact | Status |
| -------- | ------ |
| [research.md](./research.md) | ⏳ Not started — pending planning authorization |
| [data-model.md](./data-model.md) | ⏳ Not started |
| [contracts/](./contracts/) | ⏳ Not started |
| [quickstart.md](./quickstart.md) | ⏳ Not started |
| [tasks.md](./tasks.md) | 🔒 Blocked until planning authorization + design approval |

---

## Recommended Governance Sequence

1. **Review** `spec.md` + `plan.md` (this checkpoint)
2. **Record** `handoff/spec05-planning-authorization.md` + catalog bump → Planning Authorized
3. **Phase 1 design** — research, data-model, contracts, quickstart
4. **Design approval tag** (e.g., `spec05-design-approved`)
5. **`/speckit-tasks`** — task breakdown
6. **Separate** `spec05-implementation-authorization.md` before T001

---

## Verification Gates (future implementation)

```bash
php artisan test tests/Feature/Modules/Request tests/Unit/Modules/Request tests/Architecture/RequestConsumerBoundaryTest.php
vendor/bin/phpstan analyse app/Modules/Request
vendor/bin/pint app/Modules/Request
```

---

## Platform Sequence (unchanged)

```
spec01 → spec02 → spec03 (1A+1B) → spec04 (design ✅) → spec05 → spec06 → spec07 → …
```

spec05 planning does not require spec04 implementation; optional `DormitoryReadContract` stub suffices for Wave 1A.

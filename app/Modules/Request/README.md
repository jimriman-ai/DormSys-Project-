# Request Module (spec05)

**Bounded context:** Accommodation request submission, lifecycle state, and request-level approval history.  
**Spec:** `specs/005-request-management/` · **Implementation:** authorized T001–T052 ([`handoff/spec05-implementation-authorization.md`](../../../.specify/docs/handoff/spec05-implementation-authorization.md)).

## Purpose

Request is the **supplier** of accommodation application records for downstream Lottery (spec06) and Allocation (spec07). It owns request lifecycle and approval state — not employee profiles, dormitory catalog, assignment, or lottery execution.

## Request types

| Type | Description |
| ---- | ----------- |
| `Personal` | Single-employee accommodation request |
| `FamilyDirect` | Family request with **dependent snapshots** (CD-009) |
| `Mission` | Group request (2–20 members, designated leader — BR-04) |
| `LotteryRegistration` | Registration record for lottery programs (rules in spec06) |

## Ownership rules

| Concern | Owner |
| ------- | ----- |
| Request lifecycle state | **Request** |
| `RequestApproval` history (append-only) | **Request** |
| Submission / date validation enforcement | **Request** |
| Eligibility **computation** (BR-01) | **Employee** — `EmployeeEligibilityContract` |
| Eligibility **enforcement** at submit | **Request** — calls contract + local date rules |
| Dependent aggregate lifecycle | **Employee** (spec03) |
| Dormitory catalog | **Dormitory** (spec04) |

## Boundary decisions

| ID | Rule |
| -- | ---- |
| **CD-009** | FamilyDirect stores **dependent snapshots** only — no `employee_dependents` FK or aggregate ownership |
| **CD-010** | Request owns `RequestApproval` state and history; **Workflow engine deferred** (inline routing Wave 1) |
| **CD-013** | Employee **computes** eligibility; Request **enforces** at submission |
| **OA-05-09** | `PendingRequestReadPort` is **read-only / query-only** — `hasPendingRequest()` only; must never become a command boundary |

## Contracts

| Direction | Contract |
| --------- | -------- |
| Inbound | `EmployeeEligibilityContract` (spec03) |
| Inbound | `IdentityUserReadContract` (spec02, optional approver validation) |
| Inbound | `DormitoryReadContract` (spec04, optional — stub until spec04 impl) |
| Outbound | `RequestReadContract` — read-only projections; consumers cannot mutate lifecycle |
| Outbound adapter | `PendingRequestReadAdapter` implements Employee `PendingRequestReadPort` |

Canonical docs: `specs/005-request-management/contracts/`

## Explicit forbidden boundaries

- **No cross-module FK** — `employee_id`, `dormitory_id`, `approver_id` as immutable UUID refs only
- **No direct Eloquent** access to `employee_*`, `dormitory_*`, `allocation_*`, `lottery_*`, or `identity_*` tables
- **No** Workflow module implementation
- **No** Lottery logic (spec06) or Allocation logic (spec07)
- **No** post-approval states (`WaitingForAllocation`+) in spec05 — terminal success: `Approved` (spec07 handoff)

## Module layout

- Migrations: `database/migrations/modules/request/`
- Infrastructure provider: `RequestServiceProvider` — loads module migrations
- Presentation provider: `RequestPresentationServiceProvider` — Artisan commands (T025+)

## Phase 1 setup (complete)

- T001–T004: provider wiring, migration path, presentation provider shell

## References

- [`spec.md`](../../../specs/005-request-management/spec.md)
- [`data-model.md`](../../../specs/005-request-management/data-model.md)
- [`tasks.md`](../../../specs/005-request-management/tasks.md)

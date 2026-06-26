# DormSys Catalog Decisions

**Version:** 2.2.0  
**Status:** ACTIVE  
**Last Updated:** 1405/04/05 | 2026/06/26  
**Related Documents:** [`context-map.md`](context-map.md), [`spec-catalog.md`](spec-catalog.md), `CONSTITUTION v1.3.0.md`, `dormsys-architecture.md`

---

## Purpose

This document is the authoritative register of architectural boundary decisions for DormSys v1.0.  
It records:

- resolved conflicts between source documents,
- closures of open boundary questions (OQ-*),
- rationale, impact, and deferred implementation details.

Decisions here supersede provisional assumptions in discovery notes and informal history documents.

---

## Decision Index

| ID | Title | Status | Date | Closes |
| -- | ----- | ------ | ---- | ------ |
| CD-009 | Dependent Entity Ownership | ACCEPTED | 2026-06-25 | CONF-DEP-01 |
| CD-010 | Approval State vs Transition Rules | ACCEPTED | 2026-06-25 | OQ-03 |
| CD-011 | Lottery Domain Centralization | ACCEPTED | 2026-06-25 | OQ-04 |
| CD-012 | Employee ↔ Identity Attachment Mechanism | ACCEPTED | 2026-06-26 | OQ-01 |
| CD-013 | Eligibility Invariant Ownership | ACCEPTED (Recorded Assumption) | 2026-06-26 | OQ-02 (Current Scope) |
| CD-014 | Allocation ↔ Occupancy Ownership Split | ACCEPTED | 2026-06-26 | OQ-05 |

---

## Open Boundary Questions (Remaining)

| ID | Question | Priority | Next Action |
| -- | -------- | -------- | ----------- |
| OQ-06 | Is check-in / check-out inside Allocation, or a separate context? | Medium | Boundary Review when `spec07` planning starts; related to CD-014 but not resolved by it |
| OQ-07 | Where does voucher eligibility ownership live? | Medium | Evidence review during `spec08` specification |
| OQ-08 | What is the reporting projection boundary / read-model scope? | Medium | Define during `spec11` planning |

---

## Risk Register (Cross-Boundary)

| Risk ID | Description | Likelihood | Impact | Mitigation |
| ------- | ----------- | ---------- | ------ | ---------- |
| R-012-01 | Identity deletion leaves orphaned Employee records | Low | Medium | Defer to `spec03`: soft delete, event notification, or validation on Employee creation |
| R-013-01 | Employee service downtime blocks Request submission | Medium | High | Defer to `spec05`: circuit breaker, cached eligibility, or async validation |
| R-013-02 | Future eligibility reuse invalidates CD-013 | Low | High | Recorded assumption: reopen OQ-02 if evidence appears |
| R-014-01 | Allocation unavailability blocks occupancy transitions | Medium | Medium | Defer to `spec07` / `spec04`: event-driven reconciliation and idempotent handlers |

---

## Evidence Summary

| ID | Type | Primary Evidence For | Primary Evidence Against | Confidence | Status |
| -- | ---- | ------------------ | ------------------------ | ---------- | ------ |
| CONF-DEP-01 | Conflict | `dormsys-architecture.md:78` → Employee | `CONSTITUTION v1.3.0.md:642` → Request table | High | RESOLVED (CD-009) |
| OQ-01 | Open Question | `dormsys-architecture.md:324` → immutable ID, no FK | Exact sync mechanism unspecified | Medium | CLOSED (CD-012) |
| OQ-02 | Open Question | `hist03.md:2431` → logic in Employee; `CONSTITUTION:521` → BR-01 under Request | No explicit invariant owner named | Medium | CLOSED — Current Scope (CD-013) |
| OQ-03 | Open Question | `dormsys-architecture.md:80` → RequestApproval in Request; `:81,377` → Workflow engine | — | High | CLOSED (CD-010) |
| OQ-04 | Open Question | `dormsys-architecture.md:83`; `CONSTITUTION:356` → Lottery lifecycle | — | High | CLOSED (CD-011) |
| OQ-05 | Open Question | `dormsys-architecture.md:81,86` → Allocation owns assignment; `:80-83` → Dormitory + CheckIn own physical/operational | — | High | CLOSED (CD-014) |

---

## CD-009 — Dependent Entity Ownership

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-25 |
| **Type** | Conflict Resolution |
| **Status** | ACCEPTED |
| **Closes** | CONF-DEP-01 |
| **Related** | `CONSTITUTION v1.3.0.md:435,642`, `dormsys-architecture.md:78` |

### Context

Internal conflict in source documents:

- **Constitution §I.2 / line 435:** defines `Dependent ∈ Employee`
- **Constitution line 642:** states "Request owns Dependents table"
- **Architecture:** consistently places Dependent within Employee boundary

This affects aggregate root design, service boundaries, and data lifecycle.

### Evidence

| Supports Employee ownership | Supports Request ownership |
| --------------------------- | -------------------------- |
| `CONSTITUTION:435` — entity definition | `CONSTITUTION:642` — table assignment |
| `dormsys-architecture.md:78` — Employee aggregate includes Dependent | `Discovery:379` — `request_id` on Dependent (linkage only) |
| `system-flow.md:78,351` — Dependent in Employee tree | Weak: linkage ≠ aggregate ownership |
| Lifecycle: Dependent created/modified with Employee context | |

### Decision

**`Dependent ∈ Employee` (Option A).**

Request retains **snapshots or references** to Dependent data at submission time; it does not own the Dependent aggregate.

### Rationale

1. Architectural documents consistently place Dependent in Employee.
2. Dependent lifecycle is tied to Employee, not Request.
3. `request_id` on Dependent is a reference link, not ownership.
4. Conflict originated from inconsistent Constitution wording, not genuine ambiguity.

### Impact

- **Constitution v1.3.0.md:642** must be corrected to reflect Employee ownership.
- Employee BC owns Dependent CRUD.
- Request BC consumes Dependent data via Application Service or Event (read-only or snapshot).

### What Was NOT Decided

- Snapshot format on Request submission.
- Handling when Dependent is modified after request submission.

---

## CD-010 — Approval State vs Transition Rules

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-25 |
| **Type** | Domain Boundary |
| **Status** | ACCEPTED |
| **Closes** | OQ-03 |
| **Related** | `dormsys-architecture.md:80-81,377`, `CONSTITUTION v1.3.0.md:81,101,356` |

### Context

Approval spans Request data and a reusable workflow engine. Source documents split entity storage from process orchestration.

### Evidence

- `dormsys-architecture.md:80` — `Request (Request, RequestApproval)`
- `dormsys-architecture.md:81,377` — `Workflow (Approval Engine)` orchestrates multi-stage chains
- `Discovery:66` — Dept Mgr → HR → Dorm Mgr → Dorm Unit Mgr chain
- `CONSTITUTION:356` — approval as state transition mechanism

### Decision

**Split ownership:**

| Context | Owns |
| ------- | ---- |
| **Request** | `RequestApproval` entity, approval **state**, history |
| **Workflow** (deferred module) | Approval **transition rules**, chain definition, routing, orchestration |

Integration pattern:

- Request emits approval state changes as Domain Events.
- Workflow subscribes and triggers next approval steps when activated.
- Final approval is delivered back to Request via Domain Event.

### Rationale

- Approval records stay with the request aggregate (data locality).
- Workflow engine can serve multiple domains when activated.
- Single responsibility: Request manages state; Workflow manages process rules.

### Impact

- `RequestApproval` table remains in Request schema.
- Workflow module stays **deferred** per spec catalog until activation criteria are met.
- Boundary applies immediately to modeling; implementation follows module activation.

### What Was NOT Decided

- Auto-approval configuration storage location.
- Workflow module activation timeline.

---

## CD-011 — Lottery Domain Centralization

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-25 |
| **Type** | Domain Boundary |
| **Status** | ACCEPTED |
| **Closes** | OQ-04 |
| **Related** | `dormsys-architecture.md:83,393`, `system-flow.md:253`, `CONSTITUTION v1.3.0.md:356,370` |

### Context

Lottery rules, programs, registrations, and results could have been split across Request, Lottery, and Allocation.

### Evidence

- `dormsys-architecture.md:83` — `Lottery (LotteryProgram, Registration, Result)`
- `system-flow.md:253,365-367` — Lottery state machine with full lifecycle
- `Discovery:11,29,38` — lottery as core allocation mechanism
- `CONSTITUTION:356,370` — lottery rules and auditable execution in dedicated module

### Decision

**Lottery BC owns all lottery-related concerns:**

- `LotteryProgram` (rules, criteria, schedules)
- `LotteryRegistration` (participant enrollment)
- `LotteryResult` (outcome records)
- Scoring, eligibility for draw, and program lifecycle

Lottery emits **proposed allocations** to Allocation; Allocation owns assignment execution.

### Rationale

- Single source of truth for lottery logic.
- Clean separation from allocation execution.
- Audit trail integrity for draw operations.

### Impact

- Lottery has full write authority over lottery lifecycle.
- Allocation consumes lottery results as read-only input.
- Request references lottery registration status via Application Service or Event.

### What Was NOT Decided

- Exact event contract between Lottery and Allocation.
- Whether Request-level eligibility gates lottery registration or Lottery re-validates.

---

## CD-012 — Employee ↔ Identity Attachment Mechanism

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-26 |
| **Type** | Boundary Decision |
| **Status** | ACCEPTED |
| **Closes** | OQ-01 |
| **Related** | `dormsys-architecture.md:324`, `CONSTITUTION v1.3.0.md:640-641`, `context-map.md` R1 |

### Context

Identity and Employee are separate bounded contexts. The attachment mechanism between them was unspecified.

### Evidence

| Source | Location | Statement |
| ------ | -------- | --------- |
| `dormsys-architecture.md` | line 324 | FK forbidden across modules; use Immutable Identifier |
| `CONSTITUTION v1.3.0.md` | §I.2 | Identity owns Users/Roles/Permissions; Employee owns Employees/Departments |
| `Discovery` | lines 151-152 | `/identity` and `/employee` are separate paths |

### Evidence NOT Found

- No permission for FK or shared table between contexts.
- No mandated event/sync attachment pattern.
- No shared lifecycle between Employee and Identity.

### Options Considered

| Option | Verdict |
| ------ | ------- |
| A) Immutable UUID linkage, no FK | **CHOSEN** |
| B) Domain Event attachment | Not chosen — no evidence requiring eventual consistency at this stage |
| C) Shared table | Rejected — violates Constitution §I.2 |

### Decision

Employee attaches to Identity via an **immutable UUID reference** (`identity_id`), without FK or shared table.

**Domain invariant:** `identity_id` is assigned exactly once at Employee creation. Subsequent modification is prohibited by the domain model.

### Coupling Analysis

| Dimension | Assessment |
| --------- | ---------- |
| Coupling type | Data reference (unidirectional) |
| Direction | Employee → Identity |
| Autonomy impact | Low |
| Circular risk | None |
| DDD pattern | Customer-Supplier (Employee downstream of Identity) |

### Impact

- No referential-integrity coupling at the database layer.
- Cross-context Identity data resolved by identifier, not join.
- Implementation deferred to `spec02` and `spec03`.

### What Was NOT Decided

- Validation when referenced Identity is deleted or deactivated.
- Projection/query pattern for Identity lookups.
- Caching strategy.

---

## CD-013 — Eligibility Invariant Ownership

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-26 |
| **Type** | Boundary Decision |
| **Status** | ACCEPTED (Recorded Assumption) |
| **Closes** | OQ-02 (Current Scope) |
| **Related** | `CONSTITUTION v1.3.0.md:521-524`, `hist03.md:2431`, `context-map.md` R2 |

### Context

BR-01 ("Request Eligibility") requires active employee and no active allocation. Data lives in Employee; the rule is titled under Request.

### Evidence

| Source | Statement |
| ------ | --------- |
| `CONSTITUTION:521-524` | BR-01: active employee + no active allocation |
| `hist03:2431` | Eligibility logic lives in Employee; Request consumes it |
| `hist03:1724` | Final ownership was open |

### Options Considered

| Option | Verdict |
| ------ | ------- |
| A) Request enforces; Employee answers queries | **CHOSEN** |
| B) Standalone Eligibility context | Not chosen — no second consumer; premature per Playbook §VIII |
| C) Eligibility fully inside Employee | Not chosen — BR-01 defined under Request in Constitution |

### Decision

| Context | Responsibility |
| ------- | -------------- |
| **Request** | Owns **enforcement** of eligibility invariant at submission |
| **Employee** | Owns **computation** of eligibility (data + logic) |

### Recorded Assumption

If eligibility is consumed elsewhere or gains an independent lifecycle, **OQ-02 must be reopened**.

This decision governs ownership only — not whether validation uses Application Service, Domain Service, Policy, or Specification.

### Coupling Analysis

| Dimension | Assessment |
| --------- | ---------- |
| Coupling type | Query/computation dependency (unidirectional) |
| Direction | Request → Employee |
| Autonomy impact | Medium |
| Circular risk | Low |
| DDD pattern | Customer-Supplier (Request downstream of Employee) |
| Failure mode | Employee unavailable → Request submission blocked |

### Impact

- Coupling: medium. Duplication: low. Testability: high.
- Implementation deferred to `spec03` and `spec05`.

### What Was NOT Decided

- Exact query mechanism (sync API vs specification).
- Error handling on eligibility-query failure.
- Caching strategy.

---

## CD-014 — Allocation ↔ Occupancy Ownership Split

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-26 |
| **Type** | Domain Boundary |
| **Status** | ACCEPTED |
| **Closes** | OQ-05 |
| **Related** | `dormsys-architecture.md:79-84`, `system-flow.md:180-182`, `context-map.md` R7 |
| **Does NOT close** | OQ-06 (CheckIn/CheckOut module boundary) |

### Context

"Occupancy" spans assignment authority, physical bed state, and operational check-in/out transitions. Source documents split these across modules.

### Evidence

- `dormsys-architecture.md:79-84` — `Dormitory`, `Allocation`, `CheckIn` as separate modules
- `Discovery:55` — occupancy updates after lottery allocation (Allocation drives)
- `system-flow.md:180-182` — `WaitingForAllocation → Allocated → CheckedIn → CheckedOut`
- `Discovery:21` — external dormitories: no physical occupancy monitoring

### Options Considered

| Option | Verdict |
| ------ | ------- |
| A) Unified Allocation + Occupancy context | Rejected — conflates assignment with physical/operational state |
| B) Split with Allocation as driver | **CHOSEN** |
| C) Standalone Occupancy context | Deferred — no independent lifecycle evidence; see OQ-06 |

### Decision

Ownership is **split**; Allocation is the upstream driver:

| Context | Owns |
| ------- | ---- |
| **Allocation** | Assignment authority (`Allocation`, `AllocationItem`) — who is assigned to what |
| **Dormitory** | Physical occupancy state (`Room`, `Bed` capacity/availability) |
| **CheckIn/CheckOut** | Operational transitions (`CheckedIn`, `CheckedOut`) |

**Invariant:** Effective bed occupancy is derived from active Allocation + CheckIn/CheckOut state — not stored authoritatively in one place.

### Impact

- Allocation publishes assignment events.
- Dormitory updates physical availability.
- CheckIn/CheckOut consumes assignment to enable transitions.
- Coupling: `Allocation → Dormitory` (R7), unidirectional.
- **OQ-06 remains OPEN** — whether CheckIn/CheckOut is promoted to an active module.

### Recorded Assumption

If occupancy later requires a unified, independently-owned lifecycle (e.g., bed blocking independent of allocation), **OQ-05 should be reopened**.

### What Was NOT Decided

- CheckIn/CheckOut module promotion (OQ-06).
- Reconciliation when Allocation and Dormitory state diverge.

---

## Boundary Review Session — CD-012 & CD-013

**Date:** 1405/04/05 | 2026/06/26  
**Scope:** Cross-boundary coupling for Identity-Employee attachment and Request-Employee eligibility

### Cross-Boundary Dependency Chain

```
Identity
   ↓ immutable UUID (CD-012)
Employee
   ↓ eligibility query (CD-013)
Request
```

- No circular dependencies detected.
- Each context owns its aggregates and invariants.
- Coupling direction follows standard DDD Customer-Supplier pattern.

### Session Outcomes

| Decision | Verdict |
| -------- | ------- |
| CD-012 | ACCEPTED — low risk |
| CD-013 | ACCEPTED — medium risk, mitigated by Recorded Assumption |
| Implementation details | Deferred to `spec02`, `spec03`, `spec05` |

**Next review trigger:** Evidence of eligibility reuse outside Request, or independent eligibility lifecycle.

---

## Evidence Traceability Index

### By Source Document

**CONSTITUTION v1.3.0.md**

- Lines 435, 641-642 → CONF-DEP-01, CD-009
- Lines 640-641 → OQ-01, CD-012
- Lines 521-524 → OQ-02, CD-013
- Lines 81, 101, 356, 370, 422 → OQ-03, OQ-04, OQ-05, CD-010, CD-011

**dormsys-architecture.md**

- Line 78 → CONF-DEP-01, CD-009
- Lines 77-78, 324 → OQ-01, CD-012
- Lines 80-81, 377, 379 → OQ-03, CD-010
- Lines 83, 86, 393 → OQ-04, CD-011
- Lines 79-84 → OQ-05, CD-014

**hist03.md**

- Lines 3362, 2456 → CONF-DEP-01, CD-009
- Lines 2431, 1724 → OQ-02, CD-013

**DormSys Discovery Document.md**

- Lines 379-380 → CONF-DEP-01, CD-009
- Lines 151-152 → OQ-01, CD-012
- Lines 37, 188 → OQ-02, CD-013
- Line 66 → OQ-03, CD-010
- Lines 11, 29, 38, 50 → OQ-04, CD-011
- Lines 21, 29, 36, 38, 55, 134 → OQ-05, CD-014

**system-flow.md**

- Lines 78, 351 → CONF-DEP-01, CD-009
- Lines 253, 365-367 → OQ-04, CD-011
- Lines 180-182, 204, 205, 297, 463 → OQ-05, CD-014

**context-map.md**

- R1 → OQ-01, CD-012
- R2 → OQ-02, CD-013
- R3 → OQ-03, CD-010
- R4 → OQ-04, CD-011
- R7 → OQ-05, CD-014

---

## Change Log

### 2.2.0 — 2026-06-26

- Full document rewrite for consistent structure and formatting.
- Fixed contradictory "Open Items" listing OQ-05 after CD-014 closed it.
- Removed broken markdown fences and duplicate sections.
- Moved CD-014 into canonical decision order.
- Aligned `context-map.md` cross-references.

### 2.1.0 — 2026-06-26

- Added CD-012, CD-013, CD-014.
- Conducted boundary review session for CD-012 and CD-013.
- Closed OQ-01, OQ-02, OQ-05.

### 2.0.0 — 2026-06-25

- Resolved CONF-DEP-01 via CD-009.
- Resolved OQ-03 via CD-010.
- Resolved OQ-04 via CD-011.

### 1.0.0 — 2026-06-24

- Initial evidence mapping and conflict documentation.

# DormSys Context Map

**Version:** 0.4.1  
**Status:** Inventory Updated / Critical Boundaries Closed (OQ-01 through OQ-08)  
**Last Updated:** 1405/04/10 | 2026/07/01

**Purpose:** Map relationships between bounded contexts as input to Boundary Review and specification work.

This document records **relationships only** — not decisions and not detailed design.  
Architectural decisions are recorded in [`catalog-decisions.md`](catalog-decisions.md).

---

## Constitution Constraints (Binding)

- DormSys is a modular monolith with a single PostgreSQL database (AP-04).
- Each module owns its own tables.
- Direct table / repository access between modules is forbidden.
- A shared database is allowed; direct cross-module table access is not.
- Cross-module communication is only via an Application Service or a Domain Event / Saga.
- Distributed transactions are forbidden.
- Reporting is the only context with cross-boundary read access, and it never writes.

---

## Context Inventory (Ownership)

| Context      | Spec   | Ownership (Evidence + Decision) |
| ------------ | ------ | ------------------------------- |
| Identity     | spec02 | User, Role, Permission |
| Employee     | spec03 | Employee, Department, Dependent — **CD-009**: Dependent owned by Employee; Request holds snapshots/references only |
| Dormitory    | spec04 | Dormitory, Room, Bed — physical capacity and availability |
| Request      | spec05 | Request, RequestApproval, RequestMember, RequestType — **CD-010**: Request owns approval state and history |
| Lottery      | spec06 | LotteryProgram, LotteryRegistration, LotteryResult — **CD-011**: all lottery rules and lifecycle centralized here |
| Allocation   | spec07 | Allocation, AllocationItem, AllocationMethod — **CD-014**: assignment authority only |
| CheckIn/CheckOut | spec07 | Operational occupancy transitions (`CheckedIn`, `CheckedOut`) — **CD-015**: active boundary |
| Voucher | spec08 | Voucher issuance lifecycle and voucher eligibility authority — **CD-016** |
| Notification | spec09 | NotificationLog (cross-cutting delivery) |
| Audit        | spec10 | AuditLog (cross-cutting traceability) |
| Reporting | spec11 | Read-only cross-context projections only — **CD-017**: no write authority, no upstream ownership |
| Workflow     | —      | **DEFERRED** capability — **CD-010**: when activated, owns approval transition rules and orchestration (not an active module yet) |

### Candidate Contexts (Not in Active Inventory)

| Candidate              | Status | Notes |
| ---------------------- | ------ | ----- |
| Occupancy (unified)    | Rejected as standalone | **CD-014**: occupancy is cross-cutting across Allocation, Dormitory, and CheckIn/CheckOut |

---

## Relationship Map (Summary)

```
Identity (R1, R12)
   ↓ immutable UUID (CD-012)
Employee (R2)
   ↓ eligibility query (CD-013)
Request ← orchestration when active (CD-010, R3) → Workflow [deferred]
   ↓ (R4)                    ↓ (R6)
Lottery (CD-011)         Allocation (CD-014)
   ↓ (R5)                    ↓ (R7)
Allocation ──────────→ Dormitory (physical state)
                           ↓
                      CheckIn/CheckOut (CD-015)

Lottery / Allocation (R8)
   ↓ upstream triggers
Voucher (CD-016)

Notification ← events from multiple contexts (R9)
Audit        ← hooks/events from critical operations (R10)
Reporting    ← read-only projections from all contexts (R11, CD-017)
```

---

## Relationships

### R1 — Identity → Employee

| Field | Value |
| ----- | ----- |
| Direction | Upstream (Identity supplies users, roles, permissions) |
| Integration | Application Service or Domain Event — direct repository access forbidden |
| Attachment | **DECIDED (CD-012):** immutable UUID reference on Employee; no FK, no shared table |
| Implementation | spec02 **frozen**; spec03 **in progress** |
| Open Question | **OQ-01 — CLOSED (CD-012)** |

### R2 — Employee → Request

| Field | Value |
| ----- | ----- |
| Direction | Upstream (Employee supplies identity and eligibility data) |
| Integration | Application Service or Domain Event — direct repository access forbidden |
| Eligibility | **DECIDED (CD-013):** Employee owns eligibility computation; Request owns invariant enforcement at submission |
| Implementation | Deferred to `spec03` (Employee) and `spec05` (Request) |
| Open Question | **OQ-02 — CLOSED — Current Scope (CD-013, Recorded Assumption)** |

### R3 — Request ↔ Workflow (Approval)

| Field | Value |
| ----- | ----- |
| Nature | Split ownership between Request data/state and Workflow process engine |
| Request owns | `RequestApproval` entity, approval state, history |
| Workflow owns | Approval chain definition, routing, transition rules, orchestration (when module is activated) |
| Integration | Request emits approval state changes; Workflow triggers next steps; final approval delivered via Domain Event |
| Workflow status | Module deferred per spec catalog; boundary decision applies at activation |
| Open Question | **OQ-03 — CLOSED (CD-010)** |

### R4 — Request → Lottery

| Field | Value |
| ----- | ----- |
| Direction | Upstream (approved/eligible requests feed lottery participation) |
| Integration | Application Service or Domain Event — direct repository access forbidden |
| Lottery ownership | **DECIDED (CD-011):** all lottery rules, programs, registrations, and results live in Lottery |
| Implementation | Deferred to `spec05` (Request) and `spec06` (Lottery) |
| Open Question | **OQ-04 — CLOSED (CD-011)** |

### R5 — Lottery → Allocation

| Field | Value |
| ----- | ----- |
| Direction | Upstream (lottery results drive allocation proposals) |
| Integration | Application Service or Domain Event — direct repository access forbidden |
| Ownership | Lottery emits results; Allocation consumes as read-only input and owns assignment execution |
| Implementation | Deferred to `spec06` (Lottery) and `spec07` (Allocation) |

### R6 — Request → Allocation

| Field | Value |
| ----- | ----- |
| Direction | Upstream (approved request drives allocation) |
| Integration | Application Service or Domain Event — direct repository access forbidden |
| Implementation | Deferred to `spec05` (Request) and `spec07` (Allocation) |


### R7 — Allocation → Dormitory → CheckIn/CheckOut

| Field | Value |
| ----- | ----- |
| Direction | Upstream assignment and physical-state chain |
| Integration | Application Service or Domain Event — direct repository access forbidden |
| Ownership split | **DECIDED (CD-014 + CD-015):** Allocation owns assignment; Dormitory owns physical room/bed state; CheckIn/CheckOut owns operational transitions |
| Implementation | Deferred to `spec04` (Dormitory) and `spec07` (Allocation + CheckIn/CheckOut) |
| Open Question | **OQ-05 — CLOSED (CD-014)**; **OQ-06 — CLOSED (CD-015)** |

### R8 — Lottery / Allocation → Voucher

| Field | Value |
| ----- | ----- |
| Direction | Upstream triggers may initiate voucher evaluation |
| Integration | Application Service or Domain Event — direct repository access forbidden |
| Ownership | **DECIDED (CD-016):** Voucher owns voucher eligibility evaluation and issuance lifecycle |
| Implementation | Deferred to `spec08` (Voucher) with upstream facts supplied by related contexts |
| Open Question | **OQ-07 — CLOSED (CD-016)** |


### R9 — Notification ← multiple contexts

| Field | Value |
| ----- | ----- |
| Direction | Downstream (consumes events from many contexts) |
| Integration | Domain Event consumption — direct repository access forbidden |
| Implementation | Mechanism deferred to `spec09` (Notification) |

### R10 — Audit ← all critical operations

| Field | Value |
| ----- | ----- |
| Direction | Downstream (records critical operations) |
| Integration | Domain Event / hook — direct repository access forbidden |
| Implementation | Deferred to `spec10` (Audit) |

### R11 — Reporting ← all contexts

| Field | Value |
| ----- | ----- |
| Direction | Downstream read-only across contexts |
| Integration | Read-only projection allowed; write forbidden |
| Ownership | **DECIDED (CD-017):** Reporting owns no upstream business state and acts only as a projection/read-model consumer |
| Open Question | **OQ-08 — CLOSED (CD-017)** |


### R12 — Identity → all contexts

| Field | Value |
| ----- | ----- |
| Direction | Upstream (authentication and authorization for all contexts) |
| Integration | Cross-cutting auth required — mechanism deferred to `spec02` (Identity) |

---

## Open Boundary Questions

| ID | Question | Status |
| -- | -------- | ------ |
| OQ-01 | Is Employee attached to Identity, or is it a separate entity? | **CLOSED (CD-012):** separate entity; attachment via immutable UUID, no FK |
| OQ-02 | Eligibility ownership — Request or Employee? | **CLOSED — Current Scope (CD-013):** Request enforces; Employee computes. Recorded Assumption — reopen if a second consumer or independent lifecycle appears |
| OQ-03 | Does Approval stay inside Request, or move to Workflow? | **CLOSED (CD-010):** Request owns state; Workflow owns transition rules when activated; final approval via Event |
| OQ-04 | Where does lottery rule / eligibility ownership live? | **CLOSED (CD-011):** centralized in Lottery; emits proposed allocations to Allocation |
| OQ-05 | Are Allocation and Occupancy one context, or split? | **CLOSED (CD-014):** split — Allocation owns reservation/assignment; Dormitory + CheckIn/CheckOut own physical and operational occupancy |
| OQ-06 | Is check-in / check-out / occupancy inside Allocation, or a separate context? | **CLOSED (CD-015):** CheckIn/CheckOut is an active boundary for operational transitions |
| OQ-07 | Where does voucher eligibility ownership live? | **CLOSED (CD-016):** Voucher owns eligibility and issuance lifecycle |
| OQ-08 | What is the reporting projection boundary / read-model scope? | **CLOSED (CD-017):** Reporting remains downstream read-only projection consumer |


---

## Catalog vs Source-Document Discrepancies

| ID | Discrepancy | Resolution |
| -- | ----------- | ---------- |
| D1 | Workflow is an independent module in source docs but a deferred capability in the spec catalog | Recorded in catalog-decisions; **CD-010** defines split ownership when Workflow is activated |
| D2 | CheckIn appears as an independent module in source docs; catalog groups it under Allocation & Occupancy spec | **RESOLVED (CD-015):** CheckIn/CheckOut promoted to active boundary within spec07 scope |
| D3 | Constitution line 642 historically assigned Dependents table to Request; architecture assigns Dependent to Employee | **RESOLVED (CD-009):** Dependent ∈ Employee; Constitution §11 aligned in v1.3.0 governance pass (PR #2, 2026-06-26) |

---

## Decision Cross-Reference

| Decision | Closes | Key outcome |
| -------- | ------ | ----------- |
| CD-009 | CONF-DEP-01 | Dependent ∈ Employee; Request uses snapshots/references |
| CD-010 | OQ-03 | Request owns approval state; Workflow owns transition rules |
| CD-011 | OQ-04 | Lottery owns all lottery rules and lifecycle |
| CD-012 | OQ-01 | Employee ↔ Identity via immutable UUID, no FK |
| CD-013 | OQ-02 | Employee computes eligibility; Request enforces |
| CD-014 | OQ-05 | Allocation and Occupancy are separate concerns across contexts |
| CD-015 | OQ-06 | CheckIn/CheckOut promoted to active operational boundary |
| CD-016 | OQ-07 | Voucher owns eligibility and issuance lifecycle |
| CD-017 | OQ-08 | Reporting remains read-only projection consumer |


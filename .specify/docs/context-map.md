# DormSys Context Map

**Version:** 0.3  
**Status:** Inventory Frozen / Critical Boundaries Closed (OQ-01 through OQ-05)  
**Last Updated:** 1405/04/05 | 2026/06/26  
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
| Voucher      | spec08 | Voucher |
| Notification | spec09 | NotificationLog (cross-cutting delivery) |
| Audit        | spec10 | AuditLog (cross-cutting traceability) |
| Reporting    | spec11 | none (read-only projections across contexts) |
| Workflow     | —      | **DEFERRED** capability — **CD-010**: when activated, owns approval transition rules and orchestration (not an active module yet) |

### Candidate Contexts (Not in Active Inventory)

| Candidate              | Status | Notes |
| ---------------------- | ------ | ----- |
| CheckIn / CheckOut     | Open   | Operational occupancy transitions — see **OQ-06**; related to **CD-014** split but not yet promoted to inventory |
| Occupancy (unified)    | Rejected as standalone | **CD-014**: occupancy is cross-cutting across Allocation, Dormitory, and CheckIn/CheckOut |

---

## Relationship Map (Summary)

```
Identity
   ↓ immutable UUID (CD-012)
Employee
   ↓ eligibility query (CD-013)
Request ←—— orchestration when active (CD-010) —— Workflow [deferred]
   ↓                              ↓
Lottery (CD-011)              Allocation (CD-014)
   ↓                              ↓
Allocation                    Dormitory (physical state)
   ↓                              ↓
Voucher (OQ-07 open)          CheckIn/CheckOut (OQ-06 open)

Notification ← events from multiple contexts
Audit        ← hooks/events from critical operations
Reporting    ← read-only projections from all contexts
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

### R7 — Allocation → Dormitory

| Field | Value |
| ----- | ----- |
| Direction | Upstream (assignment drives physical occupancy updates) |
| Integration | Application Service or Domain Event — direct repository access forbidden |
| Ownership split | **DECIDED (CD-014):** Allocation owns assignment; Dormitory owns physical room/bed state |
| Implementation | Deferred to `spec04` (Dormitory) and `spec07` (Allocation) |
| Open Question | **OQ-05 — CLOSED (CD-014)**; **OQ-06 remains OPEN** (CheckIn/CheckOut module boundary) |

### R8 — Lottery → Voucher

| Field | Value |
| ----- | ----- |
| Direction | Upstream (lottery outcome may trigger external accommodation) |
| Integration | Application Service or Domain Event — direct repository access forbidden |
| Implementation | Deferred to `spec06` (Lottery) and `spec08` (Voucher) |
| Open Question | **OQ-07 — OPEN** (voucher eligibility ownership) |

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
| Open Question | **OQ-08 — OPEN** (projection boundary / read-model scope) |

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
| OQ-06 | Is check-in / check-out / occupancy inside Allocation, or a separate context? | **OPEN** |
| OQ-07 | Where does voucher eligibility ownership live? | **OPEN** |
| OQ-08 | What is the reporting projection boundary / read-model scope? | **OPEN** |

---

## Catalog vs Source-Document Discrepancies

| ID | Discrepancy | Resolution |
| -- | ----------- | ---------- |
| D1 | Workflow is an independent module in source docs but a deferred capability in the spec catalog | Recorded in catalog-decisions; **CD-010** defines split ownership when Workflow is activated |
| D2 | CheckIn appears as an independent module in source docs; catalog groups it under Allocation & Occupancy spec | **OQ-06 OPEN** — **CD-014** split assignment from occupancy but does not resolve CheckIn module promotion |
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

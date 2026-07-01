# Architecture Specification: Allocation & Occupancy (spec07)

**Feature Branch**: `007-allocation-checkin`

**Created**: 2026-07-01

**Status**: Architecture specification — post-freeze

**Catalog**: spec07 — Allocation & Occupancy (`spec-catalog.md`)

**Depends on**: spec01 Foundation; spec04 Accommodation Resource; spec05 Request Management; spec06 Lottery Selection

**Architecture Freeze**: APPROVED — [`architecture-freeze-spec07.md`](../../.specify/governance/freeze/architecture-freeze-spec07.md)

**Normative boundaries**: [`catalog-decisions.md`](../../.specify/docs/catalog-decisions.md) **CD-014**, **CD-015**; [`context-map.md`](../../.specify/docs/context-map.md) **R5**, **R6**, **R7**

**Governance sources**: `program-alignment-spec07-spec11.md`, `contract-stub-pack-spec07-spec11.md`, `allocation-dormitory-integration-contract.md`

---

## Purpose

Define the frozen architecture for the spec07 delivery program: room/bed assignment under Allocation and operational stay transitions under CheckIn/CheckOut. spec07 owns assignment authority and operational occupancy transitions only. Physical bed state remains owned by Dormitory (spec04). This document records locked boundaries and integration surfaces — not implementation design.

---

## Scope

| In scope | Out of scope (see § Out of Scope) |
| -------- | ----------------------------------- |
| **Allocation** bounded context — assignment authority | Physical bed operability and occupancy markers |
| **CheckIn/CheckOut** bounded context — operational transitions | Voucher, Reporting, Notification, Audit ownership |
| Contract and event references at architecture level | plan.md detail, tasks.md, schema, API, UI |
| Upstream/downstream integration boundaries (R5, R6, R7) | Workflow engine, business-rule detail |
| Coordination with Dormitory via frozen integration contracts | spec04 implementation and deployment |

**Program note:** spec07 is a single delivery program spanning Allocation and CheckIn/CheckOut; Dormitory (spec04) is coordinated upstream, not owned by spec07.

---

## Governing Decisions

### CD-014 — Allocation ↔ Occupancy Ownership Split

| Outcome | spec07 implication |
| ------- | ------------------ |
| Allocation owns assignment authority (`Allocation`, `AllocationItem`, `AllocationMethod`) | Assignment decisions live in Allocation module only |
| Dormitory owns physical room/bed capacity and occupancy markers | spec07 consumes `DormitoryReadContract`; emits signals via `AllocationPhysicalStatePort` / integration events |
| CheckIn/CheckOut owns operational transitions | Separate active boundary within spec07 program (see CD-015) |
| Allocation is upstream driver for physical-marker signals | Unidirectional R7: Allocation → Dormitory |
| Effective occupancy is cross-cutting | Not stored authoritatively in one place |

### CD-015 — CheckIn/CheckOut Module Boundary

| Outcome | spec07 implication |
| ------- | ------------------ |
| CheckIn/CheckOut promoted to active bounded context | Not folded into Allocation or Dormitory |
| Owns `CheckedIn`, `CheckedOut` operational transitions | `CheckInCommandPort` inbound boundary |
| Consumes assignment facts from Allocation | Does not own assignment authority |
| Operator role for internal dormitory check-in/out | Architecture constraint; detail deferred to implementation phase |

---

## Bounded Context

| Context | Spec | Aggregates / artifacts | Authority |
| ------- | ---- | ---------------------- | --------- |
| **Allocation** | spec07 | `Allocation`, `AllocationItem`, `AllocationMethod` | CD-014 |
| **CheckIn/CheckOut** | spec07 | Operational occupancy transitions (`CheckedIn`, `CheckedOut`) | CD-015 |
| **Dormitory** | spec04 | Physical capacity — coordinated only, not owned | CD-014 |

**Context-map relationships:** R5 (Lottery → Allocation), R6 (Request → Allocation), R7 (Allocation → Dormitory → CheckIn/CheckOut).

---

## Responsibilities

### Allocation (CD-014)

- Assignment authority — who is assigned to what
- Consume lottery results and approved requests as read-only upstream input
- Emit assignment integration signals to Dormitory (physical-marker projection)
- Emit post-approval request lifecycle commands via `RequestLifecycleCommandPort` (OA-05-03 handoff)
- Supply read surface via `AllocationReadContract`
- May supply trigger facts to Voucher via `VoucherIssuancePort` — does not own voucher policy

### CheckIn/CheckOut (CD-015)

- Operational `CheckedIn` / `CheckedOut` transitions
- Consume assignment facts from Allocation; do not decide assignments
- Operator-only execution for internal dormitories (architecture source)
- Inbound commands via `CheckInCommandPort`

### Explicit non-responsibilities (spec07)

- Physical bed operability or occupancy-marker persistence (Dormitory)
- Request approval state ownership (Request)
- Lottery rules and draw lifecycle (Lottery)
- Voucher eligibility and issuance (Voucher)
- Cross-domain reporting projections (Reporting)

---

## Upstream Dependencies

| Upstream | Relationship | Integration |
| -------- | ------------ | ----------- |
| **spec01 — Foundation** | Platform scaffold | Module structure, shared kernel |
| **spec04 — Dormitory** | Physical capacity supplier | R7 — `DormitoryReadContract` (consumer); `AllocationPhysicalStatePort` / `AllocationAssigned` / `AllocationReleased` (producer) |
| **spec05 — Request** | Approved requests | R6 — `RequestReadContract`; `RequestLifecycleCommandPort` (OA-05-03) |
| **spec06 — Lottery** | Draw results | R5 — `LotteryResultReadContract`, `ProposedAllocationPort` |

**Execution note (non-blocking for architecture):** spec04 is a runtime sequencing dependency; architectural validity does not require spec04 deployment per Architecture Freeze record.

---

## Downstream Consumers

| Downstream | Relationship | Integration |
| ---------- | ------------ | ----------- |
| **spec03 — Employee** | Read consumer | `AllocationReadContract` (replaces `ActiveAllocationReadPort` stub) |
| **spec08 — Voucher** | Upstream trigger | R8 — `VoucherIssuancePort` (trigger facts only; CD-016) |
| **spec09 — Notification** | Event consumer | R9 — domain events from Allocation / CheckIn/CheckOut |
| **spec10 — Audit** | Traceability consumer | R10 — `AuditService` facade |
| **spec11 — Reporting** | Read-only consumer | R11 — `AllocationReadContract`; CD-017 |

---

## Referenced Contracts

### Owned or produced by spec07 (Contract Stub Pack)

| Contract | Role |
| -------- | ---- |
| `AllocationReadContract` | Owner / producer — read-only assignment queries |
| `CheckInCommandPort` | Owner (CheckIn/CheckOut) — inbound operational commands |
| `RequestLifecycleCommandPort` | Producer — post-approval request transitions (OA-05-03) |
| `VoucherIssuancePort` | Producer — trigger facts only |
| `AuditService` | Producer — critical operation traceability |

### Consumed by spec07 (upstream / integration)

| Contract | Source | Role |
| -------- | ------ | ---- |
| `RequestReadContract` | spec05 | Approved request reads |
| `LotteryResultReadContract` | spec06 | Draw outcome reads |
| `ProposedAllocationPort` | spec06 | Proposed winner payloads |
| `DormitoryReadContract` | spec04 | Capacity and bed status pre-check |
| `AllocationPhysicalStatePort` | spec04 (inbound) | Physical-marker signals from Allocation |

**Integration contract:** [`allocation-dormitory-integration-contract.md`](../../.specify/governance/contracts/allocation-dormitory-integration-contract.md) (ADIC-2026-07-01-001).

---

## Referenced Events

### Emitted by spec07 (Domain Event Catalog v1)

- `AllocationCreated`
- `AllocationReleased`
- `CheckedIn`
- `CheckedOut`

### Integration events (Allocation → Dormitory)

- `AllocationAssigned`
- `AllocationReleased`

### Consumed or coordinated at spec07 boundary

- `RequestApproved`
- `RequestWaitingForAllocation`
- `RequestAllocated`
- `RequestAllocationFailed`
- `LotteryDrawCompleted`
- `ProposedAllocationEmitted`
- `BedOccupancyMarkerChanged`

*Event names only — payloads not defined in this architecture specification.*

---

## Open Dependencies (UD-01..UD-11)

| ID | Topic |
| -- | ----- |
| UD-01 | Event contract between Allocation, Dormitory, CheckIn/CheckOut |
| UD-02 | Reconciliation when Allocation and Dormitory state diverge |
| UD-03 | Exact Voucher input contract from Lottery/Allocation |
| UD-04 | Reporting model structure and refresh mechanism |
| UD-05 | Notification policy layer vs pure delivery |
| UD-06 | Domain vs technical audit semantics per module |
| UD-07 | spec04 not implemented — `DormitoryReadContract` + `AllocationPhysicalStatePort` not live |
| UD-08 | Contract stubs C-07..C-13 not fully migrated to spec-level contract artifacts |
| UD-09 | No spec07 `spec.md` skeleton |
| UD-10 | `RequestLifecycleCommandPort` payload undefined |
| UD-11 | Employee `ActiveAllocationReadPort` still null |

---

## Out of Scope

| Item | Owner / phase |
| ---- | ------------- |
| Dormitory physical domain model and bed operability management | spec04 |
| Voucher eligibility and issuance lifecycle | spec08 (CD-016) |
| Reporting projections and read-model design | spec11 (CD-017) |
| Notification delivery mechanism | spec09 |
| Central audit persistence design | spec10 |
| Lottery rules and lifecycle | spec06 |
| Request approval state and history | spec05 |
| Workflow engine orchestration | Deferred capability (CD-010) |
| tasks.md, database schema, API design, UI | Implementation phase |
| Business rules, validation rules, state machine detail | Implementation phase |
| Event payload serialization | Open execution item (Architecture Freeze §3) |
| External dormitory check-in/out and physical bed tracking | Voucher / external path (architecture source) |
| Reconciliation engine | UD-02 |

---

**End of architecture specification.**

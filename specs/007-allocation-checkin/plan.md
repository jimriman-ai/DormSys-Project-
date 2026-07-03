# Implementation Plan: Allocation & Occupancy (spec07)

**Branch**: `007-allocation-checkin` | **Date**: 2026-07-01 | **Spec**: [spec.md](./spec.md)

**Input**: spec07 delivery program — **Allocation** (assignment authority, CD-014) and **CheckIn/CheckOut** (operational transitions, CD-015); upstream coordination with Dormitory (spec04), Request (spec05), Lottery (spec06).

**Governance**: Architecture **FROZEN** — [`architecture-freeze-spec07.md`](../../.specify/governance/freeze/architecture-freeze-spec07.md). Catalog status: **Planned** — implementation **not** authorized ([`spec-catalog.md`](../../.specify/docs/spec-catalog.md)).

---

## Pre-Plan Validation

| Source | Check | Result |
| ------ | ----- | ------ |
| [spec.md](./spec.md) | Architecture sections; CD-014, CD-015; contracts and events | ✅ Frozen architecture spec present |
| [spec-catalog.md](../../.specify/docs/spec-catalog.md) | spec07 Planned; depends spec01, spec04, spec05, spec06 | ✅ Aligned |
| [context-map.md](../../.specify/docs/context-map.md) | Allocation, CheckIn/CheckOut rows; R5, R6, R7 | ✅ Aligned |
| [CD-014 / CD-015](../../.specify/docs/catalog-decisions.md) | Ownership split and CheckIn boundary | ✅ Locked — plan must not alter |
| [ADIC-2026-07-01-001](../../.specify/governance/contracts/allocation-dormitory-integration-contract.md) | Allocation → Dormitory integration semantics | ✅ Referenced |
| Module scaffold | `app/Modules/Allocation/` (spec01) | ✅ Four-layer paths exist; no business code |
| CheckIn/CheckOut module | Active boundary per CD-015 | ⬜ Module path TBD at design approval (within spec07 program) |

---

## Summary

Deliver the **spec07 program** as two coordinated bounded contexts under frozen architecture:

- **Allocation** — `Allocation`, `AllocationItem`, `AllocationMethod`; assignment authority; overlap prevention; upstream consumer of Lottery and Request; downstream supplier via `AllocationReadContract`; Dormitory signal producer.
- **CheckIn/CheckOut** — operational `CheckedIn` / `CheckedOut` transitions; consumes assignment facts; Operator role for internal dormitories.

**Planning boundary:** This plan records implementation sequencing and dependency gates only. Detailed user stories, data model, and contract artifacts are deferred until Design Approval. Architecture decisions are **not** revisable here.

**Excluded until authorized:** Application code, migrations, Livewire UI, Workflow, cross-module Eloquent.

---

## Technical Context

| Dimension | Value |
| --------- | ------- |
| **Language/Version** | PHP 8.4; Laravel 13 |
| **Primary Dependencies** | spec01 kernel; spec05/06 supplier contracts; spec04 Dormitory ports (when live) |
| **UUID strategy** | UUID v7 via `HasUuid` on module-owned tables (planned) |
| **Storage** | PostgreSQL 17 — migrations under `database/migrations/modules/allocation/` (+ check-in module path TBD) |
| **Testing** | Pest PHP 4; unit (Domain/Application), feature (actions + contracts), architecture (boundary) |
| **Constraints** | No cross-module Eloquent; no cross-module FKs; CD-014/CD-015 boundaries immutable |
| **Architecture status** | Frozen — no boundary changes in this plan |

---

## Constitution Check

| Principle | Compliance | Notes |
| --------- | ------------ | ----- |
| AP-02 Modular Monolith | ✅ PASS | Allocation module exists; CheckIn/CheckOut path at design approval |
| AP-03 Clean Architecture | ✅ PASS | Domain pure PHP; Eloquent in Infrastructure |
| AP-04 Module boundaries | ✅ PASS | Application Service / Domain Event integration only |
| AP-05 State Machines | ⬜ TBD | At feature spec authoring — not decided in architecture freeze |
| AP-06 Audit | ⚠️ CONDITIONAL | `AuditService` facade target; `RecordsActivity` interim |
| CD-014 | ✅ PASS | Assignment in Allocation; physical markers in Dormitory |
| CD-015 | ✅ PASS | CheckIn/CheckOut separate active boundary |

---

## Dependency Analysis

| Dependency | Required for | Status |
| ---------- | ------------ | ------ |
| spec01 module scaffold | Paths, providers | ✅ `app/Modules/Allocation/` exists |
| spec05 `RequestReadContract` | Approved request reads | ✅ Implemented |
| spec06 `LotteryResultReadContract`, `ProposedAllocationPort` | Lottery → Allocation handoff | ✅ Stub live |
| spec04 `DormitoryReadContract` | Capacity pre-check | ⚠️ Planned — not implemented (UD-07) |
| spec04 `AllocationPhysicalStatePort` | Physical-marker signals | ⚠️ Planned — contract doc exists (spec04) |
| spec03 `ActiveAllocationReadPort` | Eligibility | ⚠️ Null stub — replaced by `AllocationReadContract` at spec07 (UD-11) |

**Runtime sequencing:** spec04 implementation precedes spec07 end-to-end integration testing; not an architecture blocker per freeze record.

**Suggested implementation order (when authorized):** Setup → Foundational → Allocation core → Dormitory integration adapters → CheckIn/CheckOut → Supplier contracts → Request lifecycle handoff → Polish.

---

## Phase Design (planning placeholders)

| Phase | Deliverable | Status |
| ----- | ----------- | ------ |
| **0 — Design artifacts** | `data-model.md`, `contracts/` under this spec directory | Pending Design Approval |
| **1 — Setup** | Migration paths, module DI, README | Not started |
| **2 — Foundational** | Domain VOs, enums, base aggregates | Not started |
| **3 — Allocation core** | Assignment authority, overlap rules | Not started |
| **4 — Upstream adapters** | Request + Lottery read ports | Not started |
| **5 — Dormitory integration** | `DormitoryReadContract` consumer; `AllocationPhysicalStatePort` / event producer | Blocked on spec04 (UD-07) |
| **6 — CheckIn/CheckOut** | Operational transitions, `CheckInCommandPort` | Not started |
| **7 — Downstream suppliers** | `AllocationReadContract`, `RequestLifecycleCommandPort`, audit hooks | Not started |
| **8 — Polish** | Architecture tests, boundary verification | Not started |

---

## Contracts (to migrate from governance stub pack)

| Contract | Direction | Notes |
| -------- | --------- | ----- |
| `AllocationReadContract` | Outbound | spec07 supplier |
| `CheckInCommandPort` | Inbound | CheckIn/CheckOut |
| `RequestLifecycleCommandPort` | Outbound to Request | OA-05-03 handoff (UD-10) |
| `RequestReadContract` | Inbound | spec05 existing |
| `LotteryResultReadContract` | Inbound | spec06 existing |
| `ProposedAllocationPort` | Inbound | spec06 existing |
| `DormitoryReadContract` | Inbound | spec04 planned |
| `AllocationPhysicalStatePort` | Outbound to spec04 | ADIC + spec04 contract |

Stub source: [`contract-stub-pack-spec07-spec11.md`](../../.specify/governance/program-alignment/contract-stub-pack-spec07-spec11.md).

---

## Open Dependencies (carried from spec.md)

UD-01 through UD-11 remain open; see [spec.md § Open Dependencies](./spec.md). Planning must not resolve these without explicit governance review.

---

## Authorization Gate

| Artifact | Status |
| -------- | ------ |
| Architecture Freeze | ✅ APPROVED |
| Design Approval handoff | ⬜ Not created |
| Implementation Authorization | ⬜ Not granted |
| tasks.md | ⬜ Not created (out of scope for this step) |

---

**End of plan.**

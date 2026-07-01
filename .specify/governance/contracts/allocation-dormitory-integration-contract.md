# Allocation ↔ Dormitory Integration Contract

**Contract ID:** ADIC-2026-07-01-001  
**Version:** 1.0.0  
**Status:** STUB — REVIEW ONLY  
**Governance:** CD-014, context-map R7  
**Related specs:** spec04 (Dormitory), spec07 (Allocation program)  
**Related artifacts:** `specs/004-accommodation-resource/contracts/allocation-physical-state-port.md`, `contract-stub-pack-spec07-spec11.md`, `.specify/specs/spec07/spec.md`

**Purpose of this document:** Minimal integration semantics for Allocation → Dormitory physical-state synchronization. Resolves PAR PB-05 at contract-readiness level without domain redesign.

---

## 1. Contract overview

| Field | Value |
| ----- | ----- |
| **Purpose** | Allocation → Dormitory physical occupancy-marker synchronization |
| **Direction** | Allocation (assignment authority) → Dormitory (physical state projector) |
| **Integration pattern** | Application port and/or domain events — no cross-module Eloquent (context-map constitution constraints) |
| **Allocation role** | Source of truth for **assignment decisions**; emits integration signals only |
| **Dormitory role** | Owner of **physical bed state**; applies occupancy-marker projections; does not decide assignments |
| **Primary port (spec04)** | `AllocationPhysicalStatePort` — inbound to Dormitory |
| **Primary read surface (spec04)** | `DormitoryReadContract` — outbound from Dormitory for capacity/pre-check |

**CD-014 invariant:** Dormitory never stores authoritative person-to-bed assignment. Trace reference only (`signalReferenceId` / `allocationId`).

---

## 2. Event types (minimal)

Integration event names for cross-boundary signaling. **Identifiers only** — no payload schema in this contract.

| Event name | Producer | Consumer | Intent |
| ---------- | -------- | -------- | ------ |
| `AllocationAssigned` | Allocation (spec07) | Dormitory (spec04) | Assignment established; Dormitory applies occupancy marker toward reserved/occupied per transition rules |
| `AllocationReleased` | Allocation (spec07) | Dormitory (spec04) | Assignment ended; Dormitory returns bed occupancy marker to vacant capacity |

**Port mapping (spec04, informational — not redesigned):**

| Event | Typical port operation |
| ----- | ---------------------- |
| `AllocationAssigned` | `reserveBed` (Vacant → Reserved); optional follow-up `occupyBed` (Reserved → Occupied) when assignment policy requires occupied marker |
| `AllocationReleased` | `releaseBed` (Reserved \| Occupied → Vacant) |

Events and port calls MUST remain assignment-signal only — no person/employee parameters on Dormitory inbound surface.

---

## 3. State transition rules (Dormitory side)

Terminology aligned with spec04 (`operability_status`, `occupancy_marker`). No domain model change.

### BedState (`operability_status`)

| Value | Meaning |
| ----- | ------- |
| `InService` | Bed physically operable for assignment signals |
| `OutOfService` | Bed not operable; assignment signals rejected |
| `Maintenance` | Bed not operable; assignment signals rejected |

**Rules:**

- `BedState` changes **only** via Dormitory internal actions (operability management).
- Allocation **never** directly modifies `BedState`.
- `reserveBed` requires `InService` (spec04 INV-2 / transition table).

### OccupancyState (`occupancy_marker`)

| Value | Meaning (spec04 term) |
| ----- | --------------------- |
| `Vacant` | No active assignment signal; bed capacity available for new reserve |
| `Reserved` | Assignment signal applied; bed held pending occupy or release |
| `Occupied` | Assignment signal applied; bed marked occupied |

*Note:* `Vacant` is the spec04 occupancy term for unassigned physical capacity (equivalent to “free” in integration language).

**Rules:**

- `OccupancyState` changes **only** via Allocation integration events / `AllocationPhysicalStatePort` methods.
- Dormitory does not self-transition occupancy markers without inbound Allocation signal (except documented internal test stubs).
- Allowed transitions (normative, from spec04 port):

| From | `AllocationAssigned` (reserve / occupy path) | `AllocationReleased` |
| ---- | -------------------------------------------- | -------------------- |
| `Vacant` | → `Reserved` (if `InService`) | reject |
| `Reserved` | → `Occupied` (occupy path) or idempotent reserve | → `Vacant` |
| `Occupied` | reject (new assign) | → `Vacant` |

- Allocation **never** directly modifies `BedState`.
- Dormitory **never** decides who is assigned — marker updates only.

---

## 4. Idempotency rules

| Rule | Requirement |
| ---- | ----------- |
| **Idempotency key** | `(allocationId, eventType)` — `allocationId` carried as `signalReferenceId` on port |
| **Duplicate `AllocationAssigned`** | MUST NOT change final occupancy state if same key already applied |
| **Duplicate `AllocationReleased`** | MUST NOT error if bed already `Vacant` for same key; final state remains `Vacant` |
| **Ordering tolerance** | Late or duplicate events MUST NOT corrupt marker state; reject illegal transitions per §3 matrix |
| **Out-of-order release** | `AllocationReleased` after already vacant — no-op for same `allocationId` |

---

## 5. Consistency model

| Aspect | Model |
| ------ | ----- |
| **Projection** | Eventually consistent physical-state projection on Dormitory beds |
| **Authority** | Allocation authoritative for assignment logic; Dormitory authoritative for physical operability and applied occupancy markers |
| **Decision boundary** | Dormitory is a **physical state projector**, not an assignment decision maker |
| **Read path** | Allocation pre-checks capacity via `DormitoryReadContract` before emitting assign signals |
| **Write path** | Allocation emits `AllocationAssigned` / `AllocationReleased` (or port equivalents); Dormitory applies markers |
| **Divergence** | Reconciliation between Allocation and Dormitory is **out of scope** (UD-02); not defined here |

---

## 6. Explicit non-goals

- No reconciliation engine design (UD-02)
- No UI or operator presentation behavior
- No CheckIn/CheckOut logic (CD-015 — separate boundary)
- No Voucher or external accommodation logic (CD-016)
- No Reporting or read-model design (CD-017)
- No database schema or migration design
- No full domain event payload schemas (UD-01)
- No change to CD-014 or CD-015 ownership meaning
- No implementation code in this artifact

---

## 7. PAR / PB-05 readiness note

| Item | Status after this contract |
| ---- | -------------------------- |
| **PB-05 (integration contract semantics)** | Addressed — Allocation → Dormitory direction, events, state rules, and idempotency defined for review |
| **PB-05 (live spec04 implementation)** | Remains execution prerequisite — Planning Authorized; port implementation still required before spec07 runtime |
| **spec04 domain structure** | Unchanged — references existing `AllocationPhysicalStatePort` and enums |
| **spec07 program logic** | Unchanged — integration surface only |

---

**End of contract.**

# Research: Accommodation Resource (spec04)

**Date**: 2026-06-23 | **Plan**: [plan.md](./plan.md)

Consolidates OA-04-xx decisions from [spec.md](./spec.md) and resolves planning unknowns before `data-model.md` and `contracts/`.

---

## R-01 — UUID primary key version

**Decision:** UUID **v7** via spec01 `HasUuid` (`Ramsey\Uuid\Uuid::uuid7()`).

**Rationale:** Kernel standard; consistent with Identity and Employee modules.

**Alternatives considered:** UUID v4 — rejected.

---

## R-02 — Building / floor hierarchy (OA-04-01)

**Decision:** **Dormitory → Building → Room → Bed** for internal sites. **Floor** is `Room.floor_label` (nullable string or numeric label), not a separate aggregate.

**Rationale:** Floor has no independent lifecycle, permissions, or workflow; separate Floor aggregate would be decorative (spec.md OA-04-01).

**Alternatives considered:** Floor aggregate — rejected for Wave 1; deferred floor introduction only — rejected (insufficient for operations).

---

## R-03 — External dormitory inventory (OA-04-03 / BR-12)

**Decision:** External sites are rows in `dormitory_sites` with `type = external`. **No** Building, Room, or Bed children permitted.

**Rationale:** BR-12; fake capacity inventory breaks voucher vs internal allocation boundaries.

**Alternatives considered:** Empty building tree for external — rejected.

---

## R-04 — Room / bed ownership (OA-04-02 / CD-014)

**Decision:** All physical entities and `dormitory_*` tables owned exclusively by Dormitory module. **No** `employee_id`, `person_id`, or authoritative `allocation_id` on `Bed`.

**Rationale:** CD-014; prevents reconciliation nightmare and boundary erosion.

**Alternatives considered:** Bed stores assignee FK — rejected.

---

## R-05 — Assignable bed definition (formal)

**Decision:** **AssignableBed** is a **derived query concept**, not a persisted column. No `available = true` flag.

```text
AssignableBed :=
    operability_status == InService
    AND
    occupancy_marker == Vacant
```

**Rationale:** Aligns plan Phase D with spec OA-04-04; single source of truth from two orthogonal dimensions (physical operability vs occupancy marker). system-flow INV-2 satisfied via `InService` + consumer checks `Vacant` before assignment proposal.

**Alternatives considered:** Denormalized `is_available` boolean — rejected (drift risk vs operability + occupancy).

**Used by:** `DormitoryReadContract::getAssignableCapacity`, Allocation pre-check (spec07).

---

## R-06 — Last signal reference (traceability)

**Decision:** Optional column `last_signal_reference_id` (UUID, **no FK**) on `dormitory_beds` — updated whenever occupancy marker changes from **any** inbound signal source.

**Rationale:** Signal-neutral name; avoids implying Dormitory owns Allocation authority; supports future maintenance/reconciliation signals without schema rename.

**Alternatives considered:** `last_allocation_reference_id` — rejected (couples name to Allocation); `last_external_signal_id` — rejected (Allocation is in-system, not external).

**Not stored:** Person, employee, or allocation aggregate authority — trace UUID only.

---

## R-07 — Bed operability transitions

**Decision:** Enum `BedOperabilityStatus`: `InService`, `OutOfService`, `Maintenance`.

```text
                    ┌── Maintenance ──┐
                    │                 │
[InService] ◄───────┴─────────────────┘
    │
    └──► [OutOfService]
              │
              └──► [InService]   (staff restore)
```

**Policy (Wave 1):** Transition to `OutOfService` or `Maintenance` **blocked** when `occupancy_marker` is `Reserved` or `Occupied` unless explicit force policy added later (default: block).

**Rationale:** Prevents hiding occupied beds from operability changes without Allocation release (reconciliation deferred spec07).

---

## R-08 — Occupancy marker transitions

**Decision:** Enum `BedOccupancyMarker`: `Vacant`, `Reserved`, `Occupied`.

```text
[Vacant]
    │
    └── reserve signal ──► [Reserved]
                              │
                              └── occupy signal ──► [Occupied]
                                                        │
                                                        └── release signal ──► [Vacant]
```

**Authority:** Marker changes applied only via `AllocationPhysicalStatePort` (or test stub) — Dormitory does not self-assign `Occupied` without signal.

**CheckIn/CheckOut:** Does **not** transition markers in spec04 — **OQ-06 deferred** (spec07).

### Occupancy transition matrix (normative)

| Current state | Port method | Next state | Guard |
| ------------- | ----------- | ---------- | ----- |
| `Vacant` | `reserveBed` | `Reserved` | `operability_status == InService` |
| `Vacant` | `occupyBed` | — | **Rejected** (`InvalidOccupancyTransitionException`) |
| `Vacant` | `releaseBed` | — | **Rejected** (idempotent no-op optional — implement as reject in Wave 1) |
| `Reserved` | `reserveBed` | `Reserved` | Idempotent when same `signalReferenceId`; else reject |
| `Reserved` | `occupyBed` | `Occupied` | — |
| `Reserved` | `releaseBed` | `Vacant` | — |
| `Occupied` | `reserveBed` | — | **Rejected** |
| `Occupied` | `occupyBed` | — | **Rejected** (idempotent same signal optional) |
| `Occupied` | `releaseBed` | `Vacant` | — |

**Side effect:** Each successful transition sets `last_signal_reference_id` to the provided UUID (no FK).

**Not in matrix:** Direct `Vacant → Occupied` (must pass through `Reserved` unless spec07 later adds express occupy — out of scope Wave 1).

Canonical reference: [contracts/allocation-physical-state-port.md](./contracts/allocation-physical-state-port.md).

---

## R-09 — Room kind (OA-04-05)

**Decision:** Enum `RoomKind`: `Private`, `Shared`.

**Rationale:** Dormitory records kind; BR-03 enforcement remains Allocation responsibility.

---

## R-10 — Intra-module FK policy

**Decision:** FK constraints **allowed** within Dormitory tables only (`building → site`, `room → building`, `bed → room`). Denormalized `dormitory_site_id` on `dormitory_beds` for dormitory-scoped `bed_code` uniqueness and capacity queries.

**Rationale:** Same pattern as Employee module; no cross-module FK.

---

## R-11 — Domain event versioning (ADR-015 check)

**Decision:** Wave 1 Dormitory events use **spec01 `BaseEvent` + module-level `EVENT_NAME` / `VERSION` constants** (mirror `EmployeeCreated` in spec03). **No** separate event registry, outbox, or `Events\v1` namespace infrastructure in kernel today.

**Evidence:**

| Source | Finding |
| ------ | ------- |
| `app/Support/Events/BaseEvent.php` | `eventId`, `occurredAt`, `aggregateId`, `payload` — implemented |
| `EmployeeCreated` | `EVENT_NAME`, `VERSION`, `toContractPayload()` — module pattern |
| `system-flow.md` §4 ADR-015 | Describes **target** namespaced versioning (`Domain\Events\v1`) — **not** implemented as enforced kernel schema |
| spec01 tasks | No dedicated event versioning infrastructure task |

**Implication for contracts/:** Document **Application contracts only** (`DormitoryReadContract`, `AllocationPhysicalStatePort`). Domain event shapes belong in future `events.md` (spec04), not in cross-module contract files. Do **not** publish versioned public event schemas until spec07 integration requires them.

**Alternatives considered:** ADR-015 namespace folders now — deferred (YAGNI until second consumer needs breaking change).

---

## R-12 — Allocation integration (OA-04-06)

**Decision:** Inbound integration via **`AllocationPhysicalStatePort`** (application port). Outbound integration via **`DormitoryReadContract`**. Wave 1 uses `NullAllocationPhysicalStateAdapter` in tests; real Allocation adapter in spec07.

**Rationale:** R7 context-map; forbidden cross-module Eloquent.

---

## R-13 — Site lifecycle status

**Decision:** `DormitorySiteStatus`: `Active`, `Inactive`. Inactive sites excluded from assignable capacity queries; existing structure remains readable.

**Rationale:** Mirror `DepartmentStatus` / `EmployeeStatus` pattern; supports decommission without delete.

---

## Deferred (documentation only)

| Topic | Status |
| ----- | ------ |
| OQ-06 CheckIn/CheckOut module | OPEN — spec07 |
| Allocation ↔ Dormitory reconciliation | OPEN — spec07 |
| Force operability change on occupied bed | OPEN — default block Wave 1 |

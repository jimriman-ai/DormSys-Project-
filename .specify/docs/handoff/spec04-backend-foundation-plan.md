# Spec04 Backend Foundation Plan: Accommodation Resource / Dormitory

## 1. Planning Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `BACKEND_FOUNDATION_PLAN_CREATED` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Current gate** | Backend Foundation Planning |
| **Previous gate** | `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN` |
| **Implementation status** | `NOT_AUTHORIZED` |
| **Next allowed gate** | Spec04 Domain Design Approval |
| **Decision date** | 2026-07-10 |

**Prior catalog state (unchanged):** Spec04 remains **Planning Authorized**. Spec04 implementation remains on hold until a separate Spec04 implementation authorization artifact is issued.

This artifact is a **planning/design artifact only**.  
It does **not** authorize implementation.

**Activation baseline:** [`.specify/docs/handoff/spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) — `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN`.

---

## 2. Purpose

UI governance discovered missing backend readiness: Workflow has no UI-consumable Application/Domain capability, and Workflow UI must not proceed to feature-contract on that basis.

Dormitory (Spec04) is the foundational backend capability required before:

- Allocation physical-state integration (CD-014)
- CheckIn/CheckOut physical occupancy updates (CD-015)
- Voucher / external accommodation catalog semantics
- Later Dormitory UI and dependent UI governance

This plan defines the technical boundaries that must be approved before implementation authorization. It prepares the **domain**, **persistence**, **application**, **integration**, **authorization**, and **testing** design for Spec04 Accommodation Resource / Dormitory.

**Governance correction:** Spec04 owns accommodation physical resource structures (dormitories, buildings, rooms, beds, capacity, availability). Implementation was previously on hold until separate authorization. The current gate only authorizes backend foundation planning/design.

---

## 3. Backend Foundation Scope

All items in this section are **design/planning targets only**.

| Design target | Intent |
| ------------- | ------ |
| Dormitory / accommodation resource hierarchy | Authoritative physical catalog structure |
| Building / floor / room / bed structure | Hierarchy levels and ownership |
| Capacity model | How capacity is counted and exposed |
| Availability model | Assignable / available physical capacity |
| Physical room state | Room-level operability and occupancy projection |
| Physical bed state | Bed-level operability and occupancy markers |
| Occupancy state projection / ownership | Dormitory-owned physical markers per CD-014 |
| Persistence schema planning | Tables, FKs, constraints — design only |
| Read model planning | Query surfaces for consumers |
| Application query / command contract planning | Contract candidates — not code |
| Event integration boundary planning | Allocation and CheckIn/CheckOut signals |
| Authorization boundary planning | Permission/policy candidates |
| Test strategy planning | Required test categories before closure |

---

## 4. Domain Boundary Decision

### Planned hierarchy

```text
Dormitory
  -> Building
     -> Floor
        -> Room
           -> Bed
```

This is the **planned foundation model** for Spec04 backend design intake.

| Level | Responsibility |
| ----- | -------------- |
| **Dormitory** | Top-level accommodation resource container. Owns buildings and aggregate accommodation capacity. Distinguishes internal (physical inventory) vs external (catalog-only / voucher) sites. |
| **Building** | Physical building inside a dormitory/accommodation site. Groups floors and rooms. |
| **Floor** | Physical floor inside a building. Groups rooms. |
| **Room** | Physical room. Owns room-level capacity, state, and bed collection. |
| **Bed** | Assignable physical sleeping/resource unit. Owns bed-level availability and physical occupancy state. |

### Hierarchy resolution

The catalog open planning item (building/floor hierarchy) is **resolved by this plan** as:

**Dormitory → Building → Floor → Room → Bed.**

If the product later needs a simpler model (for example, floor as a room attribute only, as previously recorded in Spec04 OA-04-01 / research R-02), simplification requires a **separate governance decision** during or after Domain Design Approval. This foundation plan adopts Floor as a first-class level for capacity, availability, and operational clarity.

External dormitories remain catalog-only: no Building / Floor / Room / Bed children (BR-12 / OA-04-03).

---

## 5. Ownership Model

Aligned with **CD-014** and **CD-015**.

### Dormitory owns

- Physical accommodation resource structure
- Room state
- Bed state
- Room capacity
- Bed availability
- Physical occupancy status
- Current physical resource state projection

### Allocation owns

- Assignment of an employee/group/entity to a room or bed
- Assignment lifecycle
- Allocation eligibility / business decision
- Reservation or allocation intent

Allocation may drive physical state updates via events; it does **not** own physical room/bed state.

### CheckIn/CheckOut owns

- Operational transition that confirms physical occupancy start
- Operational transition that confirms physical occupancy end
- Check-in / check-out lifecycle events

CheckIn/CheckOut may trigger physical occupancy transitions; **Dormitory** remains the physical state owner (CD-015).

### Workflow owns

- No Dormitory physical state
- No Allocation assignment state
- No room/bed state
- Future orchestration only, when separately authorized and backed by real workflows

**Workflow UI remains blocked** until backend/application capability exists. This plan does not unblock Workflow UI.

### Request owns

- Request approval state/history where already existing
- No Dormitory physical state

---

## 6. State Model Planning

Final state names may be refined during Domain Design Approval, but ownership boundaries must remain consistent with **CD-014** and **CD-015**.

### Planned room states (examples)

| State | Meaning (planning) |
| ----- | ------------------ |
| `available` | Room has assignable physical capacity |
| `partially_occupied` | Some beds occupied/allocated; capacity remains |
| `occupied` | No remaining assignable capacity under current rules |
| `unavailable` | Administratively unavailable |
| `maintenance` | Under maintenance |
| `inactive` | Out of service / inactive |

### Planned bed states (examples)

| State | Meaning (planning) |
| ----- | ------------------ |
| `available` | Operable and vacant (assignable candidate) |
| `allocated` | Reserved/allocated marker after Allocation signal (pre-occupancy) |
| `occupied` | Physically occupied after CheckIn (or equivalent occupy signal) |
| `unavailable` | Administratively unavailable |
| `maintenance` | Under maintenance |
| `inactive` | Out of service / inactive |

### Transition ownership rules

- Allocation may cause a bed to become allocated/reserved depending on final design.
- CheckIn transitions physical occupancy to occupied.
- CheckOut transitions physical occupancy away from occupied.
- Dormitory stores the resulting physical state.
- Dormitory does **not** decide who should receive a bed; Allocation does.

**Prior Spec04 research alignment note (design risk):** existing Spec04 artifacts use separate **operability** (`InService` / `OutOfService` / `Maintenance`) and **occupancy markers** (`Vacant` / `Reserved` / `Occupied`). Domain Design Approval must reconcile example state names above with that two-axis model if retained.

---

## 7. Persistence Planning

Design the persistence model **without creating migrations**. No migrations are authorized by this plan.

### Planned tables / entities

| Planned table | Purpose | Key relationships |
| ------------- | ------- | ----------------- |
| `dormitories` (or `dormitory_sites`) | Top-level accommodation site catalog (internal/external) | Root; owns buildings |
| `dormitory_buildings` | Buildings within an internal dormitory | Belongs to dormitory |
| `dormitory_floors` | Floors within a building | Belongs to building |
| `dormitory_rooms` | Rooms within a floor | Belongs to floor |
| `dormitory_beds` | Beds within a room | Belongs to room; unique bed identity within dormitory scope |

### Expected constraints

- Building belongs to dormitory
- Floor belongs to building
- Room belongs to floor
- Bed belongs to room
- Room capacity must be consistent with bed count or explicitly configured capacity
- Bed cannot belong to multiple rooms
- Inactive parent should affect availability of child resources
- Physical state transitions must be auditable or traceable where required by project conventions (`RecordsActivity` / AuditService)
- Intra-module FKs only; no cross-module FKs to Allocation, Employee, Identity, or Request
- External dormitories must not have building/floor/room/bed children

### Index planning

| Index purpose | Target |
| ------------- | ------ |
| Dormitory lookup | Site id / code |
| Building lookup | Building id; dormitory + building code |
| Floor lookup | Floor id; building + floor identity |
| Room lookup | Room id; floor + room code |
| Bed availability lookup | Room/bed + availability/occupancy markers |
| State/status filtering | Room/bed state columns |
| Capacity / availability query support | Dormitory-scoped bed queries; denormalized site id on beds if required |

**No migrations are authorized by this plan.**

---

## 8. Application Boundary Planning

These are **design candidates only**. No application actions, commands, handlers, DTOs, or code are authorized yet.

### Queries / read contracts (candidates)

- `ListDormitories`
- `GetDormitory`
- `ListBuildingsByDormitory`
- `ListFloorsByBuilding`
- `ListRoomsByFloor`
- `ListBedsByRoom`
- `GetRoomCapacity`
- `GetAvailableBeds`
- `GetAccommodationAvailability`
- `GetPhysicalOccupancyState`

Existing Spec04 contract direction (`DormitoryReadContract`, assignable capacity, bed physical status) remains a design baseline to reconcile at Domain Design Approval.

### Commands (design candidates)

- `CreateDormitory`
- `UpdateDormitory`
- `CreateBuilding`
- `UpdateBuilding`
- `CreateFloor`
- `UpdateFloor`
- `CreateRoom`
- `UpdateRoom`
- `RegisterBed`
- `UpdateBed`
- `MarkRoomUnavailable`
- `MarkBedUnavailable`
- `ApplyAllocationStateChange`
- `ApplyCheckInOccupancyStarted`
- `ApplyCheckOutOccupancyEnded`

---

## 9. Integration Boundary Planning

Event names are planning candidates and must be finalized during domain/application design. No event classes or listeners are authorized yet.

### Allocation → Dormitory (inbound)

- `AllocationAssigned`
- `AllocationChanged`
- `AllocationReleased`
- `AllocationCancelled`

### CheckIn/CheckOut → Dormitory (inbound)

- `OccupancyStarted`
- `OccupancyEnded`
- `CheckInConfirmed`
- `CheckOutConfirmed`

### Dormitory → downstream consumers (outbound)

- `AccommodationAvailabilityChanged`
- `BedPhysicalStateChanged`
- `RoomPhysicalStateChanged`
- `CapacityChanged`

### Rules

- Allocation may drive updates through events but does not own physical room/bed state (CD-014).
- CheckIn/CheckOut may trigger physical occupancy transitions (CD-015).
- Dormitory records and exposes the resulting physical state.
- Spec07 Allocation/CheckIn integration must respect CD-014 and CD-015.
- Spec01 technical foundation remains a prerequisite.

---

## 10. Authorization Boundary Planning

Permission names are planning candidates. Final permission model requires approval before implementation. No policy classes, middleware, gates, or role changes are authorized by this plan.

### Candidate permissions

- `dormitory.view`
- `dormitory.manage_structure`
- `dormitory.manage_capacity`
- `dormitory.manage_availability`
- `dormitory.manage_physical_state`
- `dormitory.consume_allocation_events`
- `dormitory.consume_checkin_events`

---

## 11. Test Strategy Planning

Tests are planned here but **not implemented** by this artifact.

| Category | Purpose |
| -------- | ------- |
| Domain invariant tests | Hierarchy and physical-state invariants |
| Hierarchy relationship tests | Parent/child ownership and FK rules |
| Capacity calculation tests | Room/dormitory capacity consistency |
| Availability calculation tests | Assignable capacity predicates |
| Physical state transition tests | Room/bed state legality |
| Allocation integration boundary tests | Inbound assignment/release signals |
| Check-in/check-out integration boundary tests | Occupancy start/end signals |
| Authorization tests | Permission/policy enforcement |
| Repository / read model tests | Persistence and query surfaces |
| Migration tests | Only after migrations are authorized |
| Regression tests for dependent flows | Spec07 / consumer contract stability |

---

## 12. Design Risks And Open Questions

| ID | Risk / question |
| -- | --------------- |
| Q-01 | Whether room capacity is derived from bed count or stored separately. |
| Q-02 | Whether bed allocation state and physical occupancy state are separate fields. |
| Q-03 | Whether room state is derived from bed states or stored explicitly. |
| Q-04 | How inactive/maintenance status propagates from dormitory/building/floor/room to beds. |
| Q-05 | Whether allocation creates a reserved/allocated state before check-in. |
| Q-06 | Whether check-in can occur without a prior allocation. |
| Q-07 | Whether checkout directly releases allocation or only changes physical occupancy. |
| Q-08 | Whether audit/history is stored in Dormitory or emitted to an existing Audit boundary. |
| Q-09 | Which read contracts are needed by future UI governance. |
| Q-10 | Which events are synchronous commands vs asynchronous events. |
| Q-11 | Reconciliation of this plan’s Floor aggregate with prior Spec04 OA-04-01 / R-02 (`floor_label` on Room). |
| Q-12 | Reconciliation of example room/bed state names with existing operability + occupancy-marker enums. |

---

## 13. Required Design Approvals Before Implementation

Implementation authorization requires approval of:

1. Final domain hierarchy
2. Physical state model
3. Persistence schema
4. Application contract list
5. Event integration model
6. Authorization model
7. Test strategy
8. Dependency rules with Spec07
9. No-conflict confirmation with Request, Workflow, Notification, Audit, and UI governance

Until those approvals exist and a **separate** Spec04 implementation authorization artifact is created, implementation remains `NOT_AUTHORIZED`.

---

## 14. Excluded Scope

This artifact **excludes** and does **not** authorize:

| Excluded |
| -------- |
| Implementation code |
| Domain class creation |
| Migrations |
| Repositories |
| Controllers |
| Application actions |
| Commands / queries as code |
| DTOs / resources as code |
| Policies / gates as code |
| Event / listener classes |
| Seeders / factories |
| UI implementation |
| Livewire components |
| Blade views |
| Dormitory UI feature-contract |
| Workflow UI feature-contract |
| Request approval workflow changes |
| Allocation ownership changes |
| Voucher implementation |
| Reporting dashboards |
| Notification UI |
| Audit UI |
| Any implementation outside Spec04 backend foundation |

---

## 15. Next Gate

**NEXT GATE:** Spec04 Domain Design Approval

The next gate must approve the domain model, state model, persistence design, application boundary, integration boundary, authorization boundary, and test strategy before implementation authorization can be considered.

---

## 16. Stop Boundary

This artifact does **not** authorize implementation.  
This artifact does **not** authorize migrations.  
This artifact does **not** authorize code.  
This artifact does **not** authorize UI.  
This artifact does **not** authorize feature-contracts.  
This artifact does **not** resume Workflow UI governance.  
This artifact only creates the backend foundation plan for Spec04.

**Forbidden under this plan:**

- Do not modify application code.
- Do not modify domain code.
- Do not create migrations.
- Do not create repositories.
- Do not create controllers.
- Do not create application actions.
- Do not create command/query classes.
- Do not create DTOs/resources.
- Do not create policy/gate classes.
- Do not create event/listener classes.
- Do not create seeders/factories.
- Do not create Livewire components.
- Do not create Blade views.
- Do not create UI governance artifacts.
- Do not create feature-contracts.
- Do not continue workflow-ui governance.
- Do not set STATUS to `IMPLEMENTATION_AUTHORIZED`.
- Do not authorize coding.
- Do not create any artifact other than this record (at planning time).

---

## Evidence Basis

| Source | Relevant point |
| ------ | -------------- |
| [`spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) | `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN`; design intake only |
| [`.specify/docs/spec-catalog.md`](../spec-catalog.md) | Spec04 Planning Authorized; owns physical accommodation structures; impl hold |
| [`.specify/docs/catalog-decisions.md`](../catalog-decisions.md) | CD-014, CD-015 ownership split |
| [`specs/004-accommodation-resource/spec.md`](../../specs/004-accommodation-resource/spec.md) | Dormitory physical catalog; planning authored |
| [`specs/004-accommodation-resource/plan.md`](../../specs/004-accommodation-resource/plan.md) | Planning authorized; implementation not authorized |
| [`specs/004-accommodation-resource/data-model.md`](../../specs/004-accommodation-resource/data-model.md) | Prior persistence/design baseline |
| [`specs/004-accommodation-resource/tasks.md`](../../specs/004-accommodation-resource/tasks.md) | Task baseline (not authorized for execution) |
| [`specs/007-allocation-checkin/spec.md`](../../specs/007-allocation-checkin/spec.md) / [`plan.md`](../../specs/007-allocation-checkin/plan.md) / [`tasks.md`](../../specs/007-allocation-checkin/tasks.md) | Spec07 depends on Spec04; CD-014/CD-015 frozen |

---

## References

- [`spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md)
- [`spec04-planning-authorization.md`](spec04-planning-authorization.md)
- [`../spec-catalog.md`](../spec-catalog.md)
- [`../catalog-decisions.md`](../catalog-decisions.md) — CD-014, CD-015
- [`../../specs/004-accommodation-resource/plan.md`](../../specs/004-accommodation-resource/plan.md)
- [`../../specs/007-allocation-checkin/plan.md`](../../specs/007-allocation-checkin/plan.md)

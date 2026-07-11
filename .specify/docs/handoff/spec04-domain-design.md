# Spec04 Domain Design: Accommodation Resource / Dormitory

## 1. Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `DOMAIN_DESIGN_APPROVED` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Current gate** | Spec04 Domain Design Approval |
| **Previous gate** | `BACKEND_FOUNDATION_PLAN_CREATED` |
| **Implementation status** | `NOT_AUTHORIZED` |
| **Next allowed gate** | Spec04 Persistence Design |
| **Decision date** | 2026-07-10 |

This artifact approves **domain design only**. It does **not** authorize implementation.

**Prior gates:**

- [`.specify/docs/handoff/spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) — `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN`
- [`.specify/docs/handoff/spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) — `BACKEND_FOUNDATION_PLAN_CREATED`

**Catalog state (unchanged):** Spec04 remains Planning Authorized. Spec04 implementation remains on hold until a separate Spec04 implementation authorization artifact is issued.

---

## 2. Purpose

This artifact closes the **domain design gate** for Spec04 Accommodation Resource / Dormitory.

Dormitory must become a real backend bounded context before UI, Allocation integration, CheckIn/CheckOut integration, Voucher, or Workflow UI can depend on it.

This artifact defines:

- Domain ownership
- Aggregate boundary
- Hierarchy
- Entities
- State ownership
- Invariants

**Governance facts retained:**

- Spec04 owns accommodation physical resources.
- Spec04 implementation is still not authorized.
- **CD-014:** Allocation owns assignment; Dormitory owns physical room/bed state; CheckIn/CheckOut manages physical occupancy transitions.
- **CD-015:** CheckIn/CheckOut is an operational boundary and integrates with Dormitory for physical state updates.
- Workflow UI remains blocked until backend/application capability exists.

---

## 3. Dormitory Bounded Context Boundary

**Dormitory / Accommodation Resource** is the bounded context that owns:

- Accommodation physical resource structure
- Dormitory / site structure
- Buildings
- Floors
- Rooms
- Beds
- Capacity
- Availability
- Physical room state
- Physical bed state
- Current physical occupancy state projection

**Dormitory does not own:**

- Allocation decision
- Assignment lifecycle
- Request approval history
- Workflow orchestration
- Notification delivery
- UI state

External dormitory sites remain catalog-only (no Building / Floor / Room / Bed children), consistent with BR-12 / OA-04-03.

---

## 4. Approved Hierarchy

```text
Dormitory
  -> Building
     -> Floor
        -> Room
           -> Bed
```

| Level | Rule |
| ----- | ---- |
| **Dormitory** | Top-level accommodation container. |
| **Building** | Belongs to one Dormitory. |
| **Floor** | Belongs to one Building. |
| **Room** | Belongs to one Floor. |
| **Bed** | Belongs to one Room. Bed is the assignable physical accommodation unit. |

The previous building/floor hierarchy question is resolved as:

**Dormitory → Building → Floor → Room → Bed.**

---

## 5. Aggregate Design

| Role | Type |
| ---- | ---- |
| **Aggregate Root** | `Dormitory` |
| **Internal Entities** | `Building`, `Floor`, `Room`, `Bed` |

### Rationale

- Dormitory owns the physical resource hierarchy.
- Parent status can affect descendant availability.
- Capacity and availability must remain consistent across the hierarchy.
- Dormitory is the physical state owner according to **CD-014**.

Implementation may later optimize repositories or read models, but it must **not** violate this approved domain ownership model.

---

## 6. Value Objects And Domain Concepts

Approved design concepts (not code):

| Concept | Meaning |
| ------- | ------- |
| **Capacity** | Total usable accommodation capacity. |
| **Availability** | Whether a room/bed can currently be used. |
| **RoomState** | Room-level physical/operational state. |
| **BedState** | Bed-level physical/operational state. |
| **PhysicalOccupancyState** | Real occupancy, not just assignment. |
| **LocationPath** | Hierarchy position (dormitory → building → floor → room → bed). |
| **MaintenanceStatus** | Out-of-service or maintenance conditions. |

---

## 7. Physical State Ownership

Approved ownership rules:

- Dormitory owns stored physical room and bed state.
- Allocation does **not** directly mutate physical state.
- CheckIn/CheckOut triggers physical occupancy transitions.
- Dormitory validates and records the resulting physical state.
- Workflow does **not** own or mutate Dormitory state.

### Candidate Bed states

- `available`
- `allocated`
- `occupied`
- `unavailable`
- `maintenance`
- `inactive`

### Candidate Room states

- `available`
- `partially_occupied`
- `occupied`
- `unavailable`
- `maintenance`
- `inactive`

Final enum names may be refined during Persistence/Application design, but ownership boundaries are approved here.

---

## 8. Domain Invariants

### Hierarchy

- A building belongs to exactly one dormitory.
- A floor belongs to exactly one building.
- A room belongs to exactly one floor.
- A bed belongs to exactly one room.
- A bed cannot belong to multiple rooms.

### Capacity

- Room capacity must not be negative.
- Available capacity must not exceed total capacity.
- Occupied capacity must not exceed total capacity.
- Dormitory capacity must remain consistent with child room/bed capacity.

### Physical state

- An occupied bed cannot be physically available at the same time.
- A maintenance or inactive bed cannot be occupied through normal flow.
- Inactive or maintenance parent resources must affect child availability according to approved rules.
- Allocation cannot directly mutate Dormitory physical state.
- CheckIn/CheckOut cannot bypass Dormitory rules.
- Workflow cannot mutate Dormitory physical state.

---

## 9. Ownership Matrix

| Context | Owns | Does not own |
| ------- | ---- | ------------ |
| **Dormitory** | Physical resource hierarchy; room/bed physical state; capacity and availability; exposes backend read capability after later contract design | Assignment decision; request approval; workflow orchestration |
| **Allocation** | Assignment decision and assignment lifecycle; may reference room/bed identifiers | Physical occupancy storage |
| **CheckIn/CheckOut** | Operational occupancy transition process; integrates with Dormitory to update physical state | Hierarchy; long-term physical state storage |
| **Workflow** | No Dormitory state in this gate; may only orchestrate future processes after separate authorization | Dormitory physical state; Assignment state. **Workflow UI remains blocked.** |
| **Request** | Request approval state/history | Dormitory physical state |
| **Notification / Audit** | Remain separate boundaries; may consume future events only after integration design | Dormitory hierarchy or physical state authority |

---

## 10. Integration Boundary

Approved **domain-level** integration boundaries:

- Allocation may assign or release a resource, but Dormitory owns the physical state impact.
- CheckIn confirms physical occupancy start through Dormitory.
- CheckOut confirms physical occupancy end through Dormitory.
- Dormitory must validate state transitions before recording physical state.

Event names, listeners, commands, and sync/async behavior are **not** approved here; they belong to later Application/Integration design.

---

## 11. Open Questions

Remaining questions for later gates:

1. Is room capacity derived from bed count or stored separately?
2. Is room state derived from bed states or stored explicitly?
3. Should Dormitory store allocated/reserved state or only physical occupancy state?
4. Can CheckIn occur without prior Allocation?
5. Does CheckOut release Allocation or only end physical occupancy?
6. How does inactive/maintenance status propagate from parent to child resources?
7. Which state changes require audit events?
8. Which read contracts are required before UI governance can resume?
9. Which integration paths are synchronous commands versus asynchronous events?

These questions do **not** block domain design approval, but they must be resolved before implementation authorization or in the appropriate next design gate.

---

## 12. Next Gate

**NEXT GATE:** Spec04 Persistence Design

The next gate must design persistence tables/entities, relationships, constraints, indexes, state storage, and traceability requirements. It still must **not** authorize implementation unless a separate implementation authorization is created later.

---

## 13. Stop Boundary

- This artifact approves domain design only.
- It does not authorize implementation.
- It does not authorize migrations.
- It does not authorize code.
- It does not authorize repositories, controllers, actions, DTOs, policies, events, listeners, seeders, or factories.
- It does not authorize UI.
- It does not authorize feature-contracts.
- It does not resume Workflow UI governance.
- It does not authorize Spec04 implementation.

**Forbidden:**

- Do not modify application code.
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
- Do not create any artifact other than this record (at domain design approval time).

---

## Evidence Basis

| Source | Relevant point |
| ------ | -------------- |
| [`spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) | Design activation; not implementation |
| [`spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) | Foundation plan; hierarchy and ownership planning |
| [`../spec-catalog.md`](../spec-catalog.md) | Spec04 Planning Authorized; physical resource ownership; impl hold |
| [`../catalog-decisions.md`](../catalog-decisions.md) | CD-014, CD-015 |
| [`../../specs/004-accommodation-resource/spec.md`](../../specs/004-accommodation-resource/spec.md) | Spec04 requirements baseline |
| [`../../specs/004-accommodation-resource/plan.md`](../../specs/004-accommodation-resource/plan.md) | Planning baseline; impl not authorized |
| [`../../specs/004-accommodation-resource/data-model.md`](../../specs/004-accommodation-resource/data-model.md) | Prior data-model baseline |
| [`../../specs/004-accommodation-resource/tasks.md`](../../specs/004-accommodation-resource/tasks.md) | Task baseline (not authorized for execution) |
| [`../../specs/007-allocation-checkin/plan.md`](../../specs/007-allocation-checkin/plan.md) | Spec07 depends on Spec04; CD-014/CD-015 frozen |

---

## References

- [`spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md)
- [`spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md)
- [`../catalog-decisions.md`](../catalog-decisions.md) — CD-014, CD-015
- [`../../specs/004-accommodation-resource/spec.md`](../../specs/004-accommodation-resource/spec.md)
- [`../../specs/007-allocation-checkin/spec.md`](../../specs/007-allocation-checkin/spec.md)

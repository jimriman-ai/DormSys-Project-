# Spec04 Persistence Design: Accommodation Resource / Dormitory

## 1. Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `PERSISTENCE_DESIGN_APPROVED` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Current gate** | Spec04 Persistence Design |
| **Previous gate** | `DOMAIN_DESIGN_APPROVED` |
| **Implementation status** | `NOT_AUTHORIZED` |
| **Next allowed gate** | Spec04 Application Boundary / Contract Design |
| **Decision date** | 2026-07-10 |

This artifact approves **persistence design only**. It does **not** authorize migrations or implementation.

**Prior gates:**

- [`.specify/docs/handoff/spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) — `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN`
- [`.specify/docs/handoff/spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) — `BACKEND_FOUNDATION_PLAN_CREATED`
- [`.specify/docs/handoff/spec04-domain-design.md`](spec04-domain-design.md) — `DOMAIN_DESIGN_APPROVED`

**Catalog state (unchanged):** Spec04 remains Planning Authorized. Spec04 implementation remains `NOT_AUTHORIZED` until a separate Spec04 implementation authorization artifact is issued.

---

## 2. Purpose

This artifact designs how the approved Dormitory domain hierarchy should be persisted.

It translates the approved domain model into planned tables, relationships, constraints, indexes, and state storage decisions. It prepares the project for later application contract design and implementation authorization.

It does **not** create migrations, models, repositories, or code.

**Governance facts retained:**

- Spec04 owns accommodation physical resource structures.
- Spec04 implementation is still `NOT_AUTHORIZED`.
- Approved hierarchy: Dormitory → Building → Floor → Room → Bed.
- Approved aggregate root: Dormitory.
- **CD-014:** Allocation owns assignment; Dormitory owns physical room/bed state; CheckIn/CheckOut manages physical occupancy transitions.
- **CD-015:** CheckIn/CheckOut is an operational boundary and integrates with Dormitory for physical state updates.
- Workflow UI remains blocked until backend/application capability exists.

---

## 3. Approved Domain Inputs

| Decision | Value |
| -------- | ----- |
| Bounded context | Dormitory / Accommodation Resource |
| Aggregate root | Dormitory |
| Internal entities | Building, Floor, Room, Bed |
| Approved hierarchy | Dormitory → Building → Floor → Room → Bed |

**Ownership inputs:**

- Dormitory owns physical resource structure, capacity, availability, room state, bed state, and physical occupancy state.
- Allocation owns assignment, not physical state.
- CheckIn/CheckOut owns occupancy transition process, not long-term physical state storage.

External dormitory sites remain catalog-only (no Building / Floor / Room / Bed children), consistent with BR-12 / OA-04-03.

---

## 4. Planned Persistence Tables

Approved planned tables (persistence design only):

| Table | Purpose |
| ----- | ------- |
| `dormitories` | Stores top-level accommodation resource container/site. Owns building collection. Provides aggregate-level capacity and availability summary if needed. |
| `dormitory_buildings` | Stores physical buildings under a dormitory. Each building belongs to exactly one dormitory. |
| `dormitory_floors` | Stores physical floors under a building. Each floor belongs to exactly one building. |
| `dormitory_rooms` | Stores physical rooms under a floor. Owns room capacity and room-level state. |
| `dormitory_beds` | Stores assignable physical bed/resource units under a room. Owns bed-level state and physical occupancy status. |

Final column names may be refined during implementation planning, but the **table responsibility and relationship model** are approved here.

**Naming note:** Prior Spec04 `data-model.md` used `dormitory_sites` for the root table. This persistence design adopts `dormitories` as the approved planning name; migration planning may alias or rename for consistency with existing Spec04 artifacts, without changing ownership.

---

## 5. Relationship Design

| Relationship | Direction |
| ------------ | --------- |
| `dormitories` has many `dormitory_buildings` | 1 → N |
| `dormitory_buildings` belongs to `dormitories` | N → 1 |
| `dormitory_buildings` has many `dormitory_floors` | 1 → N |
| `dormitory_floors` belongs to `dormitory_buildings` | N → 1 |
| `dormitory_floors` has many `dormitory_rooms` | 1 → N |
| `dormitory_rooms` belongs to `dormitory_floors` | N → 1 |
| `dormitory_rooms` has many `dormitory_beds` | 1 → N |
| `dormitory_beds` belongs to `dormitory_rooms` | N → 1 |

**Rules:**

- A building cannot exist without a dormitory.
- A floor cannot exist without a building.
- A room cannot exist without a floor.
- A bed cannot exist without a room.
- A bed cannot belong to multiple rooms.
- Parent-child ownership must preserve the approved hierarchy.
- Foreign keys are **intra-module only** — no cross-module FKs to Allocation, Employee, Identity, or Request.

---

## 6. Identifier Strategy

| Expectation | Rule |
| ----------- | ---- |
| Stable internal identifier | Every persistent entity must have a stable internal identifier. |
| Public identifiers | Must follow existing project conventions. |
| UUID convention | If the project uses UUIDv7 (via `HasUuid`) or another public ID convention, Spec04 must follow that convention. |
| Foreign keys | Must preserve the approved hierarchy. |
| Confirmation | Identifier strategy must be confirmed before migration implementation. |

Do not define implementation code in this artifact.

---

## 7. Planned Columns By Table

Planned column **groups** only — not exact migration code.

### `dormitories`

- id / public identifier
- name
- code or reference identifier if needed
- type (internal / external) if retained from Spec04 catalog rules
- status
- aggregate capacity fields if needed
- created_at / updated_at
- deleted_at if project convention uses soft deletes

### `dormitory_buildings`

- id / public identifier
- dormitory_id
- name
- code or building reference
- status
- created_at / updated_at
- deleted_at if applicable

### `dormitory_floors`

- id / public identifier
- building_id
- floor name / number / label
- status
- created_at / updated_at
- deleted_at if applicable

### `dormitory_rooms`

- id / public identifier
- floor_id
- room number / name
- configured capacity if stored
- room_state
- availability_state if stored separately
- maintenance / status fields if needed
- room kind (private / shared) if retained from Spec04 OA-04-05
- created_at / updated_at
- deleted_at if applicable

### `dormitory_beds`

- id / public identifier
- room_id
- bed label / number
- bed_state
- physical_occupancy_state
- availability_state if stored separately
- maintenance / status fields if needed
- denormalized dormitory_id if required for scoped uniqueness / capacity queries
- created_at / updated_at
- deleted_at if applicable

Exact column names, enum names, nullable rules, and default values must be finalized before migration implementation.

---

## 8. State Storage Strategy

Approved persistence-level state storage design:

- Bed state should be stored at bed level.
- Physical occupancy state should be stored or projected at bed level.
- Room state may be stored explicitly or derived from bed states.
- Room capacity may be stored explicitly or derived from bed count.
- Dormitory / building / floor status must affect descendant availability according to approved rules.
- Allocation state may be projected into Dormitory only if later Application/Integration design approves it.
- Dormitory remains the owner of stored physical room/bed state.

Final decision on derived vs stored room state and capacity must be resolved before implementation authorization.

---

## 9. Constraint Design

Approved planned constraints:

| Constraint | Intent |
| ---------- | ------ |
| `building.dormitory_id` → `dormitories.id` | Hierarchy integrity |
| `floor.building_id` → `dormitory_buildings.id` | Hierarchy integrity |
| `room.floor_id` → `dormitory_floors.id` | Hierarchy integrity |
| `bed.room_id` → `dormitory_rooms.id` | Hierarchy integrity |
| Room capacity ≥ 0 | Capacity invariant |
| Available capacity ≤ total capacity | Capacity invariant |
| Occupied capacity ≤ total capacity | Capacity invariant |
| Bed state and physical occupancy state must not conflict | Physical state invariant |
| Inactive or maintenance parent resources prevent normal child availability | Propagation rule |
| Duplicate bed identity within the same room prevented | Uniqueness |
| Duplicate room identity within the same floor/building context prevented as required by product rules | Uniqueness |

Exact database-level constraints versus application-level validations must be finalized before implementation.

---

## 10. Index Design

Approved planned index needs:

- Lookup dormitories by status / code / name
- Lookup buildings by `dormitory_id`
- Lookup floors by `building_id`
- Lookup rooms by `floor_id`
- Lookup beds by `room_id`
- Filter rooms by `room_state` / status
- Filter beds by `bed_state` / `physical_occupancy_state` / availability
- Support available-bed queries
- Support capacity / availability summaries
- Support future UI list / filter / search needs after application contracts exist

Exact index names and composite indexes must be finalized during migration planning.

---

## 11. Traceability And Audit Design

Persistence traceability expectations:

- State transitions for room/bed availability and occupancy must be traceable.
- The design should support future audit integration.
- Dormitory may emit auditable state changes later, but Audit remains a separate boundary.
- Whether traceability is stored in Dormitory tables, history tables, or external audit must be decided before implementation authorization.

No audit tables are authorized by this artifact.

---

## 12. Deletion And Lifecycle Design

| Rule | Expectation |
| ---- | ----------- |
| Hard delete | Physical resources should not be hard-deleted if they have historical occupancy, allocation, audit, or reporting relevance. |
| Soft delete / inactive / archive | Must follow project conventions. |
| Parent inactivation | Inactive parent resources should affect descendant availability. |
| Historical references | Deletion/inactivation behavior must preserve historical references from Allocation and CheckIn/CheckOut. |

---

## 13. Integration Persistence Boundary

| Context | Persistence rule |
| ------- | ---------------- |
| **Allocation** | May reference room/bed identifiers; does not own Dormitory persistence. |
| **CheckIn/CheckOut** | May trigger occupancy state changes through approved application boundary later. |
| **Workflow** | Must not write Dormitory persistence. |
| **Request** | Approval data remains outside Dormitory persistence. |
| **Notification / Audit** | Remain separate persistence boundaries unless separately approved. |

---

## 14. Open Questions

1. Is room capacity stored or derived from bed count?
2. Is room state stored or derived from bed states?
3. Should availability be stored separately or derived from status/state?
4. Should physical occupancy state and bed state be separate columns?
5. Should allocated/reserved state be stored in Dormitory or remain in Allocation?
6. What exact identifier convention should be used for public IDs?
7. Which uniqueness rules apply to dormitory/building/floor/room/bed labels?
8. Should Dormitory need history tables for state transitions?
9. Which indexes are required by the first approved application contracts?
10. Should soft deletes be used for all hierarchy levels?

These questions must be resolved before implementation authorization or in the appropriate next design gate.

---

## 15. Next Gate

**NEXT GATE:** Spec04 Application Boundary / Contract Design

The next gate must design backend queries, commands, DTO boundaries, read contracts, integration commands/events, and authorization expectations. It still must **not** authorize implementation unless a separate implementation authorization artifact is created later.

---

## 16. Stop Boundary

- This artifact approves persistence design only.
- It does not authorize migrations.
- It does not authorize code.
- It does not authorize models, repositories, controllers, actions, DTOs, policies, events, listeners, seeders, or factories.
- It does not authorize UI.
- It does not authorize feature-contracts.
- It does not resume Workflow UI governance.
- It does not authorize Spec04 implementation.

**Forbidden:**

- Do not modify application code.
- Do not create migrations.
- Do not create models.
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
- Do not create any artifact other than this record (at persistence design approval time).

---

## Evidence Basis

| Source | Relevant point |
| ------ | -------------- |
| [`spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) | Design activation; not implementation |
| [`spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) | Persistence planning targets |
| [`spec04-domain-design.md`](spec04-domain-design.md) | Approved hierarchy, aggregate, state ownership |
| [`../spec-catalog.md`](../spec-catalog.md) | Spec04 Planning Authorized; physical resource ownership |
| [`../catalog-decisions.md`](../catalog-decisions.md) | CD-014, CD-015 |
| [`../../specs/004-accommodation-resource/data-model.md`](../../specs/004-accommodation-resource/data-model.md) | Prior schema baseline |
| [`../../specs/004-accommodation-resource/plan.md`](../../specs/004-accommodation-resource/plan.md) | Planning baseline; impl not authorized |
| [`../../specs/007-allocation-checkin/plan.md`](../../specs/007-allocation-checkin/plan.md) | Spec07 depends on Spec04; CD-014/CD-015 frozen |

---

## References

- [`spec04-domain-design.md`](spec04-domain-design.md)
- [`spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md)
- [`../catalog-decisions.md`](../catalog-decisions.md) — CD-014, CD-015
- [`../../specs/004-accommodation-resource/data-model.md`](../../specs/004-accommodation-resource/data-model.md)
- [`../../specs/007-allocation-checkin/spec.md`](../../specs/007-allocation-checkin/spec.md)

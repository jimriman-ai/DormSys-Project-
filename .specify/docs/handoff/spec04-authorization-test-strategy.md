# Spec04 Authorization & Test Strategy Design: Accommodation Resource / Dormitory

## 1. Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `AUTHORIZATION_TEST_STRATEGY_APPROVED` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Current gate** | Spec04 Authorization & Test Strategy Design |
| **Previous gate** | `INTEGRATION_BOUNDARY_DESIGN_APPROVED` |
| **Implementation status** | `NOT_AUTHORIZED` |
| **Next allowed gate** | Spec04 Implementation Authorization |
| **Decision date** | 2026-07-10 |

This artifact approves **authorization and test strategy design only**. It does **not** authorize implementation, policies, permissions, migrations, tests, UI, or feature-contracts.

**Prior gates:**

- [`.specify/docs/handoff/spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) — `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN`
- [`.specify/docs/handoff/spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) — `BACKEND_FOUNDATION_PLAN_CREATED`
- [`.specify/docs/handoff/spec04-domain-design.md`](spec04-domain-design.md) — `DOMAIN_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-persistence-design.md`](spec04-persistence-design.md) — `PERSISTENCE_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-application-boundary-contract-design.md`](spec04-application-boundary-contract-design.md) — `APPLICATION_BOUNDARY_CONTRACT_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-integration-boundary-design.md`](spec04-integration-boundary-design.md) — `INTEGRATION_BOUNDARY_DESIGN_APPROVED`

**Catalog state (unchanged):** Spec04 remains Planning Authorized. Spec04 implementation remains `NOT_AUTHORIZED` until a separate Spec04 implementation authorization artifact is issued.

---

## 2. Purpose

This artifact defines the authorization boundary for Dormitory backend capabilities. It defines conceptual permissions and access expectations. It defines required domain, application, integration, authorization, audit, and concurrency test strategy. It prepares Spec04 for a later implementation authorization gate.

It does **not** create code, policy classes, gates, middleware, seeders, permission records, tests, migrations, controllers, routes, DTOs, or UI artifacts.

**Governance facts retained:**

- Spec04 owns accommodation physical resource structures and stored physical room/bed state.
- Spec04 implementation is still `NOT_AUTHORIZED`.
- Approved hierarchy: Dormitory → Building → Floor → Room → Bed.
- Approved aggregate root: Dormitory.
- Approved persistence tables: `dormitories`, `dormitory_buildings`, `dormitory_floors`, `dormitory_rooms`, `dormitory_beds`.
- Approved application boundary: Dormitory is the only approved application layer boundary for reading Dormitory structure, validating physical state transitions, and recording room/bed physical state changes.
- Approved integration boundary: Allocation owns assignment; Dormitory owns physical room/bed state; CheckIn/CheckOut owns occupancy transition process; Request is read-oriented; Workflow has no direct write dependency; Notification/Audit may consume approved future outputs.
- **CD-014** / **CD-015** ownership split remains frozen.
- Workflow UI remains blocked until backend/application capability exists.
- UI governance must not resume from this artifact.

---

## 3. Approved Inputs

| Decision | Value |
| -------- | ----- |
| Bounded context | Dormitory / Accommodation Resource |
| Aggregate root | Dormitory |
| Internal entities | Building, Floor, Room, Bed |
| Approved hierarchy | Dormitory → Building → Floor → Room → Bed |
| Approved tables | `dormitories`, `dormitory_buildings`, `dormitory_floors`, `dormitory_rooms`, `dormitory_beds` |

**Ownership inputs:**

- Dormitory owns physical resource structure, capacity, availability, room state, bed state, and physical occupancy state.
- Allocation owns assignment and may only consume approved Dormitory support capabilities.
- CheckIn/CheckOut owns occupancy transition process and must request Dormitory validation/recording.
- Request is read-oriented unless later approved otherwise.
- Workflow must not directly mutate Dormitory state.
- Notification and Audit do not own Dormitory state.

---

## 4. Authorization Boundary Principles

| Principle | Rule |
| --------- | ---- |
| Read path | All Dormitory reads must pass through approved backend/application boundaries. |
| Mutation path | All Dormitory mutations must pass through approved backend/application boundaries. |
| No bypass | No UI, external context, or workflow may bypass Dormitory authorization. |
| Persistence isolation | No context may directly write Dormitory persistence tables. |
| Permission distinction | Authorization must distinguish read access, structure management, status management, and occupancy transition authority. |
| Allocation | Must not receive direct physical state mutation permission. |
| CheckIn/CheckOut | May request occupancy transitions; Dormitory still authorizes and validates the resulting state change. |
| Workflow | Must not receive direct write permission to Dormitory state. |
| Failure semantics | Authorization failures must be distinguishable from validation failures. |

---

## 5. Conceptual Permission Model

These are **conceptual permission names only**. No permission records, roles, seeders, gates, policies, middleware, guards, or authorization code are approved here.

### Read permissions

- `accommodation.view`
- `accommodation.view_structure`
- `accommodation.view_availability`
- `accommodation.view_occupancy`

### Structure management permissions

- `accommodation.manage_structure`
- `accommodation.create_dormitory`
- `accommodation.update_dormitory`
- `accommodation.create_building`
- `accommodation.update_building`
- `accommodation.create_floor`
- `accommodation.update_floor`
- `accommodation.create_room`
- `accommodation.update_room`
- `accommodation.create_bed`
- `accommodation.update_bed`

### Physical state permissions

- `accommodation.manage_status`
- `accommodation.mark_room_available`
- `accommodation.mark_room_unavailable`
- `accommodation.mark_room_maintenance`
- `accommodation.mark_bed_available`
- `accommodation.mark_bed_unavailable`
- `accommodation.mark_bed_maintenance`

### Occupancy transition permissions

- `accommodation.transition_occupancy`
- `accommodation.start_occupancy`
- `accommodation.end_occupancy`

### Integration consumption permissions

- `accommodation.consume_allocation_support`
- `accommodation.consume_checkin_transition`
- `accommodation.consume_checkout_transition`

---

## 6. Role / Actor Expectations

Exact role names, guards, teams, tenant scoping, and user assignments are **not** approved here.

| Actor category | May | Must not |
| -------------- | --- | -------- |
| **Dormitory administrator** | Manage Dormitory structure if authorized; manage metadata/status if authorized; view resource hierarchy | Bypass application boundary |
| **Dormitory operator** | Change approved room/bed operational states if authorized; view availability and occupancy state | Directly write persistence outside Dormitory boundary |
| **Allocation process/user** | Consume availability and assignment-support reads | Directly change physical room/bed state; treat assignment as occupancy |
| **CheckIn/CheckOut process/user** | Request occupancy start/end | Bypass Dormitory validation; directly update Dormitory persistence |
| **Request process/user** | Consume read-oriented availability information if later approved | Reserve, occupy, release, or mutate beds directly |
| **Workflow process/user** | Orchestrate future approved processes only | Directly mutate Dormitory state |
| **Notification / Audit** | Consume future approved outputs | Decide or mutate Dormitory state |

---

## 7. Authorization Matrix

Design-level matrix. Permission names are conceptual.

| Capability | Required conceptual permission category | Allowed actor category | Forbidden actor category | Notes |
| ---------- | --------------------------------------- | ---------------------- | ------------------------ | ----- |
| List dormitories | `accommodation.view` / `view_structure` | Admin, Operator, Allocation (support), Request (if later approved) | Workflow write actors; anonymous | Read-only through Dormitory boundary |
| View dormitory detail | `accommodation.view` / `view_structure` | Admin, Operator, Allocation (support) | Direct persistence consumers | No persistence leak |
| List hierarchy resources | `accommodation.view_structure` | Admin, Operator, Allocation (support) | Workflow write; Notification/Audit as writers | Building/floor/room/bed lists |
| Query availability | `accommodation.view_availability` / `consume_allocation_support` | Admin, Operator, Allocation | CheckIn as structure writer; Workflow write | Does not imply mutation |
| Query physical occupancy | `accommodation.view_occupancy` | Admin, Operator, CheckIn/CheckOut (read) | Allocation as occupancy writer | Occupancy ≠ assignment |
| Create/update dormitory | `accommodation.manage_structure` / create/update dormitory | Dormitory administrator | Allocation, CheckIn/CheckOut, Workflow, Request | Structure ownership |
| Create/update building | `accommodation.manage_structure` / create/update building | Dormitory administrator | Allocation, CheckIn/CheckOut, Workflow | Parent must be dormitory |
| Create/update floor | `accommodation.manage_structure` / create/update floor | Dormitory administrator | Allocation, CheckIn/CheckOut, Workflow | Parent must be building |
| Create/update room | `accommodation.manage_structure` / create/update room | Dormitory administrator | Allocation, CheckIn/CheckOut, Workflow | Parent must be floor |
| Create/update bed | `accommodation.manage_structure` / create/update bed | Dormitory administrator | Allocation, CheckIn/CheckOut, Workflow | Parent must be room |
| Mark room available/unavailable/maintenance | `accommodation.manage_status` / mark room * | Dormitory administrator, Dormitory operator | Allocation, Workflow, Request | Physical state only |
| Mark bed available/unavailable/maintenance | `accommodation.manage_status` / mark bed * | Dormitory administrator, Dormitory operator | Allocation, Workflow, Request | Physical state only |
| Start physical occupancy | `accommodation.start_occupancy` / `transition_occupancy` / `consume_checkin_transition` | CheckIn/CheckOut process/user | Allocation (direct), Workflow, Request | Dormitory validates and records |
| End physical occupancy | `accommodation.end_occupancy` / `transition_occupancy` / `consume_checkout_transition` | CheckIn/CheckOut process/user | Allocation (direct), Workflow, Request | Dormitory validates and records |
| Consume allocation-support reads | `accommodation.consume_allocation_support` | Allocation process/user | Allocation as physical-state writer | Read/support only |
| Consume check-in/check-out transition boundary | `accommodation.consume_checkin_transition` / `consume_checkout_transition` | CheckIn/CheckOut process/user | Direct table writers; Workflow | Transition request ≠ table write |

---

## 8. Domain Test Strategy

### Hierarchy integrity

- Dormitory owns buildings.
- Building belongs to one Dormitory.
- Floor belongs to one Building.
- Room belongs to one Floor.
- Bed belongs to one Room.
- Child resources cannot be attached to the wrong parent.

### Capacity and availability

- Room capacity cannot be negative.
- Bed counts must align with room capacity rules once finalized.
- Available capacity cannot be negative.
- Occupied capacity cannot exceed physical capacity.
- Maintenance/inactive resources are excluded from normal availability.

### State transitions

- Unavailable bed cannot be occupied.
- Maintenance bed cannot be occupied.
- Inactive bed cannot be occupied.
- Occupied bed cannot be double-occupied.
- Occupied bed cannot be incorrectly marked available without ending occupancy.
- Maintenance room blocks normal bed availability if propagation is approved.
- Invalid state transitions are rejected explicitly.

### Ownership rules

- Allocation cannot own physical occupancy.
- CheckIn/CheckOut cannot own long-term physical bed state.
- Workflow cannot mutate Dormitory state.

---

## 9. Application Boundary Test Strategy

- Read contracts return Dormitory hierarchy without leaking persistence internals.
- Availability queries respect room/bed status.
- Capacity summaries reflect approved hierarchy.
- Commands enforce parent-child ownership.
- Commands reject invalid room/bed state transitions.
- Commands reject unauthorized users/processes.
- Occupancy start validates bed usability.
- Occupancy end validates current occupancy.
- Allocation cannot bypass Dormitory boundary.
- CheckIn/CheckOut cannot bypass Dormitory validation.
- Workflow cannot write Dormitory state.

---

## 10. Integration Test Strategy

### Allocation integration

- Allocation can query available beds through Dormitory boundary.
- Allocation can reference Dormitory identifiers.
- Assignment does not automatically become physical occupancy unless a later approved flow says so.
- Allocation cannot directly mutate Dormitory physical state.

### CheckIn/CheckOut integration

- CheckIn requests occupancy start through Dormitory boundary.
- Dormitory rejects occupancy start for unavailable/maintenance/inactive/occupied bed.
- Dormitory records valid occupancy start.
- CheckOut requests occupancy end through Dormitory boundary.
- Dormitory records valid occupancy end.
- CheckOut cannot end occupancy for an unrelated or non-occupied bed.

### Request integration

- Request can consume approved read-oriented information if later authorized.
- Request cannot reserve, occupy, release, or mutate beds directly.

### Workflow integration

- Workflow cannot directly change Dormitory room/bed state.
- Workflow UI remains blocked.

### Notification/Audit integration

- Future approved outputs can be consumed without giving ownership of Dormitory state.
- Audit records must be considered for structure, status, and occupancy changes.

---

## 11. Authorization Test Strategy

- Read permissions allow only approved reads.
- Structure management requires structure permission.
- Physical state changes require status permission.
- Occupancy transitions require occupancy transition permission.
- Allocation support reads do not imply mutation permission.
- CheckIn/CheckOut transition permission does not imply direct table write access.
- Workflow has no direct Dormitory write permission.
- Unauthorized attempts return authorization failure, not validation failure.
- Authorized but invalid attempts return validation/domain failure, not authorization failure.

---

## 12. Audit And Traceability Test Strategy

- Structure changes should be traceable.
- Room/bed status changes should be traceable.
- Occupancy start/end should be traceable.
- Actor/process identity should be captured where applicable.
- Previous and resulting state should be considered for traceability.
- Audit outputs must not become Dormitory state owners.

Audit implementation details, event payloads, audit tables, and listeners are **not** approved here.

---

## 13. Concurrency Test Strategy

- Two concurrent occupancy starts cannot occupy the same bed.
- Occupancy start cannot race with maintenance/unavailable transition.
- Occupancy end cannot race with another occupancy end in a way that corrupts state.
- Availability projection/read model must not report impossible capacity after concurrent transitions.
- Commands must behave atomically at the application/persistence boundary.

The exact strategy (database transactions, row locks, optimistic locking, unique constraints, or projection repair) must be finalized before or during implementation authorization.

---

## 14. Required Test Layers Before Closure

Backend closure cannot be claimed unless implementation later includes appropriate:

- Domain tests
- Application tests
- Integration tests
- Authorization tests
- Concurrency / race-condition tests where feasible
- Audit / traceability tests where audit integration is implemented
- Regression tests for ownership boundaries

---

## 15. UI Governance Boundary

- This artifact does **not** resume Dormitory UI governance.
- This artifact does **not** resume Workflow UI governance.
- Dormitory UI may only resume after backend implementation is authorized and provides consumable backend capability, or after a separate governance artifact explicitly allows a limited read-only UI dependency.
- Workflow UI remains blocked because Workflow backend capability is not available.
- UI must not depend on persistence tables, speculative DTOs, or unauthorized backend contracts.

---

## 16. Open Questions

1. What exact roles should receive Dormitory read permissions?
2. What exact roles should receive structure management permissions?
3. What exact roles should receive room/bed status management permissions?
4. What exact actors may execute occupancy start/end?
5. Are integration consumers authenticated as users, services, jobs, or internal processes?
6. Should permissions be role-based, policy-based, capability-based, or mixed?
7. Should authorization be tenant/scoping aware?
8. Which operations require audit records?
9. What concurrency strategy should be mandatory for occupancy transitions?
10. Which tests are required before first implementation merge?
11. Which tests are required before backend closure?
12. Are permission names final or only conceptual?

These questions must be resolved before or during implementation authorization.

---

## 17. Next Gate

**NEXT GATE:** Spec04 Implementation Authorization

The next gate may authorize implementation scope for Dormitory backend foundation if governance confirms that domain, persistence, application boundary, integration boundary, authorization, and test strategy designs are complete. Implementation must remain blocked until that separate authorization artifact exists.

---

## 18. Stop Boundary

- This artifact approves authorization and test strategy design only.
- It does not authorize implementation.
- It does not authorize migrations.
- It does not authorize models.
- It does not authorize repositories.
- It does not authorize controllers.
- It does not authorize routes.
- It does not authorize actions/services.
- It does not authorize command/query classes.
- It does not authorize DTO/resource classes.
- It does not authorize policy/gate classes.
- It does not authorize permission records.
- It does not authorize seeders/factories.
- It does not authorize events/listeners.
- It does not authorize jobs/queues.
- It does not authorize tests.
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
- Do not create routes.
- Do not create application actions/services.
- Do not create command/query classes.
- Do not create DTOs/resources.
- Do not create policy/gate classes.
- Do not create permission records.
- Do not create seeders/factories.
- Do not create event/listener classes.
- Do not create jobs/queues.
- Do not create tests.
- Do not create Livewire components.
- Do not create Blade views.
- Do not create UI governance artifacts.
- Do not create feature-contracts.
- Do not continue workflow-ui governance.
- Do not set STATUS to `IMPLEMENTATION_AUTHORIZED`.
- Do not create any artifact other than this record (at authorization & test strategy design approval time).

---

## Evidence Basis

| Source | Relevant point |
| ------ | -------------- |
| [`spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) | Design activation; not implementation |
| [`spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) | Authorization and test strategy planning targets |
| [`spec04-domain-design.md`](spec04-domain-design.md) | Domain invariants and ownership |
| [`spec04-persistence-design.md`](spec04-persistence-design.md) | Persistence isolation |
| [`spec04-application-boundary-contract-design.md`](spec04-application-boundary-contract-design.md) | Application boundary; auth expectations |
| [`spec04-integration-boundary-design.md`](spec04-integration-boundary-design.md) | Integration consumers and invariants |
| [`../spec-catalog.md`](../spec-catalog.md) | Spec04 Planning Authorized |
| [`../catalog-decisions.md`](../catalog-decisions.md) | CD-014, CD-015 |
| [`../../specs/004-accommodation-resource/tasks.md`](../../specs/004-accommodation-resource/tasks.md) | Task/test baseline (not authorized for execution) |
| [`../../specs/007-allocation-checkin/plan.md`](../../specs/007-allocation-checkin/plan.md) | Spec07 depends on Spec04; CD-014/CD-015 frozen |

---

## References

- [`spec04-integration-boundary-design.md`](spec04-integration-boundary-design.md)
- [`spec04-application-boundary-contract-design.md`](spec04-application-boundary-contract-design.md)
- [`../catalog-decisions.md`](../catalog-decisions.md) — CD-014, CD-015
- [`../../specs/004-accommodation-resource/plan.md`](../../specs/004-accommodation-resource/plan.md)
- [`../../specs/007-allocation-checkin/spec.md`](../../specs/007-allocation-checkin/spec.md)

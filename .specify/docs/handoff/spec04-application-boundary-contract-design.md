# Spec04 Application Boundary / Contract Design: Accommodation Resource / Dormitory

## 1. Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `APPLICATION_BOUNDARY_CONTRACT_DESIGN_APPROVED` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Current gate** | Spec04 Application Boundary / Contract Design |
| **Previous gate** | `PERSISTENCE_DESIGN_APPROVED` |
| **Implementation status** | `NOT_AUTHORIZED` |
| **Next allowed gate** | Spec04 Integration Boundary Design |
| **Decision date** | 2026-07-10 |

This artifact approves **application boundary and backend contract design only**. It does **not** authorize implementation, feature-contracts, migrations, or UI.

**Prior gates:**

- [`.specify/docs/handoff/spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) — `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN`
- [`.specify/docs/handoff/spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) — `BACKEND_FOUNDATION_PLAN_CREATED`
- [`.specify/docs/handoff/spec04-domain-design.md`](spec04-domain-design.md) — `DOMAIN_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-persistence-design.md`](spec04-persistence-design.md) — `PERSISTENCE_DESIGN_APPROVED`

**Catalog state (unchanged):** Spec04 remains Planning Authorized. Spec04 implementation remains `NOT_AUTHORIZED` until a separate Spec04 implementation authorization artifact is issued.

---

## 2. Purpose

This artifact defines the approved application boundary for Dormitory.

It identifies planned backend read contracts, mutation boundaries, command/query responsibilities, DTO boundaries, validation responsibilities, and authorization expectations. It prepares Dormitory for later integration with Allocation, CheckIn/CheckOut, Request, Workflow, Notification, and Audit.

It does **not** create code, routes, controllers, actions, DTOs, migrations, tests, or UI artifacts.

**Governance facts retained:**

- Spec04 owns accommodation physical resource structures and stored physical room/bed state.
- Spec04 implementation is still `NOT_AUTHORIZED`.
- Approved hierarchy: Dormitory → Building → Floor → Room → Bed.
- Approved aggregate root: Dormitory.
- Approved persistence tables: `dormitories`, `dormitory_buildings`, `dormitory_floors`, `dormitory_rooms`, `dormitory_beds`.
- **CD-014:** Allocation owns assignment; Dormitory owns physical room/bed state; CheckIn/CheckOut manages physical occupancy transitions.
- **CD-015:** CheckIn/CheckOut is an operational boundary and integrates with Dormitory for physical state updates.
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
- Allocation owns assignment and may reference Dormitory resources.
- CheckIn/CheckOut owns occupancy transition process and integrates with Dormitory for physical state updates.
- Workflow does not own or mutate Dormitory state.

---

## 4. Application Boundary Definition

The Dormitory application boundary is:

- The only approved application layer boundary for reading Dormitory resource structure.
- The only approved application layer boundary for validating Dormitory physical state transitions.
- The only approved application layer boundary for recording Dormitory physical room/bed state changes.
- The integration surface consumed by future Allocation and CheckIn/CheckOut flows.
- The backend capability required before UI governance can resume.

No other bounded context may directly write Dormitory persistence tables.

---

## 5. Planned Read Contracts

Approved planned read capabilities as **design-level contracts**:

| Read capability | Notes |
| --------------- | ----- |
| List dormitories | Backend read capability |
| Get dormitory detail | Backend read capability |
| List buildings for a dormitory | Backend read capability |
| List floors for a building | Backend read capability |
| List rooms for a floor | Backend read capability |
| Get room detail | Backend read capability |
| List beds for a room | Backend read capability |
| Get bed detail | Backend read capability |
| Query room availability | Backend read capability |
| Query bed availability | Backend read capability |
| Query capacity summary by dormitory/building/floor/room | Backend read capability |
| Query physical occupancy state | Backend read capability |
| Query resources by status/state | Backend read capability |
| Query available beds for allocation/check-in planning | Backend read capability |

For each read contract:

- It is a backend read capability.
- It may later be exposed through controllers, APIs, Livewire actions, or internal services only after implementation authorization.
- It must not leak persistence internals directly to UI or other bounded contexts.

Existing Spec04 contract direction (`DormitoryReadContract`, assignable capacity, bed physical status) remains a design baseline to reconcile during Integration Boundary Design and implementation authorization.

---

## 6. Planned Mutation Boundaries

Approved planned mutation capabilities as **design-level boundaries**:

### Dormitory structure management

- Create dormitory
- Update dormitory metadata/status
- Add building to dormitory
- Update building metadata/status
- Add floor to building
- Update floor metadata/status
- Add room to floor
- Update room metadata/status/capacity
- Add bed to room
- Update bed metadata/status

### Physical state management

- Mark room unavailable
- Mark room available
- Mark room under maintenance
- Mark bed unavailable
- Mark bed available
- Mark bed under maintenance
- Record physical occupancy start
- Record physical occupancy end
- Recalculate or refresh availability projection if needed

These are approved as application boundary concepts only. No implementation, route, controller, action, command, or DTO is authorized here.

---

## 7. Command / Query Responsibility Split

### Queries

- Must not mutate Dormitory state.
- Return read models or DTOs appropriate to the consumer.
- May support filtering, sorting, pagination, and search later if approved.
- Must not expose raw persistence records as public contracts.

### Commands

- Validate domain invariants before mutation.
- Enforce hierarchy ownership rules.
- Enforce physical state transition rules.
- Enforce authorization rules.
- Record state changes only through Dormitory boundary.
- Must reject invalid transitions such as occupying unavailable, maintenance, or inactive beds.

Exact class names, method names, routes, and DTO schemas are **not** approved here.

---

## 8. DTO / Read Model Boundary

Approved design-level DTO / read model categories:

| Category | Intent |
| -------- | ------ |
| `DormitorySummary` | List / index projection |
| `DormitoryDetail` | Full dormitory read |
| `BuildingSummary` | Building list projection |
| `FloorSummary` | Floor list projection |
| `RoomSummary` | Room list projection |
| `RoomDetail` | Full room read |
| `BedSummary` | Bed list projection |
| `BedDetail` | Full bed read |
| `AvailabilitySummary` | Availability query result |
| `CapacitySummary` | Capacity aggregation |
| `PhysicalOccupancySummary` | Occupancy projection |
| `StateTransitionResult` | Mutation outcome |

These are conceptual contract shapes only. Exact fields, serialization format, API routes, Livewire props, and resource classes must be designed later or during implementation authorization.

---

## 9. Validation Responsibilities

The Dormitory application boundary must validate:

- Parent resource existence before creating a child resource.
- Parent-child ownership consistency.
- Dormitory hierarchy integrity.
- Capacity constraints.
- Room/bed state transition validity.
- Availability rules.
- Maintenance/inactive propagation rules.
- Physical occupancy transition validity.
- Attempts by Allocation, Workflow, or other contexts to bypass Dormitory ownership.
- Duplicate labels/codes within approved uniqueness scope once finalized.

Some validations may later be enforced by database constraints, domain services, policies, or application actions, but responsibility belongs to the Dormitory boundary.

---

## 10. Authorization Expectations

| Expectation | Rule |
| ----------- | ---- |
| Structure management | Requires administrative or authorized operational permission. |
| Physical state changes | Require authorized operational permission. |
| Read access | May vary by role and consumer context. |
| Allocation | May only consume approved read / assignment-support capabilities. |
| CheckIn/CheckOut | May only request approved occupancy transition capabilities. |
| Workflow | Must not receive direct write permission to Dormitory state. |
| UI users | Must not bypass backend authorization. |

Exact roles, permissions, policies, gates, middleware, and authorization code are **not** approved here.

---

## 11. Integration Consumer Expectations

| Consumer | Allowed | Forbidden |
| -------- | ------- | --------- |
| **Allocation** | Query available rooms/beds; reference approved Dormitory resource identifiers; request assignment-support data | Directly mutate physical room/bed state |
| **CheckIn/CheckOut** | Request physical occupancy start/end through Dormitory boundary | Bypass Dormitory state validation; own long-term room/bed physical state storage |
| **Request** | Reference Dormitory resource availability in future flows | Own Dormitory state |
| **Workflow** | Orchestrate future approved processes | Directly mutate Dormitory state. **Workflow UI remains blocked.** |
| **Notification / Audit** | Consume future approved events or traceability outputs | Own Dormitory state |

---

## 12. Error And Result Design

Conceptual result handling:

- Read contracts should return empty results or not-found responses according to project conventions.
- Commands should return success/failure results.
- Invalid transitions must produce explicit domain/application errors.
- Authorization failures must be distinguishable from validation failures.
- Concurrency conflicts must be considered before implementation authorization.
- Error shape and exception strategy must follow project conventions.

No exception classes or response schemas are authorized here.

---

## 13. Concurrency And Consistency Expectations

- Physical occupancy updates must be protected from race conditions.
- Available capacity and occupancy projections must remain consistent.
- Two check-ins must not occupy the same bed at the same time.
- State transitions should be atomic at the application/persistence boundary.
- Locking, transactions, optimistic concurrency, or event consistency strategy must be decided before implementation authorization.

---

## 14. UI Governance Boundary

- This artifact does **not** resume Dormitory UI governance.
- This artifact does **not** resume Workflow UI governance.
- Future UI governance may only resume after backend application capability is implemented or explicitly authorized.
- UI must depend on approved backend contracts, not persistence tables or speculative DTOs.

---

## 15. Open Questions

1. Which exact read contracts are needed first for Allocation integration?
2. Which exact read contracts are needed first for Dormitory UI?
3. Should available-bed query be optimized as a dedicated contract?
4. What filters/sorting/pagination are required for room/bed listing?
5. What exact command boundaries are required for check-in and check-out?
6. Should room availability be recalculated synchronously or projected asynchronously?
7. What role/permission model should protect Dormitory structure changes?
8. What role/permission model should protect physical state changes?
9. What concurrency strategy should protect bed occupancy transitions?
10. Which errors should be domain errors versus application errors?
11. Which contract outputs must be auditable?
12. Which events, if any, should be emitted after state transitions?

These questions must be resolved before implementation authorization or in the appropriate next design gate.

---

## 16. Next Gate

**NEXT GATE:** Spec04 Integration Boundary Design

The next gate must design how Dormitory interacts with Allocation, CheckIn/CheckOut, Request, Workflow, Notification, and Audit. It must decide integration flows, event/command boundaries, ownership protection, and sync/async expectations. It still must **not** authorize implementation unless a separate implementation authorization artifact is created later.

---

## 17. Stop Boundary

- This artifact approves application boundary and backend contract design only.
- It does not authorize implementation.
- It does not authorize migrations.
- It does not authorize models.
- It does not authorize repositories.
- It does not authorize controllers.
- It does not authorize actions/services.
- It does not authorize command/query classes.
- It does not authorize DTO/resource classes.
- It does not authorize policy/gate classes.
- It does not authorize routes.
- It does not authorize events/listeners.
- It does not authorize seeders/factories.
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
- Do not create application actions/services.
- Do not create command/query classes.
- Do not create DTOs/resources.
- Do not create policy/gate classes.
- Do not create routes.
- Do not create event/listener classes.
- Do not create tests.
- Do not create seeders/factories.
- Do not create Livewire components.
- Do not create Blade views.
- Do not create UI governance artifacts.
- Do not create feature-contracts.
- Do not continue workflow-ui governance.
- Do not set STATUS to `IMPLEMENTATION_AUTHORIZED`.
- Do not create any artifact other than this record (at application boundary / contract design approval time).

---

## Evidence Basis

| Source | Relevant point |
| ------ | -------------- |
| [`spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) | Design activation; not implementation |
| [`spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) | Application contract planning targets |
| [`spec04-domain-design.md`](spec04-domain-design.md) | Domain ownership, hierarchy, invariants |
| [`spec04-persistence-design.md`](spec04-persistence-design.md) | Approved tables and persistence boundary |
| [`../spec-catalog.md`](../spec-catalog.md) | Spec04 Planning Authorized; physical resource ownership |
| [`../catalog-decisions.md`](../catalog-decisions.md) | CD-014, CD-015 |
| [`../../specs/004-accommodation-resource/plan.md`](../../specs/004-accommodation-resource/plan.md) | `DormitoryReadContract` / port planning baseline |
| [`../../specs/004-accommodation-resource/tasks.md`](../../specs/004-accommodation-resource/tasks.md) | Task baseline (not authorized for execution) |
| [`../../specs/007-allocation-checkin/plan.md`](../../specs/007-allocation-checkin/plan.md) | Spec07 depends on Spec04; CD-014/CD-015 frozen |

---

## References

- [`spec04-persistence-design.md`](spec04-persistence-design.md)
- [`spec04-domain-design.md`](spec04-domain-design.md)
- [`../catalog-decisions.md`](../catalog-decisions.md) — CD-014, CD-015
- [`../../specs/004-accommodation-resource/contracts/dormitory-read-service.md`](../../specs/004-accommodation-resource/contracts/dormitory-read-service.md)
- [`../../specs/007-allocation-checkin/spec.md`](../../specs/007-allocation-checkin/spec.md)

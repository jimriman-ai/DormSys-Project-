# Spec04 Integration Boundary Design: Accommodation Resource / Dormitory

## 1. Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `INTEGRATION_BOUNDARY_DESIGN_APPROVED` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Current gate** | Spec04 Integration Boundary Design |
| **Previous gate** | `APPLICATION_BOUNDARY_CONTRACT_DESIGN_APPROVED` |
| **Implementation status** | `NOT_AUTHORIZED` |
| **Next allowed gate** | Spec04 Authorization & Test Strategy Design |
| **Decision date** | 2026-07-10 |

This artifact approves **integration boundary design only**. It does **not** authorize implementation, events, listeners, migrations, routes, controllers, UI, or feature-contracts.

**Prior gates:**

- [`.specify/docs/handoff/spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) — `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN`
- [`.specify/docs/handoff/spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) — `BACKEND_FOUNDATION_PLAN_CREATED`
- [`.specify/docs/handoff/spec04-domain-design.md`](spec04-domain-design.md) — `DOMAIN_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-persistence-design.md`](spec04-persistence-design.md) — `PERSISTENCE_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-application-boundary-contract-design.md`](spec04-application-boundary-contract-design.md) — `APPLICATION_BOUNDARY_CONTRACT_DESIGN_APPROVED`

**Catalog state (unchanged):** Spec04 remains Planning Authorized. Spec04 implementation remains `NOT_AUTHORIZED` until a separate Spec04 implementation authorization artifact is issued.

---

## 2. Purpose

This artifact defines how Dormitory integrates with other bounded contexts.

It protects Dormitory ownership of physical resource structure and room/bed physical state. It defines allowed integration directions with Allocation, CheckIn/CheckOut, Request, Workflow, Notification, and Audit. It prepares the project for authorization and test strategy design.

It does **not** create integration code, events, listeners, queues, controllers, routes, DTOs, or UI artifacts.

**Governance facts retained:**

- Spec04 owns accommodation physical resource structures and stored physical room/bed state.
- Spec04 implementation is still `NOT_AUTHORIZED`.
- Approved hierarchy: Dormitory → Building → Floor → Room → Bed.
- Approved aggregate root: Dormitory.
- Approved persistence tables: `dormitories`, `dormitory_buildings`, `dormitory_floors`, `dormitory_rooms`, `dormitory_beds`.
- Approved application boundary: Dormitory is the only approved application layer boundary for reading Dormitory structure, validating physical state transitions, and recording room/bed physical state changes.
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
- Allocation owns assignment and assignment lifecycle.
- CheckIn/CheckOut owns occupancy transition process.
- Workflow does not own or mutate Dormitory state.

---

## 4. Integration Principles

| Principle | Rule |
| --------- | ---- |
| Physical structure truth | Dormitory is the source of truth for physical resource structure. |
| Physical state truth | Dormitory is the source of truth for stored room/bed physical state. |
| Persistence isolation | Other bounded contexts must not directly write Dormitory persistence tables. |
| Application boundary | Other bounded contexts must interact through approved Dormitory application boundaries. |
| Allocation | May reference Dormitory resource identifiers but does not own physical state. |
| CheckIn/CheckOut | May request physical occupancy transitions; Dormitory validates and records the resulting state. |
| Workflow | May orchestrate future approved flows but must not directly mutate Dormitory state. |
| Notification / Audit | May consume future approved outputs but do not own Dormitory state. |

---

## 5. Allocation Integration Boundary

### Allocation may

- Query available dormitories, rooms, or beds through approved Dormitory read contracts.
- Reference Dormitory room/bed identifiers during assignment.
- Request assignment-support data from Dormitory.
- Own assignment decision and assignment lifecycle.

### Allocation must not

- Directly mutate Dormitory room state.
- Directly mutate Dormitory bed state.
- Directly mark a bed occupied.
- Directly update Dormitory persistence tables.
- Treat assignment as physical occupancy.

### Dormitory owns

- Physical room/bed availability.
- Physical occupancy state.
- Validation of resource usability.
- Physical state impact resulting from approved integration flows.

Whether Allocation communicates with Dormitory through synchronous commands, internal services, events, or projections must be finalized before implementation authorization.

---

## 6. CheckIn/CheckOut Integration Boundary

### CheckIn/CheckOut may

- Request physical occupancy start through Dormitory boundary.
- Request physical occupancy end through Dormitory boundary.
- Provide operational context needed for transition validation.
- Own the operational process of arrival, check-in, check-out, and departure.

### CheckIn/CheckOut must not

- Bypass Dormitory state validation.
- Directly update room/bed persistence.
- Own long-term room/bed physical state.
- Occupy an unavailable, maintenance, inactive, or invalid bed.

### Dormitory owns

- Validation of physical occupancy transition.
- Recording resulting physical room/bed state.
- Protecting against conflicting occupancy transitions.
- Maintaining physical availability consistency.

Start/end occupancy integration flow is approved conceptually, but event names, command names, listener names, transaction strategy, and implementation mechanism are **not** approved here.

---

## 7. Request Integration Boundary

### Request may

- Reference Dormitory availability in future approved request flows.
- Use approved read contracts to support request decisions if later authorized.
- Capture request intent, approval state, or approval history.

### Request must not

- Own Dormitory physical state.
- Directly reserve, occupy, release, or mutate beds.
- Bypass Allocation or CheckIn/CheckOut where those contexts own the process.

Request-to-Dormitory dependency remains **read-oriented** unless a later design gate explicitly approves a stronger interaction.

---

## 8. Workflow Integration Boundary

### Workflow may

- Orchestrate future approved business processes after separate backend capability and authorization exist.
- Reference Dormitory-related process steps only through approved application/integration boundaries.

### Workflow must not

- Directly mutate Dormitory persistence.
- Directly change room state.
- Directly change bed state.
- Directly start or end occupancy.
- Replace Allocation or CheckIn/CheckOut ownership.
- Resume Workflow UI governance.

There is **no** direct Workflow-to-Dormitory write dependency approved in this gate. **Workflow UI remains blocked.**

---

## 9. Notification Integration Boundary

### Notification may

- Consume future approved notification triggers related to Dormitory state changes.
- Notify users about approved events such as maintenance, availability changes, or occupancy transitions if later authorized.

### Notification must not

- Own Dormitory state.
- Trigger physical state transitions directly.
- Mutate Dormitory tables.

No notification events, listeners, templates, delivery channels, or inbox behavior are authorized by this artifact.

---

## 10. Audit Integration Boundary

### Audit may

- Record future approved Dormitory state transitions.
- Record who/what caused physical state changes.
- Support traceability for room/bed availability, maintenance, and occupancy transitions.

### Audit must not

- Own Dormitory state.
- Decide Dormitory state.
- Mutate Dormitory persistence.

Audit integration is required conceptually for traceability, but audit tables, event names, payloads, listeners, and storage strategy are **not** authorized here.

---

## 11. Approved Integration Flow Categories

Approved at **design level only**:

| Flow category | Conceptual steps |
| ------------- | ---------------- |
| **Allocation planning** | Allocation queries Dormitory availability → Dormitory returns available resource candidates → Allocation makes assignment decision outside Dormitory. |
| **Assignment impact** | Allocation may report assignment-related context to Dormitory only through approved future boundary → Dormitory may reflect assignment support state only if later approved → Assignment is not physical occupancy. |
| **Check-in** | CheckIn requests occupancy start → Dormitory validates bed state and availability → Dormitory records physical occupancy start if valid. |
| **Check-out** | CheckOut requests occupancy end → Dormitory validates current physical occupancy → Dormitory records physical occupancy end if valid. |
| **Maintenance** | Authorized Dormitory operation marks room/bed under maintenance → Dormitory updates availability impact → Future Notification/Audit may consume approved output. |

These are conceptual flow categories only. They do **not** authorize command classes, events, listeners, jobs, routes, or service methods.

---

## 12. Sync / Async Boundary Expectations

| Expectation | Rule |
| ----------- | ---- |
| Occupancy transitions | Critical physical occupancy transitions should behave consistently and atomically from the caller perspective. |
| Availability queries | Available-bed queries may use read models or projections if later approved. |
| Notification / Audit | May be asynchronous in future design. |
| Allocation / CheckIn | Integration may be synchronous or asynchronous depending on consistency needs. |
| Conflict visibility | Physical occupancy conflicts must not be hidden by eventual consistency. |

Exact sync/async decisions must be finalized before implementation authorization.

---

## 13. Event / Command Boundary Expectations

Approved at **concept level only**:

- Dormitory may expose commands for state-changing requests.
- Dormitory may expose queries for availability and resource structure.
- Dormitory may emit events after approved state transitions in future design.
- Other contexts may consume Dormitory events only after integration implementation is authorized.
- External contexts must not publish events that directly force Dormitory state without validation.

No event names, command names, payload schemas, listeners, jobs, queues, or dispatch mechanisms are approved here.

---

## 14. Data Ownership Matrix

| Context | Owns | Does not own |
| ------- | ---- | ------------ |
| **Dormitory** | Physical resource hierarchy; room/bed physical state; capacity and availability; physical occupancy state storage/projection | Assignment decision; request approval; workflow orchestration |
| **Allocation** | Assignment decision; assignment lifecycle; may reference Dormitory resources | Physical occupancy |
| **CheckIn/CheckOut** | Occupancy transition process; requests Dormitory to validate and record resulting physical state | Hierarchy; long-term physical state |
| **Request** | Request intent, approval state, and approval history | Dormitory physical state |
| **Workflow** | No Dormitory state; may orchestrate future approved processes only | Direct Dormitory mutation |
| **Notification** | Delivery and inbox behavior | Dormitory state |
| **Audit** | Audit records / traceability if later approved | Dormitory state |

---

## 15. Integration Invariants

- Allocation cannot directly mutate Dormitory physical state.
- Assignment does not equal physical occupancy.
- CheckIn/CheckOut cannot bypass Dormitory validation.
- Workflow cannot mutate Dormitory state.
- Notification cannot trigger Dormitory state changes directly.
- Audit cannot decide Dormitory state.
- No context may directly write Dormitory persistence tables.
- Dormitory must reject invalid occupancy transitions.
- Dormitory must protect against double occupancy of the same bed.
- Dormitory must preserve hierarchy ownership when serving integration consumers.

---

## 16. Open Questions

1. Should Allocation notify Dormitory about assignment state, or should Dormitory remain physical-only?
2. Should Dormitory store allocated/reserved state or only physical occupancy state?
3. Should available-bed planning queries be synchronous?
4. Should physical occupancy transitions be synchronous commands?
5. Which Dormitory transitions should emit events?
6. Which transitions require audit records?
7. Which transitions require notifications?
8. Should CheckIn be allowed without a prior Allocation?
9. Does CheckOut release Allocation or only end physical occupancy?
10. What concurrency strategy is required for occupancy start/end?
11. What read model or projection is needed for Allocation?
12. Which integration contracts must exist before UI governance can resume?

These questions must be resolved before implementation authorization or in the appropriate next gate.

---

## 17. Next Gate

**NEXT GATE:** Spec04 Authorization & Test Strategy Design

The next gate must define permissions, authorization rules, domain test strategy, application test strategy, integration test strategy, concurrency test expectations, and governance requirements before implementation can be authorized.

---

## 18. Stop Boundary

- This artifact approves integration boundary design only.
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
- It does not authorize events/listeners.
- It does not authorize jobs/queues.
- It does not authorize tests.
- It does not authorize seeders/factories.
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
- Do not create event/listener classes.
- Do not create jobs/queues.
- Do not create tests.
- Do not create seeders/factories.
- Do not create Livewire components.
- Do not create Blade views.
- Do not create UI governance artifacts.
- Do not create feature-contracts.
- Do not continue workflow-ui governance.
- Do not set STATUS to `IMPLEMENTATION_AUTHORIZED`.
- Do not create any artifact other than this record (at integration boundary design approval time).

---

## Evidence Basis

| Source | Relevant point |
| ------ | -------------- |
| [`spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) | Design activation; not implementation |
| [`spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) | Event / integration planning targets |
| [`spec04-domain-design.md`](spec04-domain-design.md) | Domain ownership and integration invariants |
| [`spec04-persistence-design.md`](spec04-persistence-design.md) | Persistence isolation |
| [`spec04-application-boundary-contract-design.md`](spec04-application-boundary-contract-design.md) | Application boundary; consumer expectations |
| [`../spec-catalog.md`](../spec-catalog.md) | Spec04 Planning Authorized; Spec07 dependency |
| [`../catalog-decisions.md`](../catalog-decisions.md) | CD-014, CD-015 |
| [`../../specs/004-accommodation-resource/plan.md`](../../specs/004-accommodation-resource/plan.md) | Port / signal planning baseline |
| [`../../specs/007-allocation-checkin/plan.md`](../../specs/007-allocation-checkin/plan.md) | Spec07 depends on Spec04; CD-014/CD-015 frozen |

---

## References

- [`spec04-application-boundary-contract-design.md`](spec04-application-boundary-contract-design.md)
- [`spec04-domain-design.md`](spec04-domain-design.md)
- [`../catalog-decisions.md`](../catalog-decisions.md) — CD-014, CD-015
- [`../../specs/004-accommodation-resource/contracts/allocation-physical-state-port.md`](../../specs/004-accommodation-resource/contracts/allocation-physical-state-port.md)
- [`../../specs/007-allocation-checkin/spec.md`](../../specs/007-allocation-checkin/spec.md)

# Spec04 Implementation Authorization: Accommodation Resource / Dormitory

## 1. Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `IMPLEMENTATION_AUTHORIZED` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Current gate** | Spec04 Implementation Authorization |
| **Previous gate** | `AUTHORIZATION_TEST_STRATEGY_APPROVED` |
| **Previous implementation status** | `NOT_AUTHORIZED` |
| **New implementation status** | `IMPLEMENTATION_AUTHORIZED` |
| **Next allowed gate** | Spec04 Backend Implementation |
| **Decision date** | 2026-07-10 |
| **Authority source** | Product / Governance Review |

This artifact authorizes implementation **only** for the approved Spec04 backend foundation scope. It does **not** authorize unrelated specs, Workflow backend, Workflow UI, Dormitory UI, or feature-contract/UI implementation.

**Prior design gates:**

- [`.specify/docs/handoff/spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) — `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN`
- [`.specify/docs/handoff/spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) — `BACKEND_FOUNDATION_PLAN_CREATED`
- [`.specify/docs/handoff/spec04-domain-design.md`](spec04-domain-design.md) — `DOMAIN_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-persistence-design.md`](spec04-persistence-design.md) — `PERSISTENCE_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-application-boundary-contract-design.md`](spec04-application-boundary-contract-design.md) — `APPLICATION_BOUNDARY_CONTRACT_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-integration-boundary-design.md`](spec04-integration-boundary-design.md) — `INTEGRATION_BOUNDARY_DESIGN_APPROVED`
- [`.specify/docs/handoff/spec04-authorization-test-strategy.md`](spec04-authorization-test-strategy.md) — `AUTHORIZATION_TEST_STRATEGY_APPROVED`

---

## 2. Purpose

This artifact issues formal authorization to implement the already-approved Dormitory backend foundation.

It converts approved design decisions into an authorized implementation scope. It enables backend code, migrations, queries, commands, integration code, and tests within the approved scope. It does **not** authorize expansion beyond the approved Spec04 backend foundation boundaries.

**Governance facts retained:**

- Spec04 owns accommodation physical resource structures and stored physical room/bed state.
- Approved hierarchy: Dormitory → Building → Floor → Room → Bed.
- Approved aggregate root: Dormitory.
- Approved persistence tables: `dormitories`, `dormitory_buildings`, `dormitory_floors`, `dormitory_rooms`, `dormitory_beds`.
- Dormitory is the only approved application layer boundary for reading structure, validating physical state transitions, and recording room/bed physical state changes.
- **CD-014 / CD-015:** Allocation owns assignment; Dormitory owns physical room/bed state; CheckIn/CheckOut owns occupancy transition process and integrates with Dormitory for physical state updates.
- Request remains read-oriented unless later approved otherwise.
- Workflow has no direct write dependency; Workflow UI remains blocked.
- Dormitory UI governance does not resume from this artifact.
- This authorization must not silently expand into unrelated specs or frontend scope.

---

## 3. Approved Preconditions

The following gates are complete and approved:

| Gate | Artifact | Status |
| ---- | -------- | ------ |
| Backend Foundation Activation | `spec04-backend-foundation-activation.md` | Complete |
| Backend Foundation Plan | `spec04-backend-foundation-plan.md` | Complete |
| Domain Design | `spec04-domain-design.md` | Approved |
| Persistence Design | `spec04-persistence-design.md` | Approved |
| Application Boundary / Contract Design | `spec04-application-boundary-contract-design.md` | Approved |
| Integration Boundary Design | `spec04-integration-boundary-design.md` | Approved |
| Authorization & Test Strategy Design | `spec04-authorization-test-strategy.md` | Approved |

Implementation authorization is granted because required design gates are complete.

---

## 4. Authorization Scope

Implementation is authorized **only** for Spec04 Accommodation Resource / Dormitory backend foundation, including:

- Domain model implementation for Dormitory hierarchy.
- Persistence implementation for approved Dormitory tables.
- Application read/query implementation for approved backend capabilities.
- Application command/mutation implementation for approved Dormitory boundaries.
- Integration implementation for approved Allocation and CheckIn/CheckOut interaction points.
- Authorization implementation for approved Dormitory backend capabilities.
- Test implementation required by the approved test strategy.
- Audit/traceability support only where needed for approved backend behavior.
- Concurrency protection needed for occupancy/state transitions.

---

## 5. Authorized Domain Implementation Scope

### Entities / aggregate

Authorize implementation of:

- Dormitory entity / aggregate root
- Building entity
- Floor entity
- Room entity
- Bed entity

### Supporting domain concepts

Authorize implementation of supporting domain concepts as needed, such as:

- Capacity
- Availability
- ResourceStatus
- PhysicalOccupancyState
- Domain rules required by approved ownership and transition invariants

Exact class names may follow project conventions, but implementation must preserve approved ownership and hierarchy decisions.

---

## 6. Authorized Persistence Implementation Scope

Authorize implementation of:

- Migrations for:
  - `dormitories`
  - `dormitory_buildings`
  - `dormitory_floors`
  - `dormitory_rooms`
  - `dormitory_beds`
- Foreign keys
- Uniqueness constraints where approved/finalized
- Indexes needed for approved reads and transitions
- Persistence relationships consistent with approved hierarchy
- Transaction boundaries and persistence protections needed for valid occupancy/state transitions

Persistence implementation must not introduce unauthorized ownership shifts or extra domain scope.

Intra-module FKs only. No cross-module FKs to Allocation, Employee, Identity, or Request.

---

## 7. Authorized Application Implementation Scope

### Reads

Authorize implementation of approved backend read capabilities, such as:

- List dormitories
- Dormitory detail reads
- Building / floor / room / bed hierarchy reads
- Room / bed availability queries
- Capacity summary queries
- Physical occupancy state queries
- Available-bed planning queries

### Mutations

Authorize implementation of approved backend mutation capabilities, such as:

- Create/update dormitory
- Create/update building
- Create/update floor
- Create/update room
- Create/update bed
- Mark room/bed available, unavailable, maintenance
- Start physical occupancy
- End physical occupancy

Implementation may use project-conventional controllers, actions, services, queries, DTOs, resources, policies, and validation mechanisms where required by the approved boundary.

---

## 8. Authorized Integration Implementation Scope

Authorize implementation only for approved integrations:

| Consumer | Authorized | Not authorized |
| -------- | ---------- | -------------- |
| **Allocation** | Consume Dormitory read/query capabilities for assignment support; preserve assignment ≠ physical occupancy; prevent Allocation from directly mutating Dormitory physical state | Direct physical state mutation |
| **CheckIn/CheckOut** | Request occupancy start/end through Dormitory boundary; ensure Dormitory validates and records resulting physical state | Direct persistence writes; ownership of long-term physical state |
| **Request** | Read-oriented integration only if needed within approved scope | Reserve/occupy/release beds |
| **Notification / Audit** | Support approved backend traceability and future consumable outputs only where necessary | Own Dormitory state |
| **Workflow** | — | Direct write integration is **not** authorized |

---

## 9. Authorized Authorization Implementation Scope

Authorize implementation of:

- Policies / gates / permission checks needed for Dormitory reads
- Structure management authorization
- Physical state management authorization
- Occupancy transition authorization
- Integration-consumer authorization as required by architecture

Implementation must follow the approved authorization boundary and must **not** give Allocation or Workflow direct Dormitory write ownership.

Conceptual permission categories from `spec04-authorization-test-strategy.md` remain the design baseline; exact permission names may be finalized during implementation without expanding ownership.

---

## 10. Authorized Test Implementation Scope

Authorize implementation of:

- Domain tests
- Application boundary tests
- Integration tests
- Authorization tests
- Concurrency / race-condition tests where feasible
- Audit / traceability tests where approved behavior requires them

Implementation is not complete unless tests cover the approved invariants and ownership boundaries at appropriate levels.

---

## 11. Required Invariants During Implementation

Implementation must preserve:

- Dormitory owns physical resource hierarchy.
- Dormitory owns stored room/bed physical state.
- Allocation owns assignment, not physical occupancy.
- CheckIn/CheckOut owns transition process, not Dormitory persistence ownership.
- No context may directly bypass Dormitory boundary to mutate room/bed state.
- Assignment does not equal occupancy.
- Same bed cannot be physically occupied twice at the same time.
- Invalid occupancy/state transitions must be rejected.
- Hierarchy ownership must be enforced across Dormitory → Building → Floor → Room → Bed.

---

## 12. Explicitly Authorized Artifacts / Code Categories

Allowed categories:

- Domain entities / value objects / services as needed
- Migrations
- Eloquent models or persistence models if aligned with architecture
- Repositories / read providers if aligned with architecture
- Queries / read models / DTOs / resources if aligned with architecture
- Application actions / commands / services
- Policies / gates / authorization code
- Events / listeners / jobs only if needed within approved integration scope
- Tests
- Audit support code within approved scope

---

## 13. Still Out Of Scope

This artifact does **NOT** authorize:

- Dormitory UI implementation
- Dormitory UI governance resumption by default
- Workflow backend implementation
- Workflow UI implementation
- Workflow UI governance resumption
- Unrelated specs
- Feature-contract artifacts unless separately requested/approved by governance
- Reporting UI
- Voucher implementation
- Cross-spec ownership rewrites
- Architectural expansion beyond approved Spec04 backend foundation

---

## 14. Implementation Constraints

- Implementation must remain inside approved Spec04 backend foundation scope.
- Implementation must follow existing project architecture and conventions (Modular Monolith + Clean Architecture + DDD Lite; Domain ← Application ← Infrastructure/Presentation).
- Ownership boundaries from catalog decisions (CD-014, CD-015) must not be weakened.
- Concurrency protection for occupancy transitions is required.
- Tests are mandatory as part of implementation, not optional follow-up.
- UI consumers must depend on backend capabilities, not direct persistence access.
- Any new scope discovered during implementation must return to governance rather than being absorbed silently.
- Definition of Done remains: PHPStan level 8, Pint, Domain/Application coverage targets, audit emission via `AuditService` where required, migrations with rollback.

---

## 15. Completion Expectations

Spec04 Backend Implementation will be considered ready for closure only when:

1. Approved domain model is implemented.
2. Approved persistence model is implemented.
3. Approved reads and mutations are implemented.
4. Approved integration boundaries are implemented within scope.
5. Required authorization is implemented.
6. Required tests are implemented and passing.
7. Ownership and concurrency invariants are demonstrably protected.

---

## 16. Next Gate

**NEXT GATE:** Spec04 Backend Implementation

The next phase is actual implementation of the approved Dormitory backend foundation, followed later by a backend closure artifact once code, tests, and invariants are complete.

---

## 17. Stop Boundary

- This artifact authorizes only Spec04 backend foundation implementation.
- It does not authorize Dormitory UI.
- It does not authorize Workflow backend.
- It does not authorize Workflow UI.
- It does not authorize unrelated specs.
- It does not automatically authorize feature-contracts unless separately governed.
- It does not authorize cross-context ownership changes beyond approved boundaries.

**Forbidden at authorization issuance time:**

- Do not create any artifact other than this record.
- Do not expand authorization into UI, Workflow, Voucher, Reporting, or unrelated specs.
- Do not weaken CD-014 / CD-015 ownership.

---

## Evidence Basis

| Source | Relevant point |
| ------ | -------------- |
| [`spec04-backend-foundation-activation.md`](spec04-backend-foundation-activation.md) | Design activation |
| [`spec04-backend-foundation-plan.md`](spec04-backend-foundation-plan.md) | Foundation plan |
| [`spec04-domain-design.md`](spec04-domain-design.md) | Domain hierarchy and ownership |
| [`spec04-persistence-design.md`](spec04-persistence-design.md) | Approved tables |
| [`spec04-application-boundary-contract-design.md`](spec04-application-boundary-contract-design.md) | Read/mutation contracts |
| [`spec04-integration-boundary-design.md`](spec04-integration-boundary-design.md) | Integration consumers |
| [`spec04-authorization-test-strategy.md`](spec04-authorization-test-strategy.md) | Auth and test strategy |
| [`../spec-catalog.md`](../spec-catalog.md) | Spec04 ownership; prior Planning Authorized hold |
| [`../catalog-decisions.md`](../catalog-decisions.md) | CD-014, CD-015 |
| [`../../specs/004-accommodation-resource/spec.md`](../../specs/004-accommodation-resource/spec.md) / [`plan.md`](../../specs/004-accommodation-resource/plan.md) / [`tasks.md`](../../specs/004-accommodation-resource/tasks.md) | Spec04 baselines |
| [`../../specs/007-allocation-checkin/plan.md`](../../specs/007-allocation-checkin/plan.md) | Spec07 depends on Spec04 |

---

## References

- [`spec04-authorization-test-strategy.md`](spec04-authorization-test-strategy.md)
- [`spec04-integration-boundary-design.md`](spec04-integration-boundary-design.md)
- [`spec04-application-boundary-contract-design.md`](spec04-application-boundary-contract-design.md)
- [`../catalog-decisions.md`](../catalog-decisions.md) — CD-014, CD-015
- [`../../specs/004-accommodation-resource/tasks.md`](../../specs/004-accommodation-resource/tasks.md)
- [`../../specs/007-allocation-checkin/spec.md`](../../specs/007-allocation-checkin/spec.md)

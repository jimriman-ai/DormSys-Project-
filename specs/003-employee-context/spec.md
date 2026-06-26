# Feature Specification: Employee Context (spec03)

**Feature Branch**: `003-employee-context`

**Created**: 2026-06-26

**Status**: Wave 1A (MVP) complete · US2 authorized (T027–T034) · US3+ not authorized

**Catalog**: spec03 — **Authorized — Wave 1A** (Hard Freeze v1.0.0)

**Depends on**: spec01 Foundation (Approved), spec02 Identity & Access (**Frozen — Wave 1A Complete**)

**Input**: Establish the Employee bounded context: employee profiles, organizational structure (departments), dependent records, immutable attachment to Identity via `identity_id`, and eligibility computation consumed by downstream Request (CD-013).

**Normative upstream contract**: [`../002-identity-access/contracts/identity-employee-boundary.md`](../002-identity-access/contracts/identity-employee-boundary.md) (CD-012). Identity supplier read surface: [`../002-identity-access/contracts/identity-read-service.md`](../002-identity-access/contracts/identity-read-service.md).

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Employee Profile with Identity Attachment (Priority: P1)

As an HR administrator, I need to register an employee profile linked exactly once to a platform user account identifier so that dormitory operations can reference a stable person record without tight database coupling to Identity.

**Why this priority**: CD-012 requires Employee to own `identity_id` assignment; all downstream specs (Request, Allocation) depend on a trustworthy Employee root.

**Independent Test**: Create Employee with valid `identity_id` via `IdentityUserReadContract` validation — verify immutability and rejection of unknown identifiers (BT-01, BT-03) without Request module.

**Acceptance Scenarios**:

1. **Given** a valid active Identity user identifier, **When** an administrator creates an Employee, **Then** `identity_id` is set once and persisted
2. **Given** an Employee with assigned `identity_id`, **When** a mutation of `identity_id` is attempted, **Then** the operation fails with a domain error (BT-02)
3. **Given** an unknown user identifier, **When** Employee creation is attempted, **Then** creation is rejected via Identity read contract (BT-03)
4. **Given** a disabled Identity user identifier, **When** Employee is created with valid `userExists`, **Then** creation succeeds — `isUserActive` is not a Wave 1A gate (OA-02-02 / BT-04 deferred; Employee may exist while Identity is inactive)

---

### User Story 2 - Department & Organizational Structure (Priority: P2)

As an HR administrator, I need to assign employees to departments so that approval routing and reporting can use organizational context.

**Why this priority**: Constitution and architecture place Department in Employee context; needed before Request workflows reference org structure.

**Independent Test**: CRUD department, assign employee to department, query employee by department — within Employee module only.

**Acceptance Scenarios**:

1. **Given** a department catalog, **When** an employee is assigned to a department, **Then** the assignment is persisted and queryable
2. **Given** a department with employees, **When** department is deactivated per policy, **Then** behavior is documented (no orphaned invalid states)

---

### User Story 3 - Dependent Records (Priority: P2)

As an HR administrator, I need to maintain dependent records attached to an employee so that family accommodation requests can reference dependents owned by Employee (CD-009).

**Why this priority**: CONF-DEP-01 / CD-009 — Dependent ∈ Employee; Request will hold snapshots/references only.

**Independent Test**: Add/update/list dependents for an employee; verify Request module not required.

**Acceptance Scenarios**:

1. **Given** an employee, **When** a dependent is added, **Then** dependent is owned by Employee aggregate scope
2. **Given** a dependent, **When** updated, **Then** changes remain within Employee context

---

### User Story 4 - Eligibility Computation (Priority: P3)

As the Request bounded context (future consumer), I need Employee to compute whether an employee is eligible to submit an accommodation request so that Request can enforce BR-01 at submission without owning eligibility logic (CD-013).

**Why this priority**: Recorded assumption for Wave 1A — establishes supplier API before spec05 Request.

**Independent Test**: Call eligibility service with fixture employee states — returns eligible/ineligible with reason codes; no Request persistence.

**Acceptance Scenarios**:

1. **Given** an active employee with no blocking allocation state, **When** eligibility is computed, **Then** result is eligible
2. **Given** an employee failing business rules (e.g., active allocation — detail in plan), **When** eligibility is computed, **Then** result is ineligible with stable reason code

---

### Edge Cases

- Duplicate Employee for same `identity_id`? (Must prevent or document uniqueness rule.)
- Employee created while Identity user becomes disabled mid-transaction? (Transaction boundary + contract re-check.)
- Dependent without parent Employee? (Rejected.)
- Cross-module direct query of `identity_users`? (Forbidden — architecture test BT-05.)

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST maintain **Employee** as root aggregate of Employee bounded context
- **FR-002**: System MUST store **`identity_id`** (immutable UUID reference to Identity User) on Employee — **no FK** to Identity tables (CD-012)
- **FR-003**: System MUST assign `identity_id` **exactly once** at Employee creation; mutation prohibited thereafter
- **FR-004**: System MUST validate `identity_id` via **`IdentityUserReadContract`** only — no cross-module Eloquent (FR-011 analog)
- **FR-005**: System MUST maintain **Department** entities and allow assigning employees to departments
- **FR-006**: System MUST maintain **Dependent** records as part of Employee context (CD-009)
- **FR-007**: System MUST expose **eligibility computation** for accommodation request submission (CD-013) — computation in Employee; enforcement deferred to spec05
- **FR-008**: System MUST record audit-relevant Employee lifecycle actions (constitution AP-06)

### Explicitly Out of Scope (Wave 1A)

- **FR-EX-001**: Request submission, approval workflow, request state (spec05)
- **FR-EX-002**: Allocation, lottery, dormitory inventory (spec04–spec07)
- **FR-EX-003**: Identity User lifecycle / RBAC (spec02 — frozen supplier)
- **FR-EX-004**: Login/session UX (OA-02-01 deferred)
- **FR-EX-005**: Reporting projections (OQ-08, spec11)

### Key Entities

- **Employee**: Organizational person record; owns `identity_id` reference; department assignment; audit timestamps.
- **Department**: Organizational unit for grouping employees.
- **Dependent**: Person dependent on an employee (CD-009); owned by Employee context.

---

## Success Criteria *(mandatory)*

- **SC-001**: BT-01–BT-03 boundary tests pass in spec03 test suite
- **SC-002**: Architecture test BT-05 passes — no Identity Infrastructure imports in Employee module
- **SC-003**: `identity_id` immutability enforced in domain and persistence layers
- **SC-004**: Eligibility API returns deterministic results for fixture scenarios (CD-013 baseline)
- **SC-005**: PHPStan L8 zero errors for Employee module paths (DoD)

---

## Assumptions & Recorded Decisions

### OA-03-01 — Identity attachment timing (DECIDED)

**Decision:** `identity_id` is set synchronously at Employee create using `IdentityUserReadContract` validation — no event-driven assignment required for Wave 1A (optional `EmployeeIdentityAssigned` event may be added in plan).

### OA-03-02 — Inactive Identity user (DECIDED — aligns CD-012 / boundary)

**Decision (Wave 1A):**
- **At create:** require `userExists` only (BT-03); do **not** block on `isUserActive` — HR may attach Employee to a disabled account.
- **After create:** if Identity user is deactivated, Employee record **remains**; no automatic Employee state change (OA-02-02 / BT-04 deferred to later wave).

### OA-03-03 — Eligibility inputs (DEFERRED to plan)

Full BR-01 inputs (active allocation check) may stub allocation read interface until spec07; Wave 1A delivers computation skeleton + employee-side rules.

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `spec-catalog.md` spec03 | Employee profiles, departments, dependents |
| CD-009 | Dependent ∈ Employee |
| CD-012 | `identity_id` attachment; Identity supplier |
| CD-013 | Eligibility computation ownership |
| `context-map.md` R1, R2 | Identity → Employee → Request |
| spec02 frozen contracts | `identity-employee-boundary.md`, `identity-read-service.md` |

---

## Consumer Language Guard (authoring)

- ✅ Employee **stores** `identity_id`; Identity does **not** know Employee
- ✅ Cross-context reads via `IdentityUserReadContract` only
- ❌ FK from `employees.identity_id` to `identity_users`
- ❌ `IdentityLinked` or linkage events owned by Identity

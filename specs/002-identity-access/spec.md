# Feature Specification: Identity & Access (spec02)

**Feature Branch**: `002-identity-access`

**Created**: 2026-06-26

**Status**: Draft — Wave 1A

**Catalog**: spec02 — **Authorized — Wave 1A** (Hard Freeze v1.0.0)

**Depends on**: spec01 Foundation (Approved)

**Input**: Establish the Identity bounded context: platform user accounts, role and permission baseline, account lifecycle (active/disabled), and read-only cross-context lookup by immutable account identifier — supplier-only, independent of any downstream consumer context.

**Normative boundary contract**: [`contracts/identity-employee-boundary.md`](./contracts/identity-employee-boundary.md) (CD-012). This spec does not define downstream consumer behavior.

**Domain events**: [`events.md`](./events.md)

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Platform User Account Lifecycle (Priority: P1)

As a platform administrator, I need to create and manage user accounts with a clear active/disabled lifecycle so that access to DormSys can be granted and revoked in a controlled, auditable way.

**Why this priority**: Every other capability that references a platform user depends on a stable account record and lifecycle. Without this, no downstream domain can attach to a user identity.

**Independent Test**: Can be fully tested within the Identity context alone — create account, verify unique immutable identifier is issued, disable account, confirm disabled accounts are distinguishable from active accounts — without any downstream consumer module.

**Acceptance Scenarios**:

1. **Given** no account exists for a new platform user, **When** an administrator registers a user account, **Then** the system issues a unique immutable account identifier and marks the account as active
2. **Given** an active user account, **When** an administrator disables the account, **Then** the account status becomes disabled and the change is auditable
3. **Given** a disabled user account, **When** a caller checks whether the account is active, **Then** the system reports the account as not active
4. **Given** a user account identifier, **When** a caller requests account existence, **Then** the system confirms existence or absence without exposing unrelated domain data

---

### User Story 2 - Role and Permission Baseline (Priority: P2)

As a platform administrator, I need to assign roles and permissions to user accounts so that authorization decisions can be enforced consistently across the platform.

**Why this priority**: DormSys is an enterprise system with role-based access. Roles and permissions must exist before operational features enforce access control.

**Independent Test**: Can be tested by creating a user, assigning a role with defined permissions, and verifying the user’s effective permission set — entirely within Identity & Access scope.

**Acceptance Scenarios**:

1. **Given** predefined platform roles, **When** an administrator assigns a role to a user, **Then** the user inherits the permissions associated with that role
2. **Given** a user with a role, **When** an administrator revokes the role, **Then** the associated permissions no longer apply to that user
3. **Given** a permission check for a user, **When** the user holds the required permission through role assignment, **Then** access is allowed
4. **Given** a permission check for a user, **When** the user lacks the required permission, **Then** access is denied

---

### User Story 3 - Cross-Context User Lookup (Supplier API) (Priority: P3)

As a downstream bounded context (external consumer), I need read-only ways to verify that a user account identifier exists and whether it is active so that I can store a valid reference to a platform user without tight database coupling.

**Why this priority**: Frozen architecture (CD-012) requires immutable identifier references without foreign keys. Identity must supply trustworthy read/query capabilities; consumers remain responsible for storing references.

**Independent Test**: Can be tested with stand-in consumer stubs — given a user identifier, verify existence, active status, and optional summary retrieval — without Identity knowing consumer-specific fields or aggregates.

**Acceptance Scenarios**:

1. **Given** a valid active user identifier, **When** a consumer asks whether the user exists, **Then** the system answers affirmatively
2. **Given** a valid active user identifier, **When** a consumer asks whether the user is active, **Then** the system answers affirmatively
3. **Given** an unknown user identifier, **When** a consumer asks whether the user exists, **Then** the system answers negatively without leaking internal errors
4. **Given** a disabled user identifier, **When** a consumer asks whether the user is active, **Then** the system answers negatively

---

### Edge Cases

- What happens when duplicate account creation is attempted for the same logical person? (System must prevent ambiguous duplicate accounts or enforce a documented uniqueness rule.)
- What happens when a consumer passes a malformed identifier? (System rejects with a clear validation outcome; no partial match.)
- What happens when a disabled account is referenced by an existing consumer-held identifier? (Identity reports inactive; consumer reaction is **out of scope** for spec02 — see OA-02-02.)
- What happens when role assignment references a non-existent role? (Operation fails with a clear error; no partial assignment.)
- What happens when the last administrator attempts to disable their own account? (System must prevent platform lockout or require a documented safeguard.)

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST maintain **User** accounts as the root aggregate of the Identity bounded context
- **FR-002**: System MUST assign each User account a **unique, immutable identifier** at account creation that never changes for the life of the account
- **FR-003**: System MUST support User account states of at least **active** and **disabled**
- **FR-004**: System MUST allow transition from active to disabled; reactivation policy is **out of scope** for Wave 1A unless explicitly added later
- **FR-005**: System MUST emit auditable lifecycle signals when a User account is created and when it is deactivated (see `events.md`)
- **FR-006**: System MUST provide **Role** and **Permission** constructs and support assigning roles to users for authorization baseline
- **FR-007**: System MUST allow authorized administrators to create users, assign/revoke roles, and disable users
- **FR-008**: System MUST expose **read-only** cross-context operations: verify user exists by identifier, verify user is active by identifier, and retrieve a minimal user summary for display (identifier + status + non-sensitive labels only)
- **FR-009**: Identity MUST NOT persist references to downstream consumer records (no consumer aggregate identifiers, no linkage tables owned by Identity)
- **FR-010**: Identity MUST NOT provide APIs that mutate data owned by downstream consumer contexts
- **FR-011**: Cross-context access MUST comply with modular monolith rules: no direct cross-module data store access by consumers; Identity exposes application-level read contracts only
- **FR-012**: System MUST record security-relevant Identity actions in the audit trail (integration with Audit capability per constitution)

### Explicitly Out of Scope (Wave 1A)

- **FR-EX-001**: Login flows, session management, password reset, MFA, and SSO (**OA-02-01 — deferred**)
- **FR-EX-002**: Storing or assigning consumer-held reference fields (e.g., identifiers persisted by downstream contexts) — owned by respective consumer specs
- **FR-EX-003**: Voucher eligibility (OQ-07, spec08)
- **FR-EX-004**: Reporting projections and analytics dimensions (OQ-08, spec11)
- **FR-EX-005**: End-user self-registration workflows (unless added in a later spec)

### Key Entities

- **User**: Platform account representing an authenticated principal; immutable account identifier; status (active/disabled); audit-relevant timestamps. Root aggregate for Identity.
- **Role**: Named collection of permissions assignable to users (e.g., administrator, operator — exact role catalog defined during planning).
- **Permission**: Atomic authorization capability checked by platform features (aligned with enterprise RBAC needs).

**Note:** “Identity” names the **bounded context**, not a separate aggregate. User is the aggregate root.

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An administrator can create a user account and obtain its immutable identifier in a single workflow without manual identifier assignment
- **SC-002**: 100% of permission checks for users with assigned roles return consistent allow/deny results in repeated verification tests
- **SC-003**: Cross-context existence and active-status queries return correct results for active, disabled, and unknown identifiers in acceptance test suite
- **SC-004**: All User create and deactivate operations produce audit-visible lifecycle records
- **SC-005**: Identity module passes architecture boundary tests — no imports of downstream consumer domain/infrastructure types

---

## Assumptions & Recorded Decisions

### OA-02-01 — Authentication behavior (DECIDED)

**Decision:** Authentication behavior (login flows, session establishment, credential policies) is **deferred** until real entry flows are defined in a later spec or phase.

**spec02 delivers:** User account lifecycle, Role/Permission baseline, read-only supplier lookup by account identifier, and lifecycle events — **not** interactive authentication UX or session management.

*Rationale:* spec-catalog open question resolved as documented non-blocking uncertainty per Hard Freeze v1.0.0. Avoids blocking Wave 1A supplier context on entry-flow design.

### OA-02-02 — Disabled user referenced by downstream consumer (DEFERRED)

When a User is disabled but a downstream context still holds a reference to that account identifier, consumer-side behavior is **not decided** in CD-012. Identity reports inactive; consumer reaction is deferred to the relevant consumer spec.

### OA-02-03 — Caching of cross-context lookups (DEFERRED)

Caching strategy for supplier lookup operations is deferred to implementation planning.

### Other Assumptions

- User-facing administration UI language is Persian (Farsi), RTL, per constitution localization
- Initial role catalog covers minimum platform administration needs; operational roles expand in later specs
- spec01 modular structure (`app/Modules/Identity/`) and shared kernel conventions apply
- UUID format aligns with platform foundation (immutable identifiers, no cross-module FK)

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `spec-catalog.md` spec02 | Scope: accounts, roles, permissions, access-control baseline |
| `context-map.md` R1, R12 | Identity upstream; User, Role, Permission ownership |
| CD-012 | Immutable identifier supplier; Identity does not know consumers |
| `contracts/identity-employee-boundary.md` | Normative cross-context contract (downstream consumer rules live there) |
| spec01 Foundation | Module scaffold, audit, architecture tests, UUID conventions |

---

## Supplier-Only Language Guard (authoring)

When reviewing this spec:

- ✅ Allowed: “downstream bounded context”, “external consumer”, “consumer-held reference”, “account identifier”
- ❌ Red flag: Identity “links to”, “knows”, “tracks”, or stores consumer-specific aggregates
- ❌ Red flag: User stories that require consumer domain nouns to complete Identity behavior

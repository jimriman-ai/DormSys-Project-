# Canonical Record: employee-request-self-service

# Boundary: HR Access Governance Cluster

# Phase: Spec / Domain Decision (Pre-Implementation)

# Naming convention: All roles and entities are recorded in English

# Last Updated: 2026-07-14

## Status

CLUSTER_PHASE: Pre-Implementation
ALL_ACTIVE_GAPS_IN_THIS_CLUSTER_CLOSED: false # confirmed — D-L6-2 / D-L6-4-C2 open; F-L7-9 residual E1/F deferred
IMPLEMENTATION_GATE_OPEN: true
IMPLEMENTATION_AUTHORIZED: true # scope-limited: HR Manager / student_records only
IMPLEMENTATION_SCOPE_NOTE: HR Manager / student_records cycle only
ACTIVE_OPEN_GAP: D-L6-4-C2 (student_records route); D-L6-2 (subject binding); F-L7-9 residual (E1, F deferred)

---

## HDAC-05 — Business Owner

Status: RESOLVED (Layer 1–2, Governance Decision)
Decision: Business Owner = HR Manager
Accountable Role: HR Manager
Change Authority: HR Manager
Note: Resolved at the governance layer (Business/Governance).
Implementation is downstream; this record does not open it.

---

## HDAC-06 — HR Manager Authority Scope

Status: RESOLVED (Layer 1–2, Governance Decision)
Decision:

- Access: HR Manager can read and edit student records across all departments within the authorized product surface; non-delegatable.

- Delegation: Non-delegatable
Note: Resolved at the governance layer.
Delegation enforcement mechanism is downstream (Layer 4).

### Permission Inventory (Ratified 2026-07-14)
| Permission              | Capability                        |
|-------------------------|-----------------------------------|
| student_records.read    | Read student records (all depts)  |
| student_records.edit    | Edit student records (all depts)  |

Role mapping: Display name: HR Manager | Spatie identifier: HRMgr

Subject binding for student_records.* — Deferred Pending Evidence (no Student entity in codebase)

### Guard Binding
- Auth guard for student_records flow: `api` (Identity `UserModel` provider)
- Authorization principal type: `UserModel` (unchanged)
- Spatie permission/role rows: guard name `web` (matches `UserModel::guardName()` / HRMgr seed)
- Decision: D-L6-4 A (2026-07-14)
- OPEN-1 RESOLVED: `App\Models\User` has no `HasRoles`
- OPEN-2 RESOLVED: default FormRequest `web` principal mismatched; FormRequest now uses `$this->user('api')`

Caveat: Feature tests that call the PEP directly with a `UserModel` bypass HTTP/guard. Green unit of PEP ≠ runtime-ready. HTTP-level test under `api` guard is required before closure.

### Runtime wiring (GAP — open)
- GAP-ID: D-L6-4-C2
- Status: OPEN
- Title: student_records edit route + `api` middleware group not wired
- Note: D-L6-4 A alone does not make the flow runtime-complete. Track separately; do not treat as deferred subject-binding.

### Enforcement Loci
| Permission           | Primary Locus                         | Supplementary         |
|----------------------|---------------------------------------|-----------------------|
| student_records.read | PolicyEnforcementPoint pattern        | —                     |
| student_records.edit | PolicyEnforcementPoint + Form Request | —                     |

Note: Middleware and Livewire component checks are supplementary only.
They are not the canonical authority contract.

---

## DDG-HR-ENTITY — HR Entity Definition

Status: RESOLVED (Layer 3, Domain Decision)
Decision:

- Type: Role assigned to the existing User entity
- Scope: System-wide
Downstream Gap (open, not blocking):
- Define HR as an entity/aggregate in the domain model
- Reopen: during the Domain Modeling phase, by explicit human decision

---

## DDG-HR-ACCESS-SCOPE — Access Enforcement Mechanism

Status: RESOLVED (Layer 4, Domain Decision)
Decision: Dynamic runtime check against the `roles` table

---

## DDG-HR-ROLE-LOCK — Role Table Structure

Status: RESOLVED (Layer 4, Domain Decision)
Decision: Entities `Role` + `Spatie morph (model_has_roles)` (pivot table `model_has_roles`) —
many-to-many relationship between `User` and `Role`

---

## DDG-HR-ROLE-ASSIGN-GUARD — Spatie vs Auth Guard Orthogonality

Status: RESOLVED
Layer: 4 (Implementation)
Scope: Guard binding evidence for student_records / Identity UserModel only.
Does not cover actor permission checks on AssignRoleToUserAction (see
DDG-IDENTITY-ROLE-ASSIGN-AUTHZ / F-L7-7).
Evidence: "Spatie guard = model guardName (‘web’), UserModel.php:39-42;
auth guard ‘api’ (driver: session, provider ‘identity’ ->
App\Modules\Identity\Infrastructure\Persistence\Models\UserModel,
auth.php:16-19, 28-31) is orthogonal to Spatie guard.
Verified at runtime by 8-test HTTP pass:
200 HRMgr / 403 Administrator / 401 guest under guard ‘api’."

---

## DDG-IDENTITY-ROLE-ASSIGN-AUTHZ — Role-Assign Actor Permission (F-L7-7)

Status: RESOLVED
Finding ID: F-L7-7
Severity: High
Domain: Identity / Mutation Authorization
Decision: D-L7-1 IMPLEMENTED
Capability key: identity.role.assign (MutationCapabilityCatalog::IDENTITY_ROLE_ASSIGN)
Actor permission: identity.roles.manage
Enforcement locus: IdentityMutationAuthorizationGate::assertAssignRole()
Enforcement mechanism: IdentityUserReadContract::userHasPermission (fail-closed via
UserRepository::checkPermissionTo); no direct Eloquent/UserModel access in the gate
Verification:
- php artisan test tests/Feature/Modules/Identity/IdentityRoleAssignAuthorizationTest.php
  tests/Feature/Modules/Identity/RoleAssignmentTest.php — 10 passed
- php artisan test (Identity + Development + RoleAssign set) — 43 passed
- Consumers: IdentityAuditIntegrationTest + CheckInBoundaryTest + ApiAuthSessionEntryTest
  — 13 passed
- php vendor/bin/phpstan analyse … IdentityMutationAuthorizationGate.php
  DevelopmentUserProvisioner.php IdentityRoleSeeder.php — 0 errors
Current exposure closed for AssignRoleToUserAction central gate; no HTTP role-assignment
surface added
Scope statement: independent Identity authorization GAP; not D-L6-2 and not D-L6-4-C2

Open backlog (separate): production role→permission ownership of identity.roles.manage
beyond pre-existing Spec02 IdentityRoleSeeder SystemAdministrator syncPermissions —
D-L7-1 did not authorize new production role grants; HRMgr / Administrator unchanged.

---

## F-L7-9 — Test Adequacy (Identity / HR Access Boundary)

Status: CLOSED_ACTIONABLE_COVERED
Finding ID: F-L7-9
Domain: Identity / HR Access / Mutation Authorization — test suite coverage
Decision: D-L7-2
Note: Actionable gaps A1/A2/C/D1/D2/E2 covered by L8 tests. Full F-L7-9
closure remains blocked on D-L6-4-C2 (deferred gaps E1, F).
No production role mapping or authorization contract change.
Verification (2026-07-14): full-suite run — 92 passed / 253 assertions;
target files HRManagerStudentRecordsAuthTest + IdentityRoleAssignAuthorizationTest
— 22 tests / 41 assertions.

### ACTIONABLE_NOW → COVERED (L8)

| ID | Gap | Severity | Evidence |
|----|-----|----------|----------|
| A1 | EditStudentRecordRequest::authorize() with App\Models\User (web guard) | HIGH | `tests/Feature/Auth/HRManagerStudentRecordsAuthTest.php` — `denies edit authorize when credential web user is authenticated` |
| A2 | authorize() unauthenticated / FormRequest false path | MEDIUM | `tests/Feature/Auth/HRManagerStudentRecordsAuthTest.php` — `authorize returns false for unauthenticated form request` |
| C | Actor HRMgr without identity.roles.manage | HIGH | `tests/Feature/Modules/Identity/IdentityRoleAssignAuthorizationTest.php` — `denies role assign when actor is hrmgr without roles manage` |
| D1 | PEP canEdit(null) | LOW | `tests/Feature/Auth/HRManagerStudentRecordsAuthTest.php` — `denies edit when user is null` |
| D2 | PEP with wrong-type App\Models\User | MEDIUM | `tests/Feature/Auth/HRManagerStudentRecordsAuthTest.php` — `rejects wrong principal type on pep` |
| E2 | api middleware + web user (scaffold on temp route) | HIGH | `tests/Feature/Auth/HRManagerStudentRecordsAuthTest.php` — `rejects api student edit when only web guard authenticated` |

### DEFERRED_PENDING_D-L6-4-C2

| ID | Gap | Severity |
|----|-----|----------|
| E1 | canRead via real HTTP route | MEDIUM |
| F | missing api middleware enforcement | HIGH |

---

## Confirmation

HDAC05_RESOLVED_AT_GOVERNANCE_LAYER: true
HDAC06_RESOLVED_AT_GOVERNANCE_LAYER: true
DDG_HR_ENTITY_RESOLVED: true
DDG_HR_ACCESS_SCOPE_RESOLVED: true
DDG_HR_ROLE_LOCK_RESOLVED: true
DDG_HR_ROLE_ASSIGN_GUARD_DEFERRED: false
DDG_IDENTITY_ROLE_ASSIGN_AUTHZ_OPEN: false
F_L7_9_STATUS: CLOSED_ACTIONABLE_COVERED
NO_GATE_OPENED: false
NO_IMPLEMENTATION_DETAIL_IN_GOVERNANCE_DECISIONS: true
ALL_ACTIVE_GAPS_IN_THIS_CLUSTER_CLOSED: false
IMPLEMENTATION_AUTHORIZED: true
IMPLEMENTATION_AUTHORIZED_SCOPE: HR Manager / student_records cycle only

---

## Decision Ledger (closure log)

D-L6-4: CLOSED — Option A ratified. EditStudentRecordRequest::authorize()
resolves principal via $this->user(‘api’) -> UserModel (HasRoles).

D-L6-5: RESOLVED — permissions + role ‘HRMgr’ seeded with guard_name=‘web’,
matching UserModel::guardName(); no seeder change required.

D-L6-4-C2: OPEN — Deferred Pending Evidence. No production route/middleware
for student-record edit flow; blocked on student entity (aligned with D-L6-2).

D-L6-2: Deferred Pending Evidence — subject/student entity binding for
student_records.* unchanged by F-L7-7.

D-L7-1: IMPLEMENTED — IdentityMutationAuthorizationGate::assertAssignRole()
requires active principal + IdentityUserReadContract::userHasPermission
(..., identity.roles.manage). F-L7-7 / DDG-IDENTITY-ROLE-ASSIGN-AUTHZ RESOLVED
after IdentityRoleAssignAuthorizationTest + related Identity suites passed.

D-L7-2: L8 actionable-gap work DONE — F-L7-9 gaps A1, A2, C, D1, D2, E2
COVERED (CLOSED_ACTIONABLE_COVERED). Deferred branch (E1, F) unchanged —
blocked pending D-L6-4-C2 resolution. Full F-L7-9 closure remains open on E1/F.
Verification 2026-07-14: 92 passed / 253 assertions (full suite cited);
target files 22 tests / 41 assertions.

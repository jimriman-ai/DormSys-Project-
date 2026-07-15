# Canonical Record: employee-request-self-service

# Boundary: HR Access Governance Cluster

# Phase: Phase E CLOSED → Ready for Phase F (Auth Gate)

# Naming convention: All roles and entities are recorded in English

# Last Updated: 2026-07-15

## Status

CLUSTER_PHASE: Phase_E_CLOSED_READY_FOR_PHASE_F_AUTH_GATE
PHASE_E_EXIT_READINESS: CLOSED
PHASE_F_AUTH_GATE: READY
ALL_PHASE_D_GAPS_CLOSED: true
# Phase D closures: D-L6-4-C2, D-L6-5-DORMSTRUCT, D-L6-6-ROLESURFACE (+ security findings)
ALL_ACTIVE_GAPS_IN_THIS_CLUSTER_CLOSED: false # non-blocking: identity.roles.manage production ownership (TEST_ONLY_PERMISSION) deferred
IMPLEMENTATION_GATE_OPEN: true
IMPLEMENTATION_AUTHORIZED: true # scope-limited: HR Manager / employee_records only
IMPLEMENTATION_SCOPE_NOTE: HR Manager / employee_records cycle only
ACTIVE_OPEN_GAP: identity.roles.manage production ownership backlog (non-blocking for Phase F Auth Gate)

Operational note: Full-suite acceptance runs MUST be exclusive: no concurrent
php artisan test processes against the shared testing database. Contention
symptoms (deadlocks, missing tables, seed races) invalidate the run.

### Phase E — Exit Readiness Cleanup (CLOSED 2026-07-15)

Confirmed closed:

| Gap | Status |
|-----|--------|
| D-L6-4-C2 | CLOSED |
| D-L6-5-DORMSTRUCT | CLOSED (TEST-SETUP DEFECT; exclusive acceptance green) |
| D-L6-6-ROLESURFACE | CLOSED |
| S2-I3 last-admin | FIXED |
| S2-I4 transactional delete + lockForUpdate | FIXED |
| S3 trim | FIXED |
| S5 audit emit | FIXED |

Final gate (post findings resolution): **1841 passed / 0 failed** (4 skipped);
PHPStan 0 errors; Pint pass. Governance artifacts verified. No blocking TODO/FIXME.

**STATUS: Phase E CLOSED → Ready for Phase F (Auth Gate)**

---

## Auth Principal Contract

Recorded: 2026-07-15 (evidence: `config/auth.php`, `app/Models/User.php`,
`app/Modules/Identity/Infrastructure/Persistence/Models/UserModel.php`).

Guard `web` → driver `session` → provider `users` →
`App\Models\User` (credential model; overridable via `AUTH_MODEL` env).

Guard `api` → driver `session` → provider `identity` →
`App\Modules\Identity\Infrastructure\Persistence\Models\UserModel`
(table `identity_users`; RBAC principal; password login out of scope;
`getAuthPassword()` throws — `UserModel.php:79-82`).

Spatie note: `UserModel::guardName()` returns `'web'` (`UserModel.php:54-57`)
even though auth uses guard `api` — therefore ALL Spatie role/permission rows
for Identity principals must carry `guard_name = 'web'`.

### OPEN QUESTIONS (do not resolve here)

- Runtime values of `AUTH_GUARD` / `AUTH_MODEL` env.
- Physical table of `App\Models\User` (not declared on the model class).
- DB connection inheritance via `BaseModel` (not inspected in this record).
- No password broker entry for provider `identity` in `config/auth.php`.

### Rule going forward

Tests and helpers that type-hint the auth principal MUST use `UserModel`
(`api` guard), never `App\Models\User`.

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

- Access: HR Manager can read and edit employee records across all departments within the authorized product surface; non-delegatable.

- Delegation: Non-delegatable
Note: Resolved at the governance layer.
Delegation enforcement mechanism is downstream (Layer 4).

### Permission Inventory (Re-ratified 2026-07-15)

| Permission              | Capability                         |
|-------------------------|------------------------------------|
| employee_records.read   | Read employee records (all depts)  |
| employee_records.edit   | Edit employee records (all depts)  |

Supersedes inventory ratified 2026-07-14 (`student_records.read` / `student_records.edit`).
Terminology alignment only; authority scope unchanged. No Employee  entity authorized.

Role mapping: Display name: HR Manager | Spatie identifier: HRMgr

Subject binding for employee_records.* — Employee aggregate (`app/Modules/Employee`).
No Employee entity is introduced here. Record-level checks may still be layered later.
Allocation continues to use generic `person_id` / person abstractions (unchanged).

### Guard Binding

- Auth guard for employee_records flow: `api` (Identity `UserModel` provider)
- Authorization principal type: `UserModel` (unchanged)
- Spatie permission/role rows: guard name `web` (matches `UserModel::guardName()` / HRMgr seed)
- Decision: D-L6-4 A (2026-07-14)
- OPEN-1 RESOLVED: `App\Models\User` has no `HasRoles`
- OPEN-2 RESOLVED: default FormRequest `web` principal mismatched; FormRequest now uses `$this->user('api')`

Caveat: Feature tests that call the PEP directly with a `UserModel` bypass HTTP/guard. Green unit of PEP ≠ runtime-ready. HTTP-level test under `api` guard is required before closure.

### Runtime wiring (GAP — closed)

- GAP-ID: D-L6-4-C2
- Status: CLOSED
- Title: employee_records production route wired under auth:api
- Evidence: `routes/web.php` resource `employee-records` → `EmployeeRecordController`;
  middleware `auth:api` + FormRequest/PEP enforcement;
  `tests/Feature/EmployeeRecord/EditEmployeeRecordAuthzTest.php`.

### Enforcement Loci

| Permission            | Primary Locus                         | Supplementary         |
|-----------------------|---------------------------------------|-----------------------|
| employee_records.read | PolicyEnforcementPoint pattern        | —                     |
| employee_records.edit | PolicyEnforcementPoint + Form Request | —                     |

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
Scope: Guard binding evidence for employee_records / Identity UserModel only.
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

### ACTIVE_OPEN_GAP — identity.roles.manage production ownership

Status: **OPEN** (triaged 2026-07-15; no implementation; no seeder/config change)

#### Q1–Q6 Evidence Table

| Q | Finding | Citation |
|---|---------|----------|
| Q1 | Permission **DEFINED** in production seeder `IdentityRoleSeeder`: constant + `PERMISSIONS` list + `Permission::findOrCreate($permissionName, $guard)` with `$guard = 'web'`. No migration/enum/config definition found under `database/` beyond this seeder. **guard_name: `web`.** | `database/seeders/IdentityRoleSeeder.php:31`, `:47`, `:68-71` |
| Q2 | Production role grant: **`SystemAdministrator`** only (`Role::findOrCreate(..., $guard)` + `syncPermissions` including `identity.roles.manage`). Role `guard_name`: `web`. HRMgr / Administrator are **not** granted this permission in the seeder. Comments state D-L7-1 did not authorize new production role grants. **Not** `PRODUCTION_HOLDER: NONE`. | `IdentityRoleSeeder.php:74-81`; role name via `PlatformRoles::SYSTEM_ADMINISTRATOR` = `'SystemAdministrator'` (`app/Modules/Identity/Domain/PlatformRoles.php:9`); `IdentityRoleSeeder.php:16` |
| Q3 | Enforcement: `IdentityMutationAuthorizationGate::assertAssignRole()` checks `identity.roles.manage` via `IdentityUserReadContract::userHasPermission`. Called from `AssignRoleToUserAction::execute()`. **MISSING** — no FormRequest / middleware / Livewire / Policy check of this permission found under `app/Modules/Identity/Presentation` or `routes/`. Note: `assertRevokeRole()` does **not** check this permission. | Gate: `IdentityMutationAuthorizationGate.php:17`, `:34-42`; Action: `AssignRoleToUserAction.php:28` |
| Q4 | Tests obtain permission via **direct** `givePermissionTo` through helper `grantIdentityRolesManagePermission()` (`Permission::findOrCreate(..., 'web')` then grant on `UserModel`). Helper comment: “Test-only … Not a production role→permission mapping decision.” Call sites: `IdentityRoleAssignAuthorizationTest`, `IdentityEmployeeMutationAuthorizationTest`. Also used by `assignRoleThroughMutation()`. Separately, `DevelopmentUserProvisioner::ensureActorCanAssignRoles()` direct-grants for development flows — **not** a production role mapping. Tests do **not** primarily mirror the SystemAdministrator production mapping. | `tests/Support/mutation-acting.php:63-75`, `:89-92`; tests cited above; `DevelopmentUserProvisioner.php:278-290` |
| Q5 | All production definitions/grants and test `findOrCreate` use **`guard_name = 'web'`**, matching `UserModel::guardName()`. **No GUARD_MISMATCH** found for this permission in Q1–Q4 paths. | Seeder `:68-71`, `:76`; mutation-acting `:69`; UserModel `:54-57` |
| Q6 | **NO_PRODUCTION_SURFACE** — no matches for AssignRole / role-assign routes under `routes/`; no Presentation Livewire/Controller role-management surface under `app/Modules/Identity/Presentation`. Governance already notes “no HTTP role-assignment surface added.” | `routes/` grep empty; Presentation grep empty; this file DDG-IDENTITY-ROLE-ASSIGN-AUTHZ § “Current exposure…” |

#### CLASSIFICATION: `TEST_ONLY_PERMISSION`

Permission is defined and enforced in Application-layer mutation code, and
`SystemAdministrator` already holds it in the production seeder
(`IdentityRoleSeeder.php:76-81`). There is **no** production HTTP/UI surface that
exercises role assignment today (Q6). Tests and development provisioners
synthesize the actor permission via direct grants rather than relying on the
SystemAdministrator role mapping (Q4). Expanding ownership to HRMgr/Administrator
(or adding a surface) is a product/governance decision for when a real
role-management UI/route exists — **not** a same-round seeder/code fix, and **not**
SEED_GAP (a production holder already exists). Guard rows are consistent (`web`).

#### PROPOSED remediation scope (NOT executed)

- **Files a future fix might touch (only after human approval):**
  - `database/seeders/IdentityRoleSeeder.php` — if human decides additional
    production roles must hold `identity.roles.manage`, or to document
    SystemAdministrator as intentional sole production holder in seeder comments.
  - Optional later: Presentation/routes if a production role-assign surface is
    authorized (out of current cluster scope).
- **Expected test impact:** If ownership stays SystemAdministrator-only with
  surface deferred — tests remain on `grantIdentityRolesManagePermission`
  synthesis; maybe add one optional assertion that SystemAdministrator role
  implies the permission after seed. If roles are granted to additional roles —
  update `IdentityRoleAssignAuthorizationTest` “hrmgr without roles manage”
  expectations and any role matrix docs.
- **Human decision required:** Confirm whether `SystemAdministrator` is the
  **sole intentional** production holder until a role-management surface is
  authorized; OR approve expanding grants; OR authorize building a production
  AssignRole UI/route (separate surface re-authorization).

Gap remains **OPEN**. No seeder/config/app/test change in this triage round.

### D-L6-6-ROLESURFACE — Production API surface for identity.roles.manage

Status: **IMPLEMENTED** (2026-07-15)

Routes under `/api/identity` (middleware: `auth:api`,
`permission:identity.roles.manage,api`, `request.mutation.principal`,
`audit.principal`):

- `GET|POST /roles`, `PATCH|DELETE /roles/{role}`, `GET /roles/{role}/users`
- `PUT /users/{user}/roles` (atomic sync)

Invariants: I1 new roles `guard_name=web`; I2 SystemAdministrator rename/delete → 409;
I3 cannot remove SystemAdministrator from self → 409; I4 delete role with users → 409.
Permission-to-role assignment remains out of scope (seeder-only / runtime read-only).

Note: Spatie middleware requires `,api` guard argument so Auth resolves Identity
`UserModel` (default auth guard is `web` credential model). Application gate
`assertManageRoles()` remains defense-in-depth.

Verification: `php artisan test --filter=Role` — 45 passed.

### D-L6-6-ROLESURFACE — L7 Security Review + L8 Acceptance (2026-07-15)

#### S1 Guard-resolution chain (OK)

1. `routes/api.php` — middleware `auth:api` then `permission:identity.roles.manage,api`
2. Spatie `PermissionMiddleware` → `Auth::guard('api')->user()` → Identity `UserModel`
3. `UserModel::guardName()` returns `'web'` → Spatie permission lookup on `guard_name=web`
4. Seeded permission `identity.roles.manage` rows use `guard_name=web` (`IdentityRoleSeeder`)
5. No path authenticates credential `App\Models\User` for this surface when `,api` is present.

#### Security findings table

| ID | Severity | Location | Status |
|----|----------|----------|--------|
| S1 | — | Guard chain api→UserModel→Spatie web | OK |
| S2-I2 | — | RenameRoleAction exact name match; case/whitespace payload tests | OK |
| S2-I3 | HIGH | SyncUserRolesAction self-only; no last-admin-overall | FINDING |
| S2-I4 | MEDIUM | DeleteRoleAction count then delete — not one transaction | FINDING |
| S3-sync | — | SyncUserRolesRequest exists+guard web; api role → 422 | OK |
| S3-store-trim | LOW | StoreRoleRequest no `trim`/prepareForValidation | FINDING |
| S4 | — | assertManageRoles without HTTP middleware (defense test) | OK |
| S5-principal | — | request.mutation.principal sets audit_principal from api UserModel | OK |
| S5-audit-emit | MEDIUM | Create/Rename/Delete/Sync do not call IdentityAuditEmitter | FINDING |
| A1-MPEP | — | Wired IDENTITY_ROLE_MANAGE + MPEP for architecture boundary | OK (gate fix) |

#### L8 Acceptance

- A1 `php artisan test` — **1838 passed / 0 failed / 4 skipped** (1842 total; 5180 assertions)
- A2 `php vendor/bin/phpstan analyse --no-progress` — **0 errors**
- A3 `php vendor/bin/pint --test` — **passed**

**Verdict: FINDINGS_BLOCK** — L8 tools green; L7 residual FINDINGs (I3 HIGH last-admin-overall missing; I4 MEDIUM TOCTOU; S3-store-trim LOW; S5-audit-emit MEDIUM) block security CLOSE of D-L6-6. Do not expand scope in this gate; schedule remediation after human priority on I3.

### D-L6-6-ROLESURFACE — Finding Resolution Round (2026-07-15)

| Finding | Fix | Status |
|---------|-----|--------|
| S2-I3 | `SyncUserRolesAction` last-admin via `countSystemAdministratorsExcluding` → `LastSystemAdministratorException` 409 | FIXED |
| S2-I4 | `RoleRepository::deleteWebRole` — `DB::transaction` + `lockForUpdate` + `UserModel::role` count | FIXED |
| S5-audit | `IdentityAuditEmitter` role.created/updated/deleted + user.roles.synced; `AuditEventType` extended | FIXED |
| S3-trim | `StoreRoleRequest` / `UpdateRoleRequest` `prepareForValidation` trim | FIXED |

L8 re-run: A1 **1841 passed / 0 failed** (1845 total, 4 skipped); A2 PHPStan **0**; A3 Pint **pass**.

**Verdict: CLOSE_READY**

---

## F-L7-9 — Test Adequacy (Identity / HR Access Boundary)

status: CLOSED_COVERED
gaps_covered: [A1, A2, C, D1, D2, E2, E1, F]
gaps_deferred: []
deferred_blocked_on: none
verification: 2026-07-15 | post D-L6-4-C2 suite 1819 tests — 1746 passed / 16 failed / 4678 assertions
Finding ID: F-L7-9
Domain: Identity / HR Access / Mutation Authorization — test suite coverage
Decision: D-L7-2
Note: Actionable gaps A1/A2/C/D1/D2/E2 covered by L8; E1/F covered by production-route
EditEmployeeRecordAuthzTest after D-L6-4-C2. No production role mapping change.

### ACTIONABLE_NOW → COVERED (L8)

| ID | Gap | Severity | Evidence |
|----|-----|----------|----------|
| A1 | EditEmployeeRecordRequest::authorize() with App\Models\User (web guard) | HIGH | `tests/Feature/Auth/HRManagerEmployeeRecordsAuthTest.php` — `denies edit authorize when credential web user is authenticated` |
| A2 | authorize() unauthenticated / FormRequest false path | MEDIUM | `tests/Feature/Auth/HRManagerEmployeeRecordsAuthTest.php` — `authorize returns false for unauthenticated form request` |
| C | Actor HRMgr without identity.roles.manage | HIGH | `tests/Feature/Modules/Identity/IdentityRoleAssignAuthorizationTest.php` — `denies role assign when actor is hrmgr without roles manage` |
| D1 | PEP canEdit(null) | LOW | `tests/Feature/Auth/HRManagerEmployeeRecordsAuthTest.php` — `denies edit when user is null` |
| D2 | PEP with wrong-type App\Models\User | MEDIUM | `tests/Feature/Auth/HRManagerEmployeeRecordsAuthTest.php` — `rejects wrong principal type on pep` |
| E2 | api middleware + web user (scaffold on temp route) | HIGH | `tests/Feature/Auth/HRManagerEmployeeRecordsAuthTest.php` — `rejects api employee edit when only web guard authenticated` |

### E1/F → CLOSED_COVERED (production route)

| ID | Gap | Severity | Evidence |
|----|-----|----------|----------|
| E1 | canRead via real HTTP route | MEDIUM | `tests/Feature/EmployeeRecord/EditEmployeeRecordAuthzTest.php` — production `/employee-records/{id}` getJson allow/deny |
| F | missing api middleware enforcement | HIGH | same file — unauthenticated put redirects to `/login` under auth:api group |

---

## F-L7-6 — EditEmployeeRecordRequest Validation Contract

Status: CLOSED
Finding ID: F-L7-6
Domain: Identity / HR Access — FormRequest validation
Evidence: `EditEmployeeRecordRequest::authorize()` → EmployeeRecordsPolicyEnforcementPoint::canEdit;
`rules()` populated from employee_employees / EmployeeHub field constraints
(employee_code, first_name, last_name, national_code, department_id, hire_date,
base_lottery_score, status).

---

## Confirmation

HDAC05_RESOLVED_AT_GOVERNANCE_LAYER: true
HDAC06_RESOLVED_AT_GOVERNANCE_LAYER: true
DDG_HR_ENTITY_RESOLVED: true
DDG_HR_ACCESS_SCOPE_RESOLVED: true
DDG_HR_ROLE_LOCK_RESOLVED: true
DDG_HR_ROLE_ASSIGN_GUARD_DEFERRED: false
DDG_IDENTITY_ROLE_ASSIGN_AUTHZ_OPEN: false
F_L7_9_STATUS: CLOSED_COVERED
F_L7_6_STATUS: CLOSED
D_L6_4_C2_STATUS: CLOSED
D_L6_5_DORMSTRUCT_STATUS: CLOSED
D_L6_6_ROLESURFACE_STATUS: CLOSED
PHASE_E_EXIT_READINESS: CLOSED
PHASE_F_AUTH_GATE: READY
NO_GATE_OPENED: false
NO_IMPLEMENTATION_DETAIL_IN_GOVERNANCE_DECISIONS: true
ALL_ACTIVE_GAPS_IN_THIS_CLUSTER_CLOSED: false # ownership backlog non-blocking
ALL_PHASE_D_GAPS_CLOSED: true
IMPLEMENTATION_AUTHORIZED: true
IMPLEMENTATION_AUTHORIZED_SCOPE: HR Manager / employee_records cycle only

---

## Decision Ledger (closure log)

D-L6-4: CLOSED — Option A ratified. EditEmployeeRecordRequest::authorize()
resolves principal via $this->user(‘api’) -> UserModel (HasRoles).
(Historical name: EditStudentRecordRequest; renamed 2026-07-15 terminology alignment.)

D-L6-5: RESOLVED — permissions + role ‘HRMgr’ seeded with guard_name=‘web’,
matching UserModel::guardName(); no seeder change required.

D-L6-4-C2: CLOSED — Production route wired: Route::resource('employee-records',
EmployeeRecordController::class)->only([...]) under auth:api group in routes/web.php.
Controller + EditEmployeeRecordRequest PEP enforcement; Authz tests on real routes.

D-L6-2: CLOSED (2026-07-15) — Subject for employee_records.*= Employee aggregate.
Terminology alignment from legacy student_records.*; no Employee  entity.
Unchanged by F-L7-7. Allocation person_id abstractions preserved.

D-L6-2-TERM: RATIFIED (2026-07-15) — Human re-ratification of permission inventory:
employee_records.read / employee_records.edit supersede student_records.read /
student_records.edit. Authority scope unchanged. Historical logs not rewritten.

D-L7-1: IMPLEMENTED — IdentityMutationAuthorizationGate::assertAssignRole()
requires active principal + IdentityUserReadContract::userHasPermission
(..., identity.roles.manage). F-L7-7 / DDG-IDENTITY-ROLE-ASSIGN-AUTHZ RESOLVED
after IdentityRoleAssignAuthorizationTest + related Identity suites passed.

D-L7-2: L8 actionable-gap work DONE (A1/A2/C/D1/D2/E2 covered).
E1/F CLOSED_COVERED via EditEmployeeRecordAuthzTest on production routes after
D-L6-4-C2. Full F-L7-9 status CLOSED_COVERED.

F-L7-6: CLOSED — EditEmployeeRecordRequest rules() populated; authorize() via PEP.

BASELINE-PRE-D-L6-4-C2 (2026-07-15): Test baseline before D-L6-4-C2 wiring —
1814 tests — 1731 passed / 16 failed / 4645 assertions.
The 16 failures are classified PRE_EXISTING, all sharing root cause
UnauthorizedDormitoryStructureAccessException at
DormitoryStructureAuthorizationGate.php:59 (test actors in
Allocation/Request modules lack dormitory.structure.* grants).
Attribution: NOT caused by D-L6-2 terminology migration
(RENAME_CAUSED: 0). Tracked under D-L6-5-DORMSTRUCT (CLOSED — see
POST-D-L6-5-DORMSTRUCT-R3).
Purpose: any failure appearing after D-L6-4-C2 wiring that is NOT in
this list of 16 is a NEW regression attributable to this wave.

D-L6-5-DORMSTRUCT: CLOSED — Classification: TEST-SETUP DEFECT (not an
authorization-scope defect). Root cause: test actors in Allocation/Request/
Mission/Lottery flows lacked the dormitory.structure.view grant expected by
DormitoryStructureAuthorizationGate (VIEW or MANAGE). Resolution: Option A
across 3 rounds; production gate/policy/seeder untouched except the
human-approved formatting-only Pint exception on Request DTOs.
Confirm: RENAME_CAUSED: 0 / PRE_EXISTING: 16 (no relation to student→employee
migration).

POST-D-L6-5-DORMSTRUCT-R3 (2026-07-15): Final exclusive acceptance —
1815 passed / 0 failed / 0 errors; PHPStan green; Pint green.
Intermediate contention full-suite run INVALIDATED (cause: concurrent
php artisan test processes on shared testing DB); superseded by exclusive run.
Pint-guard verdict: PASS — `git diff -w --` on
EmployeeRequestListQueryDTO.php, PaginatedRequestSummaryListDTO.php,
RequestEmployeeListFilterOptions.php is EMPTY (worktree blobs identical to
HEAD; no non-whitespace residual). R1→R2 resolved baseline 16 + 5 follow-on
errors via test helpers only; R3 closed PHPStan (tests/) under local contract.
Helpers/tests touched (R1–R3): structure-authorization.php;
Request/Lottery/Allocation http-mutation + mutation-principal helpers;
DormitoryReadIntegrationTest; MissionRequestTest; FamilyDirectSnapshotTest;
HRManagerEmployeeRecordsAuthTest; DormitoryStructureAuthorizationBindingTest.

D-L6-6-ROLESURFACE: IMPLEMENTED (2026-07-15) — HTTP API under `/api/identity`
for identity.roles.manage holders. Verified: php artisan test --filter=Role
(45 passed). Ownership backlog (ACTIVE_OPEN_GAP) remains OPEN separately.

D-L6-6-FINDINGS-FIX: CLOSED (2026-07-15) — I3 last-admin, I4 delete lock,
audit emit, name trim. L8: 1841 passed / 0 failed; PHPStan 0; Pint pass.
Verdict CLOSE_READY.

PHASE-E-EXIT: CLOSED (2026-07-15) — Exit Readiness Cleanup complete.
Phase D gaps D-L6-4-C2 / D-L6-5-DORMSTRUCT / D-L6-6-ROLESURFACE closed;
ROLESURFACE security findings resolved. Ready for Phase F (Auth Gate).
Ownership backlog remains OPEN non-blocking (TEST_ONLY_PERMISSION).

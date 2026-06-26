# Tasks: Identity & Access (spec02)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md) (§8 scope lock), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/), [events.md](./events.md), [quickstart.md](./quickstart.md)

**Branch**: `002-identity-access`

**Scope guards** (from plan.md §8):

- **Auth (OA-02-01):** No login/session/Fortify/Sanctum tasks
- **Authorization:** RBAC via Spatie only
- **Events:** Exactly `UserCreated` + `UserDeactivated` (sync); no `IdentityLinked`, no outbox

**Status**: Wave 1A implementation complete (T001–T034, T038–T042). Livewire admin T035–T037 deferred per plan §Phase E.

---

## Phase Summary

| Phase | Purpose | Task IDs | MVP? |
|-------|---------|----------|------|
| 1 — Setup | Spatie RBAC wiring, module paths | T001–T004 | Yes |
| 2 — Foundational | User aggregate + persistence (blocks all stories) | T005–T012 | Yes |
| 3 — US1 (P1) | Lifecycle, events, audit | T013–T021 | Yes |
| 4 — US2 (P2) | Roles & permissions | T022–T027 | No |
| 5 — US3 (P3) | Supplier read contract FR-008 | T028–T034 | No |
| 6 — Polish | Admin UI tail, quality gates | T035–T042 | Optional (UI deferred) |

**Total tasks:** 42

---

## User Story Mapping

| Story | Priority | Phases | Independent Test |
|-------|----------|--------|------------------|
| US1 — Platform User Account Lifecycle | P1 | 2→3 | Create user → UUID v7 → disable → inactive check (no consumer module) |
| US2 — Role and Permission Baseline | P2 | 4 | Assign/revoke role; permission allow/deny |
| US3 — Cross-Context User Lookup | P3 | 5 | `IdentityUserReadContract` via stub consumer; arch boundary |

**Suggested MVP:** Complete Phases 1–3 (T001–T021) — delivers US1 with events and audit.

---

## Dependency Graph

```text
Phase 1 (Setup)
    └── Phase 2 (Foundational) ──blocks──► Phase 3 (US1)
                                              ├──► Phase 4 (US2)  [needs UserModel + Spatie]
                                              └──► Phase 5 (US3)  [needs User + repository]
Phase 6 (Polish) — after US1 minimum; Livewire after US2 recommended
```

**Parallel opportunities:** Tasks marked `[P]` within a phase touch different files and have no ordering dependency on other `[P]` tasks in the same phase.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: RBAC package configuration and Identity module migration path — no authentication gateway.

- [x] T001 Publish Spatie permission config to `config/permission.php` and set `models` / guard to Identity `UserModel` per `data-model.md`
- [x] T002 [P] Ensure `database/migrations/modules/identity/` exists and is loaded by `app/Modules/Identity/Infrastructure/Providers/IdentityServiceProvider.php`
- [x] T003 [P] Add Spatie permission table migrations under `database/migrations/modules/identity/` (roles, permissions, pivots)
- [x] T004 [P] Create `database/seeders/IdentityRoleSeeder.php` skeleton with `SystemAdministrator` role and `identity.*` permissions per `data-model.md`

**Checkpoint**: Spatie tables migratable; seeder class exists — no Fortify/Sanctum/login routes added.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: User aggregate root and persistence — MUST complete before US1/US2/US3.

**⚠️ CRITICAL**: No user story work until this phase is complete.

- [x] T005 Create `UserId` value object in `app/Modules/Identity/Domain/ValueObjects/UserId.php` with `fromString()` validation
- [x] T006 [P] Create `UserStatus` enum in `app/Modules/Identity/Domain/Enums/UserStatus.php` (`Active`, `Disabled`)
- [x] T007 [P] Create domain exceptions in `app/Modules/Identity/Domain/Exceptions/` (e.g. `UserNotFoundException`, `InvalidUserStateTransitionException`, `DuplicateUserEmailException`)
- [x] T008 Create migration `database/migrations/modules/identity/*_create_identity_users_table.php` per `data-model.md` (UUID PK, status, display_name, email unique nullable, audit columns)
- [x] T009 Create pure PHP `User` entity in `app/Modules/Identity/Domain/Entities/User.php` with `disable()` invariant (no reactivation Wave 1A)
- [x] T010 Create `UserModel` in `app/Modules/Identity/Infrastructure/Persistence/Models/UserModel.php` extending `App\Support\Models\BaseModel` with `HasUuid` and `RecordsActivity` traits; table `identity_users`
- [x] T011 Create `UserRepository` interface in `app/Modules/Identity/Application/Contracts/UserRepositoryContract.php` and Eloquent implementation in `app/Modules/Identity/Infrastructure/Repositories/UserRepository.php`
- [x] T012 [P] Unit test `tests/Unit/Modules/Identity/Domain/UserTest.php` — UUID immutability, disable transition, no reactivation

**Checkpoint**: `identity_users` migrates; repository can persist/load User — still no login UI.

---

## Phase 3: User Story 1 — Platform User Account Lifecycle (Priority: P1) 🎯 MVP

**Goal**: Create and disable user accounts with immutable UUID v7, synchronous lifecycle events, and audit trail.

**Independent Test**: Run `CreateUserAction` / `DeactivateUserAction` (Artisan or test) — verify UUID v7, active→disabled, `UserCreated`/`UserDeactivated` dispatched, activity logged — without any downstream module.

### Domain events (plan §8.3 — exactly two)

- [x] T013 [US1] Create `UserCreated` domain event in `app/Modules/Identity/Domain/Events/UserCreated.php` with payload per `events.md` (`identity.user.created` v1.0)
- [x] T014 [P] [US1] Create `UserDeactivated` domain event in `app/Modules/Identity/Domain/Events/UserDeactivated.php` with payload per `events.md` (`identity.user.deactivated` v1.0)

### Application actions

- [x] T015 [US1] Implement `CreateUserAction` in `app/Modules/Identity/Application/Services/CreateUserAction.php` — persist via repository, dispatch `UserCreated` synchronously after commit
- [x] T016 [US1] Implement `DeactivateUserAction` in `app/Modules/Identity/Application/Services/DeactivateUserAction.php` — disable transition, dispatch `UserDeactivated` synchronously; enforce last-admin lockout safeguard per spec edge case
- [x] T017 [P] [US1] Create Artisan commands `app/Modules/Identity/Presentation/Console/CreateUserCommand.php` and `DeactivateUserCommand.php` for dev/quickstart (no session auth — plan §8.1)

### Tests

- [x] T018 [P] [US1] Unit test `tests/Unit/Modules/Identity/Application/CreateUserActionTest.php` — asserts UUID v7 assigned, `UserCreated` faked/dispatched
- [x] T019 [P] [US1] Unit test `tests/Unit/Modules/Identity/Application/DeactivateUserActionTest.php` — asserts status disabled, `UserDeactivated` dispatched, last-admin guard
- [x] T020 [US1] Feature test `tests/Feature/Modules/Identity/UserLifecycleTest.php` — end-to-end create → disable → `isUserActive` false via repository/read path
- [x] T021 [P] [US1] Feature test asserting `RecordsActivity` entries on create/disable in `tests/Feature/Modules/Identity/UserAuditTest.php`

### Explicit exclusions (no tasks)

- ❌ No Fortify, Sanctum, login routes, password columns, or session middleware
- ❌ No `IdentityLinked` or async event queue/outbox

**Checkpoint**: US1 acceptance scenarios 1–4 satisfiable via tests and Artisan; quickstart Scenarios 1–2 pass.

---

## Phase 4: User Story 2 — Role and Permission Baseline (Priority: P2)

**Goal**: Assign/revoke roles; permission checks via Spatie — authorization baseline only (not authentication).

**Independent Test**: Seed roles → assign `SystemAdministrator` → `hasPermissionTo` passes → revoke → fails.

- [x] T022 [US2] Complete `database/seeders/IdentityRoleSeeder.php` — `SystemAdministrator` + `identity.users.manage` (and related) permissions per `data-model.md`
- [x] T023 [US2] Add `HasRoles` trait usage on `UserModel` and register Spatie model type in `config/permission.php`
- [x] T024 [US2] Implement `AssignRoleToUserAction` in `app/Modules/Identity/Application/Services/AssignRoleToUserAction.php`
- [x] T025 [P] [US2] Implement `RevokeRoleFromUserAction` in `app/Modules/Identity/Application/Services/RevokeRoleFromUserAction.php`
- [x] T026 [P] [US2] Feature test `tests/Feature/Modules/Identity/RoleAssignmentTest.php` — assign, permission allow, revoke, deny; non-existent role fails cleanly
- [x] T027 [US2] Wire seeder in `database/seeders/DatabaseSeeder.php` or document `sail artisan db:seed --class=IdentityRoleSeeder` in `quickstart.md`

**Checkpoint**: US2 acceptance scenarios 1–4 pass; quickstart Scenario 4 pass.

---

## Phase 5: User Story 3 — Cross-Context User Lookup (Priority: P3)

**Goal**: FR-008 supplier read contract — sole cross-module read surface.

**Independent Test**: Resolve `IdentityUserReadContract` from container; stub consumer test calls `userExists`, `isUserActive`, `findUserSummary` — no `UserModel` import outside Identity.

- [x] T028 [US3] Create `UserSummaryDTO` in `app/Modules/Identity/Application/DTOs/UserSummaryDTO.php` (id, status, displayName only — no credentials)
- [x] T029 [US3] Create `IdentityUserReadContract` in `app/Modules/Identity/Application/Contracts/IdentityUserReadContract.php` per `contracts/identity-read-service.md`
- [x] T030 [US3] Implement `IdentityUserReadService` in `app/Modules/Identity/Application/Services/IdentityUserReadService.php` delegating to `UserRepository` inside module only
- [x] T031 [US3] Bind `IdentityUserReadContract` → `IdentityUserReadService` in `app/Modules/Identity/Infrastructure/Providers/IdentityServiceProvider.php`
- [x] T032 [P] [US3] Feature test `tests/Feature/Modules/Identity/IdentityUserReadContractTest.php` — active, disabled, unknown UUID, malformed `UserId` rejection
- [x] T033 [P] [US3] Architecture test in `tests/Architecture/ModuleBoundaryTest.php` (or new `IdentitySupplierBoundaryTest.php`) — Employee module must not import `UserModel` or Identity Infrastructure
- [x] T034 [US3] Add mock-consumer contract test `tests/Feature/Modules/Identity/StubConsumerReadContractTest.php` demonstrating FR-011 compliance

**Checkpoint**: US3 acceptance scenarios 1–4 pass; quickstart Scenario 3 & 5 pass; SC-005 boundary green.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Documentation and quality gates. **Livewire admin (T035–T037) deferred** — plan §Phase E optional; OA-02-01 blocks login UI; Wave 1A exit via Artisan + tests.

- [ ] ~~T035~~ **DEFERRED** — Livewire `UserIndex` (optional; future spec or Phase E tail)
- [ ] ~~T036~~ **DEFERRED** — Livewire create/disable/role-assign components
- [ ] ~~T037~~ **DEFERRED** — Identity `web.php` admin routes (no public auth endpoints when added)
- [x] T038 Update `app/Modules/Identity/README.md` with module boundaries, contract usage, and OA-02-01 deferral
- [x] T039 Run `sail composer run pint` and fix formatting across Identity module
- [x] T040 Run `sail composer run phpstan` — zero errors for Identity paths (verified: `vendor/bin/phpstan analyse app/Modules/Identity` in Sail)
- [x] T041 Execute `quickstart.md` Scenarios 1–5 manually or via documented test suite and record pass/fail
- [x] T042 [P] Verify no tasks introduced Fortify/Sanctum/`routes/auth.php` — scope audit against plan.md §8.1

**Checkpoint**: Definition of Done — PHPStan L8, Pint, tests green, quickstart validated.

---

## Parallel Execution Examples

### Within Phase 2 (after T008 migration exists)

```bash
# Parallel: enum, exceptions, unit test scaffold
T006 UserStatus enum
T007 Domain exceptions  
T012 User domain unit tests (after T009 entity exists)
```

### Within Phase 3 (US1)

```bash
# Parallel: both event classes
T013 UserCreated
T014 UserDeactivated

# Parallel: unit tests after actions complete
T018 CreateUserActionTest
T019 DeactivateUserActionTest
```

### Across stories (after Phase 2)

```text
US2 (Phase 4) and US3 (Phase 5) can proceed in parallel once US1 actions exist,
but US3 read service only needs T005–T012 + seeded user from US1 tests.
```

---

## Implementation Strategy

1. **MVP first**: Phases 1–3 (T001–T021) — supplier User lifecycle with events; unblocks spec03 Employee `identity_id` assignment.
2. **Incremental**: Add US2 RBAC, then US3 read contract before Employee integration.
3. **Defer**: Login/session (OA-02-01), async events, Notification listeners, Livewire if timeboxed.
4. **Validate early**: Run architecture tests after T033 before declaring spec02 complete.

---

## Format Validation

- [x] All tasks use `- [ ] T###` checkbox format
- [x] Story labels `[US1]`–`[US3]` on user-story phase tasks only
- [x] `[P]` only where parallel-safe
- [x] Every task includes concrete file path(s)
- [x] Auth scope: zero login/session tasks
- [x] Event scope: T013–T016 for `UserCreated`/`UserDeactivated` only

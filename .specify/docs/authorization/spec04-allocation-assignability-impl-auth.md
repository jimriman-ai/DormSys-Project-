---
artifact: spec04_allocation_assignability_impl_auth
spec: Spec04
status: AUTH_PREPARATION_COMPLETE
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
work_item: Spec04 Allocation Assignability (Implementation Authorization Preparation)
basis_contract: .specify/docs/contracts/spec04-allocation-assignability-contract-definition.md
basis_ownership: .specify/docs/decision/spec04-allocation-physical-state-ownership-decision.md
date: 2026-07-12
---

# Spec04 Allocation Assignability — Implementation Authorization Preparation

**Artifact type:** Implementation Authorization Preparation (non-executing)  
**Status:** `AUTH_PREPARATION_COMPLETE`  
**Mutation permission:** `none`  
**Execution authority:** `none`

This artifact defines the strict file-modification boundary, draft concrete interface/persistence shapes, execution checklist, and verification exit criteria for the upcoming coding phase.

It does **not** authorize code changes, migrations, tests execution as delivery, or catalog/spec mutations.

---

## 1. Change Boundary (File Allowlist)

### 1.1 Allowed to create / modify

Paths below are the **only** targets eligible for a future Implementation Authorization Approval. Exact new filenames may vary slightly if Approval names them; **directory ownership must not expand**.

#### Spec04 Dormitory — Domain

| Path | Purpose |
| ---- | ------- |
| `app/Modules/Dormitory/Domain/Enums/PhysicalOccupancyState.php` | Extend to `VACANT` / `RESERVED` / `OCCUPIED` (string-backed) |
| `app/Modules/Dormitory/Domain/` (new VO / policy / service files as needed under Domain only) | Assignability / physical-state transition rules owned by Spec04 (no Spec07 imports) |

#### Spec04 Dormitory — Application

| Path | Purpose |
| ---- | ------- |
| `app/Modules/Dormitory/Application/Contracts/` (new assignability / physical-state contracts) | Supplier-facing Application contracts (read assignability + inbound signal application) |
| `app/Modules/Dormitory/Application/DTOs/` (new DTOs if required) | Result / error payloads for assignability + signal outcomes |
| `app/Modules/Dormitory/Application/Services/` (new services) | Implement assignability evaluation + `RESERVE` / `OCCUPY_MARKER` / `RELEASE` application |
| `app/Modules/Dormitory/Application/Exceptions/` (new if required) | Typed rejection for non-VACANT assign / illegal transitions |

#### Spec04 Dormitory — Infrastructure

| Path | Purpose |
| ---- | ------- |
| `app/Modules/Dormitory/Infrastructure/Persistence/Eloquent/Models/BedModel.php` (or equivalent bed Eloquent model path if renamed) | Persist `physical_occupancy_state` including `reserved`; optional `last_signal_reference_id` |
| `app/Modules/Dormitory/Infrastructure/Repositories/` (new or existing bed/physical repositories) | Persistence adapters for assignability reads + state writes |
| `app/Modules/Dormitory/Infrastructure/Providers/DormitoryServiceProvider.php` | Bind new Spec04 Application contracts → Infrastructure implementations |

#### Persistence (migration)

| Path | Purpose |
| ---- | ------- |
| `database/migrations/modules/dormitory/` **new** alter migration (do **not** rewrite historical create migration unless Approval explicitly requires squash — prefer additive alter) | Expand `dormitory_beds_occupancy_check` to include `'reserved'`; add nullable `last_signal_reference_id` (uuid) if chosen for correlation |

#### Integration / composition root (live Null replacement)

| Path | Purpose |
| ---- | ------- |
| `app/Integrations/Allocation/` (new bridge classes) | Live adapters implementing Allocation `DormitoryReadPort` / `PhysicalStateSignalPort` by calling Spec04 Application contracts only |
| `app/Providers/IntegrationServiceProvider.php` | Register live Allocation↔Dormitory bridge bindings |

#### Allocation composition binding (narrow)

| Path | Purpose |
| ---- | ------- |
| `app/Modules/Allocation/Infrastructure/Providers/AllocationServiceProvider.php` | **Binding-only:** stop binding Null adapters for `DormitoryReadPort` / `PhysicalStateSignalPort` when Integration owns live bindings (or document dual-binding rule in Approval). **No** CreateAllocationAction / domain rewrite unless Approval expands scope. |

#### Tests

| Path | Purpose |
| ---- | ------- |
| `tests/Unit/Modules/Dormitory/` (new) | Domain/Application unit coverage for assignability + transitions |
| `tests/Feature/Modules/Dormitory/` (new) | Persistence + Application feature tests for T1–T3 supplier behavior |
| `tests/Feature/Modules/Allocation/` (new or extend existing allocation feature tests) | End-to-end: live ports reject RESERVED/OCCUPIED; VACANT→RESERVED success; Null-path regression if retained in test env only |
| `tests/Architecture/` **only if** Approval requires registry update for new Integration path | Decay-prevention registry alignment — **no new undocumented exceptions** |

#### Design contract alignment (optional doc-only under Approval)

| Path | Purpose |
| ---- | ------- |
| `specs/004-accommodation-resource/contracts/allocation-physical-state-port.md` | Sync wording to locked VACANT/RESERVED/OCCUPIED model **only if** Approval includes doc sync; not required for code gate |

---

### 1.2 Strictly Frozen (forbidden)

| Area | Paths / examples | Reason |
| ---- | ---------------- | ------ |
| Spec02 Auth | `app/Modules/Identity/**`, Spatie permission models, auth policies rewrite | Ownership: `SPEC02_IDENTITY` |
| Spec07 Check-in / occupancy truth | `app/Modules/CheckIn/**` core write models, check-in domain rules | Spec07 owns resident presence / OCCUPIED truth application |
| Spec07 Allocation domain/actions beyond binding | `CreateAllocationAction.php`, Allocation Domain entities, Allocation ports **interface signatures** (unless Approval explicitly allows additive method docs only) | Consumer ports already exist; residual is Spec04 supplier + live binding |
| UI / routes outside Spec04 structure UI | Livewire, Blade, `routes/**` for Allocation/CheckIn/Request UI | Dormitory UI is independent feature; not this residual |
| Spec06 / Spec11 | Lottery / Reporting modules and regularization reopen | Out of mode |
| Catalog / governance reopen | `.specify/docs/spec-catalog.md`, prior Wave regularization decisions | Forbidden |
| Cross-module Eloquent from Allocation into Dormitory tables | Any Allocation Infra querying `dormitory_beds` directly | Violates modular boundary; must use Integration + Spec04 Application |
| Spec07 writing Spec04 physical columns as authority | CheckIn Infra updating `physical_occupancy_state` without Spec04 Application API | Contract: Spec07 must not write Spec04 DB as assignability authority |

---

## 2. Concrete Interface & Persistence Design Draft

> **Draft only** — for Authorization Approval review. Not executable authority. Maps 1:1 to locked contract capabilities.

### 2.1 Domain enum (draft)

```php
enum PhysicalOccupancyState: string
{
    case Vacant = 'vacant';
    case Reserved = 'reserved';
    case Occupied = 'occupied';
}
```

### 2.2 Spec04 Application — assignability read (draft)

```php
interface AllocationAssignabilityContract
{
    public function bedExists(string $bedId): bool;

    /** True only when bed exists, inventory status allows assignment, and physical state is VACANT. */
    public function isBedAssignable(string $bedId): bool;

    public function getPhysicalOccupancyState(string $bedId): PhysicalOccupancyState;
}
```

Maps to contract: **Spec04 assignability reads**.

### 2.3 Spec04 Application — inbound physical signals (draft)

```php
enum PhysicalStateSignalType: string
{
    case Reserve = 'reserve';
    case OccupyMarker = 'occupy_marker';
    case Release = 'release';
}

final readonly class ApplyPhysicalStateSignalCommand
{
    public function __construct(
        public string $bedId,
        public PhysicalStateSignalType $signalType,
        public ?string $correlationId = null,
    ) {}
}

final readonly class ApplyPhysicalStateSignalResult
{
    public function __construct(
        public bool $accepted,
        public PhysicalOccupancyState $resultingState,
        public ?string $rejectionCode = null,
    ) {}
}

interface AllocationPhysicalStateApplicationContract
{
    public function apply(ApplyPhysicalStateSignalCommand $command): ApplyPhysicalStateSignalResult;
}
```

Transition draft (Spec04-owned markers):

| Signal | From | To | Reject when |
| ------ | ---- | -- | ----------- |
| `reserve` | VACANT | RESERVED | not VACANT / missing bed |
| `occupy_marker` | RESERVED (or VACANT if Approval allows) | OCCUPIED | illegal source state |
| `release` | RESERVED or OCCUPIED | VACANT | already VACANT / missing bed |

Maps to contract: **inbound RESERVE / OCCUPY_MARKER / RELEASE**; **validation reject if not VACANT** for assign/reserve path.

### 2.4 Integration bridges (draft — implement Allocation consumer ports)

```php
// app/Integrations/Allocation/DormitoryAssignabilityReadBridge.php
final class DormitoryAssignabilityReadBridge implements DormitoryReadPort
{
    public function __construct(private AllocationAssignabilityContract $assignability) {}

    public function bedExists(string $bedId): bool { /* delegate */ }
    public function isBedAssignable(string $bedId): bool { /* delegate */ }
}

// app/Integrations/Allocation/PhysicalStateSignalBridge.php
final class PhysicalStateSignalBridge implements PhysicalStateSignalPort
{
    public function __construct(private AllocationPhysicalStateApplicationContract $signals) {}

    public function reserveBed(string $bedId): void { /* apply Reserve; throw/typed fail if rejected */ }
    public function occupyBed(string $bedId): void { /* apply OccupyMarker */ }
    public function releaseBed(string $bedId): void { /* apply Release */ }
}
```

Maps to contract: **live replacement of Null adapters** via Integration / composition root.

### 2.5 Persistence migration draft

**Table:** `dormitory_beds` (existing)

**Alter (additive preferred):**

```sql
-- drop old check, add expanded check
ALTER TABLE dormitory_beds DROP CONSTRAINT dormitory_beds_occupancy_check;
ALTER TABLE dormitory_beds ADD CONSTRAINT dormitory_beds_occupancy_check
  CHECK (physical_occupancy_state IN ('vacant', 'reserved', 'occupied'));

-- optional correlation column
ALTER TABLE dormitory_beds ADD COLUMN last_signal_reference_id UUID NULL;
```

**Do not** alter Spec07 allocation/check-in tables in this residual.

---

## 3. Step-by-Step Execution Plan

Execute **only after** `IMPLEMENTATION_AUTHORIZATION_APPROVAL` is granted. Order is mandatory.

1. **Migration**
   - [ ] Add additive dormitory beds migration for `reserved` CHECK (+ optional `last_signal_reference_id`).
   - [ ] Run migrate in target env; confirm rollback path exists.

2. **Domain**
   - [ ] Extend `PhysicalOccupancyState` with `Reserved`.
   - [ ] Encode transition rules in Domain (no outer-layer imports).

3. **Application contracts + services**
   - [ ] Add `AllocationAssignabilityContract` (+ impl).
   - [ ] Add `AllocationPhysicalStateApplicationContract` (+ impl) for RESERVE / OCCUPY_MARKER / RELEASE.
   - [ ] Reject assign/reserve when state ≠ VACANT.

4. **Infrastructure**
   - [ ] Persist three-state occupancy (+ optional signal reference).
   - [ ] Bind contracts in `DormitoryServiceProvider`.

5. **Integration + binding**
   - [ ] Create Allocation Integration bridges implementing existing Allocation ports.
   - [ ] Register in `IntegrationServiceProvider`.
   - [ ] Adjust `AllocationServiceProvider` Null bindings so live path is used in app runtime.

6. **Tests T1–T3**
   - [ ] **T1:** Assign/reserve against RESERVED or OCCUPIED → fail / reject.
   - [ ] **T2:** VACANT → RESERVE succeeds; resulting state RESERVED.
   - [ ] **T3:** Live Spec04 path differs from Null (Null must not remain the production assignability authority).

7. **Verification gate**
   - [ ] Run exit-criteria commands in §4; record outputs in Approval closeout (separate artifact).

---

## 4. Verification Exit Criteria

Implementation is **not** complete for closeout until all of the following succeed.

### 4.1 Migration

```bash
php artisan migrate --path=database/migrations/modules/dormitory --force --ansi
```

Expect: new alter migration applied; no error on CHECK including `reserved`.

Optional inspect:

```bash
php artisan db:show --table=dormitory_beds
```

(or equivalent schema check confirming constraint / column).

### 4.2 Tests (T1–T3 coverage)

```bash
php artisan test --filter=Assignability
```

and/or explicit files once named by Approval, e.g.:

```bash
php artisan test tests/Feature/Modules/Dormitory tests/Feature/Modules/Allocation --ansi
```

Expect: **all green**; T1 reject, T2 VACANT→RESERVED, T3 live≠Null assertions pass.

### 4.3 Static analysis / formatting (Definition of Done)

```bash
php vendor/bin/phpstan analyse --no-progress
php vendor/bin/pint --test
```

Expect: PHPStan level 8 zero errors on touched paths; Pint clean (or apply Pint then re-check).

### 4.4 Architecture decay (if Integration paths touched)

```bash
composer run arch --ansi
```

Expect: pass; no new undocumented legacy registry entries.

---

## 5. Explicit Non-Authorization

This preparation does **not** authorize:

- application / test / migration code changes
- running migrations as a delivery act
- Spec02 / Spec07 / UI / catalog mutations
- expanding Allocation domain rules beyond live port binding
- Spec07 writing Spec04 physical state as authority

---

## 6. Authorization Decision Block

```text
SPEC04_ALLOCATION_ASSIGNABILITY_IMPLEMENTATION_AUTHORIZATION

Authorization Status:
AUTHORIZATION_PREPARED

Next Required Step:
IMPLEMENTATION_AUTHORIZATION_APPROVAL

Target Code Scope:
app/Modules/Dormitory/{Domain,Application,Infrastructure} (assignability + physical-state supplier);
database/migrations/modules/dormitory (additive reserved CHECK + optional last_signal_reference_id);
app/Integrations/Allocation (live DormitoryReadPort / PhysicalStateSignalPort bridges);
app/Providers/IntegrationServiceProvider.php;
app/Modules/Allocation/Infrastructure/Providers/AllocationServiceProvider.php (binding-only Null→live);
tests/Unit|Feature/Modules/Dormitory and Allocation assignability coverage (T1–T3)
```

---

## 7. No-Change Confirmation

`No application, test, migration, catalog, or contract files were modified by this preparation step.`

Only this artifact was created:

- `.specify/docs/authorization/spec04-allocation-assignability-impl-auth.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`AUTH_PREPARATION_COMPLETE`** / **`AUTHORIZATION_PREPARED`**  
- Next: **`IMPLEMENTATION_AUTHORIZATION_APPROVAL`**  
- Owner: Governance / Implementation Authorization Prep  
- Last Updated: 2026-07-12

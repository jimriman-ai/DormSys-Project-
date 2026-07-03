# Data Model: Allocation & Occupancy (spec07)

**Date**: 2026-07-01 | **Spec**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

**Authority:** CD-014 (Allocation assignment), CD-015 (CheckIn/CheckOut operational transitions). Physical bed markers remain in Dormitory (spec04) — not persisted here.

---

## Bounded contexts

| Context | Module path | Owned aggregates |
| ------- | ----------- | ---------------- |
| **Allocation** | `app/Modules/Allocation/` | `Allocation`, `AllocationItem` |
| **CheckIn/CheckOut** | `app/Modules/CheckIn/` | `CheckInRecord` (operational stay) |
| **Dormitory** | spec04 — **not owned** | Coordinated via ports only |

Cross-module references use **UUID value references only** — no FK to `request_*`, `lottery_*`, `dormitory_*`, `employee_*`, or `identity_*`.

---

## Derived concept: Effective occupancy (not authoritative in one store)

Per CD-014, effective occupancy is **cross-cutting**:

| Layer | Owner | Stored state |
| ----- | ----- | ------------ |
| Assignment | Allocation (spec07) | Who is assigned to which bed for which date range |
| Physical marker | Dormitory (spec04) | `vacant` / `reserved` / `occupied` on bed |
| Operational stay | CheckIn/CheckOut (spec07) | `checked_in_at` / `checked_out_at` on stay record |

No single table is authoritative for “effective occupancy.”

---

## 1. Allocation aggregate

### Domain entity: `Allocation`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `AllocationId` | UUID v7 |
| `personId` | `PersonAllocationRef` | UUID — employee or request member ref; no FK |
| `bedId` | `string` (UUID) | Bed reference from Dormitory catalog; no FK |
| `dateRange` | `DateRange` | Inclusive assignment window |
| `method` | `AllocationMethod` | `manual`, `request_sourced`, `lottery_sourced` |
| `status` | `AllocationStatus` | `active`, `released` |
| `sourceRequestId` | `string?` | UUID ref when method = request_sourced |
| `sourceLotteryResultId` | `string?` | UUID ref when method = lottery_sourced |
| `releasedAt` | `DateTimeImmutable?` | Set on release |
| `releaseReason` | `string?` | Required when released |

### Domain entity: `AllocationItem` (line item)

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `AllocationItemId` | UUID v7 |
| `allocationId` | `AllocationId` | Parent allocation |
| `bedId` | `string` (UUID) | Bed ref |
| `sequence` | `int` | Ordering within allocation |

*Wave 1 may collapse to single bed per allocation; `AllocationItem` supports multi-bed assignments without schema change.*

### Enums

**`AllocationMethod`:** `manual` | `request_sourced` | `lottery_sourced`

**`AllocationStatus`:** `active` | `released`

### Persistence: `allocations`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | UUID v7 via `HasUuid` |
| `person_id` | `uuid` | No FK |
| `bed_id` | `uuid` | No FK |
| `date_range` | `daterange` | PostgreSQL `daterange` |
| `method` | `string` | |
| `status` | `string` | |
| `source_request_id` | `uuid` nullable | |
| `source_lottery_result_id` | `uuid` nullable | |
| `released_at` | `timestamptz` nullable | |
| `release_reason` | `text` nullable | |
| audit columns | | `RecordsActivity` |

### Persistence: `allocation_items`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | |
| `allocation_id` | `uuid` | FK → `allocations.id` (intra-module) |
| `bed_id` | `uuid` | No cross-module FK |
| `sequence` | `smallint` | |
| audit columns | | |

**Module path:** `database/migrations/modules/allocation/`

---

## 2. Occupancy state model (cross-context — reference only)

Allocation **does not** store physical occupancy markers. Reference model from spec04:

| Marker | Owner | Meaning |
| ------ | ----- | ------- |
| `vacant` | Dormitory | Bed has no reservation marker |
| `reserved` | Dormitory | Assignment signal received; bed held |
| `occupied` | Dormitory | Physical occupancy marker applied |

**Signal producer:** Allocation via `AllocationPhysicalStatePort` / `AllocationAssigned` / `AllocationReleased` (R7, ADIC).

**Pre-check consumer:** Allocation reads assignability via `App\Modules\Dormitory\Application\Contracts\DormitoryReadContract` — supplier is spec04 only.

---

## 3. CheckIn / CheckOut operational model (CD-015)

### Domain entity: `CheckInRecord`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `CheckInRecordId` | UUID v7 |
| `allocationId` | `string` (UUID) | Ref to active allocation; no FK |
| `checkedInAt` | `DateTimeImmutable` | UTC storage |
| `checkedOutAt` | `DateTimeImmutable?` | Null until check-out |
| `operatorId` | `string` (UUID) | Identity ref; Operator role required |

### Operational state transitions

```text
(no record) ──checkIn──► CheckedIn (checked_out_at IS NULL)
CheckedIn   ──checkOut──► CheckedOut (checked_out_at set)
```

| Transition | Owner | Preconditions |
| ---------- | ----- | ------------- |
| **CheckedIn** | CheckIn/CheckOut | Active allocation exists; Operator role; internal dormitory path |
| **CheckedOut** | CheckIn/CheckOut | Prior check-in; Operator role |

CheckIn/CheckOut **does not** create, modify, or release allocations (CD-015).

### Persistence: `check_in_records`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | |
| `allocation_id` | `uuid` | No FK to `allocations` |
| `checked_in_at` | `timestamptz` | |
| `checked_out_at` | `timestamptz` nullable | |
| `operator_id` | `uuid` | No FK |
| audit columns | | |

**Module path:** `database/migrations/modules/check_in/`

---

## 4. Invariants and enforcement points

| ID | Invariant | Enforced by | CD |
| -- | --------- | ----------- | -- |
| INV-A01 | One active assignment per person per overlapping date range | PostgreSQL exclusion on `(person_id, date_range)` + domain validation | CD-014, BR-02 |
| INV-A02 | Allocation does not persist physical bed markers | Schema — no `occupancy_marker` columns in `allocation_*` | CD-014 |
| INV-A03 | Bed assignability pre-check uses Dormitory read port only | `CreateAllocationAction` + `DormitoryReadContract` | CD-014, R7 |
| INV-A04 | Physical marker changes only via outbound port to Dormitory | `AllocationPhysicalStateAdapter` | CD-014, R7 |
| INV-C01 | Check-in requires active allocation fact | `CheckInAction` via assignment read port | CD-015 |
| INV-C02 | Check-in/out does not mutate allocation rows | CheckIn module has no Allocation repository writes | CD-015 |
| INV-C03 | Operator role required for check-in/out | Application layer gate | CD-015 |

---

## 5. Dependency mapping (read-only upstream)

| Upstream | Relationship | Read surface | Mutation |
| -------- | ------------ | ------------ | -------- |
| **Request** (spec05) | R6 | `App\Modules\Request\Application\Contracts\RequestReadContract` | None — read only |
| **Lottery** (spec06) | R5 | `LotteryResultReadContract`, `ProposedAllocationPort` | None — read only |
| **Dormitory** (spec04) | R7 consumer | `App\Modules\Dormitory\Application\Contracts\DormitoryReadContract` | None — read only; stub until spec04 live (UD-07) |

**Outbound (not read):** `AllocationPhysicalStatePort` (producer to Dormitory), `RequestLifecycleCommandPort` (command to Request), `VoucherIssuancePort` (trigger facts to Voucher), `AllocationReadContract` (supplier read).

---

## 6. Excluded from this model

| Item | Owner |
| ---- | ----- |
| Dormitory site/building/room/bed catalog | spec04 |
| Request approval state | spec05 |
| Lottery program lifecycle | spec06 |
| Voucher eligibility / issuance | spec08 |
| Reconciliation when Allocation and Dormitory diverge | UD-02 — out of scope |
| External dormitory check-in/out | Voucher path |

---

## References

- [contracts/](./contracts/)
- [`allocation-dormitory-integration-contract.md`](../../.specify/governance/contracts/allocation-dormitory-integration-contract.md)
- [`catalog-decisions.md`](../../.specify/docs/catalog-decisions.md) CD-014, CD-015

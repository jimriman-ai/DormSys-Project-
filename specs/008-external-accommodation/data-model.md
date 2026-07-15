# Data Model: External Accommodation / Voucher (spec08)

**Date**: 2026-07-15 | **Spec**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

**Authority:** CD-016 (Voucher). This artifact is a **code mirror** of `app/Modules/Voucher/` (SGAP-04).  
**HARD RULE:** Derived from existing implementation only — no new design.

---

## Bounded context

| Context | Module path | Owned aggregates / records |
| ------- | ----------- | -------------------------- |
| **Voucher** | `app/Modules/Voucher/` | `VoucherIssuanceTrigger`, `VoucherEligibilityOutcome`, `Voucher`, `VoucherLifecycleTransition` |

Cross-module references use **UUID value references only** (`employee_id`, optional `dormitory_id`, `request_id`) — no FK to employee / dormitory / request / lottery tables.

---

## Domain models (as implemented)

### 1. VoucherIssuanceTrigger

**Path:** `app/Modules/Voucher/Domain/Models/VoucherIssuanceTrigger.php`  
**Table:** `voucher_issuance_triggers` (`2026_07_01_000001_create_voucher_issuance_triggers_table.php`)

| Attribute | Type (domain / persistence) | Notes from code |
| --------- | --------------------------- | --------------- |
| `id` | `TriggerId` / uuid PK | Assigned on persist |
| `correlationId` | `CorrelationId` / `correlation_id` unique | Intake idempotency |
| `employeeId` | string UUID | Validated UUID ref |
| `source` | `TriggerSource` | `lottery` \| `allocation` |
| `stayPeriod` | `StayPeriod` / PostgreSQL `daterange` | NOT NULL |
| `status` | `TriggerIntakeStatus` | `accepted` \| `superseded` |
| `dormitoryId` | ?string UUID | Optional ref |
| `requestId` | ?string UUID | Optional ref |
| `upstreamFacts` | `array` / jsonb | Upstream trigger facts |
| `issuancePathCompletedAt` | ?DateTimeImmutable | Path completion marker |
| `supersededByTriggerId` | ?TriggerId | When superseded |

### 2. VoucherEligibilityOutcome

**Path:** `app/Modules/Voucher/Domain/Models/VoucherEligibilityOutcome.php`  
**Table:** `voucher_eligibility_outcomes`

| Attribute | Type | Notes |
| --------- | ---- | ----- |
| `id` | `EligibilityOutcomeId` | |
| `triggerId` | `TriggerId` | unique per trigger |
| `correlationId` | `CorrelationId` | |
| `employeeId` | string UUID | |
| `dormitoryId` | ?string UUID | |
| `requestId` | ?string UUID | |
| `outcome` | `EligibilityOutcome` | `eligible` \| `ineligible` \| `deferred` |
| `reasonCodes` | `list<string>` / jsonb | e.g. ineligibility / deferred codes |
| `rationale` | string | |
| `evaluatedAt` | DateTimeImmutable | |

### 3. Voucher

**Path:** `app/Modules/Voucher/Domain/Models/Voucher.php`  
**Table:** `vouchers`

| Attribute | Type | Notes |
| --------- | ---- | ----- |
| `id` | `VoucherId` | |
| `eligibilityOutcomeId` | `EligibilityOutcomeId` | unique |
| `triggerId` | `TriggerId` | |
| `correlationId` | `CorrelationId` | |
| `employeeId` | string UUID | |
| `dormitoryId` | ?string UUID | |
| `requestId` | ?string UUID | |
| `upstreamSource` | `TriggerSource` | |
| `code` | `VoucherCode` | unique string(32) |
| `lifecycleState` | `VoucherLifecycleState` | see enum |
| `stayPeriod` | `StayPeriod` / daterange | |
| `validityStart` / `validityEnd` | DateTimeImmutable | From stay period on issue |
| `issuedAt` | DateTimeImmutable | |
| `archivedAt` | ?DateTimeImmutable | |

**Lifecycle transitions implemented on domain:** `issue` → Issued; `expire` / `cancel` / `supersede` from Issued; `archive` sets `archivedAt`.

### 4. VoucherLifecycleTransition

**Path:** `app/Modules/Voucher/Domain/Models/VoucherLifecycleTransition.php`  
**Table:** `voucher_lifecycle_transitions`

| Attribute | Type | Notes |
| --------- | ---- | ----- |
| `id` | ?string UUID | |
| `voucherId` | `VoucherId` | |
| `fromState` | ?`VoucherLifecycleState` | nullable |
| `toState` | `VoucherLifecycleState` | |
| `correlationId` | `CorrelationId` | |
| `occurredAt` | DateTimeImmutable | |
| `payload` | array / jsonb | Includes reserve-promotion outcome records |

---

## Enums (as implemented)

| Enum | Cases (value) |
| ---- | ------------- |
| `VoucherLifecycleState` | `issued`, `expired`, `cancelled`, `superseded` |
| `EligibilityOutcome` | `eligible`, `ineligible`, `deferred` |
| `TriggerSource` | `lottery`, `allocation` |
| `TriggerIntakeStatus` | `accepted`, `superseded` |
| `AccommodationClassification` | `external`, `internal` |
| `IneligibilityReasonCode` | `missing_dormitory_reference`, `not_external_dormitory`, `internal_assignment_path` |
| `DeferredReasonCode` | `classification_pending` |
| `ExternalLotteryWinnerDisposition` | `issued`, `not_eligible`, `deferred`, `skipped_capacity`, `duplicate_rejected` |
| `ExternalLotteryBatchDisposition` | `processed`, `ignored_internal_program` |
| `ReservePromotionDisposition` | `issued`, `no_eligible_reserves`, `reserve_ineligible`, `reserve_deferred`, `ignored_internal_program`, `duplicate_rejected` |

---

## Persistence note

Migrations under `database/migrations/modules/voucher/`. Soft deletes + audit columns (`created_by` / `updated_by` / `deleted_by`) present on tables as coded. No cross-module FK constraints.

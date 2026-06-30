# Data Model: Lottery Selection (spec06)

**Date**: 2026-06-30 | **Plan**: [plan.md](./plan.md) | **Spec**: [spec.md](./spec.md)

---

## Bounded context

**Lottery** — aggregate roots: **LotteryProgram**, **LotteryRegistration**, **LotteryResult**. Snapshot: **EligibleSnapshot** (immutable at lock).

Lottery does **not** import or FK to Request, Employee, Dormitory, or Allocation tables.

---

## 1. LotteryProgram (aggregate root)

### Domain model: `LotteryProgram`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `LotteryProgramId` | UUID v7 |
| `title` | string | Operator label |
| `dormitoryId` | `DormitorySiteId` | Target site — no FK |
| `capacity` | int | Winners count; must be > 0 at creation |
| `registrationStartsAt` | `DateTimeImmutable` | UTC |
| `registrationEndsAt` | `DateTimeImmutable` | After starts |
| `status` | `LotteryProgramState` | spatie model state (AP-05) |
| `randomSeed` | string? | Set at lock |
| `scoringConfigVersion` | string? | Settings version at lock |
| `cancelledReason` | string? | Set on cancel |
| `lockedAt` | `DateTimeImmutable`? | Set at lock |
| `drawnAt` | `DateTimeImmutable`? | Set after draw |

### State machine

| Value | Terminal |
| ----- | -------- |
| `draft` | no |
| `waiting_approval` | no |
| `approved` | no |
| `registration_open` | no |
| `registration_closed` | no |
| `locked` | no |
| `drawn` | no |
| `completed` | yes |
| `cancelled` | yes |

---

## 2. Persistence: `lottery_programs`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | UUID v7 via `HasUuid` |
| `title` | `string` | |
| `dormitory_id` | `uuid` | no FK |
| `capacity` | `unsignedInteger` | |
| `registration_starts_at` | `timestamp` | UTC |
| `registration_ends_at` | `timestamp` | UTC |
| `status` | `string` | state machine value |
| `random_seed` | `string` nullable | |
| `scoring_config_version` | `string` nullable | |
| `cancelled_reason` | `text` nullable | |
| `locked_at` | `timestamp` nullable | |
| `drawn_at` | `timestamp` nullable | |
| audit + soft delete | | `BaseModel`, `RecordsActivity` |

**Module path:** `database/migrations/modules/lottery/`

---

## 3. LotteryRegistration

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `LotteryRegistrationId` | UUID v7 |
| `programId` | `LotteryProgramId` | Intra-module FK |
| `requestId` | `RequestReferenceId` | No FK to Request |
| `employeeId` | `EmployeeReferenceId` | No FK to Employee |
| `weightedScore` | float? | Set at lock/scoring |
| `enrolledAt` | `DateTimeImmutable` | UTC |

**Unique:** (`program_id`, `request_id`)

### Persistence: `lottery_registrations`

Intra-module FK → `lottery_programs.id`. No cross-module FK.

---

## 4. LotteryResult

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `LotteryResultId` | UUID v7 |
| `programId` | `LotteryProgramId` | |
| `registrationId` | `LotteryRegistrationId` | |
| `rank` | int | 1-based ordering |
| `outcome` | `LotteryResultOutcome` | `winner`, `reserve` |

**Unique:** (`program_id`, `registration_id`)

---

## 5. EligibleSnapshot (lock-time capture)

### Persistence: `lottery_eligible_snapshots`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | |
| `program_id` | `uuid` | unique — one snapshot per program |
| `payload` | `json` | Frozen registration set |
| `random_seed` | `string` | |
| `scoring_config` | `json` | Resolved settings at lock |
| `scoring_config_version` | `string` nullable | |

---

## Cross-module references (no FK)

| Column | Referenced context | Access |
| ------ | ------------------ | ------ |
| `request_id` | Request (spec05) | `RequestReadContract` |
| `employee_id` | Employee (spec03) | `EmployeeLotteryScorePort` stub |
| `dormitory_id` | Dormitory (spec04) | Optional read stub |

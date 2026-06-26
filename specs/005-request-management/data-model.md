# Data Model: Request Management (spec05)

**Date**: 2026-06-23 | **Plan**: [plan.md](./plan.md) | **Research**: [research.md](./research.md)

---

## Bounded context

**Request** — aggregate root: **Request**. Child entities: **RequestApproval** (append-only history), **RequestMember** (Mission), **DependentSnapshot** (FamilyDirect), **MissionDetails** (Mission 1:1).

Request does **not** import or FK to Employee, Identity, Dormitory, Allocation, or Lottery tables.

---

## Derived concept: PendingRequest (not persisted)

**Definition (R-04):** Employee has a pending request when any `requests` row exists for `employee_id` with `status` **not in** (`approved`, `rejected`, `cancelled`).

Used by `PendingRequestReadPort` adapter — computed query, not a column.

---

## 1. Request (aggregate root)

### Domain entity: `Request`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `RequestId` | UUID v7 |
| `code` | `RequestCode` | `REQ-YYYYMMDD-NNNN` (R-02) |
| `employeeId` | UUID VO | Submitting employee — no FK |
| `dormitoryId` | UUID VO | Target site — no FK |
| `type` | `RequestType` | `Personal`, `FamilyDirect`, `Mission`, `LotteryRegistration` |
| `checkInDate` | date | Stored UTC date; not in past at submit |
| `checkOutDate` | date | Strictly after check-in |
| `status` | `RequestState` | spatie model state (R-07) |
| `submittedAt` | `DateTimeImmutable`? | Set on submit |
| `cancelledAt` | `DateTimeImmutable`? | Set on cancel |
| `rejectionReason` | string? | Terminal reject summary (optional; detail in approvals) |

### Invariants

1. `code` unique platform-wide
2. BR-01 enforced at submit (R-05)
3. Cancellation only from `Draft` or `Submitted` (FR-017)
4. Terminal states: `Approved`, `Rejected`, `Cancelled` — no further transitions in spec05
5. Type-specific child rules at submit (R-12)

### State machine (persistence value)

Column `requests.status` — string enum matching state classes:

| Value | Terminal |
| ----- | -------- |
| `draft` | no |
| `submitted` | no |
| `pending_department_manager` | no |
| `pending_hr` | no |
| `pending_dormitory_manager` | no |
| `pending_dormitory_unit` | no |
| `approved` | yes |
| `rejected` | yes |
| `cancelled` | yes |

Transitions: [research.md R-07](./research.md#r-07--request-lifecycle-state-machine-oa-05-01)

---

## 2. Persistence: `requests`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | UUID v7 via `HasUuid` |
| `code` | `string` | unique |
| `employee_id` | `uuid` | no FK |
| `dormitory_id` | `uuid` | no FK |
| `type` | `string` | `personal`, `family_direct`, `mission`, `lottery_registration` |
| `check_in_date` | `date` | |
| `check_out_date` | `date` | |
| `status` | `string` | state machine value |
| `submitted_at` | `timestamp` nullable | UTC |
| `cancelled_at` | `timestamp` nullable | UTC |
| `rejection_reason` | `text` nullable | |
| audit + soft delete | | `BaseModel`, `RecordsActivity` |

**Module path:** `database/migrations/modules/request/`

**Indexes:** `employee_id`, `status`, (`employee_id`, `status`) for pending query

**Cross-module FK:** none

---

## 3. RequestApproval (append-only child)

### Domain entity: `RequestApproval`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | UUID | |
| `requestId` | `RequestId` | Parent |
| `stage` | `ApprovalStage` | Four stages |
| `decision` | `ApprovalDecision` | `Approved`, `Rejected` |
| `approverId` | UUID | Identity user or system actor — no FK |
| `reason` | string? | Required if rejected |
| `decidedAt` | `DateTimeImmutable` | UTC |

### Invariants

1. **Append-only** — no UPDATE/DELETE (R-08)
2. One row per decision event (auto-approval still inserts row)

### Persistence: `request_approvals`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | |
| `request_id` | `uuid` | FK → `requests.id` (intra-module) |
| `stage` | `string` | `department_manager`, `hr`, `dormitory_manager`, `dormitory_unit` |
| `decision` | `string` | `approved`, `rejected` |
| `approver_id` | `uuid` | no FK |
| `reason` | `text` nullable | |
| `decided_at` | `timestamp` | UTC |
| `created_at` | `timestamp` | append-only audit |

**No** `updated_at` / soft delete on approval rows (immutable log).

---

## 4. DependentSnapshot (FamilyDirect child)

### Domain entity: `DependentSnapshot`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | UUID | |
| `requestId` | `RequestId` | |
| `sourceDependentId` | UUID? | Trace only — **no FK** (R-10) |
| `firstName` | string | Immutable after submit |
| `lastName` | string | |
| `relationship` | string | See DR-05-03 |
| `nationalCode` | string? | |
| `capturedAt` | `DateTimeImmutable` | Set at submit |

### Persistence: `request_dependent_snapshots`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | |
| `request_id` | `uuid` | FK → `requests.id` |
| `source_dependent_id` | `uuid` nullable | no FK |
| `first_name` | `string` | |
| `last_name` | `string` | |
| `relationship` | `string` | |
| `national_code` | `string` nullable | |
| `captured_at` | `timestamp` | UTC |
| `created_at` | `timestamp` | |

**Invariant:** Only for `type = family_direct`; ≥1 row required at submit.

---

## 5. RequestMember (Mission child)

### Domain entity: `RequestMember`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | UUID | |
| `requestId` | `RequestId` | |
| `employeeId` | UUID | Member — no FK |
| `isLeader` | bool | Exactly one `true` per request |

### Persistence: `request_members`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | |
| `request_id` | `uuid` | FK → `requests.id` |
| `employee_id` | `uuid` | no FK |
| `is_leader` | `boolean` | |
| audit | | `created_at` |

**Invariant:** 2–20 rows at submit; exactly one `is_leader = true`; leader must be in member set (BR-04).

---

## 6. MissionDetails (Mission 1:1 child)

### Domain entity: `MissionDetails`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `requestId` | `RequestId` | PK / unique FK |
| `missionDocumentUrl` | string? | Optional attachment reference |
| `description` | string | Required for Mission |

### Persistence: `request_mission_details`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `request_id` | `uuid` PK | FK → `requests.id` |
| `mission_document_url` | `string` nullable | |
| `description` | `text` | |
| `created_at` | `timestamp` | |

---

## 7. Enums (persistence values)

| Enum | Values |
| ---- | ------ |
| `RequestType` | `personal`, `family_direct`, `mission`, `lottery_registration` |
| `RequestStatus` | see §1 state table |
| `ApprovalStage` | `department_manager`, `hr`, `dormitory_manager`, `dormitory_unit` |
| `ApprovalDecision` | `approved`, `rejected` |

---

## 8. Migration order

```text
requests
  → request_approvals
  → request_dependent_snapshots
  → request_members
  → request_mission_details
```

---

## 9. Cross-context references

| Field | Rule |
| ----- | ---- |
| `employee_id` | Immutable UUID; validated via contracts at write time |
| `dormitory_id` | Immutable UUID; optional `DormitoryReadContract` |
| `approver_id` | UUID; optional `IdentityUserReadContract` |
| `source_dependent_id` | Trace only; no FK (CD-009) |
| Allocation / Lottery | **No columns** in spec05 |

---

## 10. Query semantics

| Query | Definition |
| ----- | ---------- |
| `hasPendingRequest(employeeId)` | Exists request where `employee_id` = id AND `status` NOT IN terminal set (R-04) |
| `getApprovedRequest(requestId)` | `status = approved` — supplier read |
| Approval history | Ordered by `decided_at` ASC on `request_approvals` |

---

## Related

- [research.md](./research.md) — R-04 pending definition, R-07 transitions
- [contracts/request-read-service.md](./contracts/request-read-service.md)
- [contracts/employee-request-boundary.md](./contracts/employee-request-boundary.md)
- [contracts/request-eligibility-enforcement.md](./contracts/request-eligibility-enforcement.md)

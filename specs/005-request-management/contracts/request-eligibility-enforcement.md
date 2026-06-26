# Contract: Request Eligibility Enforcement (submit)

**Version:** 1.0.0  
**Spec:** spec05 Request Management  
**Implements:** spec.md FR-005, BR-01, R-05  
**Status:** Phase 1 design — implementation not authorized

---

## Purpose

Defines the **submit-time validation orchestration** owned by Request. Separates **computation** (Employee) from **enforcement** (Request) per CD-013.

---

## Entry point

**Action (planned):** `App\Modules\Request\Application\Services\SubmitRequestAction`

**Precondition:** Request in `Draft` state.

---

## Validation pipeline (ordered)

| Step | Owner | Check | On failure |
| ---- | ----- | ----- | ---------- |
| 1 | Request | State is `Draft` | `InvalidRequestTransitionException` |
| 2 | Request | `check_out_date > check_in_date` | `RequestValidationException` |
| 3 | Request | `check_in_date >= today` (UTC date) | `RequestValidationException` |
| 4 | Request | Type-specific rules (R-12) | `RequestValidationException` / `InvalidGroupRequestException` |
| 5 | Employee | `EmployeeEligibilityContract::computeRequestEligibility(employeeId)` | `RequestNotEligibleException` + reason codes |
| 6 | Request (optional) | `DormitoryReadContract::siteExists(dormitoryId)` when bound | `RequestValidationException` |
| 7 | Request | Persist snapshots / members; transition state; emit events | — |

**Fail closed:** Any exception from step 5 (including upstream unavailable) → submit rejected (R-013-01).

---

## Type-specific rules (step 4)

| `RequestType` | Rules |
| ------------- | ----- |
| `Personal` | None |
| `LotteryRegistration` | None (program linkage = spec06) |
| `FamilyDirect` | ≥1 `request_dependent_snapshots` row |
| `Mission` | 2–20 `request_members`; exactly one `is_leader`; `request_mission_details` present |

---

## Employee eligibility mapping

When `EligibilityResultDTO.eligible === false`, Request maps `reasonCodes` to stable submit failure:

| Code | User-facing category |
| ---- | -------------------- |
| `employee_inactive` | Employee not active |
| `active_allocation_exists` | Active allocation exists |
| `pending_request_exists` | Pending request exists |

Request **does not** re-implement eligibility logic — only propagates codes.

---

## Date rules (Request-owned subset of BR-01)

| Rule | Implementation |
| ---- | -------------- |
| Check-in not in past | Compare `check_in_date` to `today()` UTC |
| Check-out after check-in | Strict inequality |

**Not in spec05:** Stay duration limits from settings — defer unless constitution mandates in Wave 1.

---

## State transition on success

Per [research.md R-07](../research.md#r-07--request-lifecycle-state-machine-oa-05-01):

1. Set `submitted_at`
2. Transition `Draft` → `Submitted` → `PendingDepartmentManager` (same transaction recommended)
3. Emit `RequestSubmitted`
4. Apply auto-approval chain if settings enabled (R-09)

---

## Dormitory validation (optional)

| Adapter | When |
| ------- | ---- |
| `NullDormitoryReadAdapter` | Wave 1A — accepts valid UUID |
| `DormitoryReadContract` consumer | When spec04 implemented |

---

## Exceptions (planned)

| Exception | When |
| --------- | ---- |
| `RequestNotEligibleException` | Step 5 — carries `reasonCodes` |
| `RequestValidationException` | Steps 2–4, 6 |
| `InvalidGroupRequestException` | Mission BR-04 violation |
| `InvalidRequestTransitionException` | Wrong state |

---

## Testing requirements

| Test | Proves |
| ---- | ------ |
| BT-R01 | Eligible path succeeds |
| BT-R02 | Ineligible → reason codes |
| Inactive employee | `employee_inactive` |
| Mock pending port true | `pending_request_exists` |
| Past check-in | Request validation fails without calling Employee |

---

## Related

- [employee-request-boundary.md](./employee-request-boundary.md)
- [../003-employee-context/contracts/employee-eligibility-service.md](../../003-employee-context/contracts/employee-eligibility-service.md)
- [data-model.md](../data-model.md)

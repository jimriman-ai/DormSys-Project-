# Quickstart: Request Management (spec05)

**Date**: 2026-06-23 | **Plan**: [plan.md](./plan.md)

Validation scenarios for Request module **after implementation is authorized**. Prerequisites assume spec01 Foundation, spec02 auth, spec03 Employee with eligibility contract.

**Not executable until implementation authorization.**

---

## Prerequisites

```powershell
docker compose up -d
docker compose exec laravel.test php artisan migrate
# Employee fixture with active status required
# PendingRequestReadPort: real adapter bound (not null stub)
```

---

## Scenario 1 — Personal request submit (US1)

**Proves:** FR-001, FR-005, SC-001, BT-R01

1. `php artisan request:create-personal {employeeId} {dormitoryId} --check-in=2026-07-01 --check-out=2026-12-31`
2. `php artisan request:submit {requestId}`
3. Assert status `pending_department_manager` (or `submitted` then advanced)
4. Assert `employee_id`, `dormitory_id` stored — no cross-module FK

**Expected:** Eligibility contract invoked; dates validated; request code assigned.

---

## Scenario 2 — Ineligible submit rejected (US1)

**Proves:** SC-002, BT-R02

1. Use inactive employee OR bind mock `PendingRequestReadPort` returning true
2. Attempt submit
3. Assert `RequestNotEligibleException` with stable reason code

**Expected:** No state transition past `Draft`.

---

## Scenario 3 — Four-stage approval to Approved (US3)

**Proves:** FR-007, FR-008, SC-005, BT-R03

1. Submit personal request
2. Approve as DeptMgr → HR → DormMgr → DormUnit (commands or actions)
3. Assert 4 `request_approvals` rows append-only
4. Assert terminal status `approved`

**Expected:** No `WaitingForAllocation` state (OA-05-03).

---

## Scenario 4 — Reject with reason (US2/US3)

**Proves:** BT-R04

1. Submit request; reject at `pending_hr` with reason
2. Assert status `rejected`; approval row has `decision=rejected` and reason

---

## Scenario 5 — Cancel early only (US2)

**Proves:** FR-017

1. Cancel from `draft` → success
2. Cancel from `pending_department_manager` → rejected transition

---

## Scenario 6 — PendingRequestReadPort loop (US1 / CD-013)

**Proves:** BT-R08, BT-R09, OA-05-09

1. Employee A has draft request
2. `PendingRequestReadPort::hasPendingRequest(A)` → `true`
3. Second submit attempt for A → `pending_request_exists` via eligibility
4. Verify adapter exposes **only** `hasPendingRequest` — no mutation methods

---

## Scenario 7 — FamilyDirect snapshots (US4 / Wave 1B)

**Proves:** SC-003, BT-R06, CD-009

1. Create FamilyDirect request with dependent snapshot lines
2. Submit; mutate Employee dependent fixture (if US3 live)
3. Re-read snapshots — unchanged

**Gate:** spec03 US3 or approved test fixtures.

---

## Scenario 8 — Mission group validation (US5)

**Proves:** SC-004, BT-R07, BR-04

1. Mission with 1 member → submit rejected
2. Mission with 21 members → rejected
3. Mission with 3 members, no leader → rejected
4. Valid 3-member mission with leader → submit succeeds

---

## Scenario 9 — LotteryRegistration type (US6)

**Proves:** FR-002, type flagging

1. Create/submit `lottery_registration` request
2. `RequestReadContract::listApprovedByType('lottery_registration')` includes request
3. No lottery draw executed (spec06 out of scope)

---

## Scenario 10 — Supplier read contract (downstream stub)

**Proves:** FR-014, SC-006

1. `RequestReadContract::getRequestSummary(approvedId)`
2. Architecture test: no Employee/Dormitory/Allocation/Lottery Infrastructure imports in Request

---

## MVP gate commands (future)

```powershell
docker compose exec laravel.test php artisan test tests/Feature/Modules/Request tests/Unit/Modules/Request tests/Architecture/RequestConsumerBoundaryTest.php
docker compose exec laravel.test vendor/bin/phpstan analyse app/Modules/Request
docker compose exec laravel.test vendor/bin/pint --test app/Modules/Request
```

---

## Out of scope (documented only)

| Scenario | Deferred to |
| -------- | ----------- |
| `WaitingForAllocation` → `Allocated` | spec07 |
| Real `DormitoryReadContract` validation | spec04 implementation |
| Workflow engine subscription | Deferred module |
| Livewire employee UI | Post-MVP |
| Lottery draw | spec06 |

---

## Related

- [data-model.md](./data-model.md)
- [research.md](./research.md)
- [contracts/](./contracts/)

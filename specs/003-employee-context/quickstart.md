# Quickstart: Employee Context (spec03)

**Date**: 2026-06-26 | **Plan**: [plan.md](./plan.md)

Validation scenarios for Wave 1A Employee module after implementation. Prerequisites assume spec01 Foundation and spec02 Identity are running.

---

## Prerequisites

```powershell
docker compose up -d
docker compose exec laravel.test php artisan migrate
docker compose exec laravel.test php artisan db:seed --class=IdentityRoleSeeder
```

Create a test Identity user (spec02):

```powershell
docker compose exec laravel.test php artisan identity:user-create "Test Employee User" --email=employee-user@example.com
```

Capture the returned **user UUID** — used as `identity_id` below.

---

## Scenario 1 — Create employee with identity_id (BT-01)

**Proves:** FR-002–FR-004, SC-001, SC-003

1. Run `php artisan employee:create {identity-uuid} --code=EMP001 --first-name=Ali --last-name=Rezaei --national-code=0012345679 --hire-date=2024-01-01`
2. Assert employee row in `employee_employees` with matching `identity_id`
3. Assert `identity_id` unique index — second create with same UUID fails

**Expected:** Success; immutable `identity_id` set once.

---

## Scenario 2 — Reject unknown identity_id (BT-03)

**Proves:** FR-004, SC-001

1. Attempt create with random valid UUID not in `identity_users`
2. Assert domain/application exception — no employee row persisted

**Expected:** Rejected via `IdentityUserReadContract::userExists === false`.

---

## Scenario 3 — Block identity_id mutation (BT-02)

**Proves:** FR-003, SC-003

1. Use feature test or repository guard to attempt `identity_id` update on existing employee
2. Assert `IdentityIdImmutableException` (or equivalent)

**Expected:** Mutation prohibited.

---

## Scenario 4 — Create with disabled Identity user (OA-03-02)

**Proves:** OA-03-02

1. Create Identity user; deactivate via `identity:user-deactivate`
2. Create Employee with that user's UUID
3. Assert success — `isUserActive` not used as create gate

**Expected:** Employee exists while Identity inactive.

---

## Scenario 5 — Department assignment (US2)

**Proves:** FR-005

1. Create department via action/command
2. Assign employee to department
3. Query employee summary — `departmentId` populated

---

## Scenario 6 — Dependent CRUD (US3 / CD-009)

**Proves:** FR-006

1. Add dependent to employee from Scenario 1
2. List dependents for employee
3. Assert `employee_dependents.employee_id` FK; no `request_id` column

---

## Scenario 7 — Eligibility contract (US4 / CD-013)

**Proves:** FR-007, SC-004

Runtime signature (accepted consumer truth / DOC-OPT v1.1.0):

```php
$contract = app(\App\Modules\Employee\Application\Contracts\EmployeeEligibilityContract::class);
$result = $contract->computeRequestEligibility($employeeIdString, $excludingRequestId);

// Active employee + Null ActiveAllocation + no pending → eligible
$result->eligible; // true

// After deactivate employee → employee_inactive reason
```

**Port checks:** Mock `ActiveAllocationReadPort` → `true` asserts `active_allocation_exists`; mock `PendingRequestReadPort` → `true` asserts `pending_request_exists`.  
Feature evidence: `tests/Feature/Modules/Employee/EmployeeEligibilityContractTest.php`.

---

## Scenario 8 — Architecture boundary (BT-05)

```powershell
php artisan test tests/Architecture/EmployeeSupplierBoundaryTest.php
php artisan test tests/Feature/Modules/Employee/EmployeeIdentityBoundaryTest.php
```

**Expected:** Employee module does not import `App\Modules\Identity\Infrastructure\*`.

---

## Scenario 9 — Supplier read contract

**Status: N/A — deferred at Spec03 close** (`SPEC03_ITEM_B_DEFERRED`).

`EmployeeReadContract` / T049–T052 are **not** part of the Spec03 closed deliverable. Do **not** treat this scenario as executable Spec03 Phase 8 DoD. Future delivery requires separate Implementation Authorization.

---

## References

- [data-model.md](./data-model.md)
- [contracts/employee-eligibility-service.md](./contracts/employee-eligibility-service.md)
- [contracts/employee-read-service.md](./contracts/employee-read-service.md)
- spec02 [identity-employee-boundary.md](../002-identity-access/contracts/identity-employee-boundary.md)

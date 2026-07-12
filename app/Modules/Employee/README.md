# Employee Module (spec03)

**Bounded context:** Employee profiles, departments, dependents (CD-009).  
**Upstream:** Identity via `IdentityUserReadContract` only (CD-012) — **never** import `App\Modules\Identity\Infrastructure\*`.

## Prerequisites

Create an Identity user before linking an employee:

```bash
php artisan identity:user-create "Display Name" --email=user@example.com
php artisan employee:create {uuid} --code=EMP001 --first-name=... --last-name=... --national-code=... --hire-date=2024-01-01
```

## Delivered scope (Spec03 closed deliverable path)

| Slice | Status |
| ----- | ------ |
| US1 Employee + immutable `identity_id` | Delivered |
| US2 Department | Delivered |
| US3 Dependent (CD-009) | Delivered |
| US4 Eligibility (CD-013) Batch 1b | Delivered |
| Phase 7 `EmployeeReadContract` | **Deferred at Spec03 close** (`SPEC03_ITEM_B_DEFERRED`) — not delivered |
| Phase F Livewire HR admin | Deferred (separate UI feature path; not Spec03 Phase 8 DoD) |

## Boundaries

- **CD-012:** `employee_employees.identity_id` — immutable UUID, **no FK** to `identity_users`
- **CD-009:** Dependents owned by Employee; no `request_id` on `employee_dependents` in Spec03
- **CD-013:** Eligibility **computed** in Employee; Request **enforces** at submission
- No cross-module Eloquent; no Identity Infrastructure imports (BT-05)
- Mutations go through Application actions + `EmployeeMutationAuthorizationGate`

## Contracts

| Direction | Contract | Notes |
| --------- | -------- | ----- |
| Consumer | `IdentityUserReadContract` | Create-path `userExists` only (OA-03-02) |
| Supplier | `EmployeeEligibilityContract` | `computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null)` — see `contracts/employee-eligibility-service.md` v1.1.0 |
| Supplier | `EmployeeReadContract` | **Deferred at Spec03 close** — do not assume present |

## Eligibility port bindings (production)

| Port | Binding | Where |
| ---- | ------- | ----- |
| `ActiveAllocationReadPort` | `NullActiveAllocationReadAdapter` (always `false`) | `EmployeeServiceProvider` |
| `PendingRequestReadPort` | `PendingRequestReadBridge` (live) | `IntegrationServiceProvider` |

Dual Null stubs are **not** the production PendingRequest path. Live Allocation replacement of the Null ActiveAllocation adapter is out of Spec03 / separately authorized.

## Tests (quickstart mapping)

| Quickstart | Evidence |
| ---------- | -------- |
| Scenarios 1–4 | `tests/Feature/Modules/Employee/EmployeeIdentityBoundaryTest.php` (+ related unit/audit/duplicate tests) |
| Scenario 5 | `tests/Feature/Modules/Employee/DepartmentTest.php` |
| Scenario 6 | `tests/Feature/Modules/Employee/DependentTest.php` |
| Scenario 7 | `tests/Feature/Modules/Employee/EmployeeEligibilityContractTest.php` |
| Scenario 8 | `tests/Architecture/EmployeeSupplierBoundaryTest.php` |
| Scenario 9 | **N/A — deferred** (EmployeeRead) |

## Design references

- `specs/003-employee-context/spec.md`
- `specs/003-employee-context/contracts/employee-eligibility-service.md`
- `specs/003-employee-context/contracts/internal-read-ports.md`
- `specs/003-employee-context/contracts/employee-read-service.md` (design only while deferred)

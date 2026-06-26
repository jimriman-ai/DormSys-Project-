# Data Model: Employee Context (spec03)

**Date**: 2026-06-26 | **Plan**: [plan.md](./plan.md)

---

## Bounded context

**Employee** — aggregate roots: **Employee**, **Department**. **Dependent** is entity within Employee aggregate scope (CD-009).

Employee does **not** import or FK to Identity, Request, or Allocation tables.

---

## 1. Employee (aggregate root)

### Domain entity: `Employee`

| Attribute | Type | Rules |
|-----------|------|-------|
| `id` | `EmployeeId` (UUID v7) | Immutable after persist |
| `identityId` | `UserId` (Identity VO) | Set **once** at create; immutable (BT-01, BT-02) |
| `employeeCode` | string | Unique; organizational identifier |
| `firstName` | string | Required |
| `lastName` | string | Required |
| `nationalCode` | `NationalCode` | Unique; kernel VO validation |
| `departmentId` | `DepartmentId`? | Nullable until assigned |
| `hireDate` | `CarbonImmutable` date | Required |
| `baseLotteryScore` | int | Default `0`; used by Lottery (spec07+) |
| `status` | `EmployeeStatus` | `Active` \| `Inactive` |
| `createdAt` / `updatedAt` | UTC datetime | |

### Invariants

1. `identityId` assigned exactly once; no `reassignIdentityId()`
2. `identityId` unique across all employees (DB unique index)
3. `nationalCode` unique across employees
4. `employeeCode` unique
5. Only `Active` employees pass BR-01 employee-active eligibility rule
6. Assignment to `Inactive` department blocked on create/update

### State transitions

```text
[Active] ──deactivate()──► [Inactive]
[Inactive] ──activate()──► [Active]   (Wave 1A: allowed for HR correction)
```

---

## 2. Persistence: `employee_employees`

| Column | Type | Notes |
|--------|------|-------|
| `id` | `uuid` PK | UUID v7 via `HasUuid` |
| `identity_id` | `uuid` | **Unique**, no FK to Identity |
| `employee_code` | `string` | unique |
| `first_name` | `string` | |
| `last_name` | `string` | |
| `national_code` | `string(10)` | unique |
| `department_id` | `uuid` nullable | FK → `employee_departments.id` (intra-module) |
| `hire_date` | `date` | |
| `base_lottery_score` | `integer` | default 0 |
| `status` | `string` | `active`, `inactive` |
| `created_at` / `updated_at` | `timestamp` | UTC |
| `created_by` / `updated_by` | `uuid` nullable | audit (BaseModel) |
| `deleted_at` | `timestamp` nullable | soft delete |

**Module path:** `database/migrations/modules/employee/`

**Cross-module FK:** none

**Guards:** Application/DB layer must reject `identity_id` updates after insert (BT-02).

---

## 3. Department (aggregate root)

### Domain entity: `Department`

| Attribute | Type | Rules |
|-----------|------|-------|
| `id` | `DepartmentId` | UUID v7 |
| `name` | string | Required |
| `code` | string | Unique |
| `managerId` | `EmployeeId`? | Optional; must reference employee in same module |
| `parentId` | `DepartmentId`? | Optional tree |
| `lotteryPriority` | int | Default 0 |
| `status` | `DepartmentStatus` | `Active` \| `Inactive` |

### Persistence: `employee_departments`

| Column | Type | Notes |
|--------|------|-------|
| `id` | `uuid` PK | |
| `name` | `string` | |
| `code` | `string` | unique |
| `manager_id` | `uuid` nullable | FK → `employee_employees.id` |
| `parent_id` | `uuid` nullable | FK → `employee_departments.id` |
| `lottery_priority` | `integer` | default 0 |
| `status` | `string` | `active`, `inactive` |
| audit + soft delete columns | | BaseModel |

---

## 4. Dependent (entity — CD-009)

### Domain entity: `Dependent`

| Attribute | Type | Rules |
|-----------|------|-------|
| `id` | `DependentId` | UUID v7 |
| `employeeId` | `EmployeeId` | Required; parent employee |
| `firstName` | string | |
| `lastName` | string | |
| `relationship` | `DependentRelationship` | `Spouse`, `Child`, `Parent` |
| `age` | int? | Optional |
| `nationalCode` | `NationalCode`? | Optional |

### Persistence: `employee_dependents`

| Column | Type | Notes |
|--------|------|-------|
| `id` | `uuid` PK | |
| `employee_id` | `uuid` | FK → `employee_employees.id` |
| `first_name` | `string` | |
| `last_name` | `string` | |
| `relationship` | `string` | enum values |
| `age` | `integer` nullable | |
| `national_code` | `string(10)` nullable | |
| audit + soft delete columns | | |

**Deferred:** `request_id` — added in spec05 for snapshot linkage, not Wave 1A.

---

## 5. Value objects

| VO | Location | Usage |
|----|----------|-------|
| `EmployeeId` | `Employee/Domain/ValueObjects/` | Primary key, contract signatures |
| `DepartmentId` | same | Department references |
| `DependentId` | same | Dependent references |
| `UserId` | Identity module | `identityId` field type in Application layer |
| `NationalCode` | `App\Support\ValueObjects\Identity\NationalCode` | Shared kernel |

---

## 6. Application DTOs

### `EmployeeSummaryDTO` (supplier read — optional Wave 1A)

| Field | Type | Notes |
|-------|------|-------|
| `id` | string (UUID) | |
| `identityId` | string (UUID) | |
| `employeeCode` | string | |
| `fullName` | string | Presentation convenience |
| `departmentId` | string? | |
| `status` | string | `active` \| `inactive` |

### `EligibilityResultDTO` (CD-013)

| Field | Type | Notes |
|-------|------|-------|
| `eligible` | bool | |
| `reasonCodes` | `EligibilityReasonCode[]` | Empty when eligible |
| `evaluatedAt` | datetime UTC | Audit/debug |

### `EligibilityReasonCode` (enum)

| Code | BR-01 / rule | Wave 1A |
|------|--------------|---------|
| `EMPLOYEE_INACTIVE` | Employee not active | ✅ Live |
| `ACTIVE_ALLOCATION_EXISTS` | Has active allocation | Stub port |
| `PENDING_REQUEST_EXISTS` | Has pending request | Stub port |
| `IDENTITY_USER_INACTIVE` | Optional warning only | **Not** used as hard gate (OA-03-02) |

---

## 7. Internal ports (not cross-module public API)

| Port | Purpose | Wave 1A adapter |
|------|---------|-----------------|
| `ActiveAllocationReadPort` | `hasActiveAllocation(EmployeeId): bool` | `NullActiveAllocationReadAdapter` → always `false` |
| `PendingRequestReadPort` | `hasPendingRequest(EmployeeId): bool` | `NullPendingRequestReadAdapter` → always `false` |

Replaced by real Allocation/Request module contracts in spec05/spec07.

---

## 8. Explicit non-entities

| Rejected | Reason |
|----------|--------|
| `role_id` on Employee | Identity owns RBAC (R-05) |
| FK `identity_id` → `identity_users` | CD-012 |
| `request_id` on Dependent (Wave 1A) | spec05 snapshot flow |
| Person STI hierarchy | Over-engineering for Wave 1A |

---

## 9. Traceability

| spec.md | Model element |
|---------|---------------|
| FR-001–FR-004 | Employee + `identity_id` |
| FR-005 | Department |
| FR-006 | Dependent |
| FR-007 | EligibilityResultDTO + calculator |
| FR-008 | RecordsActivity on models |
| SC-001–SC-003 | BT-01–BT-03, immutability |
| CD-009 | Dependent.employeeId |
| CD-012 | identity_id column rules |
| CD-013 | Eligibility computation |

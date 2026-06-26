# Research: Employee Context (spec03)

**Date**: 2026-06-26 | **Plan**: [plan.md](./plan.md)

---

## R-01 — UUID primary key version

**Decision:** UUID **v7** via spec01 `HasUuid` (`Ramsey\Uuid\Uuid::uuid7()`).

**Rationale:** Kernel standard; consistent with Identity and Foundation.

**Alternatives considered:** UUID v4 — rejected.

---

## R-02 — `identity_id` attachment (CD-012)

**Decision:** Column `employees.identity_id` — UUID string, **unique**, **no FK** to `identity_users`. Set once in `CreateEmployeeAction` after `IdentityUserReadContract::userExists`.

**Rationale:** Frozen boundary contract; Customer–Supplier coupling Identity → Employee.

**Alternatives considered:** FK constraint — rejected (CD-012); event-only assignment — rejected (OA-03-01 synchronous create).

---

## R-03 — Create-time Identity gate (OA-03-02)

**Decision:** Require `userExists` only; **do not** gate on `isUserActive`.

**Rationale:** Boundary contract line 80 (`isUserActive` optional); HR may link disabled accounts; Employee persists when Identity later deactivated (BT-04 deferred).

**Alternatives considered:** Reject inactive Identity at create — rejected (contradicts OA-03-02).

---

## R-04 — One Employee per Identity user

**Decision:** Unique index on `employees.identity_id` (where not null).

**Rationale:** spec.md edge case — duplicate attachment must fail; one platform user maps to at most one employee profile in Wave 1A.

**Alternatives considered:** Allow duplicates — rejected (downstream Request/Allocation ambiguity).

---

## R-05 — Platform RBAC vs Employee `role_id`

**Decision:** **No** `role_id` on Employee. Authorization uses Identity roles via `identity_id` link.

**Rationale:** Discovery draft listed `role_id`; architecture places Role in Identity (spec02 frozen). Employee holds organizational data only.

**Alternatives considered:** Duplicate role on Employee — rejected (split-brain RBAC).

---

## R-06 — National code validation

**Decision:** Reuse `App\Support\ValueObjects\Identity\NationalCode` from spec01 kernel.

**Rationale:** Shared Iranian national ID validation already implemented; Employee is primary owner of employee `national_code` persistence.

**Alternatives considered:** Inline validation in Employee — rejected (duplication).

---

## R-07 — Employee lifecycle status

**Decision:** Enum `EmployeeStatus: Active | Inactive` on domain + persistence (separate from Identity `UserStatus`).

**Rationale:** BR-01 requires "employee is currently active"; organizational deactivation independent of Identity account disable.

**Alternatives considered:** Mirror Identity status only — rejected (OA-02-02: Employee remains when Identity disabled).

---

## R-08 — Intra-module FK policy

**Decision:** FK constraints **allowed** within Employee module tables (`department_id`, `employee_id` on dependents, `parent_id` on departments).

**Rationale:** Constitution prohibits **cross-module** FKs only (AP-04). Same bounded context may use FK for integrity.

**Alternatives considered:** UUID refs only inside module — rejected (unnecessary complexity for owned aggregates).

---

## R-09 — Dependent ownership (CD-009)

**Decision:** `employee_dependents.employee_id` required FK within module. **No** `request_id` in Wave 1A schema.

**Rationale:** CD-009 — Dependent ∈ Employee; Request snapshots/references deferred to spec05.

**Alternatives considered:** Discovery `request_id` on Dependent as owner — rejected (CD-009 supersedes).

---

## R-10 — Cross-context Identity reads

**Decision:** Inject `IdentityUserReadContract` in Application layer only; never import Identity Infrastructure.

**Rationale:** FR-004, BT-05, frozen `identity-read-service.md`.

**Alternatives considered:** Direct `identity_users` query — rejected.

---

## R-11 — Eligibility ownership (CD-013)

**Decision:** `EmployeeEligibilityContract` in Employee `Application/Contracts/` with `EligibilityCalculator` domain service orchestrating rules.

**Rationale:** Employee computes; Request enforces at submission (spec05). Recorded assumption per catalog.

**Alternatives considered:** Eligibility in Request module — rejected (CD-013).

---

## R-12 — BR-01 partial implementation (Wave 1A)

**Decision:** Wave 1A implements **employee-active** rule fully; **allocation** and **pending request** rules use **internal ports** with **stub adapters** returning safe defaults until spec05/spec07.

| BR-01 rule | Wave 1A |
|------------|---------|
| Employee active | ✅ Implemented |
| No active allocation | ⚠️ Stub port (`ActiveAllocationReadPort` → always `false` in tests) |
| No active pending request | ⚠️ Stub port (`PendingRequestReadPort` → always `false`) |
| Date range validity | ⏸ spec05 (submission-time enforcement) |

**Rationale:** OA-03-03; avoids circular dependency on Request/Allocation modules.

**Alternatives considered:** Full BR-01 now — rejected (modules not implemented).

---

## R-13 — Downstream supplier read API

**Decision:** Optional Wave 1A `EmployeeReadContract` with `findEmployeeSummary(EmployeeId): ?EmployeeSummaryDTO` for future Request module — minimal fields (id, name, department, identity_id).

**Rationale:** Mirrors Identity supplier pattern; enables spec05 without Eloquent cross-queries.

**Alternatives considered:** Eligibility-only API — insufficient for Request display needs.

---

## R-14 — Domain events

**Decision:** Optional `EmployeeCreated` synchronous event in Wave 1A; `EmployeeIdentityAssigned` **not** required (identity_id set at create, same transaction).

**Rationale:** OA-03-01; boundary lists optional event only.

**Alternatives considered:** Mandatory linkage event — rejected (no separate assignment step).

---

## R-15 — Admin UI

**Decision:** **Defer** Livewire HR admin to post-MVP tail (mirror spec02 T035–T037 pattern).

**Rationale:** Wave 1A provable via Actions/Artisan + tests; Persian RTL admin not blocking supplier contracts.

**Alternatives considered:** Full admin UI in Wave 1A — rejected (scope).

---

## R-16 — Support path

**Decision:** Use `app/Support/` (`BaseModel`, `HasUuid`, `NationalCode`, `RecordsActivity`).

**Rationale:** Codebase truth from spec01/spec02.

---

## R-17 — Department deactivation

**Decision:** `DepartmentStatus: Active | Inactive`; inactive departments cannot receive **new** employee assignments; existing employees remain assigned (documented, no cascade delete).

**Rationale:** US2 acceptance scenario 2; avoids orphaned employees.

**Alternatives considered:** Block deactivation if employees assigned — deferred (admin workflow).

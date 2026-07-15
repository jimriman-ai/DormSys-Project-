# Work Breakdown — employee-auth-ui

| ID   | Stage | Title                                    | Status  | Blocker / Evidence |
|------|-------|------------------------------------------|---------|---------------------|
| W-01 | L3    | Spec: login flow & route guards [PARALLEL with W-06] | **CLOSED** | [l3-spec.md](./l3-spec.md) §4; Option B accepted impl |
| W-02 | L3    | Spec: UserModel ↔ Employee relationship  | **CLOSED** | DGAP-07 Decision A — `identity_id` UUID sufficient |
| W-03 | L3    | Spec: password broker decision           | **CLOSED** | RESOLVED — NO ACTION ([l3-spec.md](./l3-spec.md) §2–§3) |
| W-04 | L5    | Auth gate review                         | **CLOSED** | Option B — existing impl as L5 evidence |
| W-05 | L6    | Impl: login Livewire + views             | **CLOSED** | `EmployeeLogin` + `employee.login` + F-W07-01 RateLimiter |
| W-06 | L6    | Impl: IdentityRoleGuard → Shared Kernel [PARALLEL with W-01] | **CLOSED** | `app/Shared/Auth/IdentityRoleGuard.php`; BL-04 Delivered |
| W-07 | L7    | Security review                          | **CLOSED** | [w07-security-review-report.md](./w07-security-review-report.md) — Lead Acceptance |
| W-08 | L8    | Tests                                    | **CLOSED** | [w08-scope.md](./w08-scope.md) — Lead Acceptance; W08-C PASS |

**F2 boundary `employee-auth-ui`:** work items W-01…W-08 **CLOSED**.

**Reconciliation note (Lead Option B, 1405/04/24):** Login + Shared IdentityRoleGuard evidence accepted.

**UI entry:** Not authorized. Next gates = Product authorization for **new** UI slug → Feature Contract → Implementation authorization. Spec04 / DGAP-03/05/06/08 / BL-B1-01 remain parked / deferred as applicable.

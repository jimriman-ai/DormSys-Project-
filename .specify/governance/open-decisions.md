# Open Decisions — DEPRECATED POINTER

> **DEPRECATED / NON-CANONICAL**
>
> This file is **not** a Decision Gate Register.
> **One decision boundary = one canonical record.**
>
> **Canonical register only:** [`docs/governance/open-decisions.md`](../../docs/governance/open-decisions.md)
>
> Do not duplicate Status rows, Metadata, or Changelog here.
> Do not use this file for stage transitions.

*(Historical archaeology removed 1405/04/24 — SGAP disposition control: pointer-only.)*

## Decision Record — 2026-07-20 (Lead)

- **Q-DBT-1-AUTH** → RESOLVED — Option B (Policy-based authorization).
- **Q-DASH-3-ROLE-SOURCE** → RESOLVED — Option A: add `ROLE_EMPLOYEE` constant to
  `app/Shared/Auth/IdentityRoleGuard.php`. Shared guard remains the single role SoT.
- **01-B** → REVIEWED (human gate passed).
- **DBT-1 (ledger sync)** → `listSites()` is DELIVERED; residual scope = UI wiring + authorization only.
- **OQ-DASH-04** → DASH-02 declared CLOSED / VERIFIED by Lead (satisfies WP-DASH-G04).
- **Roadmap protocol** → No new `roadmap-execution-protocol.md` will be created.
  DGAP-15 remains the sole Sprint C sequencing SoT. Its absence MUST NOT be re-raised
  as DECISION_REQUIRED.

## WP-DASH-G09 — CLOSED (2026-07-20)

- Employee role SoT unified into IdentityRoleGuard::ROLE_EMPLOYEE.
- DashboardIdentityRoles.php deprecated → verified zero references → deleted (G09-B).
- Evidence: DashboardNavTest 3/3 pass; Pint/PHPStan clean; pre-deletion grep = 1 hit (stub only).

## Q-EMP-DORM — RESOLVED

| فیلد | مقدار |
|---|---|
| **تاریخ تصویب** | ۱۴۰۵/۰۴/۲۹ (2026-07-20) |
| **تصمیم** | Option B — Assignment-based |
| **مفاد** | دسترسی کارمند به Dormitory محدود به انتصابات صریح است. رابطه: `Employee 1—* DormitoryAssignment *—1 Dormitory`. کارمند فقط Dormitoryهایی را می‌بیند که رکورد انتساب فعال برای او وجود دارد. |
| **مرجع شواهد** | ER sketch (Employee 1—* Dormitory) + DBT-1 residual |
| **تأثیر بر G02** | آرتیفکت‌های Quarantine شده باید بر اساس مدل انتساب بازنویسی شوند |
| **تصویب** | Lead — ۱۴۰۵/۰۴/۲۹ |
| **وضعیت قبلی** | Q-DBT-1-AUTH: تأیید تصمیم، اجرای معوق |
| **وضعیت جدید** | Q-EMP-DORM: RESOLVED → Option B |

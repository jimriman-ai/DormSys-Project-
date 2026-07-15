# Open Decisions — DormSys Governance

> **قانون:** هیچ گزینه‌ای در این فایل به‌عنوان توصیه‌شده علامت‌گذاری نمی‌شود.
> تصمیم نهایی فقط توسط Decision Owner تأیید می‌شود.

---

## Decision Gate Table

| ID | Gap | Cluster | Options | Decision Owner | Trigger / Deadline | Status | Notes |
|----|-----|---------|---------|----------------|--------------------|--------|-------|
| DG-01 | مالکیت boundary F2 و زمان باز شدن آن | Scope Ownership | A) F2 در مالکیت همین ماژول باقی بماند B) F2 به یک boundary مستقل منتقل شود C) F2 به backlog defer شود با owner مشخص و reopen trigger صریح | Lead | شروع برنامه‌ریزی Phase F | DECIDED-DEFERRED-EXECUTION | F2 deferred؛ owner مشخص و reopen trigger صریح ثبت شود. Trigger: شروع رسمی F2 یا نیاز مصرف‌کننده جدید به F2 boundaries. |
| DG-02 | تعریف دقیق Phase F: narrow vs broad | Phase F Definition | A) Phase F فقط employee-records باشد (narrow) B) Phase F شامل UI و Auth مرتبط هم باشد (broad) C) Phase F به دو زیرفاز تقسیم شود | Lead | قبل از L0 Phase F | DECIDED | Phase F به دو زیرفاز تقسیم می‌شود: F1 (employee-records — تکمیل‌شده) و F2 (UI/Auth — deferred، وابسته به DG-01). |
| DG-03 | مالکیت Identity Helper: ماژولی vs shared kernel | Identity Helper Ownership | A) Identity Helper در مالکیت dormitory-admin-ui بماند B) به shared kernel منتقل شود C) implementation محلی در هر boundary تا زمان تصمیم shared-kernel مجاز بماند | Lead | قبل از استفاده در boundary دوم | DECIDED-DEFERRED-EXECUTION | IdentityRoleGuard در مالکیت dormitory-admin-ui باقی می‌ماند. Reopen trigger: پیدایش مصرف‌کننده دوم → ارتقا به shared kernel بازبینی شود. |
| DG-04 | مالک ریسک‌های پذیرفته‌شده و cadence بازبینی | Governance | A) Lead مالک تمام accepted risks باشد، بازبینی ۶ ماهه B) هر boundary مالک ریسک‌های خود باشد C) یک Risk Register مرکزی با مالک مشخص per-risk | Lead | قبل از merge PR فاز G | DECIDED | Risk Register مرکزی در docs/security/risk-register.md ایجاد می‌شود؛ هر accepted risk یک owner انسانی مشخص و review cadence دارد. SEC-G-04 اولین entry است. همسو با BL-03. |
| DG-05 | سیاست استفاده از Student vs Employee در UI و کد | Terminology | A) Student در UI عمومی، Employee در کد داخلی B) یکسان‌سازی کامل روی یک واژه C) glossary رسمی با mapping صریح | Lead | قبل از شروع Phase F UI | DECIDED | Glossary رسمی در docs/governance/glossary.md ایجاد می‌شود با mapping صریح: Student (UI/فارسی) ↔ Employee (کد/schema). واژگان کد تغییر نمی‌کنند. |

---

## Decision Metadata

### DG-01

- **Selected Option:** C
- **Decided-On:** 1405/04/24
- **Decision-Owner:** Lead
- **Decision-Method:** Evidence-grounded Options (طبق governance method پروژه)

### DG-02

- **Selected Option:** C
- **Decided-On:** 1405/04/24
- **Decision-Owner:** Lead
- **Decision-Method:** Evidence-grounded Options (طبق governance method پروژه)

### DG-03

- **Selected Option:** A
- **Decided-On:** 1405/04/24
- **Decision-Owner:** Lead
- **Decision-Method:** Evidence-grounded Options (طبق governance method پروژه)

### DG-04

- **Selected Option:** C
- **Decided-On:** 1405/04/24
- **Decision-Owner:** Lead
- **Decision-Method:** Evidence-grounded Options (طبق governance method پروژه)

### DG-05

- **Selected Option:** C
- **Decided-On:** 1405/04/24
- **Decision-Owner:** Lead
- **Decision-Method:** Evidence-grounded Options (طبق governance method پروژه)

---

## Pending Artifacts

- [ ] `docs/security/risk-register.md` (از DG-04) — شامل مهاجرت SEC-G-04
- [ ] `docs/governance/glossary.md` (از DG-05) — mapping Student/Employee
- [ ] Roadmap update: split Phase F → F1/F2 (از DG-02)

---

## B1 — Assignment migrations disposition (L9-R Phase G)

**Rule (locked):** IF grep empty → Option 2 (remove + backlog); IF non-empty → Option 1 (extract foundation + rebase).

### Grep evidence (2026-07-15)

Command (excludes the two migration files themselves):

```text
rg -n "dormitory_manager_assignments" app tests database --glob "*.php" --glob "!**/2026_07_15_000001*" --glob "!**/2026_07_15_000002*"
```

Result (**NON-EMPTY**):

```text
tests\Feature\Modules\DormitoryAdmin\DormitoryManagerDashboardTest.php:145:    DB::table('dormitory_manager_assignments')->insert([
app\Modules\DormitoryAdmin\DormitoryManagerDashboard.php:44:            ->join('dormitory_manager_assignments as dma', function ($join) use ($userId): void {
```

Also referenced (unit assignments): `dormitory_unit_manager_assignments` in `DormitoryUnitManagerDashboard.php` and `DormitoryUnitManagerDashboardTest.php`.

### Executed option

**Option 1 — extract + rebase**

- Branch: `foundation/dormitory-admin-tables` (commit `60d9b6c` — only the two migrations).
- `security/g-phase-dormitory-admin-ui` rebased `--onto foundation/dormitory-admin-tables` from `origin/011-reporting-projections`.
- Verify: `git diff --name-only foundation/dormitory-admin-tables...HEAD` must **not** list `2026_07_15_000001` / `_000002`.
- PR integration note: until foundation is merged into `011-reporting-projections`, security PR base should be `foundation/dormitory-admin-tables` (or foundation merges first).

Former `l9-b1-migration-split-note.md` content absorbed here; that note file is deleted.

---

## Changelog

| تاریخ | تغییر | توسط |
|-------|-------|------|
| ۱۴۰۵/۰۴/۲۴ | ایجاد فایل، ثبت ۵ gap اولیه | AI assistant |
| ۱۴۰۵/۰۴/۲۴ | اصلاح DG-01/C، DG-03/C، قانون ۳ | AI assistant |
| ۱۴۰۵/۰۴/۲۴ | DG-01 / DG-03 / DG-04 → DECIDED per L5-G-CANONICAL-01 (D-G-10, D-G-11, D-G-13) | Lead (recorded) |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | DG-01…DG-05 finalized by Lead. Selections: DG-01(C), DG-02(C), DG-03(A), DG-04(C), DG-05(C). DG-01 و DG-03 دارای reopen trigger هستند (status: DECIDED-DEFERRED-EXECUTION). | Lead |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | B1 Option 1 executed (grep NON-EMPTY); foundation/dormitory-admin-tables extracted; security rebased; l9-b1 note absorbed | Agent (L9-R Round 2) |

---

## قوانین این فایل

1. هیچ گزینه‌ای بدون تأیید صریح Decision Owner به `DECIDED` تغییر نمی‌کند.
2. هر تصمیم باید با ID، تاریخ، و نام تأییدکننده در Changelog ثبت شود.
3. شناسه هر gap immutable است. به‌روزرسانی Status / Notes / Decision Owner فقط با ثبت changelog مجاز است.
4. gap جدید فقط با شناسه‌ی بعدی (`DG-06`, ...) اضافه می‌شود.

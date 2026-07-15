# Open Decisions — DormSys Governance

> **قانون:** هیچ گزینه‌ای در این فایل به‌عنوان توصیه‌شده علامت‌گذاری نمی‌شود.
> تصمیم نهایی فقط توسط Decision Owner تأیید می‌شود.
>
> **Canonical register:** This file (`docs/governance/open-decisions.md`) is the authoritative Decision Gate Register.
> `.specify/governance/open-decisions.md` is non-canonical / deprecated reference only.

---

## Decision Gate Table

| ID | Gap | Cluster | Options | Decision Owner | Trigger / Deadline | Status | Notes |
|----|-----|---------|---------|----------------|--------------------|--------|-------|
| DG-01 | مالکیت boundary F2 و زمان باز شدن آن | Scope Ownership | A) F2 در مالکیت همین ماژول باقی بماند B) F2 به یک boundary مستقل منتقل شود C) F2 به backlog defer شود با owner مشخص و reopen trigger صریح | Lead | شروع برنامه‌ریزی Phase F | RESOLVED | F2 deferred. **B1 removal COMPLETE** (Round 2.1); deleted assignment tests → **BL-B1-01** in `docs/governance/risk-register.md`. RESOLVED at F2 kick-off: F2 proceeds as an independent boundary `employee-auth-ui`. B1 removal remains final; removed tests remain tracked under BL-B1-01 (unchanged, still Open/Deferred). |
| DG-02 | تعریف دقیق Phase F: narrow vs broad | Phase F Definition | A) Phase F فقط employee-records باشد (narrow) B) Phase F شامل UI و Auth مرتبط هم باشد (broad) C) Phase F به دو زیرفاز تقسیم شود | Lead | قبل از L0 Phase F | DECIDED | F1 (employee-records — تکمیل‌شده) و F2 (UI/Auth) — split per Option C. F2 ACTIVE under DG-01 RESOLVED + product-authorization-employee-auth-ui; Option B reconciliation accepts W-01/W-06 evidence. |
| DG-03 | مالکیت Identity Helper: ماژولی vs shared kernel | Identity Helper Ownership | A) Identity Helper در مالکیت dormitory-admin-ui بماند B) به shared kernel منتقل شود C) implementation محلی در هر boundary تا زمان تصمیم shared-kernel مجاز بماند | Lead | قبل از استفاده در boundary دوم | RESOLVED | Reopen trigger met (second consumer = employee-auth-ui). Lead: migrate IdentityRoleGuard to Shared Kernel. **W-06 executed:** `app/Shared/Auth/IdentityRoleGuard.php`. Residual formal L7/L8 still tracked in employee-auth-ui work-breakdown. |
| DG-04 | مالک ریسک‌های پذیرفته‌شده و cadence بازبینی | Governance | A) Lead مالک تمام accepted risks باشد، بازبینی ۶ ماهه B) هر boundary مالک ریسک‌های خود باشد C) یک Risk Register مرکزی با مالک مشخص per-risk | Lead | قبل از merge PR فاز G | DECIDED | **DELIVERED** `docs/governance/risk-register.md` (1405/04/24). SEC-G-04 + BL-B1-01 seeded. |
| DG-05 | سیاست استفاده از Student vs Employee در UI و کد | Terminology | A) Student در UI عمومی، Employee در کد داخلی B) یکسان‌سازی کامل روی یک واژه C) glossary رسمی با mapping صریح | Lead | قبل از شروع Phase F UI | DECIDED | **DELIVERED** `docs/governance/glossary.md` (1405/04/24). Student ↔ Employee mapping. |
| DGAP-07 | F2 W-02: Eloquent/Application relation UserModel↔Employee vs `identity_id` UUID value-reference | Domain Gap / F2 W-02 | A) UUID reference sufficient — close W-02 as-is B) Eloquent relation required — new scoped work item, NOT silent code addition | Lead | F2 W-07/W-08 scoping | DECIDED | **Selected A** (Lead, 2026/07/15): UUID value-reference sufficient — close W-02 as-is. No Eloquent/Application UserModel↔Employee relation required. Source: Domain Gap Audit 2026-07-15. |
| DGAP-03 | Department↔Dormitory / Organization structural link | Domain Gap / Spec04 Auth | — (parked; options deferred) | Lead | Spec04 Auth packet | OPEN / PARKED | Blocker: **DGAP-08**. Source: Domain Gap Audit 2026-07-15. Not for answer now. |
| DGAP-05 | Approver actor binding / stage visibility | Domain Gap / Spec04 Auth | — (parked; options deferred) | Lead | Spec04 Auth packet | OPEN / PARKED | Blocker: **DGAP-08**. Source: Domain Gap Audit 2026-07-15. Not for answer now. |
| DGAP-06 | Department.managerId vs Stage-1 approver binding | Domain Gap / Spec04 Auth | — (parked; options deferred) | Lead | Spec04 Auth packet | OPEN / PARKED | Blocker: **DGAP-08**. Source: Domain Gap Audit 2026-07-15. Not for answer now. |
| DGAP-08 | Business Owner designation | Domain Gap / HDAC | — (parked; designation by org authority) | human org authority (HDAC track) | HDAC track (root blocker) | OPEN / PARKED | Root blocker for DGAP-03/05/06. Source: Domain Gap Audit 2026-07-15. Not for answer now via this gate. |
| DGAP-01 | Organization aggregate | Domain Gap Audit | CLOSE — NOT-A-GAP | Lead | DGAP Decision Gate | **CLOSED — NOT-A-GAP** | Lead (2026/07/15): No product requirement for Organization aggregate. No L6 fill. |
| DGAP-02 | Unit entity | Domain Gap Audit | CLOSE — NOT-A-GAP | Lead | DGAP Decision Gate | **CLOSED — NOT-A-GAP** | Lead (2026/07/15): Unit only meaningful under assignment schema (frozen). No L6 fill. |
| DGAP-04 | Workflow module / engine | Domain Gap Audit | CLOSE — NOT-A-GAP | Lead | DGAP Decision Gate | **CLOSED — NOT-A-GAP** | Lead (2026/07/15): Workflow shell intentional per CD-010 deferral. No L6 fill. |
| DGAP-10 | Dual User model (`App\Models\User` vs `UserModel`) | Domain Gap Audit | CLOSE — NOT-A-GAP by design | Lead | DGAP Decision Gate | **CLOSED — NOT-A-GAP by design** | Lead (2026/07/15): Intentional dual-guard architecture. No L6 fill. |
| DGAP-09 | Manager/unit assignment schema | Domain Gap / BL-B1-01 | NO ACTION this phase | Lead | DGAP Decision Gate | **FROZEN — NO ACTION (F2)** | Lead (2026/07/15): Remains frozen under BL-B1-01. Do not reopen without formal unfreeze. |
| SGAP-01 | Spec001 Status Draft vs delivered | Spec Completion Audit | DOC Status sync | Lead | SGAP Disposition | **CLOSED** | Status header → delivered/CLOSED (DOC-only). |
| SGAP-02 | Spec006 missing research/quickstart | Spec Completion Audit | ACCEPTED-MINIMAL | Lead | SGAP Disposition | **ACCEPTED-MINIMAL** | Intentional post-impl; do not create artifacts. |
| SGAP-03 | Spec007 missing research/quickstart | Spec Completion Audit | ACCEPTED-MINIMAL | Lead | SGAP Disposition | **ACCEPTED-MINIMAL** | Intentional post-impl; do not create artifacts. |
| SGAP-04 | Spec008 missing data-model/contracts | Spec Completion Audit | DOC mirror from Voucher code | Lead | SGAP Disposition | **CLOSED** | data-model.md + contracts/ mirrored from `app/Modules/Voucher` only. |
| SGAP-05 | Spec006 GOVERNANCE_OPEN / AUTHORITY_NOT_AVAILABLE | Spec Completion Audit | PARK | Lead | SGAP Disposition | **PARKED** | Unlock gate shared with DGAP-08 (BO designation). Separate entry — not conflated. |
| SGAP-06 | CLAUDE/AGENTS CheckIn “candidate” wording | Spec Completion Audit | DOC wording sync | Lead | SGAP Disposition | **CLOSED** | Synced to Spec07 CLOSED + `app/Modules/CheckIn`. |
| SGAP-07 | Spec04 Product PENDING_RESIDUAL | Spec Completion Audit | Backlog + PARK | Lead | SGAP Disposition | **BACKLOG + PARKED** | Explicit backlog; Spec04 Auth packet / DGAP-08 untouched. |
| SGAP-08 | Spec011 outside audit list | Spec Completion Audit | DEFER | Lead | SGAP Disposition | **DEFERRED** | Separate audit only if 011 enters UI path. |
| SGAP-09 | debug.log under specs 008/009/010 | Spec Completion Audit | Cleanup | Lead | SGAP Disposition | **CLOSED** | debug.log files deleted. |

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

### DGAP-07

- **Status:** DECIDED
- **Selected Option:** A
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Decision-Owner:** Lead
- **Decision-Method:** Human Decision Gate (Lead answer)
- **Source:** Domain Gap Audit (READ-ONLY, 2026-07-15)
- **Tier:** 1 — was ACTIVE (F2 W-07/W-08 scoping)
- **Options:**
  - A) UUID reference sufficient — close W-02 as-is
  - B) Eloquent relation required — new scoped work item, NOT silent code addition
- **Effect:** Existing `identity_id` UUID value-reference is sufficient. No Eloquent/Application UserModel↔Employee relation. W-02 close-as-is (feature-doc sync is separate from this register update).

### DGAP-03

- **Status:** OPEN / PARKED
- **Blocker:** DGAP-08
- **Decision-Owner:** Lead
- **Source:** Domain Gap Audit (READ-ONLY, 2026-07-15)
- **Tier:** 2 — PARKED (Spec04 Auth packet)
- **Selected Option:** — (not for answer now)

### DGAP-05

- **Status:** OPEN / PARKED
- **Blocker:** DGAP-08
- **Decision-Owner:** Lead
- **Source:** Domain Gap Audit (READ-ONLY, 2026-07-15)
- **Tier:** 2 — PARKED (Spec04 Auth packet)
- **Selected Option:** — (not for answer now)

### DGAP-06

- **Status:** OPEN / PARKED
- **Blocker:** DGAP-08
- **Decision-Owner:** Lead
- **Source:** Domain Gap Audit (READ-ONLY, 2026-07-15)
- **Tier:** 2 — PARKED (Spec04 Auth packet)
- **Selected Option:** — (not for answer now)

### DGAP-08

- **Status:** OPEN / PARKED
- **Decision-Owner:** human org authority (HDAC track)
- **Source:** Domain Gap Audit (READ-ONLY, 2026-07-15)
- **Tier:** 2 — PARKED (root blocker for Spec04 Auth–parked items)
- **Selected Option:** — (not for answer now via this gate)

### DGAP-01

- **Status:** CLOSED — NOT-A-GAP
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Decision-Owner:** Lead
- **Decision:** No product requirement for Organization aggregate. No L6 fill.

### DGAP-02

- **Status:** CLOSED — NOT-A-GAP
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Decision-Owner:** Lead
- **Decision:** Unit only meaningful under assignment schema (frozen). No L6 fill.

### DGAP-04

- **Status:** CLOSED — NOT-A-GAP
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Decision-Owner:** Lead
- **Decision:** Workflow shell intentional per CD-010 deferral. No L6 fill.

### DGAP-10

- **Status:** CLOSED — NOT-A-GAP by design
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Decision-Owner:** Lead
- **Decision:** Dual `User` / `UserModel` is intentional dual-guard architecture. No L6 fill.

#### Evidence Matrix

> PR-N5 documentation enrichment only (Lead 2026-07-15). Status / Decided-On / Decision unchanged.
> Cross-module `UserModel` imports in Auth/Http layers: **Accepted Intentional** (auth principal type-cast / PEP — not cross-module CRUD Eloquent).

| Axis | `App\Models\User` | `UserModel` (Identity Infrastructure) | Domain `Identity\Domain\Entities\User` |
|------|-------------------|----------------------------------------|----------------------------------------|
| Path | `app/Models/User.php` | `app/Modules/Identity/Infrastructure/Persistence/Models/UserModel.php` | `app/Modules/Identity/Domain/Entities/User.php` |
| Table | `users` (`database/migrations/0001_01_01_000000_create_users_table.php`) | `identity_users` (`database/migrations/modules/identity/2026_06_26_000001_create_identity_users_table.php`) | N/A |
| PK | bigint (`$table->id()`) | UUID (`BaseModel` → `HasUuid`) | `UserId` VO |
| Auth traits | `HasFactory`, `Notifiable` — **no** `HasUuid` / `HasRoles` | `Authenticatable` + `HasRoles`; `getAuthPassword()` throws | none |
| Spatie `$guard_name` | N/A | `['web', 'identity']` | N/A |
| Provider | `users` → `User::class` (`config/auth.php`) | `identity` → `UserModel::class` (`config/auth.php`) | N/A |
| Guards | `web` | `api`, `identity` | N/A |
| Password broker | `passwords.users` → provider `users` | none | N/A |
| Role | Credential principal (`SessionAuthenticator` / `Auth::attempt`) | Session principal after bind (`EstablishApiSession…` → `loginUsingId` on `api`+`identity`); Spatie `default_model` (`config/permission.php`) | Domain entity only |

Corrected finding: early assumption “`User.php` uses HasUuids” is **false** — UUID applies only to `UserModel` via `BaseModel`.

### DGAP-09

- **Status:** FROZEN — NO ACTION (F2)
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Decision-Owner:** Lead
- **Decision:** Remains frozen under BL-B1-01. Do not reopen without formal unfreeze.

### SGAP Disposition (Spec Completion Audit, Lead 2026/07/15)

| ID | Status | One-line |
|----|--------|----------|
| SGAP-01 | CLOSED | Spec001 Status header synced to delivered/CLOSED. |
| SGAP-02 | ACCEPTED-MINIMAL | Spec006 missing research/quickstart intentional; no files created. |
| SGAP-03 | ACCEPTED-MINIMAL | Spec007 missing research/quickstart intentional; no files created. |
| SGAP-04 | CLOSED | Spec008 data-model + contracts mirrored from Voucher code only. |
| SGAP-05 | PARKED | Spec06 GOVERNANCE_OPEN / AUTHORITY_NOT_AVAILABLE; unlock gate = DGAP-08 BO (not conflated). |
| SGAP-06 | CLOSED | CLAUDE.md / AGENTS.md CheckIn wording synced to Spec07 + module. |
| SGAP-07 | BACKLOG + PARKED | Spec04 Product PENDING_RESIDUAL → backlog below; Auth packet untouched. |
| SGAP-08 | DEFERRED | Spec011 observation only until UI-path entry. |
| SGAP-09 | CLOSED | Deleted `debug.log` under specs/008, 009, 010. |

### Backlog — SGAP-07 (Spec04 Product residual)

- **Item:** Spec04 Product layer `PENDING_RESIDUAL` (composite GDR) — track as product residual backlog, not Spec Auth packet work.
- **Status:** PARKED pending product disposition.
- **Forbidden:** Do not reopen Spec04 Auth packet or DGAP-08 via this item.

---

## F2 Process Re-sync (Option B)

- **Decision:** Option B — Governance Reconciliation
- **Decided-By:** Lead
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Accepted evidence:** `EmployeeLogin` (`app/Modules/Auth/Presentation/Livewire/EmployeeLogin.php`), `IdentityRoleGuard` (`app/Shared/Auth/IdentityRoleGuard.php`), related routes in `routes/web.php` (`employee.login`)
- **Effect:** W-04 PASS/CLOSED; W-05 and W-06 IMPLEMENTED in `docs/features/employee-auth-ui/work-breakdown.md`. L3 §4 updated accordingly.
- **Freeze:** No *new* F2/UI/Auth features until further Lead authorization. W-07/W-08 remain Pending.

---

## Pending Artifacts

- [x] `docs/governance/risk-register.md` (از DG-04) — delivered 1405/04/24; includes SEC-G-04 + BL-B1-01
- [x] `docs/governance/glossary.md` (از DG-05) — delivered 1405/04/24; Student/Employee mapping
- [x] Roadmap update: split Phase F → F1/F2 (از DG-02) — delivered `docs/governance/roadmap.md`

---

## Changelog

| تاریخ | تغییر | توسط |
|-------|-------|------|
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **SGAP Disposition:** SGAP-01/04/06/09 CLOSED (DOC); SGAP-02/03 ACCEPTED-MINIMAL; SGAP-05 PARKED (gate≈DGAP-08); SGAP-07 BACKLOG+PARK; SGAP-08 DEFERRED (011). No code. | Lead |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **DGAP Decision Gate:** DGAP-01/02/04/10 CLOSED (NOT-A-GAP); DGAP-09 FROZEN — NO ACTION (F2) under BL-B1-01. No L6 fill. Parked DGAP-03/05/06/08 untouched. | Lead |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **DGAP-07 DECIDED (A):** UUID `identity_id` value-reference sufficient — close W-02 as-is. No Eloquent UserModel↔Employee relation. Lead answer. Tier 2 DGAP-03/05/06/08 remain OPEN/PARKED. | Lead |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **Human Decision Gate — Domain Gap Audit registration:** Registered DGAP-07 (OPEN/ACTIVE), DGAP-03/05/06 (OPEN/PARKED, blocker DGAP-08), DGAP-08 (OPEN/PARKED, HDAC). Source: Domain Gap Audit READ-ONLY 2026-07-15. NOT-A-GAP items (DGAP-01/02/04/10) not registered. No answers recorded. | Agent (Decision Gate) |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **Option B — Governance Reconciliation:** Existing F2 impl evidence accepted (EmployeeLogin, Shared IdentityRoleGuard, employee.login). Canonical register affirmed = this file. W-04 closed; W-05/W-06 marked implemented; no new F2 features until further Lead auth. | Lead |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | [F2 kick-off] DG-01 RESOLVED (independent boundary employee-auth-ui); DG-03 RESOLVED (IdentityRoleGuard → Shared Kernel, execution = BL-04 / F2-L6). BL-04 formalized in risk-register.md. | Lead |
| ۱۴۰۵/۰۴/۲۴ | ایجاد فایل، ثبت ۵ gap اولیه | AI assistant |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | DG-01…DG-05 finalized by Lead. Selections: DG-01(C), DG-02(C), DG-03(A), DG-04(C), DG-05(C). | Lead |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **DG-01:** B1 removal COMPLETE; dangling assignment refs cleared; deleted tests logged in **BL-B1-01** | Agent (L9-R Round 2.1) |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **DG-04:** `docs/governance/risk-register.md` DELIVERED | Agent (L9-R Round 2.1) |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **DG-05:** `docs/governance/glossary.md` DELIVERED | Agent (L9-R Round 2.1) |
| ۱۴۰۵/۰۴/۲۴ | DG-02 roadmap artifact delivered (`docs/governance/roadmap.md`, program-level canonical) | Lead |

---

## قوانین این فایل

1. هیچ گزینه‌ای بدون تأیید صریح Decision Owner به `DECIDED` تغییر نمی‌کند.
2. هر تصمیم باید با ID، تاریخ، و نام تأییدکننده در Changelog ثبت شود.
3. شناسه هر gap immutable است. به‌روزرسانی Status / Notes / Decision Owner فقط با ثبت changelog مجاز است.
4. gap جدید فقط با شناسه‌ی بعدی (`DG-06`, ...) اضافه می‌شود.

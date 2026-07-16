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
| DG-02 | تعریف دقیق Phase F: narrow vs broad | Phase F Definition | A) Phase F فقط employee-records باشد (narrow) B) Phase F شامل UI و Auth مرتبط هم باشد (broad) C) Phase F به دو زیرفاز تقسیم شود | Lead | قبل از L0 Phase F | DECIDED | F1 (employee-records — تکمیل‌شده) و F2 (UI/Auth) — split per Option C. F2 status **PARTIAL** (W-01…W-08 CLOSED — `docs/features/employee-auth-ui/work-breakdown.md:14`; F-W07-04 CARRIED FORWARD — `docs/features/employee-auth-ui/w07-security-review-report.md:19`). Prior “F2 ACTIVE” note superseded. (reconciled 2026-07-15, ref: DGAP-12) |
| DG-03 | مالکیت Identity Helper: ماژولی vs shared kernel | Identity Helper Ownership | A) Identity Helper در مالکیت dormitory-admin-ui بماند B) به shared kernel منتقل شود C) implementation محلی در هر boundary تا زمان تصمیم shared-kernel مجاز بماند | Lead | قبل از استفاده در boundary دوم | RESOLVED | Reopen trigger met (second consumer = employee-auth-ui). Lead: migrate IdentityRoleGuard to Shared Kernel. **W-06 executed:** `app/Shared/Auth/IdentityRoleGuard.php`. W-07/W-08 subsequently **CLOSED** (`work-breakdown.md:11-12`). (reconciled 2026-07-15, ref: DGAP-12) |
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
| DGAP-09 | Manager/unit assignment schema | Domain Gap / BL-B1-01 | NO ACTION this phase | Lead | DGAP Decision Gate | **FROZEN (RE-FROZEN 2026-07-16 after scoped BL-B1-01)** | Temporary unfreeze YES scoped to BL-B1-01 only (Lead); schema+wire executed RM-BL-B1; **RE-FROZEN** — no further schema without new unfreeze. |
| SGAP-01 | Spec001 Status Draft vs delivered | Spec Completion Audit | DOC Status sync | Lead | SGAP Disposition | **CLOSED** | Status header → delivered/CLOSED (DOC-only). |
| SGAP-02 | Spec006 missing research/quickstart | Spec Completion Audit | ACCEPTED-MINIMAL | Lead | SGAP Disposition | **ACCEPTED-MINIMAL** | Intentional post-impl; do not create artifacts. |
| SGAP-03 | Spec007 missing research/quickstart | Spec Completion Audit | ACCEPTED-MINIMAL | Lead | SGAP Disposition | **ACCEPTED-MINIMAL** | Intentional post-impl; do not create artifacts. |
| SGAP-04 | Spec008 missing data-model/contracts | Spec Completion Audit | DOC mirror from Voucher code | Lead | SGAP Disposition | **CLOSED** | data-model.md + contracts/ mirrored from `app/Modules/Voucher` only. |
| SGAP-05 | Spec006 GOVERNANCE_OPEN / AUTHORITY_NOT_AVAILABLE | Spec Completion Audit | PARK | Lead | SGAP Disposition | **PARKED** | Unlock gate shared with DGAP-08 (BO designation). Separate entry — not conflated. |
| SGAP-06 | CLAUDE/AGENTS CheckIn “candidate” wording | Spec Completion Audit | DOC wording sync | Lead | SGAP Disposition | **CLOSED** | Synced to Spec07 CLOSED + `app/Modules/CheckIn`. |
| SGAP-07 | Spec04 Product PENDING_RESIDUAL | Spec Completion Audit | Backlog + PARK | Lead | SGAP Disposition | **BACKLOG + PARKED** | Explicit backlog; Spec04 Auth packet / DGAP-08 untouched. |
| SGAP-08 | Spec011 outside audit list | Spec Completion Audit | DEFER | Lead | SGAP Disposition | **DEFERRED** | Separate audit only if 011 enters UI path. |
| SGAP-09 | debug.log under specs 008/009/010 | Spec Completion Audit | Cleanup | Lead | SGAP Disposition | **CLOSED** | debug.log files deleted. |
| DGAP-11 | Roadmap ↔ UI Productization reconciliation | Governance / Roadmap | A) merge into Roadmap as UI-1..UI-7 B) keep separate doc with cross-refs | Lead | F3 Sprint A entry | **CLOSED — RESOLVED** (2026-07-15) | Lead AUTHORIZED Option A (scope corrected): canonical catalog **UI-M1, UI-M2, UI-A1** in `docs/governance/roadmap.md` § F3 Catalog (supersedes UI-1…UI-7 claim). Artifact delivered; F3 ACTIVE — Sprint A. |
| DGAP-12 | Governance doc hard-conflict reconciliation (F2 / G / DGAP-11) | Governance / Roadmap | Docs-only status sync per Lead DGAP-12 rules | Lead | Immediate | **EXECUTED — DOCS** (reconciled 2026-07-15, ref: DGAP-12) | Conflicts 1–3 applied in `docs/governance/roadmap.md`, this file, `docs/features/employee-auth-ui/feature-brief.md`. No code. No commit. |
| UI-M1-COV | UI-M1 residual test-coverage risk (S-2, S-4, S-5) | UI-M1 / Verification | ACCEPT residual depth; mitigate via dedicated test DB hygiene | Lead | UI-M1 L8 closeout | **ACCEPTED** (Lead, 2026-07-16) | Boundary: UI-M1 audit-history / dashboard verification depth. S-2 dual-session e2e ACCEPT-BY-RISK; S-4 raw-query grep CI ACCEPT (defer N-11); S-5 injection fuzz ACCEPT-BY-RISK. Hygiene: suite must use dedicated DB — overlapping runs on shared DB caused transient `40P01` (FLAKE, L8-RERUN). |

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

- **Status:** **FROZEN (RE-FROZEN 2026-07-16 after scoped BL-B1-01)**
- **Decided-On:** 1405/04/24 (2026/07/15); temporary unfreeze + RE-FREEZE 2026-07-16
- **Decision-Owner:** Lead
- **Decision (historical):** Remains frozen under BL-B1-01. Do not reopen without formal unfreeze.
- **Scoped unfreeze (Lead, 2026-07-16):** YES — BL-B1-01 only; `user_id` FK = CONSTRAINED_IDENTITY + `restrictOnDelete()`.
- **Execution:** RM-BL-B1 (RM-01/02/04/05/06/07) restored assignment tables + dashboard wire + tests. RM-03 Eloquent models skipped (Q1=B).
- **Post-execution:** **RE-FROZEN**. Further assignment-schema work requires a new formal unfreeze.

### DGAP-11

- **Status:** **CLOSED — RESOLVED** (2026-07-15)
- **Prior Status (historical):** CLOSED — DECIDED (undelivered) → **REOPENED** (DGAP-12, artifact missing) → closed herein
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Decision-Owner:** Lead
- **Decision (historical, Option A — UI-1…UI-7):** Merge UI Productization into the phase Roadmap; rename items UI-1..UI-7. Decided by Lead on 2026-07-15. **Superseded** by Lead AUTHORIZED Option A (scope corrected), 2026-07-15.
- **Problem:** The UI Productization document (L6.1–L6.7) existed outside the phase Roadmap, creating two parallel planning sources and a numbering collision with the L-cycle.
- **Options considered:**
  - Option A — merge UI Productization into the Roadmap, rename items to UI-1..UI-7 (**CHOSEN historically; undelivered**)
  - Option B — keep as separate document with cross-references (**REJECTED:** dual source of truth)
- **Evidence (historical claim — INVALIDATED):** Merged Roadmap section claiming UI-1…UI-7 without content.
- **Evidence (verification 2026-07-15):** Repo-wide search found no UI-1…UI-7 definitions; F3 stub only (DGAP-12).
- **Resolution (Lead AUTHORIZED Option A — scope corrected, 2026-07-15):**
  - Canonical catalog: **UI-M1**, **UI-M2**, **UI-A1** (supersedes UI-1…UI-7 claim).
  - **UI-M1:** Manager Dashboard — wire data (dep: BL-B1-01).
  - **UI-M2:** Unit-Manager Dashboard — wire data (dep: BL-B1-01).
  - **UI-A1:** Auth layout / identity guard integration (`IdentityRoleGuard`, dual-guard). Assignment schema/UI is **not** UI-A1; propose **UI-A2** separately if needed.
  - **Artifact:** `docs/governance/roadmap.md` § **F3 — Catalog (Sprint A)** (all three items **PENDING**).
  - **Phase entry:** F3 **ACTIVE — Sprint A**. Docs-only; no PHP/migrations authorized by this closure.
- **Follow-up (open):** Implement / L3 per UI-M1/M2/A1 under separate Lead authorization. F-W07-04 remains CARRIED FORWARD (target F3 Sprint A or later) under F2 ID.

### DGAP-12

- **Status:** EXECUTED — DOCS (reconciled 2026-07-15, ref: DGAP-12)
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Decision-Owner:** Lead (supervised reconciliation)
- **Decision:** Apply docs-only hard-conflict reconciliation rules for (1) F2 PARTIAL vs stale ACTIVE, (2) G PARTIAL vs L9 NOT READY, (3) DGAP-11 REOPENED for undelivered UI merge.
- **Scope:** `docs/` only. No code. No commit.

### UI-M1 residual test-coverage risk — ACCEPTED (Lead, 2026-07-16)

- **ID:** UI-M1-COV
- **Status:** **ACCEPTED**
- **Decided-On:** 1405/04/25 (2026/07/16)
- **Decision-Owner:** Lead
- **Boundary:** UI-M1 audit-history verification depth (manager dashboard aggregates + related L7/L8 security depth).
- **S-2 dual-session e2e:** ACCEPT-BY-RISK — session architecture verified by evidence at L6; e2e cost/benefit unfavorable.
- **S-4 raw-query grep in CI:** ACCEPT — deferred to hygiene pass (N-11).
- **S-5 injection fuzz:** ACCEPT-BY-RISK — parameterized bindings verified.
- **Mitigation note:** Test suite must run against a dedicated database; overlapping suite runs on a shared DB caused transient `SQLSTATE[40P01]` deadlocks (dispositioned FLAKE, L8-RERUN). Single-process suite (no ParaTest) — prior deadlocks were external concurrent DB clients, not in-suite parallelism.
- **L8-TEST-ADD:** S-6 soft-delete fixture covered in `tests/Feature/Modules/DormitoryAdmin/DormitoryManagerDashboardTest.php` (`excludes soft-deleted rooms and beds from manager occupancy aggregates`).

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
- **Freeze:** No *new* F2/UI/Auth features until further Lead authorization.
- **Annotation (reconciled 2026-07-15, ref: DGAP-12):** Prior line “W-07/W-08 remain Pending” is **superseded**. W-07 and W-08 are **CLOSED** (`docs/features/employee-auth-ui/work-breakdown.md:11-12`; `w07-security-review-report.md:24`; `w08-scope.md:32`). F2 remains **PARTIAL** solely because F-W07-04 is **CARRIED FORWARD** (`w07-security-review-report.md:19`).

### F-W07-04 — Carried-forward (F2 canonical ID retained)

- **ID:** F-W07-04 (do **not** re-number as W-09 or other ID — one decision boundary = one canonical record).
- **Status:** **CARRIED FORWARD** — source: `docs/features/employee-auth-ui/w07-security-review-report.md:19`.
- **Meaning:** Post-login surface catalog / next UI slug product-authorization gate; keeps program F2 = **PARTIAL**.
- **Carried-forward → target: F3 Sprint A (or later)** (Lead-Advisory 2026-07-15).
- **Forbidden:** Closing F2 to COMPLETE while this ID remains open; inventing a parallel work-item ID for the same commitment.

---

## Pending Artifacts

- [x] `docs/governance/risk-register.md` (از DG-04) — delivered 1405/04/24; includes SEC-G-04 + BL-B1-01
- [x] `docs/governance/glossary.md` (از DG-05) — delivered 1405/04/24; Student/Employee mapping
- [x] Roadmap update: split Phase F → F1/F2 (از DG-02) — delivered `docs/governance/roadmap.md`

---

## Changelog

| تاریخ | تغییر | توسط |
|-------|-------|------|
| ۱۴۰۵/۰۴/۲۵ (2026/07/16) | **UI-M1-COV ACCEPTED:** residual coverage S-2/S-4/S-5 accepted at UI-M1 L8 closeout; dedicated-test-DB hygiene noted; S-6 soft-delete fixture test added. | Agent (Lead AUTHORIZE L8-TEST-ADD + closeout) |
| ۱۴۰۵/۰۴/۲۵ (2026/07/16) | **DGAP-09 RE-FROZEN** after scoped BL-B1-01 unfreeze+execute (RM-BL-B1). BL-B1-01 → RESOLVED (pending Lead commit). | Agent (Lead AUTHORIZE ALL) |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **DGAP-11 CLOSED — RESOLVED:** Lead AUTHORIZED Option A (scope corrected). Catalog UI-M1/M2/A1 → `roadmap.md` § F3; F3 ACTIVE — Sprint A. Supersedes UI-1…UI-7. Docs-only; no code/commit. | Agent (Lead AUTHORIZED) |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **Lead-Advisory (DGAP-12 follow-ups):** DGAP-11 resolution-path = F3 Sprint A — first work item (remains REOPENED). F-W07-04 stays carried-forward under F2 ID; target: F3 Sprint A (or later). No new work-item ID. | Agent (Lead-Advisory apply) |
| ۱۴۰۵/۰۴/۲۴ (2026/07/15) | **DGAP-12 reconciliation:** F2 → PARTIAL (F-W07-04 open; W-01…W-08 CLOSED); G mirrored PARTIAL on roadmap; DGAP-11 REOPENED (UI-1…UI-7 artifact never delivered); DGAP-12 EXECUTED — DOCS. Option B freeze note corrected. | Agent (Lead-supervised) |
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

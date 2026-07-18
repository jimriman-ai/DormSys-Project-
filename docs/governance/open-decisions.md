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
| DG-03 | مالکیت Identity Helper: ماژولی vs shared kernel | Identity Helper Ownership | A) Identity Helper در مالکیت dormitory-admin-ui بماند B) به shared kernel منتقل شود C) implementation محلی در هر boundary تا زمان تصمیم shared-kernel مجاز بماند | Lead | قبل از استفاده در boundary دوم | **CLOSED** | **Selected Option B** (Lead ruling **AUTH-013**, Resolution Date **2026-07-16**). IdentityRoleGuard → Shared Kernel. **W-06 executed:** `app/Shared/Auth/IdentityRoleGuard.php`. W-07/W-08 **CLOSED**. Evidence: AUTH-013. |
| DG-04 | مالک ریسک‌های پذیرفته‌شده و cadence بازبینی | Governance | A) Lead مالک تمام accepted risks باشد، بازبینی ۶ ماهه B) هر boundary مالک ریسک‌های خود باشد C) یک Risk Register مرکزی با مالک مشخص per-risk | Lead | قبل از merge PR فاز G | DECIDED | **DELIVERED** `docs/governance/risk-register.md` (1405/04/24). SEC-G-04 + BL-B1-01 seeded. |
| DG-05 | سیاست استفاده از Student vs Employee در UI و کد | Terminology | A) Student در UI عمومی، Employee در کد داخلی B) یکسان‌سازی کامل روی یک واژه C) glossary رسمی با mapping صریح | Lead | قبل از شروع Phase F UI | DECIDED | **DELIVERED** `docs/governance/glossary.md` (1405/04/24). Student ↔ Employee mapping. |
| DGAP-07 | F2 W-02: Eloquent/Application relation UserModel↔Employee vs `identity_id` UUID value-reference | Domain Gap / F2 W-02 | A) UUID reference sufficient — close W-02 as-is B) Eloquent relation required — new scoped work item, NOT silent code addition | Lead | F2 W-07/W-08 scoping | DECIDED | **Selected A** (Lead, 2026/07/15): UUID value-reference sufficient — close W-02 as-is. No Eloquent/Application UserModel↔Employee relation required. Source: Domain Gap Audit 2026-07-15. |
| DGAP-03 | Department↔Dormitory / Organization structural link | Domain Gap / Spec04 Auth | — (parked; options deferred) | Lead | Spec04 Auth packet | OPEN / PARKED | Blocker cleared — DGAP-08 RESOLVED, BO = HR Business Owner (2026-07-18). Source: Domain Gap Audit 2026-07-15. Not for answer now. |
| DGAP-05 | Approver actor binding / stage visibility | Domain Gap / Spec04 Auth | A) Dept line manager B) Dorm ops authority C) Dual/split Stage-1 | Lead | Spec04 Auth packet | **DECIDED** | **Selected A** (Lead, 1405/04/27 \| 2026-07-18): Department line manager = Stage-1 approver. Dorm ops = downstream capacity/ops. |
| DGAP-06 | Department.managerId vs Stage-1 approver binding | Domain Gap / Spec04 Auth | Visibility V1–V3; UI U1–U3 (Lead decision packet) | Lead | Spec04 Auth packet | **DECIDED** | **Selected V1 + U2** (Lead, 1405/04/27 \| 2026-07-18): Visibility = subject + assigned approver only; UI = separate approver console. Stage-1 actor identity follows DGAP-05 A (dept manager). |
| DGAP-08 | Business Owner designation | Domain Gap / HDAC | — (parked; designation by org authority) | human org authority (HDAC track) | HDAC track (root blocker) | **RESOLVED** | **BO = HR Business Owner** / Human Resources Department (`employee-request-self-service`). Resolved 1405/04/27. Unblocks DGAP-05, DGAP-06, SGAP-05. |
| DGAP-01 | Organization aggregate | Domain Gap Audit | CLOSE — NOT-A-GAP | Lead | DGAP Decision Gate | **CLOSED — NOT-A-GAP** | Lead (2026/07/15): No product requirement for Organization aggregate. No L6 fill. |
| DGAP-02 | Unit entity | Domain Gap Audit | CLOSE — NOT-A-GAP | Lead | DGAP Decision Gate | **CLOSED — NOT-A-GAP** | Lead (2026/07/15): Unit only meaningful under assignment schema (frozen). No L6 fill. |
| DGAP-04 | Workflow module / engine | Domain Gap Audit | CLOSE — NOT-A-GAP | Lead | DGAP Decision Gate | **CLOSED — NOT-A-GAP** | Lead (2026/07/15): Workflow shell intentional per CD-010 deferral. No L6 fill. |
| DGAP-10 | Dual User model (`App\Models\User` vs `UserModel`) | Domain Gap Audit | CLOSE — NOT-A-GAP by design | Lead | DGAP Decision Gate | **CLOSED — NOT-A-GAP by design** | Lead (2026/07/15): Intentional dual-guard architecture. No L6 fill. |
| DGAP-09 | Manager/unit assignment schema | Domain Gap / BL-B1-01 | NO ACTION this phase | Lead | DGAP Decision Gate | **FROZEN (RE-FROZEN 2026-07-16 after scoped BL-B1-01)** | Temporary unfreeze YES scoped to BL-B1-01 only (Lead); schema+wire executed RM-BL-B1; **RE-FROZEN** — no further schema without new unfreeze. |
| DGAP-13 | Canonical Stage-1 approver role: DeptMgr vs dormitory-manager | Domain Gap / IMPL-PERMIT-03 | Canonical auth-path role = `dormitory-manager` | Lead | IMPL-PERMIT-03 gate/snapshot conflict | **DECIDED** | Lead 2026-07-18. **ID note:** Lead prompt labeled this gap **DGAP-09**; register ID **DGAP-09** already FROZEN (assignment schema) — recorded as **DGAP-13** (collision STOP). Scoped to IMPL-PERMIT-03 Stage-1 authorization path only. IMPL-PERMIT-02 CLOSED @ 32c677b untouched. |
| IMPL-PERMIT-03 | Stage-1 Approval Console Wiring (approve/reject + role alignment) | Spec04 / Implementation Permit | — | Lead | After DGAP-13 + gate alignment | **CLOSED** | Closed 1405/04/27 \| 2026-07-18. Decision basis **DGAP-13**; Lead commit `25104a70ed381d4d81ab8b9b5570e3dd51ad3efd` (from § IMPL-PERMIT-03 metadata — Decision Gate Table placeholder sync). Residual DeptMgr inventory → **DGAP-14** (DECIDED). |
| DGAP-14 | Residual DeptMgr references outside the Stage-1 authorization path | Domain Gap / Spec04 residual | Inventory recorded; dispositions PROPOSED only | Lead | Post IMPL-PERMIT-03 close | **DECIDED** | See decision block — DECIDED 2026-07-18. Residual items tracked separately. |
| SGAP-01 | Spec001 Status Draft vs delivered | Spec Completion Audit | DOC Status sync | Lead | SGAP Disposition | **CLOSED** | Status header → delivered/CLOSED (DOC-only). |
| SGAP-02 | Spec006 missing research/quickstart | Spec Completion Audit | ACCEPTED-MINIMAL | Lead | SGAP Disposition | **ACCEPTED-MINIMAL** | Intentional post-impl; do not create artifacts. |
| SGAP-03 | Spec007 missing research/quickstart | Spec Completion Audit | ACCEPTED-MINIMAL | Lead | SGAP Disposition | **ACCEPTED-MINIMAL** | Intentional post-impl; do not create artifacts. |
| SGAP-04 | Spec008 missing data-model/contracts | Spec Completion Audit | DOC mirror from Voucher code | Lead | SGAP Disposition | **CLOSED** | data-model.md + contracts/ mirrored from `app/Modules/Voucher` only. |
| SGAP-05 | Spec006 GOVERNANCE_OPEN / AUTHORITY_NOT_AVAILABLE | Spec Completion Audit | PARK | Lead | SGAP Disposition | **PARKED** | unlock gate: DGAP-08 RESOLVED (2026-07-18); remaining gate: Spec06 GOVERNANCE_OPEN |
| SGAP-06 | CLAUDE/AGENTS CheckIn “candidate” wording | Spec Completion Audit | DOC wording sync | Lead | SGAP Disposition | **CLOSED** | Synced to Spec07 CLOSED + `app/Modules/CheckIn`. |
| SGAP-07 | Spec04 Product PENDING_RESIDUAL | Spec Completion Audit | Backlog + PARK | Lead | SGAP Disposition | **BACKLOG + PARKED** | Explicit backlog; Spec04 Auth packet / DGAP-08 untouched. |
| SGAP-08 | Spec011 outside audit list | Spec Completion Audit | DEFER | Lead | SGAP Disposition | **DEFERRED** | Separate audit only if 011 enters UI path. |
| SGAP-09 | debug.log under specs 008/009/010 | Spec Completion Audit | Cleanup | Lead | SGAP Disposition | **CLOSED** | debug.log files deleted. |
| DGAP-11 | Roadmap ↔ UI Productization reconciliation | Governance / Roadmap | A) merge into Roadmap as UI-1..UI-7 B) keep separate doc with cross-refs | Lead | F3 Sprint A entry | **CLOSED — RESOLVED** (2026-07-15) | Lead AUTHORIZED Option A (scope corrected): canonical catalog **UI-M1, UI-M2, UI-A1** in `docs/governance/roadmap.md` § F3 Catalog (supersedes UI-1…UI-7 claim). Artifact delivered; F3 ACTIVE — Sprint A. |
| DGAP-12 | Governance doc hard-conflict reconciliation (F2 / G / DGAP-11) | Governance / Roadmap | Docs-only status sync per Lead DGAP-12 rules | Lead | Immediate | **EXECUTED — DOCS** (reconciled 2026-07-15, ref: DGAP-12) | Conflicts 1–3 applied in `docs/governance/roadmap.md`, this file, `docs/features/employee-auth-ui/feature-brief.md`. No code. No commit. |
| UI-M1-COV | UI-M1 residual test-coverage risk (S-2, S-4, S-5) | UI-M1 / Verification | ACCEPT residual depth; mitigate via dedicated test DB hygiene | Lead | UI-M1 L8 closeout | **ACCEPTED** (Lead, 2026-07-16) | Boundary: UI-M1 audit-history / dashboard verification depth. S-2 dual-session e2e ACCEPT-BY-RISK; S-4 raw-query grep CI ACCEPT (defer N-11); S-5 injection fuzz ACCEPT-BY-RISK. Hygiene: suite must use dedicated DB — overlapping runs on shared DB caused transient `40P01` (FLAKE, L8-RERUN). |
| AUTH-012 | Spec/governance disposition audit — 18 rows §3 | Governance / Catalog | CONFIRM all dispositions as recorded | Lead | AUTH-013 recording | **CONFIRMED** (Lead, 2026-07-16) | All 18 disposition rows CONFIRMED verbatim, including mismatches corrected: **spec05 → DELIVERED-NEEDS-CLOSEOUT**; **spec06 → DECISION-BLOCKED** (not NOT-STARTED). Cursor reconciliation validated. Cite AUTH-013. |
| HD-01 | DGAP-08 Business Owner designation posture | Domain Gap / F3 Sprint A | A) Resolve now B) Remain PARKED C) Defer to HDAC only | Lead | AUTH-013 | **DECIDED — B** | **DGAP-08 stays PARKED.** Sprint A does not require Spec04 Auth. **Re-entry trigger:** `employee-request-self-service` enters scope. (AUTH-013) |
| HD-02 | spec06 Lottery governance posture | Spec06 / Governance debt | A) Exception accepted; governance debt; new Lottery FROZEN B) Treat as NOT-STARTED C) Full closeout now | Lead | AUTH-013 | **DECIDED — A** | Exception ACCEPTED; governance debt recorded; **new Lottery work FROZEN** pending authority resolution. Treating delivered code as NOT-STARTED is falsification of history. (AUTH-013) |
| HD-03 | spec11 Reporting governance posture | Spec11 / F3 scope | A) Same exception as spec06 B) Full closeout C) In-scope for F3 | Lead | AUTH-013 | **DECIDED — A (+C posture)** | Same exception pattern as spec06; **Reporting parked OUT-OF-CURRENT-F3.** Re-entry trigger: explicit F-next feature requiring reporting enters scope. **No blocker on L9.** (AUTH-013) |
| HD-04 | Workflow module posture | CD-010 / Workflow | A) Deferred placeholder per CD-010 B) Activate now C) NOT-A-GAP retract | Lead | AUTH-013 | **DECIDED — A** | Workflow **deferred** per CD-010; activation criterion **≥2 workflow instances**. Placeholder only — **not a work-gap.** (AUTH-013) |
| HD-05 | F-W07-04 carry target | F2 / F3 sequencing | A) Carry to F3 Sprint B B) Close now C) Carry to Sprint A | Lead | AUTH-013 | **DECIDED — A** | **F-W07-04 carry to F3 Sprint B.** Consistent with work-breakdown posture. (AUTH-013) |
| HD-06 | L9 merge scope / checklist freshness | Phase G / L9 | A) Formal A1 waiver after checklist refresh B) Merge with stale checklist C) Refresh checklist first, then waiver | Lead | AUTH-013 | **DECIDED — C then A** | **First:** refresh L9 checklist (resolves GAP-DOC-04). **Then:** Lead issues formal waiver — UI-A1 presence in branch is **intentional and accepted**. Merge with stale checklist = invalid evidence. (AUTH-013) |
| HD-07 | spec02 + spec05 docs-only closeout timing | Spec closeout / doc-lag | A) Docs-only closure per spec03 pattern in Wave 2 B) Execute now C) Defer indefinitely | Lead | AUTH-013 | **DECIDED — A** | **spec02 + spec05 docs-only closure** following spec03 pattern; execute in **Wave 2 (after merge)**, not now. One write, not two. (AUTH-013) |
| OQ-AUTH-01 | Spec04 Spatie role naming (`identity` guard) | Spec04 Auth / Technical | A) Dual kebab B) `employee` + `DeptMgr` C) Single shared role | Lead | Spec04 Auth packet | **DECIDED** | **Selected B** (Lead, 1405/04/27 \| 2026-07-18). No impl auth. |
| OQ-AUTH-02 | Spec04 L5 middleware / V1 request bridge | Spec04 Auth / Technical | A) App V1 B) Middleware V1 C) App-only bridge | Lead | Spec04 Auth packet | **DECIDED** | **Selected B** (Lead, 1405/04/27 \| 2026-07-18). No impl auth. |
| OQ-AUTH-03 | Spec04 Stage-1 dept-manager → identity binding | Spec04 Auth / Technical | A) Live manager_id B) Snapshot at submit C) Alternate HR rule | Lead | Spec04 Auth packet | **DECIDED** | **Selected B** (Lead, 1405/04/27 \| 2026-07-18). No impl auth. |
| OQ-AUTH-05 | Spec04 Auth Packet DRAFT → acceptance | Spec04 Auth / Governance | A) Accept after recorded/validated decisions (impl unauthorized) B) Keep DRAFT until named external deps resolved | Lead | Spec04 Auth packet | **DECIDED** | **Selected A** (Lead, 1405/04/27 \| 2026-07-18): governance-accepted artifact. No L5/L6/impl auth. DGAP-03/SGAP-05 unchanged. |

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

- **Selected Option:** B
- **Status:** CLOSED
- **Decided-On / Resolution Date:** 2026-07-16
- **Evidence:** AUTH-013
- **Decision-Owner:** Lead
- **Decision-Method:** Lead ruling AUTH-013 (W2-FIX Group D); Shared Kernel migration executed as W-06
- **Prior metadata note:** Historical 1405/04/24 row recorded Option A; superseded by AUTH-013 Option B (2026-07-16)

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
- **Blocker:** Blocker cleared — DGAP-08 RESOLVED, BO = HR Business Owner (2026-07-18)
- **Decision-Owner:** Lead
- **Source:** Domain Gap Audit (READ-ONLY, 2026-07-15)
- **Tier:** 2 — PARKED (Spec04 Auth packet)
- **Selected Option:** — (not for answer now)

### DGAP-05

- **Status:** **DECIDED**
- **Selected Option:** A — Department line manager is the Stage-1 approver
- **Decided-On:** 1405/04/27 (2026-07-18)
- **Decision-Owner / Authority:** Lead Decision
- **Business rationale:** Stage-1 represents organizational confirmation of employee housing need. The direct department manager owns the first business acceptance of the employee request. Dormitory operations remains responsible for downstream operational/capacity concerns.
- **Prior status:** OPEN / PARKED (blocker DGAP-08 cleared 2026-07-18)
- **Source:** Domain Gap Audit (READ-ONLY, 2026-07-15); Lead decision after criteria review
- **Effect:** Stage-1 accountable actor = department line manager for `employee-request-self-service`. Does **not** authorize implementation.

### DGAP-06

- **Status:** **DECIDED**
- **Selected Options:**
  - **Visibility V1** — Subject + assigned approver only
  - **UI Boundary U2** — Separate approver console
- **Decided-On:** 1405/04/27 (2026-07-18)
- **Decision-Owner / Authority:** Lead Decision
- **Business rationale (V1):** Visibility follows minimum necessary access. Request status is visible only to the employee and the accountable assigned approver.
- **Business rationale (U2):** Employee self-service and approval workflows are separate business surfaces. Approval actions must not be embedded into the employee self-service surface.
- **Linkage:** Stage-1 approver identity follows **DGAP-05 Option A** (department line manager).
- **Prior status:** OPEN / PARKED (blocker DGAP-08 cleared 2026-07-18)
- **Source:** Domain Gap Audit (READ-ONLY, 2026-07-15); Lead decision after criteria review
- **Effect:** Visibility and UI ownership fixed for governance. Does **not** authorize implementation.

### OQ-AUTH-01 — Spec04 Spatie role naming

- **Status:** **DECIDED**
- **Selected Option:** **B** — `employee` + `DeptMgr` (`guard_name = identity`)
- **Decided-On:** 1405/04/27 (2026-07-18)
- **Decision-Owner / Authority:** Lead Decision
- **Rationale:** Aligns Stage-1 approver role with existing workflow abbreviations (`HRMgr` / `DormMgr`) while keeping a distinct `employee` role for self-service; supports U2 surface split without reusing dormitory-manager roles (DGAP-05 A).
- **Effect:** Canonical identity-guard role names for Spec04 surfaces. Does **not** authorize seeders, routes, or implementation.
- **Packet:** `docs/specs/spec04-auth-packet.md` §8.1 — v0.4.0-DRAFT

### OQ-AUTH-02 — Spec04 L5 middleware / V1 bridge

- **Status:** **DECIDED**
- **Selected Option:** **B** — Dual `identity.role:*` stacks + thicker middleware enforcing V1 (subject OR assigned Stage-1 approver); optional Application re-assert
- **Decided-On:** 1405/04/27 (2026-07-18)
- **Decision-Owner / Authority:** Lead Decision
- **Rationale:** Early reject at the HTTP edge for principals outside V1; dual role stacks keep approve/reject off the employee self-service surface (U2).
- **Effect:** L5 gate pattern fixed for Spec04. Does **not** authorize middleware registration or routes.
- **Packet:** `docs/specs/spec04-auth-packet.md` §8.2 — v0.4.0-DRAFT

### OQ-AUTH-03 — Spec04 Stage-1 identity binding

- **Status:** **DECIDED**
- **Selected Option:** **B** — Snapshot at submit: resolve `manager_id` chain once; store assigned Stage-1 identity UUID on the request (value-ref only)
- **Decided-On:** 1405/04/27 (2026-07-18)
- **Decision-Owner / Authority:** Lead Decision
- **Rationale:** Literal fit to DGAP-06 V1 “assigned approver” — accountability frozen at submit; later org-chart manager changes do not silently reassign visibility/approval.
- **Effect:** Binding mechanism fixed for governance. Does **not** authorize migrations or schema changes.
- **Packet:** `docs/specs/spec04-auth-packet.md` §8.3 — v0.4.0-READY-FOR-REVIEW

### OQ-AUTH-05 — Spec04 DRAFT → acceptance (Human Gate)

- **Status:** **DECIDED**
- **Selected Option:** **A** — Accept Spec04 Auth Packet as a governance-ready artifact
- **Decided-On:** 1405/04/27 (2026-07-18)
- **Decision-Owner / Authority:** Lead Decision
- **Rationale:** All Spec04-specific business and authentication decisions have been resolved (DGAP-05, DGAP-06, OQ-AUTH-01, OQ-AUTH-02, OQ-AUTH-03). Remaining unrelated dependencies do not block Spec04 governance acceptance.
- **Constraints:**
  - No implementation authorization
  - No L5/L6 authorization
  - No code, migration, route, or UI implementation permitted
- **Next gate:** Separate implementation authorization process after required contracts and execution approvals
- **Packet:** `docs/specs/spec04-auth-packet.md` — **0.5.0-GOVERNANCE-ACCEPTED**
- **Unchanged:** DGAP-03 (OPEN/PARKED); SGAP-05 (PARKED)

### DGAP-08 — Business Owner: employee-request-self-service

| Field | Value |
|-------|-------|
| **Status** | **RESOLVED** |
| **Resolved** | 1405/04/27 |
| **Authority** | Lead Decision |
| **Business Owner** | HR Business Owner / Human Resources Department |
| **BO Scope** | Business requirements, product-surface scope, accountable business authority |
| **Excluded from BO** | Technical implementation, architecture, permission enforcement, code approval |
| **Unblocks** | DGAP-05, DGAP-06, SGAP-05 |

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
- **ID collision note (2026-07-18):** A separate Lead decision on Stage-1 approver role was prompted as “DGAP-09”; that substance is recorded under **DGAP-13** so this assignment-schema ID remains immutable.

### DGAP-13 — Canonical Stage-1 approver role: DeptMgr vs dormitory-manager

| Field | Value |
|-------|-------|
| **ID** | **DGAP-13** (Lead prompt label **DGAP-09** — ID collision with FROZEN assignment-schema DGAP-09; substance unchanged) |
| **Title** | Canonical Stage-1 approver role: DeptMgr vs dormitory-manager |
| **Status** | **DECIDED** |
| **Decided-On** | 1405/04/27 \| 2026-07-18 |
| **Decision-Owner / Authority** | Lead (Human Decision Authority) |
| **Canonical role (Stage-1 approver authorization path)** | `dormitory-manager` (`IdentityRoleGuard` / identity guard) |
| **Non-canonical for this path only** | `DeptMgr` — remove only from Stage-1 approver authorization path gates |

**Evidence:**

- Gate code (pre-alignment): `routes/web.php` middleware `identity.role:DeptMgr`; `Stage1ApproverConsolePage` `IdentityRoleGuard::assertIdentityRole(ROLE_DEPT_MGR)`; `ApproveStage1RequestAction` / `RejectStage1RequestAction` same.
- Snapshot (CLOSED): IMPL-PERMIT-02 @ `32c677b` — `Stage1ApproverIdentityReadBridge` / `IdentityRoleGuard::resolveActiveIdentityIdForRole` with `IdentityRoleSeeder::ROLE_DORMITORY_MANAGER`.
- Seeder: `database/seeders/IdentityRoleSeeder.php` — `ROLE_DORMITORY_MANAGER = 'dormitory-manager'`; `ROLE_DEPT_MGR = 'DeptMgr'` (latter remains seeded; not Stage-1 gate).

**Non-Scope (explicit):** this decision resolves **IMPL-PERMIT-03** Stage-1 approver authorization path only. It does **NOT** define canonical roles for Stage-2/Stage-3 approval, other consoles, or the RBAC model at large. Any wider role consolidation requires a **new** gap record. No downstream decision implied beyond this scoped record (DGAP-13 / Lead-labeled DGAP-09).

**Consequence:** Stage-1 gates align to snapshot identity role; **IMPL-PERMIT-02 remains CLOSED** — do not modify snapshot logic, migration, or its tests.

### IMPL-PERMIT-03 — Stage-1 Approval Console Wiring

| Field | Value |
|-------|-------|
| **Permit ID** | **IMPL-PERMIT-03** |
| **Status** | **CLOSED** |
| **Closed-On** | 1405/04/27 \| 2026-07-18 |
| **Decision-Owner** | Lead |
| **Decision basis** | **DGAP-13** (canonical Stage-1 approver authorization-path role = `dormitory-manager`) |
| **Lead commit** | `25104a70ed381d4d81ab8b9b5570e3dd51ad3efd` |
| **Delivered** | Stage-1 approve/reject Application Actions + console wiring; gates aligned to `dormitory-manager` via `IdentityRoleGuard`; coherence test green |
| **Residual** | Report-only `DeptMgr` inventory registered as **DGAP-14** (DECIDED) |
| **Non-Scope retained** | Does not reopen DGAP-09 (FROZEN) or widen DGAP-13 beyond Stage-1 auth path |

### DGAP-14 — Residual DeptMgr references outside the Stage-1 authorization path

| Field | Value |
|-------|-------|
| **ID** | **DGAP-14** |
| **Title** | Residual DeptMgr references outside the Stage-1 authorization path |
| **Status** | **DECIDED** |
| **Registered** | 1405/04/27 \| 2026-07-18 |
| **Decision-Owner** | Lead (Human Decision Authority) |
| **ID verification** | Decision Gate Table scanned: DGAP-01…13 present; **DGAP-14** absent → next free DGAP integer. Not a FROZEN ID. |

**Non-Scope:** no code, spec, domain, or Blade changes under this registration. Docs-only inventory.

**Evidence — report-only inventory (verbatim from IMPL-PERMIT-03 / DGAP-13 alignment report):**

| Hit | Apparent purpose | Disposition |
|-----|------------------|-------------|
| `IdentityRoleSeeder::ROLE_DEPT_MGR` (`database/seeders/IdentityRoleSeeder.php`) | Role seed / OQ-AUTH-01 catalog | **RESIDUAL (Sprint B)** |
| Historical governance: **DGAP-05 A** (department line manager = Stage-1) | Decided Spec04 actor binding (business) | **EXCLUDED** |
| Historical governance: **OQ-AUTH-01 B** (`employee` + `DeptMgr`) | Decided Spec04 Spatie role naming | **EXCLUDED** |
| `docs/governance/IMPL-PERMIT-01.md` | Historical permit middleware / role text (`identity.role:DeptMgr`) | **APPLIED (R5.1, 2026-07-18)** |
| `docs/specs/spec04-auth-packet.md` | Packet role naming | **APPLIED (R5.1, 2026-07-18)** |
| `docs/specs/spec04-imp-q-technical-proposal.md` | IMP-Q technical proposal role naming | **APPLIED (R5.1, 2026-07-18)** |
| `docs/governance/spec04-imp-q-human-decision-pack.md` | IMP-Q human-decision pack (outside R5.1 path) | **EXCLUDED** |
| `specs/005-request-management/*` (spec.md, tasks.md, quickstart.md) | Workflow stage naming (DeptMgr → HR → …) | **APPLIED (R5.1, 2026-07-18)** |
| `specs/010-audit-trail/contracts/audit-history-read-contract.md` | Audit deny matrix lists `DeptMgr` | **APPLIED (R5.1, 2026-07-18)** |
| `docs/ui/contracts/requests/employee-request-self-service.feature-contract.yaml` | Feature-contract role label `stage1_approver: DeptMgr` | **APPLIED (R5.1, 2026-07-18)** |
| Domain status `PendingDepartmentManagerState` | Request lifecycle Stage-1 state name | **RESIDUAL (Sprint B)** |
| Console Blade «مدیر واحد» (`resources/views/livewire/request/stage1-approver-console-page.blade.php`) | UI label (not a role gate) | **RESIDUAL (Sprint B)** |
| Test helper names using DeptMgr as negative-path fixture (`Stage1ApproverConsoleActionsTest` DeptMgr-only 403) | Negative-path fixture for gate rejection | **RESIDUAL (Sprint B)** |
| `CLAUDE.md` workflow abbreviation line | Docs workflow shorthand | **APPLIED (R5.1, 2026-07-18)** |
| `.specify/docs/discovery/hr-semantic-evidence-clarification.md` | Discovery evidence citing Spec05 DeptMgr stage | **EXCLUDED** |
| `docs/governance/IMPL-PERMIT-02.md` ambiguity note referencing DeptMgr | Closed permit narrative | **APPLIED (R5.1, 2026-07-18)** |

**Effect:** DECIDED (Option A, 2026-07-18 / 1405-04-27, Lead). `dormitory-manager` is the canonical role. Documentation/contract dispositions below are APPLIED per R5.1 (22 points). Runtime identifiers are RESIDUAL — accepted out-of-scope, tracked for Sprint B. See governance-log.md.

**Decision (DECIDED — Option A):**
- Canonical role: `dormitory-manager` (supersedes `DeptMgr`).
- Applied: R5.1 replacement across 22 documented points (docs/contracts).
- Exclusions: runtime code identifiers (classes, seeders, state names) — intentionally untouched.
- Residuals → Sprint B: `RoleSeeder`, `PendingDepartmentManagerState`.
- Authority: Lead, 2026-07-18. Logged in governance-log.md.

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
  - **Artifact:** `docs/governance/roadmap.md` § **F3 — Catalog (Sprint A)** — statuses synced W2 (2026-07-18): UI-M1 L8/L9-pending; UI-M2 READY FOR L3; UI-A1 L8 COMPLETE.
  - **Phase entry:** F3 **ACTIVE — Sprint A**. Docs-only; no PHP/migrations authorized by this closure.
- **Follow-up:** UI-M1 L9 merge SHA pending Lead (GAP-GOV-02). UI-M2 L3 → W3. F-W07-04 → **F3 Sprint B** (HD-05A).

### DGAP-12

- **Status:** EXECUTED — DOCS (reconciled 2026-07-15, ref: DGAP-12)
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Decision-Owner:** Lead (supervised reconciliation)
- **Decision:** Apply docs-only hard-conflict reconciliation rules for (1) F2 PARTIAL vs stale ACTIVE, (2) G PARTIAL vs L9 NOT READY, (3) DGAP-11 REOPENED for undelivered UI merge.
- **Scope:** `docs/` only. No code. No commit.

### AUTH-012 — Disposition confirmation (Lead, 2026-07-16)

- **ID:** AUTH-012
- **Status:** **CONFIRMED** (recorded under AUTH-013, 2026-07-16)
- **Decision-Owner:** Lead
- **Scope:** All 18 rows of spec/governance disposition audit §3
- **Effect:** Every disposition row accepted verbatim. Corrected mismatches:
  - **spec05** → `DELIVERED-NEEDS-CLOSEOUT` (not merely Implementation Authorized)
  - **spec06** → `DECISION-BLOCKED` / governance exception (not NOT-STARTED)
- **Authority:** AUTH-013 (docs-only recording)

### HD-01 — DGAP-08 stays PARKED (AUTH-013)

- **Selected Option:** B
- **Decided-On:** 1405/04/25 (2026/07/16)
- **Decision-Owner:** Lead
- **Rationale:** Sprint A does not require Spec04 Auth.
- **Re-entry trigger:** `employee-request-self-service` enters scope.

### HD-02 — spec06 exception accepted (AUTH-013)

- **Selected Option:** A
- **Decided-On:** 1405/04/25 (2026/07/16)
- **Decision-Owner:** Lead
- **Rationale:** Delivered Lottery code cannot be labeled NOT-STARTED; governance debt recorded; new Lottery work FROZEN.

### HD-03 — spec11 exception + OUT-OF-CURRENT-F3 (AUTH-013)

- **Selected Option:** A (+C posture)
- **Decided-On:** 1405/04/25 (2026/07/16)
- **Decision-Owner:** Lead
- **Rationale:** Parallel spec06 exception; Reporting outside current F3; no L9 blocker.
- **Re-entry trigger:** Explicit F-next feature requiring reporting enters scope.

### HD-04 — Workflow deferred placeholder (AUTH-013)

- **Selected Option:** A
- **Decided-On:** 1405/04/25 (2026/07/16)
- **Decision-Owner:** Lead
- **Rationale:** CD-010 deferral; activation criterion ≥2 workflow instances; not a work-gap.

### HD-05 — F-W07-04 → F3 Sprint B (AUTH-013)

- **Selected Option:** A
- **Decided-On:** 1405/04/25 (2026/07/16)
- **Decision-Owner:** Lead
- **Rationale:** Consistent with `work-breakdown.md` carry posture.

### HD-06 — L9 checklist refresh then A1 waiver (AUTH-013)

- **Selected Option:** C then A
- **Decided-On:** 1405/04/25 (2026/07/16)
- **Decision-Owner:** Lead
- **Sequence:** (1) Refresh `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md`; (2) Formal waiver — UI-A1 in branch intentional/accepted.

### W1-A1 — UI-A1 L9 scope waiver (AUTH-011 Band 2 / D1)

- **ID:** W1-A1 / GAP-GOV-01
- **Status:** **ACTIVE** (attached to checklist refreshed 2026-07-16)
- **Decision-Owner:** Lead
- **Decided-On:** 1405/04/25 (2026/07/16)
- **Verbatim waiver:**
  > WAIVER W1-A1 (Lead, 2026-07-16): The presence of UI-A1 artifacts in the UI-M1 branch is intentional and accepted for the L9 merge. UI-A1 items on the L9 checklist are marked WAIVED (not PASS). This waiver is valid ONLY when attached to a checklist refreshed in this session (per HD-06: stale checklist = invalid evidence).
- **Effect:** L9 checklist A1 → WAIVED; broad file set accepted for UI-M1 merge.

### HD-07 — spec02/spec05 closeout in Wave 2 (AUTH-013)

- **Selected Option:** A
- **Decided-On:** 1405/04/25 (2026/07/16)
- **Decision-Owner:** Lead
- **Rationale:** Docs-only closure per spec03 pattern; execute after merge (W2), not before.
- **W2 hygiene note (2026-07-18):** Decision remains **DECIDED — A**. Full closeout package (spec03-pattern handoff) **not authored in this hygiene pass** — awaiting Lead confirmation of artifact depth (see W2 D3). Catalog already annotated `DELIVERED-NEEDS-CLOSEOUT` / Frozen Wave 1A.
- **Execution (1405/04/27 | 2026-07-18):** **COMPLETE** — Lead F3 Sprint A authorization. UI-M1 merge **not** required. Status sync: spec02 HD-07 docs closeout COMPLETE (remains Frozen); spec05 → **`SPEC05_CLOSED`**. Evidence: Identity/Request modules + prior handoffs; `spec-catalog.md` §1.0.32. No UI IA implied.

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
| SGAP-05 | PARKED | Spec06 GOVERNANCE_OPEN / AUTHORITY_NOT_AVAILABLE; unlock gate: DGAP-08 RESOLVED (2026-07-18); remaining gate: Spec06 GOVERNANCE_OPEN |
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
- **Carried-forward → target: F3 Sprint B** (Lead **HD-05 → A**, AUTH-013, 2026-07-16). Prior Sprint A target superseded.
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
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **W2 hygiene (merge-independent):** Decision Gate Table IMPL-PERMIT-03 commit placeholder synced from § metadata SHA `25104a70…`. Merge SHA **UNVERIFIED**. Report: `docs/governance/w2-hygiene-sync-report.md`. No new decision. | Agent (docs sync) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **IMPL-PERMIT-03 CLOSED** (basis DGAP-13; Lead commit `<LEAD-FILLS-IN>`). **DGAP-14 OPEN:** residual DeptMgr references outside Stage-1 auth path — inventory recorded, dispositions PROPOSED only, undecided. DGAP-09/13 unmodified. Ref: PATCH-F3A-SYNC (IMPL-PERMIT-03 close / DGAP-14). | Agent (Lead closeout docs) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **DGAP-13 DECIDED** (Lead prompt label DGAP-09; ID collision with FROZEN assignment-schema DGAP-09 → registered as DGAP-13): canonical Stage-1 approver **authorization path** role = `dormitory-manager`. Non-Scope: IMPL-PERMIT-03 only; no Stage-2/3 / RBAC-wide consolidation. IMPL-PERMIT-02 CLOSED untouched. **no downstream decision implied beyond DGAP-09/13 scope.** Ref: PATCH-F3A-SYNC (DGAP-13). | Agent (Lead Decision Record) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **OQ-AUTH-05 DECIDED — A:** Spec04 Auth Packet **governance-accepted**. Rationale: Spec04-specific decisions complete; unrelated deps non-blocking. **No** L5/L6/impl auth. Spec04 → 0.5.0-GOVERNANCE-ACCEPTED. DGAP-03/SGAP-05 untouched. | Agent (Lead Decision Record) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **OQ-AUTH-05 reframed → AWAITING HUMAN DECISION:** Options A (accept now) vs B (hold DRAFT for external deps). Spec04 → **0.4.0-DRAFT**. Prior “DECIDED/READY-FOR-REVIEW” superseded for acceptance path; §9.1 criteria retained. DGAP-03/SGAP-05 untouched. No impl auth. | Agent (Lead Human Decision Gate) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **OQ-AUTH-05 DECIDED:** Spec04 §9 Exit Criteria defined; packet → **0.4.0-READY-FOR-REVIEW**. Docs-only; **no impl auth**. *(Superseded for acceptance-path gate — see row above.)* | Agent (Lead Exit Criteria) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **OQ-AUTH-01/02/03 DECIDED — B/B/B** (Lead): roles `employee`+`DeptMgr`; middleware V1 bridge; snapshot Stage-1 identity at submit. Spec04 → 0.4.0-DRAFT. Docs-only; **no impl auth**. | Agent (Lead Technical Selection) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **DGAP-05 DECIDED — A:** Department line manager = Stage-1 approver (Lead). **DGAP-06 DECIDED — V1 + U2:** visibility subject+assignee; separate approver console (Lead). Docs-only; no impl auth. | Agent (Lead Human Decision) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | Blocker-sync: DGAP-03 notes/metadata updated to reflect DGAP-08 resolution. No gap status changed. Ref: PATCH-F3A-SYNC-03. | Agent (PATCH-F3A-SYNC-03) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | Blocker-sync: DGAP-06 and SGAP-05 notes/metadata updated to reflect DGAP-08 resolution. No gap statuses changed. Ref: PATCH-F3A-SYNC-02. | Agent (PATCH-F3A-SYNC-02) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **Lead confirm:** DGAP-03 remains **OPEN/PARKED** (no gate change). DGAP-08 draft awaits **Business Owner** designation for `employee-request-self-service`. Cascade DGAP-05/06 + SGAP-05 blocked until BO. Cursor assigns no BO. | Agent (Lead confirm) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **HD-07 EXECUTED:** spec02 docs closeout COMPLETE (Frozen retained); spec05 → `SPEC05_CLOSED`. **DGAP-08 Decision Record Draft** added (DRAFT — Pending Lead Decision; not a new gate OPEN row; BO not assigned). DGAP-03 “closed” claim from prompt **not** applied — gate still OPEN/PARKED. | Agent (Lead F3 Sprint A auth) |
| ۱۴۰۵/۰۴/۲۷ (2026-07-18) | **W2-FIX Group D:** DG-03 → **CLOSED**, Selected Option **B**, Resolution Date **2026-07-16**, Evidence **AUTH-013**. Docs-only. | Agent (Lead W2-FIX auth) |
| ۱۴۰۵/۰۴/۲۷ (2026/07/18) | **W2 Documentation Hygiene (facts sync):** GAP-DOC-01/02/03 + GAP-UI-M1-01 closed in docs; F-W07-04 advisory text → Sprint B (HD-05A already decided). No new decision. HD-07 / N-11 remain for Lead depth choice (D3). | Agent (Lead W2 auth) |
| ۱۴۰۵/۰۴/۲۵ (2026/07/16) | **AUTH-013 W0:** AUTH-012 disposition CONFIRMED (18 rows); HD-01…HD-07 recorded verbatim; F-W07-04 target → F3 Sprint B; DGAP-08 re-entry trigger set. Docs-only. | Agent (Lead AUTH-013) |
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

-**Status:** OPEN
+**Status:** DECIDED
+**Decided by:** Lead
+**Decision date:** [تاریخ]
+
+**Decision:**
+Canonical governance references in documentation and contracts
+shall use `dormitory-manager`. This applies to governance artifacts,
+UI contracts, and implementation permits only.
+
+**Explicit scope exclusions:**
+Runtime identifiers, domain class names, and historical specs
+are out of scope and require separate authorization to change.
+
+**Open residuals (not decided here — tracked separately):**
+- `config/roles.php` string value `'DeptMgr'` — runtime constraint
+- `PendingDepartmentManagerState` and related class names — domain layer
+- `RoleSeeder` string values — runtime constraint
+- `spec04-auth-packet.md` — intentional archival, no change

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
| DG-02 | تعریف دقیق Phase F: narrow vs broad | Phase F Definition | A) Phase F فقط employee-records باشد (narrow) B) Phase F شامل UI و Auth مرتبط هم باشد (broad) C) Phase F به دو زیرفاز تقسیم شود | Lead | قبل از L0 Phase F | DECIDED | F1 (employee-records — تکمیل‌شده) و F2 (UI/Auth) — split per Option C. F2 status **PASS** (W-01…W-08 CLOSED — `docs/features/employee-auth-ui/work-breakdown.md:14`; F-W07-04 Wave 1 **COMPLETED** — `governance-log.md` **F-W07-04-D2** / **D3**; prior PARTIAL/CARRIED superseded). (synced PA-01, 2026-07-19) |
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
| SB-D6 | UI-M2 L3 Spec ACCEPTED (PASS-with-fixes) | F3 / UI-M2 | A) Accept L3 (PASS-with-fixes) B) Reject C) Hold | Lead | WP-01 rev-4 | **DECIDED (A)** | L3 spec accepted; hygiene fixes C-1/C-2/G-3 authorized; L6+ implementation NOT authorized; Implementation Lock required |
| SB-D7 | UI-M2 L6+ Authorization / Implementation Lock | F3 / UI-M2 | A) Issue L6+ + Lock B) Hold C) Reject | Lead | PA-03 PASS; WP-UI-M2-01 | **DECIDED (A) — ISSUED** | L6+ authorized under Lock; auth_gate=`dormitory-unit-manager` (identity); Lock=`docs/features/ui-m2/implementation-lock.md` |
| DG-ARCH-01 | DormitoryPolicy reads Infrastructure model directly (no Port) | DDD Boundary | Option B (DECIDED): DormitoryAssignmentReader Port in Domain/Contracts | Lead | Freeze v1.0 | DECIDED | Evidence: DEBT-DISCOVERY-01 T1 |
| DG-REQ-01 | ListPendingStage1RequestsAction depends on Identity UserModel | Identity Boundary | Option A (DECIDED, amended): execute(string $approverIdentityId) | Lead | Freeze v1.0 | DECIDED | Resolution moves to HTTP boundary (ref: DGAP-07) |
| DG-DORM-01 | Manager assignment tables: join vs lifecycle vs entity | Domain Gap | Option A (DECIDED): pure join table | Lead | Freeze v1.0 | DECIDED | WP-DEBT-02 → CLOSED — NO-ACTION |
| DG-SETTINGS-01 | settings table ownership + missing production migration | Cross-cutting | System module ownership (DECIDED) | Lead | Freeze v1.0 | DECIDED | WP-DEBT-04 CREATE = DELIVERED (`modules/system/2026_07_20_000001_create_settings_table.php`). Remaining seam = SettingsReadContract → **WP-SYS-01** (D-SETTINGS-CONTRACT Option B SIGNED-OFF). GAP-PREUI-14 CLOSED. |
| DEC-ARCH-POLICY-01 | Framework Policy placement (Laravel Gate / Eloquent) | DDD Boundary / Adapter | Option A (DECIDED): `Infrastructure/Policies/` | Lead | Freeze v1.0 / WP-DEBT-05 | IMPLEMENTED | WP-DEBT-05 CLOSED/ACCEPTED. GAP-PREUI-15 CLOSED (docs). Evidence: DELIVERY CONFIRMATION; 1928 passed. |
| D-SETTINGS-CONTRACT | System settings Application read seam | Cross-cutting / System | A) Eloquent model B) QB/DB contract C) keep per-module readers | Lead | Pre-UI Decision Sweep | **IMPLEMENTED** | SIGNED-OFF Option B. WP-SYS-01 DELIVERED: SettingsReadContract + QueryBuilderSettingsReader; Request/Audit/Notification migrated. Lottery exception unchanged (R2). Suite: 1932 passed. |
| D-SETTINGS-LOTTERY-X | LotteryScoringConfigReader direct DB::table under HD-02 freeze | Lottery / Settings | Accept temporary exception until HD-02 unfreeze | Lead | Pre-UI Decision Sweep | **ACCEPTED EXCEPTION** | R2 SIGNED-OFF. Not in WP-SYS-01 scope. |
| D-G03-FORM | PersonalRequestFormPage vs assignment-based access | Requests / Authz | A) assignment-scoped B) free-site exception C) defer+temp note | Lead | Pre-UI Decision Sweep | **DECIDED — A** | SIGNED-OFF 1405/04/29. Owner = WP-REQ-04 only. No WP-FORM-01. Free-site rejected. |
| D-ENTRYPOINT-RULE | Presentation may inject repository contracts? | Architecture / Presentation | Actions/Services only (vs thin repo injection) | Lead | Pre-UI Decision Sweep | **DECIDED** | R4 SIGNED-OFF. Presentation → Application Actions/Services only. WP-CHECKIN-01 **CLOSED** (Action wire delivered). |
| D-POLICY-AUTH-BOUNDARY | Gate Policy Identity Eloquent / UserModel typing | DDD / Auth | Blocker vs advisory vs accepted tension | Lead | Pre-UI Decision Sweep | **ACCEPTED TENSION** | R5 SIGNED-OFF. Authenticatable + Domain port OK. No code WP. Reopen on proven harm. |
| OQ-REQ-02 | Request Identity FK / model normalization (OQ-REQ-02-SYNC) | Requests / Spec05 R-03 | Option A (Normalized) | Lead | WP-REQ-01 | **CLOSED** | Option A (Normalized) — FK removed, Request model standalone. Signed-off. OQ-REQ-02-SYNC lifted. Physical drop executed under WP-REQ-01 L3 per scope. |
| SB-D9 | F-W07-04 Wave 2 (Stage-1 list/filter UX + tests) | F3 / stage1-approver-console | A) Authorize Wave 2 B) Hold C) Reject | Lead | F-W07-04-D3; WP-RQ-W2-01 | **DECIDED (A) — ISSUED**; WP **DONE** | Wave 2 list/filter UX + tests; auth_gate=`dormitory-manager` unchanged; SHA UNVERIFIED (merge-agnostic); Sprint B CLOSED |
| SB-D10 | Exempt registry classification — `ListPendingStage1RequestsAction` | F3 / stage1-approver-console / MPEP | A) Issue read-only exempt classification B) Hold C) Reject | Lead | WP-RQ-W2-01 review session | **DECIDED (A) — ISSUED**; **Recorded** | Read-only registry classification for MPEP discovery compatibility; no functional behavior change. Authority: Lead in-session during WP-RQ-W2-01; recording: retroactive (WP-DOC-SYNC-01); Sprint B CLOSED |
| DGAP-15 | Sprint C role-based dashboard track — Decision Register (D1–D5) + debt + WP sequence | Sprint C / Dashboard / DASH-00 | CLOSED — Lead-approved (no re-litigation); record D1–D5 + DBT-1…7 + WP sequence | Lead | WP-UI-C-DASH-00 | **CLOSED** | Tag **DASH-00**. **DASH-01 CLOSED** (2026-07-19). Layout delivered under `App\Modules\Dashboard`. Does **not** reopen DGAP-10/13/14. See decision block. |

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

### DGAP-15 — Sprint C Dashboard Decision Register (DASH-00)

| Field | Value |
|-------|-------|
| **ID** | **DGAP-15** |
| **Tag** | **DASH-00** / WP-UI-C-DASH-00 |
| **Title** | Sprint C role-based dashboard — formal Decision Register (D1–D5), debt register, WP sequence |
| **Status** | **CLOSED** (Lead-approved; docs-only registration — no re-litigation) |
| **Registered** | 1405/04/28 \| 2026-07-19 |
| **Decision-Owner** | Lead (Human Decision Authority) |
| **ID verification** | Decision Gate Table scanned: DGAP-01…14 present; **DGAP-15** absent → next free DGAP integer. Not a FROZEN ID. |
| **Related (cite only; not reopened)** | **DGAP-13** / **DGAP-14** (Stage-1 approver auth path = `dormitory-manager`); **DGAP-10** (dual-guard by design); **UI-M1-COV** (40P01 hygiene note — related to DBT-5) |

**Non-Scope:** no PHP, Blade, route, config, migration, seeder, or test changes under this registration. Does **not** authorize DASH-01+ implementation. Does **not** reopen any OPEN/PARKED gate (DGAP-03, SGAP-05/07, etc.).

#### Closed decisions (D1–D5)

| ID | Decision | Status | Notes / Evidence |
|----|----------|--------|------------------|
| **D1** | Shared dashboard shell = `components.layouts.dashboard` (header + optional sidebar). `components.layouts.dormitory-admin` stays separate for now. | **CLOSED** | **Delivered** WP-UI-C-DASH-01 — `resources/views/components/layouts/dashboard.blade.php` + `App\Modules\Dashboard\Presentation\Livewire\DashboardPage`. |
| **D2** | Navigation Option A — single nav + View Composer (pattern: `LayoutNavAuditLinkComposer` on `components.layouts.app`). Role SoT: `app/Shared/Auth/IdentityRoleGuard.php` (`guard_name=identity`). No role names hard-coded in Blade. Sprint C nav scope: `employee` + `dormitory-manager` only; `dormitory-unit-manager` deferred; web-guard roles out of scope. | **CLOSED** | Composer evidence: `app/Modules/Audit/Presentation/View/Composers/LayoutNavAuditLinkComposer.php`. Stage-1 role alignment: DGAP-13 / DGAP-14. **Impl:** DASH-02 ACTIVE. |
| **D3** | Target guard = `identity`. Transitional dual-session (`api` + `identity`) at login. Full `auth:api` → `identity` migration = **REGISTERED DEBT (Hard STOP)** — not Sprint C. | **CLOSED** | Dual-session: `app/Http/Controllers/Web/AuthSessionController.php` (`store`) + `app/Modules/Auth/Presentation/Livewire/EmployeeLogin.php` via `EstablishApiSessionFromCredentialLoginAction`. Route split: `routes/web.php`. **DASH-01:** named `dashboard` now `auth:identity` (was under `auth:api` stub — see DBT-3 note). |
| **D4** | `SystemAdministrator` remains on `web` guard. Migration to identity role = **REGISTERED DEBT**, out of Sprint C. | **CLOSED** | Aligns with dual-guard posture (DGAP-10 CLOSED — NOT-A-GAP by design). |
| **D5** | Dashboard code follows Modules convention (`app/Modules/...`), **not** `app/Livewire/Dashboard/`. | **CLOSED** | **Confirmed** module home: `App\Modules\Dashboard` (WP-UI-C-DASH-01 Lead closeout). |

#### Debt register (DBT-1…7)

| ID | Title | Priority | Hard STOP / blocker | Status |
|----|-------|----------|---------------------|--------|
| **DBT-1** | WP-UI-C-01-B — `DormitoryReadContract::listSites()` missing (`siteExists` only) → empty site `<select>` on canonical form | High | **Yes — blocks DASH-03** | **OPEN** |
| **DBT-2** | Post-login redirect not role-aware (`requests.index`). Pre-DASH-01: named `dashboard` was redirect stub → `/requests` (WP-UI-C-01-HOTFIX-01). Stub replaced by DASH-01 page; role-aware landing still DASH-05. | Medium | No | **OPEN** |
| **DBT-3** | Mixed `auth:api` / `auth:identity` route surface | High | **Yes (Hard STOP)** — full migrate out of Sprint C | **OPEN** |
| **DBT-4** | `SystemAdministrator` on `web` guard | Low | No | **OPEN** |
| **DBT-5** | Test DB Isolation — PostgreSQL `40P01` on concurrent suites sharing `testing` (not shared with dev). Related hygiene: UI-M1-COV. | — | **Lifted** (was Before DASH-01) | **CLOSED — NOT-A-GAP (config/operational)** |
| **DBT-6** | Transitional route `dormitory.requests.create` (302 stub in `routes/web.php`) — remove after stray-reference scan | Low | No | **OPEN** |
| **DBT-7** | Historical docs still referencing `RequestCreatePage` → DOC-SYNC | Low | No | **OPEN** |

**DBT-1 note:** Dormitory module already exposes `listDormitories()` (`app/Modules/Dormitory/Application/Contracts/DormitoryStructureReadContract.php`); gap is the Request-facing contract (`app/Modules/Request/Application/Contracts/DormitoryReadContract.php`), not data availability.

**DBT-3 note (HOTFIX-01 / DASH-01 drift — Lead closeout 2026-07-19):** Narrative that WP-UI-C-01-HOTFIX-01 registered `dashboard` under `auth:identity` is **incorrect vs committed tip**. Pre-DASH-01 `git show HEAD:routes/web.php` evidence: `Route::redirect('/dashboard', '/requests')->name('dashboard')` lived inside the `auth:api` + mutation/audit middleware group. DASH-01 correctly moved named `dashboard` to `auth:identity` per D3. This is an instance of the mixed-guard surface (DBT-3), not a new gap — full `auth:api`→`identity` migrate remains Hard STOP / out of Sprint C.

##### WP-UI-C-DASH-01 — Closeout Record

| Field | Value |
|-------|-------|
| **ID** | **WP-UI-C-DASH-01** |
| **Status** | **CLOSED** |
| **Closed** | 1405/04/28 \| 2026-07-19 |
| **Decision-Owner** | Lead |
| **Delivered** | `components.layouts.dashboard`; `App\Modules\Dashboard\Presentation\Livewire\DashboardPage`; route `dashboard` → page behind `auth:identity`; `DashboardShellTest` + `DashboardRouteTest` |
| **Lead confirmations (Q1–Q3)** | (1) `auth:api`→`auth:identity` for `dashboard` **approved** (D3); HOTFIX narrative drift logged under DBT-3. (2) New `DashboardRouteTest` **accepted**. (3) Module home `App\Modules\Dashboard` **approved** (D5). |
| **Non-Scope retained** | No nav/roles (DASH-02); no landings/data (DASH-03/04); no post-login resolver (DASH-05). |

##### DBT-5 — Decision Record (Lead, WP-UI-C-TEST-ISO-01 / DASH-00 close)

| Field | Value |
|-------|-------|
| **ID** | **DBT-5** (under DGAP-15 / DASH-00) |
| **Status** | **CLOSED — NOT-A-GAP (config/operational)** |
| **Closed** | 1405/04/28 \| 2026-07-19 |
| **Decision-Owner** | Lead |
| **Root cause** | `40P01` deadlock from **concurrent suite execution** on the shared **test** DB named `testing` — not from sharing the development database |
| **Evidence** | Phase 1 probe (D1–D6): `phpunit.xml` → `DB_DATABASE=testing`; `.env` / app boot → `laravel`; Sail init already creates `testing`; Feature suite uses `RefreshDatabase`; no ParaTest in composer; leftover `testing_test_*` DBs observed |
| **Resolution** | Not a gap — isolation already exists at DB level (`laravel` ≠ `testing`) |
| **Mitigation** | Run **one suite at a time**; CI must use **exclusive** test execution. No code / phpunit / compose / rename change required |
| **Residual risk** | Flake if suites run in parallel — **acceptable per Lead** |
| **Effect** | Hard STOP (DBT-5 before DASH-01) **lifted**. TEST-ISO-01 Phase 2 **not authorized / not needed**. |

#### WP sequence (Sprint C — Dashboard Track)

```
DASH-00 (DONE) → TEST-ISO-01 / DBT-5 (CLOSED — NOT-A-GAP) → DASH-01 (CLOSED) → DASH-02 (nav ACTIVE)
  → DASH-03 (employee landing; depends on WP-UI-C-01-B / DBT-1)
  → DASH-04 (manager landing; DASH-SEED prerequisite DONE — Verify PASS;
             dev.manager@dormsys.local / identity role dormitory-manager)
  → DASH-05 (role-aware post-login resolver)
```

WP-UI-C-01-B (DBT-1) runs **in parallel** with DASH-02 and **must land before DASH-03**.

**Effect:** Formal ledger for Sprint C dashboard decisions. DASH-00 + DASH-01 CLOSED; DBT-5 Hard STOP lifted. DASH-02 / WP-UI-C-01-B next (parallel sessions).

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

### DG-ARCH-01 (DormitoryPolicy → Infrastructure coupling)

- **Selected Option:** Option B
- **Decided-On:** 2026-07-20
- **Decision-Owner:** Lead
- **Rationale:**
  - Policy در لایه Application مستقیماً Infrastructure model می‌خواند؛ نقض وارونگی وابستگی.
  - مسیر `app/Modules/Dormitory/Domain/Contracts/` وجود ندارد — Port باید تازه تعریف شود.
- **Evidence:** DEBT-DISCOVERY-01 T1 — `app/Modules/Dormitory/Application/Policies/DormitoryPolicy.php` L7, L24–27, L35–39
- **Effect:** WP-DEBT-01 مجاز به اجرا: تعریف `DormitoryAssignmentReader` + binding در ServiceProvider.
- **Constraint:** Port فقط read متدهای مصرف‌شده در Policy را expose کند (minimal interface).
- **Notes:** (ref: WP-DEBT-01)

### DG-REQ-01 (ListPendingStage1RequestsAction identity coupling)

- **Selected Option:** Option A (amended)
- **Decided-On:** 2026-07-20
- **Decision-Owner:** Lead
- **Rationale:**
  - Discovery نشان داد `approverIdentityId` در context موجود نیست؛ فرض اولیه اصلاح شد.
  - تغییر signature به `execute(string $approverIdentityId)` وابستگی به `UserModel` را از Application حذف می‌کند.
- **Evidence:** DEBT-DISCOVERY-01 T2 — `app/Modules/Request/Application/Services/ListPendingStage1RequestsAction.php` L7, L27, L32
- **Effect:** WP-DEBT-03 مجاز به اجرا؛ resolve کردن identity به HTTP/Livewire boundary منتقل می‌شود.
- **Constraint:** هیچ import از `App\Modules\Identity\*` در لایه Application باقی نماند.
- **Notes:** (ref: DGAP-07)

### DG-DORM-01 (Manager assignment tables nature)

- **Selected Option:** Option A
- **Decided-On:** 2026-07-20
- **Decision-Owner:** Lead
- **Rationale:**
  - هر دو جدول assignment فاقد ستون lifecycle (`revoked_at` و مشابه) و فاقد Eloquent Model هستند.
  - ماهیت فعلی join table ساده است؛ ارتقاء بدون نیاز اثبات‌شده = over-engineering.
- **Evidence:** DEBT-DISCOVERY-01 T3 — `database/migrations/modules/dormitory/2026_07_16_000001_*` و `2026_07_16_000002_*`
- **Effect:** WP-DEBT-02 → CLOSED — NO-ACTION. در صورت نیاز آتی به lifecycle، DG جدید ثبت شود.
- **Constraint:** هیچ Model یا ستونی اضافه نشود.
- **Notes:** —

### DG-SETTINGS-01 (settings table ownership + missing migration)

- **Selected Option:** System module ownership
- **Decided-On:** 2026-07-20
- **Decision-Owner:** Lead
- **Rationale:**
  - هیچ create migration برای `settings` در Production وجود ندارد — جدول صرفاً test-only است.
  - چهار مصرف‌کننده (Request, Lottery, Notification, Audit) read-only با fallback هستند.
  - مصرف cross-module ⇒ مالک واحد و خنثی: System.
- **Evidence:** DEBT-DISCOVERY-01 T4 — `tests/Feature/Modules/Lottery/LotteryFeatureSupport.php` L28–32
- **Effect:** WP-DEBT-04 بازتعریف: ایجاد migration اصلی (`id` uuid PK, `key` string unique, `value` json, timestamps) ذیل System.
- **Constraint:** Schema باید دقیقاً با Blueprint تست‌ها منطبق باشد (جلوگیری از test drift).
- **Notes:** (ref: WP-DEBT-04)
- **Register sync (1405/04/29 \| 2026-07-20 — DG-SETTINGS-01-REGISTER-SYNC):** Production create migration EXISTS at `database/migrations/modules/system/2026_07_20_000001_create_settings_table.php`. WP-DEBT-04 migration = DELIVERED. GAP-PREUI-14 = CLOSED (stale-register lag). Remaining Application seam = **D-SETTINGS-CONTRACT Option B** → **WP-SYS-01** (Lead Ruling Pack SIGNED-OFF).

### DEC-ARCH-POLICY-01 — Framework Policy Placement

- **Status:** IMPLEMENTED (1405/04/29 \| 2026-07-20)
- **Selected Option:** Option A — `app/Modules/<Module>/Infrastructure/Policies/`
- **Decision-Owner:** Lead
- **Context:** Laravel Policies receive Eloquent models via Gate and depend on framework Auth contracts; they cannot satisfy the Application-layer forbidden-imports rule. `docs/architecture/boundary-rules.md` was previously silent on Policy placement.
- **Decision:** Laravel Policy classes are framework adapters and **MUST** live under `app/Modules/<Module>/Infrastructure/Policies/`. Importing the module's own Infrastructure models there is legal. Domain/read logic **MUST** remain behind Domain ports (e.g. `DormitoryAssignmentReader`).
- **Rejected:** Option B (keep in Application — impossible without ForbiddenImportsScan failure on Gate-injected Eloquent type-hints). Option C (Application authorization service + thin Infra Policy — deferred backlog; requires new interface; out of Freeze WP-DEBT-05 scope).
- **Consequence:** `docs/architecture/boundary-rules.md` amended; `DormitoryPolicy` relocation in WP-DEBT-05 is **pre-authorized**.
- **Implementation status:** IMPLEMENTED — Evidence: WP-DEBT-05 DELIVERY CONFIRMATION; Validation: 1928 passed (5568 assertions).
- **Register sync (R6b / GAP-PREUI-15):** WP-DEBT-05-STATUS-SYNC = CLOSED (docs-only). No re-implementation. Any residual “awaiting WP-DEBT-05” language is stale.

---

## F2 Process Re-sync (Option B)

- **Decision:** Option B — Governance Reconciliation
- **Decided-By:** Lead
- **Decided-On:** 1405/04/24 (2026/07/15)
- **Accepted evidence:** `EmployeeLogin` (`app/Modules/Auth/Presentation/Livewire/EmployeeLogin.php`), `IdentityRoleGuard` (`app/Shared/Auth/IdentityRoleGuard.php`), related routes in `routes/web.php` (`employee.login`)
- **Effect:** W-04 PASS/CLOSED; W-05 and W-06 IMPLEMENTED in `docs/features/employee-auth-ui/work-breakdown.md`. L3 §4 updated accordingly.
- **Freeze:** No *new* F2/UI/Auth features until further Lead authorization.
- **Annotation (reconciled 2026-07-15, ref: DGAP-12; synced PA-01 2026-07-19):** Prior line “W-07/W-08 remain Pending” is **superseded**. W-07 and W-08 are **CLOSED** (`docs/features/employee-auth-ui/work-breakdown.md:11-12`; `w07-security-review-report.md:24`; `w08-scope.md:32`). Prior “F2 PARTIAL solely because F-W07-04 CARRIED FORWARD” is **superseded** by **F-W07-04-D2** (F2 → **PASS**) and **F-W07-04-D3** (Wave 1 **COMPLETED**) — `governance-log.md:16–17`.

### F-W07-04 — Wave 1 COMPLETED (F2 canonical ID retained)

- **ID:** F-W07-04 (do **not** re-number as W-09 or other ID — one decision boundary = one canonical record).
- **Status:** **Wave 1 ✅ COMPLETED** — `governance-log.md` **F-W07-04-D3** (2026-07-18). Prior CARRIED FORWARD (HD-05A / AUTH-013) superseded for Wave 1 scope.
- **Slug / FC:** `stage1-approver-console` (**F-W07-04-D1**); Feature Contract ACCEPTED; F2 gate for F-W07-04 **PASS** (**F-W07-04-D2**).
- **Meaning (historical):** Post-login surface catalog / next UI slug product-authorization gate — resolved for Stage-1 console Wave 1 (list/filter); UX-test expansion deferred per D3.
- **Forbidden:** Inventing a parallel work-item ID for the same commitment; reopening D1–D3 without Lead.

---

## Pending Artifacts

- [x] `docs/governance/risk-register.md` (از DG-04) — delivered 1405/04/24; includes SEC-G-04 + BL-B1-01
- [x] `docs/governance/glossary.md` (از DG-05) — delivered 1405/04/24; Student/Employee mapping
- [x] Roadmap update: split Phase F → F1/F2 (از DG-02) — delivered `docs/governance/roadmap.md`

---

## Changelog

| تاریخ | تغییر | توسط |
|-------|-------|------|
| 2026-07-20 | **Lead Ruling Pack SIGNED-OFF (R1–R6):** D-SETTINGS-CONTRACT=B; Lottery settings exception (HD-02); D-G03-FORM=A (WP-REQ-04 only, no WP-FORM-01); ENTRYPOINT-RULE + WP-CHECKIN-01 mandatory; POLICY-AUTH-BOUNDARY=Accepted Tension; R6 register sync — GAP-PREUI-14/15 CLOSED. | Lead |
| 2026-07-20 | **WP-SYS-01 CLOSED:** SettingsReadContract + QueryBuilderSettingsReader bound in SystemServiceProvider; Request/Audit/Notification readers migrated; Lottery untouched. Suite: 1932 passed / 0 failed. PHPStan scoped paths: OK. | Agent (Mandate CHAIN-5) |
| 2026-07-20 | **WP-CHECKIN-01 CLOSED:** CheckInFlowController routes via GetOpenCheckInByAllocationAction; Presentation no longer injects repository contract. Suite: 1930 passed / 0 failed. | Agent (Mandate CHAIN-2) |
| 2026-07-20 | **DG-SETTINGS-01-REGISTER-SYNC:** Gate/metadata note corrected — settings create migration EXISTS (`modules/system/2026_07_20_000001_…`); WP-DEBT-04 migration DELIVERED; contract seam → WP-SYS-01 (Option B). GAP-PREUI-14 CLOSED. | Agent (Lead R6a) |
| 2026-07-20 | **WP-DEBT-05-STATUS-SYNC:** Confirmed CLOSED / ACCEPTED — DormitoryPolicy under Infrastructure/Policies; DEC-ARCH-POLICY-01 IMPLEMENTED; GAP-PREUI-15 CLOSED (docs). Suite evidence previously recorded (1928 passed). | Agent (Lead R6b) |
| 2026-07-20 | **OQ-REQ-02 CLOSED:** Option A (Normalized) — FK removed, Request model standalone. Signed-off. OQ-REQ-02-SYNC lifted; Requests module WPs unblocked for Domain-First Phase 2. WP-REQ-01 remains execution vehicle for schema. | Lead |
| 2026-07-20 | **OQ-REQ-02-SYNC:** OQ-REQ-02 → Option A ACCEPTED (DECIDED → IMPLEMENTATION AUTHORIZED); Spec05 R-03 normalize (drop FK, retain UUID); sunset REMOVED; WP-REQ-01 AUTHORIZED TO OPEN. OQ-DORM-04 → SEQUENTIAL WP-DORM-04 after WP-REQ-01 CLOSED. Registered OPEN: OQ-REQ-11, OQ-REQ-12, D-G03-FORM→WP-REQ-04. | Lead |
| 2026-07-20 | **DEC-ARCH-POLICY-01 IMPLEMENTED:** WP-DEBT-05 CLOSED / ACCEPTED; Evidence: WP-DEBT-05 DELIVERY CONFIRMATION; Validation: 1928 passed (5568 assertions). Freeze v1.0 SIGNED-OFF (project-state). | Lead |
| 2026-07-20 | **DEC-ARCH-POLICY-01 DECIDED — A:** Laravel Policies → `Infrastructure/Policies/`; own-module Eloquent type-hints legal; Domain reads via ports. Pre-authorizes WP-DEBT-05. Amend `docs/architecture/boundary-rules.md`. | Lead |
| 2026-07-20 | DG-ARCH-01(B), DG-REQ-01(A-amended), DG-DORM-01(A), DG-SETTINGS-01(System) — DECIDED per DEBT-DISCOVERY-01 | Lead |
| ۱۴۰۵/۰۴/۲۸ (2026-07-19) | **WP-UI-C-DASH-01 CLOSED:** shell layout + `DashboardPage` + `dashboard` route on `auth:identity`. Lead Q1–Q3 confirmed (guard move / DashboardRouteTest / `App\Modules\Dashboard`). **DBT-3 note:** HOTFIX-01 stub was committed under `auth:api` (not identity) — narrative drift recorded; full migrate remains Hard STOP. | Agent (Lead DASH-01 closeout) |
| ۱۴۰۵/۰۴/۲۸ (2026-07-19) | **DBT-5 CLOSED — NOT-A-GAP (config/operational)** (Lead): 40P01 = concurrent suites on shared `testing` DB; Phase 1 proved `testing` ≠ `laravel`; mitigation = single-suite / exclusive CI; no infra change; Hard STOP before DASH-01 **lifted**. Ref: WP-UI-C-TEST-ISO-01 / DASH-00 close. | Agent (Lead DBT-5 close) |
| ۱۴۰۵/۰۴/۲۸ (2026-07-19) | **DGAP-15 CLOSED** (tag **DASH-00** / WP-UI-C-DASH-00): Sprint C dashboard Decision Register — D1–D5 CLOSED (shell/nav/auth/admin/path); debt DBT-1…7 recorded; WP sequence DASH-00→TEST-ISO-01→DASH-01…05 (+ WP-UI-C-01-B ‖ before DASH-03). Docs-only; no re-litigation; DGAP-10/13/14 not reopened. | Agent (WP-UI-C-DASH-00) |
| ۱۴۰۵/۰۴/۲۸ (2026-07-19) | **Sprint B CLOSED** (Lead Final Closure Record): WP-RQ-W2-01 / WP-UI-M2-01 / WP-DOC-SYNC-01 **DONE**; SB-D10 Recorded; commit SHA **UNVERIFIED** (merge-agnostic); WP-GOV-SHA-01/01b **CANCELLED**. G7 remains DEFERRED (Wave-3). | Agent (Lead Sprint B Closure) |
| ۱۴۰۵/۰۴/۲۸ (2026-07-19) | **SB-D10 DECIDED (A) — ISSUED (retroactive record):** Exempt registry classification for `ListPendingStage1RequestsAction` (MPEP discovery compatibility; no functional behavior change). Authority: Lead in-session during WP-RQ-W2-01 review; documentation recorded retroactively (WP-DOC-SYNC-01). | Agent (Lead WP-DOC-SYNC-01) |
| ۱۴۰۵/۰۴/۲۸ (2026-07-19) | **SB-D9 DECIDED (A) — ISSUED:** F-W07-04 Wave 2 (Stage-1 list/filter UX + tests). auth_gate=`dormitory-manager` unchanged. WP-RQ-W2-01. | Agent (Lead WP-RQ-W2-01) |
| ۱۴۰۵/۰۴/۲۸ (2026-07-19) | **SB-D7 DECIDED (A) — ISSUED:** UI-M2 L6+ Authorization + Implementation Lock. auth_gate=`dormitory-unit-manager`. Lock=`docs/features/ui-m2/implementation-lock.md`. WP-UI-M2-01 verify/align-to-L3. | Agent (Lead WP-UI-M2-01) |
| ۱۴۰۵/۰۴/۲۸ (2026-07-19) | **SB-D6 DECIDED (A):** UI-M2 L3 Spec ACCEPTED (PASS-with-fixes). Hygiene C-1/C-2/G-3 authorized. L6+ NOT authorized; Implementation Lock required. WP-01 rev-4. SB-D1…D5 unmodified. | Agent (Lead WP-01 rev-4) |
| ۱۴۰۵/۰۴/۲۸ (2026-07-19) | **PA-01 DOC sync:** F-W07-04 § → Wave 1 COMPLETED (**D3**); F2 notes → **PASS** (**D2**). **DGAP-14** verified already **DECIDED** with inventory dispositions — status unchanged (not reopened). Trailing orphan diff fragment removed. No new decision. | Agent (PA-01) |
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

## Q-EMP-DORM — RESOLVED

| Field | Value |
|---|---|
| **Decision date** | 1405/04/29 (2026-07-20) |
| **Decision** | Option B — Assignment-based |
| **Statement** | Employee access to Dormitories is restricted to explicit assignments. Relationship: `Employee 1—* DormitoryAssignment *—1 Dormitory`. An employee can only see Dormitories for which an active assignment record exists for that employee. Global (all-dormitories) access for the Employee role is rejected. |
| **Evidence** | ER sketch (`Employee 1—* Dormitory`) + DBT-1 residual |
| **Impact on G02** | Quarantined WP-DASH-G02 artifacts (DormitoryPolicy + tests) must be rewritten against the assignment model. Supersedes the hard-coded global-access assumption. |
| **Approved by** | Lead — 1405/04/29 |
| **Previous status** | Q-DBT-1-AUTH: decision approved, implementation deferred |
| **New status** | Q-EMP-DORM: RESOLVED → Option B |

### Addendum — WP-DASH-G02-R1 implementation constraints (Lead-approved, 1405/04/29)

1. **FK target:** `dormitory_assignments.user_id` references `identity_users.id` (NOT `users.id`), consistent with `dormitory_manager_assignments`, `dormitory_unit_manager_assignments`, and the `auth:identity` guard.
2. **Table independence:** `dormitory_assignments` is a new, standalone employee↔dormitory table. It does not extend, replace, or interact with `dormitory_manager_assignments` or `dormitory_unit_manager_assignments`.
3. **Lifecycle:** `dormitory_assignments` uses `revoked_at` for soft revocation. The two manager-assignment tables intentionally do not have `revoked_at`; this asymmetry is accepted and documented.

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

## Q-G03-SCOPE — سطح هدف WP-DASH-G03

- **Status:** RESOLVED
- **Date:** 2026-07-20 (1405/04/29)
- **Context:** گزارش STOP در WP-DASH-G03 نشان داد لایه Presentation ماژول
  Dormitory خالی است (فقط .gitkeep) و هیچ route کارمندی برای index/show
  وجود ندارد. فرض «wire-up سطح موجود» در تعریف WP نادرست بود.
- **Decision:** هدف G03-R1 ساخت دو صفحه‌ی جدید Livewire مختص نقش employee
  است: DormitoryIndexPage و DormitoryShowPage در
  `app/Modules/Dormitory/Presentation/Livewire/`.
  اتصال فرم PersonalRequestFormPage خارج از scope است (ر.ک. D-G03-FORM).
- **Constraints:** فیلترینگ صرفاً در سطح Query بر اساس
  `DormitoryAssignment::active()`؛ authorize در mount کامپوننت Show؛
  Empty-state الزامی؛ URL غیرمنتسب → 403.

## Q-G03-MGR-PATH — جداسازی مسیر manager از مسیر کارمندی

- **Status:** RESOLVED
- **Date:** 2026-07-20 (1405/04/29)
- **Context:** ابهام Agent درباره‌ی دسترسی نقش dormitory-manager به
  صفحات جدید خوابگاه.
- **Decision:** جداسازی کامل. مسیرهای `/dormitories` و
  `/dormitories/{dormitory}` فقط برای نقش employee مجاز است؛ نقش
  dormitory-manager از این مسیرها 403 دریافت می‌کند و همچنان صرفاً از
  DormitoryManagerDashboard موجود استفاده می‌کند. هیچ query یا کامپوننت
  مشترکی بین دو نقش ساخته نمی‌شود.
- **Basis:** Addendum §2 (استقلال dormitory_manager_assignments از جدول
  کارمندان) و Q-EMP-DORM گزینه B.
- **Enforcement:** تست Feature الزامی — دسترسی manager به /dormitories
  باید 403 برگرداند.

## Q-G03-SEED — سیاست داده‌ی آزمایشی در G03-R1

- **Status:** RESOLVED
- **Date:** 2026-07-20 (1405/04/29)
- **Decision:** داده‌ی انتساب صرفاً به صورت fixture داخل تست‌های Feature
  ساخته می‌شود. DevelopmentUserSeeder در این WP تغییر نمی‌کند. دموی
  local در صورت نیاز از طریق Tinker دستی یا WP جداگانه انجام می‌شود.

---

## D-G03-FORM — بدهی حاکمیتی: تناقض PersonalRequestFormPage با مدل انتساب‌محور

- **Status:** **DECIDED — A** (rule); implementation OPEN under WP-REQ-04
- **Selected Option:** A — assignment-scoped sites via `listAssignedSites*` (OQ-REQ-03 / Q-EMP-DORM Option B)
- **Signed-Off:** 1405/04/29 \| 2026-07-20 — Lead Ruling Pack R3
- **Topic:** assigned-sites form incomplete state
- **Dependency:** WP-REQ-04 (sole owner)
- **Forbidden:** No WP-FORM-01. Free-site listing rejected. No WP other than WP-REQ-04 may change PersonalRequestFormPage / `DormitoryReadContract` list surface.
- **Block:** none until WP-REQ-04 (implementation)
- **Context:** فرم «ثبت درخواست شخصی» (`/requests/personal/create`)
  فهرست خوابگاه‌ها را از `DormitoryReadContract::listSites()` می‌گیرد که
  با تصمیم Q-EMP-DORM (دسترسی صرفاً بر اساس انتساب فعال) در تناقض است.
  در وضعیت فعلی select خالی رندر می‌شود (`"sites": []`).
- **Blocked artifact:** PersonalRequestFormPage و
  DormitoryReadContract::listSites()
- **Resolution path:** WP-REQ-04 (assigned-sites / OQ-REQ-03 contract). Prior suggestion WP-DASH-G04 superseded for sequencing by Requests L1 plan.
- **WP-DORM-UI-READ:** independent — may proceed without waiting on form remediation.

## Phase 1 — Gap Decomposition: Final Dispositions

**Closed:** 1405/04/29 | Criterion: Best path to complete primary phases

---

### Identity FK Exception Cluster

**OQ-REQ-02** | CLOSED | Option A (Normalized) — FK removed, Request model standalone. Signed-off.

- **Status:** **CLOSED**
- **Decision:** Option A (Normalized) — FK removed, Request model standalone. Signed-off.
- **Rationale:** Normalize Request module per Spec05 R-03. Remove direct FK coupling to `identity_users`. Retain UUID reference only. Identity resolution remains outside Request ownership boundary.
- **Authorized by:** Lead (L0)
- **Effective / Closed:** 1405/04/29 \| 2026-07-20
- **Sunset clause:** REMOVED (normalization is the canonical path)
- **OQ-REQ-02-SYNC:** LIFTED — Option A vs B conflict closed
- **Execution WP:** WP-REQ-01 (schema drop FK; UUID retained) — Requests module WPs unblocked for Domain-First Phase 2 sequencing

**OQ-DORM-04** | Option B retained as temporary exception until sequential WP

- Exception is outside Spec04 core; Spec04 remains authoritative
- **Sequencing:** SEQUENTIAL — separate WP after WP-REQ-01 closes
- **Designated WP:** WP-DORM-04
- **Dependency:** WP-REQ-01 CLOSED is prerequisite gate
- **Rationale:** Module boundary isolation; independent rollback; clean per-module test suite
- Non-blocking for Requests WP-REQ-01

---

### Resolved — No Further Action Required

**OQ-REQ-03** | Option B — RESOLVED

- `listSites` scoped; new contract `listAssignedSites*` defined
- Unblocks G03

**OQ-REQ-05** | Option B — RESOLVED (doc-only)

- Naming alignment with Assignment-based model; no implementation change

**OQ-EMP-01** | Option A — RESOLVED

- `ActiveAllocationReadPort` bridged to `AllocationReadContract`
- Eligibility chain restored

**OQ-EMP-04** | Option A — RESOLVED

- `Employee` designated as source for `DependentSnapshotSourceContract`
- Domain ownership clarified

**OQ-ALLOC-01** | Option B — RESOLVED

- Unused ports deprecated; no active callers

**OQ-ALLOC-04** | Option A — RESOLVED

- `ProposedAllocation` payload: `bed_uuid` + stay dates confirmed

---

### Accepted — Pending Reconciliation or Future Action

**OQ-REQ-06** | Option A — ACCEPTED, PENDING RECONCILIATION

- Filter by assignee/snapshot confirmed as direction
- Operational behavior impact requires reconciliation analysis before closing

**OQ-DORM-03** | Option A — ACCEPTED

- Dual pattern retained; boundary documentation MANDATORY in Reconciliation Package
- Boundary must be defined in this phase, not deferred

**OQ-ALLOC-02** | Option C — ACCEPTED AS DEBT

- Dual naming retained temporarily; non-blocking
- Remediation note: normalize naming before Phase 3

---

## Phase 1 — Final Decisions (Gap Decomposition)

### OQ-REQ-02 + OQ-DORM-04 — Identity FK Exception Cluster

#### OQ-REQ-02

- **Status:** **CLOSED**
- **Decision:** Option A (Normalized) — FK removed, Request model standalone. Signed-off.
- **OQ-REQ-02-SYNC:** LIFTED
- **Rationale:** Normalize Request module per Spec05 R-03. Remove direct FK coupling to `identity_users`. Retain UUID reference only. Identity resolution remains outside Request ownership boundary.
- **Authorized by:** Lead (L0)
- **Closed:** 1405/04/29 \| 2026-07-20
- **Sunset clause:** REMOVED (normalization is the canonical path)
- **WP:** WP-REQ-01 executes physical FK drop (UUID retained); Requests Phase 2 WPs unblocked for sequencing

#### OQ-DORM-04

- **Status:** ACCEPTED — Temporary Governance Exception (pending sequential WP)
- **Sequencing:** SEQUENTIAL — separate WP after WP-REQ-01 closes
- **Designated WP:** WP-DORM-04
- **Dependency:** WP-REQ-01 CLOSED is prerequisite gate
- **Rationale:** Module boundary isolation; independent rollback; clean per-module test suite

---

### OQ-REQ-03

**Status:** RESOLVED
**Decision:** Option B — قرارداد جدید `listAssignedSites*` تعریف می‌شود.

---

### OQ-REQ-05

**Status:** RESOLVED
**Decision:** Option B — doc-only.

---

### OQ-REQ-06

**Status:** PENDING RECONCILIATION
**Decision:** Option A — فیلتر روی `assigned_stage1_approver_identity_id`.
**Action:** WP اجرای فیلتر در Repository.

---

### OQ-DORM-03

**Status:** RESOLVED
**Decision:** Option A — dual pattern با boundary مستند در Spec04.
**Action:** annotation به Spec04 اضافه شود.

---

### OQ-EMP-01

**Status:** RESOLVED
**Decision:** Option A — bridge به `AllocationReadContract`.

---

### OQ-EMP-04

**Status:** RESOLVED
**Decision:** Option A — Employee منبع `DependentSnapshotSourceContract`.

---

### OQ-ALLOC-01

**Status:** RESOLVED
**Decision:** Option B — Deprecate unused port.

---

### OQ-ALLOC-02

**Status:** DEBT — Accepted
**Decision:** Option C — dual naming موقتاً پذیرفته.
**Debt log:** نام‌گذاری دوگانه باید در WP آینده یکسان‌سازی شود.

---

### OQ-ALLOC-04

**Status:** RESOLVED
**Decision:** Option A — `bed_uuid` + stay dates.

---

## OPEN — Requests L1 (no implementation block)

### OQ-REQ-11

- **Topic:** Stage-1 snapshot scope (Personal create only vs. broader)
- **Status:** OPEN — domain decision pending
- **Block:** none

### OQ-REQ-12

- **Topic:** Auth middleware standardization (`auth:api` vs `auth:identity`)
- **Status:** OPEN — DEFERRED to WP-REQ-07
- **Block:** none until route consolidation

### D-G03-FORM

- **Topic:** assigned-sites form incomplete state
- **Status:** **DECIDED — A** (rule); implementation OPEN under WP-REQ-04
- **Signed-Off:** 1405/04/29 \| Lead Ruling Pack R3
- **Dependency:** WP-REQ-04 (sole owner; no WP-FORM-01)
- **Block:** none (resolution owned by WP-REQ-04)

---

## Deferred / Tracked

| ID | Title | Notes | Date |
|----|-------|-------|------|
| OQ-ALLOC-02 | Domain Models/ vs Entities/ naming debt | Option C accepted as debt; track-only; rename deferred to post-Phase 2 / before Phase 3 | 1405/04/29 \| 2026-07-20 |
| OQ-REQ-11 | Stage-1 snapshot scope | OPEN — domain decision pending; Block: none | 1405/04/29 \| 2026-07-20 |
| OQ-REQ-12 | Auth middleware standardization | OPEN — DEFERRED to WP-REQ-07; Block: none until route consolidation | 1405/04/29 \| 2026-07-20 |
| D-G03-FORM | assigned-sites form incomplete | **DECIDED — A**; impl OPEN under WP-REQ-04 only; no WP-FORM-01 | 1405/04/29 \| 2026-07-20 |

## Temporary Governance Exceptions (append-only record)

| ID | Title | Notes | Date |
|----|-------|-------|------|
| OQ-REQ-02 | Stage-1 `assigned_stage1_approver_identity_id` FK → `identity_users` | **CLOSED** \| Option A (Normalized) — FK removed, Request model standalone. Signed-off. OQ-REQ-02-SYNC lifted. WP-REQ-01 = execution vehicle for schema. | 1405/04/29 \| 2026-07-20 |
| OQ-DORM-04 | Dormitory assignment tables `user_id` FK → `identity_users` | ACCEPTED temporary exception outside Spec04 core; **Sequencing:** SEQUENTIAL after WP-REQ-01 CLOSED; **Designated WP:** WP-DORM-04; independent rollback / per-module tests | 1405/04/29 \| 2026-07-20 |
| D-SETTINGS-LOTTERY-X | LotteryScoringConfigReader → `DB::table('settings')` | ACCEPTED temporary exception under HD-02 Lottery freeze; **not** in WP-SYS-01; sunset = HD-02 unfreeze | 1405/04/29 \| 2026-07-20 |

### WP-REQ-01 — CLOSED (1405/04/29)

- Option A: FK `requests_assigned_stage1_approver_identity_id_foreign` DROPPED.
- Column + index retained. Suite: 1928 passed / 0 failed. Review: ACCEPT.

### GAP-PREUI-12 — RESOLVED-BY-DELIVERY

- Audit note "FK Constrained" was stale. FK dropped, column retained (Option A) — WP-REQ-01 DELIVERED.

### WP-DEBT-05 — CLOSED / ACCEPTED (status sync 1405/04/29)

- DormitoryPolicy resides at `app/Modules/Dormitory/Infrastructure/Policies/DormitoryPolicy.php`.
- DEC-ARCH-POLICY-01 Status/Implementation = IMPLEMENTED (evidence: DELIVERY CONFIRMATION; 1928 passed).
- Residue: none for Policy relocation scope.
- **GAP-PREUI-15 / WP-DEBT-05-STATUS-SYNC:** CLOSED (docs-only; Lead Ruling Pack R6b).

### GAP-PREUI-14 — CLOSED (stale-register lag)

- DG-SETTINGS-01 notes corrected: WP-DEBT-04 CREATE migration DELIVERED.
- Remaining work = SettingsReadContract under WP-SYS-01 (D-SETTINGS-CONTRACT Option B).
- Authority: Lead Ruling Pack R6a — SIGNED-OFF 1405/04/29.

### GAP-PREUI-15 — CLOSED (docs-only)

- DEC-ARCH-POLICY-01 / WP-DEBT-05 = IMPLEMENTED. No re-implementation.
- Authority: Lead Ruling Pack R6b — SIGNED-OFF 1405/04/29.

---

## Lead Ruling Pack — Pre-UI Decision Sweep (SIGNED-OFF)

| Field | Value |
|-------|-------|
| **Status** | **SIGNED-OFF** |
| **Date** | 1405/04/29 \| 2026-07-20 |
| **Authority** | DormSys Architect / Lead |
| **Source** | Pre-UI Decision Sweep |

### R1 — D-SETTINGS-CONTRACT → Option B ✅

System owns a read-only `SettingsReadContract` (port). Implementation uses query builder / `DB::table` under System Infrastructure. No Settings Eloquent model. **WP-SYS-01** delivers the contract, binding in `SystemServiceProvider`, and migrates Request, Audit, Notification readers onto it.

### R2 — Lottery Settings Exception (HD-02) ✅

`LotteryScoringConfigReader` remains untouched under Lottery freeze. Direct `DB::table('settings')` there is an accepted temporary exception until HD-02 unfreeze. **Not** in WP-SYS-01 scope.

### R3 — D-G03-FORM → Option A ✅

Employee personal-request dormitory selection is assignment-scoped via `listAssignedSites*` (OQ-REQ-03 / Q-EMP-DORM Option B). Free-site listing rejected. Implementation owner = **WP-REQ-04** only. **No WP-FORM-01** shall be created.

### R4 — ENTRYPOINT-RULE + WP-CHECKIN-01 ✅

HTTP/Livewire Presentation must inject Application Actions/Services. Injecting repository contracts directly into Presentation is prohibited. **WP-CHECKIN-01** was mandatory until `CheckInFlowController` routed through an Application Action. **CLOSED (1405/04/29 \| 2026-07-20):** Presentation injects `GetOpenCheckInByAllocationAction` only; suite 1930 passed / 0 failed.

### R5 — POLICY-AUTH-BOUNDARY → Accepted Tension ✅

Cross-module Identity Eloquent typing in Gate Policies is advisory / accepted tension. Current `Authenticatable` + Domain port pattern satisfies the architecture decision. No code WP. Reopen only on proven harm (ForbiddenImportsScan failure or runtime Gate failure).

### R6 — Register Sync (Docs-only) ✅

- **(a)** DG-SETTINGS-01: WP-DEBT-04 CREATE = DELIVERED. Remaining = SettingsReadContract / WP-SYS-01. GAP-PREUI-14 = CLOSED.
- **(b)** DEC-ARCH-POLICY-01 / WP-DEBT-05 = IMPLEMENTED. GAP-PREUI-15 / WP-DEBT-05-STATUS-SYNC = docs-only close. No re-implementation.

### Post-ruling execution sequence

1. DG-SETTINGS-01-REGISTER-SYNC + WP-DEBT-05-STATUS-SYNC — Docs (**this recording**)
2. WP-CHECKIN-01 — Code — **CLOSED** (Mandate CHAIN-2; suite 1930 passed)
3. WP-SYS-01 — Code — **CLOSED** (Mandate CHAIN-5; suite 1932 passed)
4. WP-DORM-UI-READ — Code
5. WP-REQ-04 (form sites) — Code

### Hard-Stop Conditions (Active)

1. HD-02 Lottery unfreeze requested inside WP-SYS-01 → STOP
2. Any WP other than WP-REQ-04 touches PersonalRequestFormPage / listSites → STOP
3. Schema change on settings or assignment tables without new unfreeze → STOP
4. New Settings Eloquent model contrary to R1 → STOP
5. Proven Gate/ForbiddenImports failure on Policy principal → REOPEN R5
6. WP-CHECKIN-01 expands beyond Action extraction → STOP


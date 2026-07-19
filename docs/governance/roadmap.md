# Program Roadmap — Canonical Phase Sequence

**Purpose:** This document canonicalizes the program-level phase sequence (A→G) and F3 dormitory-admin UI productization catalog. It is NOT an execution/feature tracker.

**Created:** 1405/04/24 | **Owner:** Lead | **Source:** DG-02

---

## Scope & Provenance

**Scope:** program-level phase sequence only. Feature-specific execution and remediation status (e.g., dormitory-admin-ui) is tracked in the respective feature artifacts, not here.

**Provenance:** some phase labels are reconstructed from governance evidence (decision records, ledgers, test baselines) rather than a single original planning document.

---

## Phase Sequence

| Phase | Title/Scope | Status |
|-------|-------------|--------|
| A | Domain Foundation (entity inventory, relationship matrix, actor/role taxonomy) | COMPLETE |
| B | Ownership & Authority (ownership matrix, authority gap register, human decision pack) | COMPLETE |
| C | Spec Gap Closure (dependency tables, canonical glossary, completed boundary specs) | COMPLETE |
| D | PEP Rebuild (PEP inventory, decomposition, readiness screening, canonical policy-level records) | COMPLETE |
| E | Exit Criteria / Domain Stabilization Exit | COMPLETE |
| F1 | Auth: employee-records | COMPLETE |
| F2 | Auth: Employee UI/Auth broad scope | **PASS** (boundary W-01…W-08 CLOSED — `docs/features/employee-auth-ui/work-breakdown.md:14`; F-W07-04 Wave 1 **COMPLETED** — `governance-log.md` **F-W07-04-D2** / **F-W07-04-D3**; prior PARTIAL solely due to F-W07-04 carry superseded). Spec04 Auth / BO-parked packet still not authorized from F2 closeout. (synced PA-01, 2026-07-19) |
| G | UI: dormitory-admin-ui | **PARTIAL** (BL-B1-01 **RESOLVED** on branch `369a106`; L9 checklist refreshed 2026-07-18 — `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md`; merge into `011-reporting-projections` pending Lead). |
| F3 | Dormitory Admin UI productization | **Sprint A = CLOSED** \| **Sprint B = CLOSED** (Lead Final Closure 1405/04/28 \| 2026-07-19). Catalog: **UI-M1, UI-M2, UI-A1**. DGAP-11 CLOSED. See § F3 Sprint B Backlog. |

### F3 — Catalog (Sprint A)

**Entry:** AUTHORIZED — Option A (scope corrected), Lead, 2026-07-15.  
**Supersedes:** undelivered UI-1…UI-7 / L6.1–L6.7 claim under prior DGAP-11 Option A wording.  
**Artifact authority:** this section of `docs/governance/roadmap.md` (DGAP-11 closure evidence).

| ID | Title / Scope | Status | Dependency / Evidence |
|----|---------------|--------|------------------------|
| **UI-M1** | Manager Dashboard — wire data (`/dormitory-admin`, manager role) | **OBSERVED-L8-COMPLETE — L9 merge pending Lead** | BL-B1-01 **RESOLVED** (`369a106`). L3: `docs/features/ui-m1/l3-spec.md`; L8: `docs/features/ui-m1/l8-closeout.md`. Suite 1888/0 (`storage/logs/w1-close-test.log`). Shell: `routes/web.php`; `DormitoryManagerDashboard.php`. |
| **UI-M2** | Unit Manager Dashboard | **L6+ AUTHORIZED (SB-D7) — Lock DONE (WP-UI-M2-01)** | L3: `docs/features/ui-m2/l3-spec.md` (SB-D6); Lock: `docs/features/ui-m2/implementation-lock.md`; auth_gate=`dormitory-unit-manager`; SHA UNVERIFIED (merge-agnostic) |
| **UI-A1** | Auth layout / identity guard integration (`IdentityRoleGuard`, dual-guard) | **COMPLETE — L8 closeout delivered** (2026-07-15). Evidence: `docs/features/ui-a1/l7-verification.md` PASS; `docs/features/ui-a1/l8-closeout.md`. | Shell: `resources/views/components/layouts/dormitory-admin.blade.php`; logout: `routes/web.php`; tests: `UiA1AuthLayoutTest.php`. |

**Out of this catalog (do not conflate):** Assignment schema / assignment UI is **not** UI-A1. If required later, propose separately as **UI-A2** (Lead note, 2026-07-15).

**Sprint A first work item posture:** Catalog defined; implementation / L3 per item requires separate Lead authorization (docs-only close of DGAP-11 does **not** authorize PHP/migrations).

### F3 — Sprint B Backlog (Lead CONFIRM 1405/04/27 \| 2026-07-18)

| Priority | ID | Title / Scope | Status |
|----------|-----|---------------|--------|
| 1 | **RESIDUAL-01** | بررسی شناسه runtime: `ROLE_DEPT_MGR` (`IdentityRoleSeeder`) | OPEN — Sprint B |
| 2 | **RESIDUAL-02** | بررسی نام کلاس دامنه: `PendingDepartmentManagerState` | OPEN — Sprint B |
| 3 | **UI-M2** | Unit Manager Dashboard | **L6+ AUTHORIZED (SB-D7) — Lock DONE (WP-UI-M2-01)** |
| 4 | **F-W07-04** | Stage-1 approver console (`stage1-approver-console`) — Wave 1+2 list/filter | **Wave 1 ✅ D3**; **Wave 2 ✅ DONE** (**SB-D9** / WP-RQ-W2-01); SHA UNVERIFIED (merge-agnostic) |

> Sprint B backlog is sequencing guidance only. Implementation of each ID requires separate Lead authorization.

---

## F3 Execution Waves (AUTH-013 — sequencing only)

> **Authority:** Lead AUTH-013 (2026-07-16). Sequencing record only — does not authorize code, merge, or spec content.
> **Logic:** Decisions recorded → merge → doc batch → new work. Doc rewrites before merge would stale again post-merge.

| Wave | Content | Gaps Resolved | Gate |
|------|---------|---------------|------|
| **W0 — Decision Recording** (docs-only) | Record HD-01…07 in `open-decisions.md` + disposition annotations in `spec-catalog.md` | GAP-GOV-01 (waiver path), GAP-SPEC-06/11 (→ ACCEPTED-EXCEPTION), GAP-UI-M2-01 (→ scheduled W3) | **AUTH-013** (this prompt) |
| **W1 — L9 Merge** | Refresh checklist → disposition unstaged files → union `bootstrap/app.php` verified → readiness for Lead merge | GAP-DOC-04, GAP-GOV-01, GAP-GOV-03 closed in working tree; GAP-GOV-02 pending Lead merge SHA | **AUTH-011 Band 2** — **COMPLETE** (verified 1405/04/27 \| 2026-07-18); merge commit pending Lead |
| **W2 — Hygiene & Doc-lag Batch** | N-11 (S-4 grep CI + dedicated test DB note) + all doc-lags: GAP-DOC-01/02/03, GAP-UI-M1-01, spec02/spec05 closeout (HD-07A), GAP-N11-01 | All doc-lag tags in `project-state.md` §8 | **AUTH-011 Band 3** — **ACTIVE** (hygiene execution 1405/04/27 \| 2026-07-18) |
| **W3 — UI-M2 L3** | `docs/features/ui-m2/l3-spec.md` per UI-M1 pattern | GAP-UI-M2-01 executed | **AUTH-011 Band 4** |
| **P — Parked Lane** (no work) | DGAP-03 (OPEN/PARKED), DGAP-14 (DECIDED), SGAP-05, SGAP-07, spec06/11 authority debt, Workflow | — each with explicit re-entry trigger in `open-decisions.md`. **Removed (stale):** DGAP-08 RESOLVED; DGAP-05/06 DECIDED; F-W07-04 Wave 1 COMPLETED (D3) — not parked. | Future Lead decision |

**W2 acceptance criterion:** After W2, `project-state.md` §8 contains only decision-pending rows with explicit triggers — **zero open doc-lag rows**.

**Current wave:** **Sprint B CLOSED** (Lead Final Closure 1405/04/28 \| 2026-07-19). Sprint A **CLOSED**. UI-M2 Lock **DONE** (WP-UI-M2-01). F-W07-04 Wave 1+2 **DONE** (D3 / WP-RQ-W2-01). WP-DOC-SYNC-01 **DONE** (SB-D10). WP-GOV-SHA-01/01b **CANCELLED**. Commit SHA **UNVERIFIED** (merge-agnostic). G7 requester-name filter remains **DEFERRED** (Wave-3 candidate). RESIDUAL-01/02 addressed under SB-D1/D2. GAP-GOV-02 tip SHA pending Lead (unchanged).

---

## Cross-References

| Ref | Path |
|-----|------|
| DG-01, DG-02, DG-03 | `docs/governance/open-decisions.md` |
| BL-B1-01 | `docs/governance/risk-register.md` |
| Terminology | `docs/governance/glossary.md` |
| Phase G L9 merge checklist (source of truth for merge readiness) | `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md` (reconciled 2026-07-15, ref: DGAP-12) |
| F2 work breakdown / W-07 findings | `docs/features/employee-auth-ui/work-breakdown.md`, `docs/features/employee-auth-ui/w07-security-review-report.md` |
| DGAP-11 / DGAP-12 | `docs/governance/open-decisions.md` |
| AUTH-013 waves | `docs/governance/roadmap.md` § F3 Execution Waves |
| Project state snapshot | `docs/governance/project-state.md` |

---

## Changelog

| تاریخ | تغییر | توسط |
|-------|-------|------|
| 1405/04/28 (2026/07/19) | **Sprint B CLOSED** (Lead Final Closure): WP-RQ-W2-01 / WP-UI-M2-01 / WP-DOC-SYNC-01 **DONE**; SB-D10 Recorded; WP-GOV-SHA-01/01b **CANCELLED**; SHA UNVERIFIED (merge-agnostic). | Agent (Lead Sprint B Closure) |
| 1405/04/28 (2026/07/19) | **SB-D7 / WP-UI-M2-01:** UI-M2 L6+ AUTHORIZED; Implementation Lock COMPLETED (verify/align-to-L3). auth_gate=`dormitory-unit-manager`. | Agent (Lead WP-UI-M2-01) |
| 1405/04/28 (2026/07/19) | **SB-D6:** UI-M2 catalog + Sprint B backlog → **L3 ACCEPTED (SB-D6)**; L6+ NOT authorized. WP-01 rev-4. | Agent (Lead WP-01 rev-4) |
| 1405/04/28 (2026/07/19) | **PA-01 DOC sync:** F2 → **PASS** (**F-W07-04-D2**); F-W07-04 Sprint B row → Wave 1 **COMPLETED** (**F-W07-04-D3**). No new decision. | Agent (PA-01) |
| 1405/04/27 (2026/07/18) | **SPRINT-A CLOSE / SPRINT-B ENTRY (Lead CONFIRM):** Sprint A = CLOSED; Sprint B = ACTIVE. Backlog: RESIDUAL-01, RESIDUAL-02, UI-M2, F-W07-04. | Agent (Lead CONFIRM) |
| 1405/04/27 (2026/07/18) | **Sprint A CLOSED / Sprint B ACTIVE:** DGAP-14 F1–F3 decision block applied (NO-COMMIT). Priorities: UI-M2, F-W07-04, residuals. | Agent (Lead RATIFY F1-F3) |
| 1405/04/27 (2026/07/18) | **W2 hygiene (merge-independent):** Parked Lane membership synced to open-decisions (drop stale DGAP-08/05/06). Merge SHA **UNVERIFIED**. Report: `docs/governance/w2-hygiene-sync-report.md`. | Agent (docs sync) |
| 1405/04/27 (2026/07/18) | **IMPL-PERMIT-02:** Stage-1 approver identity snapshot at personal-request **create** time (org-chart dept manager); unit test PASS. Tracker: `project-state.md`. | Agent (IMPL-PERMIT-02) |
| 1405/04/27 (2026/07/18) | **W2 hygiene:** F3 catalog status sync (UI-M1 L8/L9-pending; UI-M2 READY FOR L3; Phase G L9 checklist refreshed). Doc-lag HIGH batch. | Agent (Lead W2 auth) |
| 1405/04/27 (2026/07/18) | **W1-CLOSE:** Union `bootstrap/app.php` verified PASS; suite 1888/0; W1 COMPLETE (merge pending Lead); W2 ACTIVE — entry **W2-start**. Facts only. | Agent (Lead W1-CLOSE auth) |
| 1405/04/25 (2026/07/16) | **AUTH-013 W0:** Four-wave + Parked Lane sequencing recorded; W0 complete / W1 next. HD-01…07 decisions cite AUTH-013. | Agent (Lead AUTH-013) |
| 1405/04/25 (2026/07/16) | **BL-B1-01 / RM-BL-B1:** UI-M1/UI-M2 → UNBLOCKED — READY FOR L3/L6; assignment schema restored; DGAP-09 RE-FROZEN | Agent (Lead AUTHORIZE ALL) |
| 1405/04/24 (2026/07/15) | **UI-A1 L8 closeout:** status → COMPLETE — READY FOR COMMIT AND CLOSURE REVIEW (`docs/features/ui-a1/l8-closeout.md`); L7 PASS | Agent (L8 docs) |
| 1405/04/24 (2026/07/15) | **F3 Sprint A entry / DGAP-11 close:** Canonical catalog UI-M1, UI-M2, UI-A1 (PENDING); supersedes UI-1…UI-7 claim; F3 ACTIVE — Sprint A | Agent (Lead AUTHORIZED Option A) |
| 1405/04/24 (2026/07/15) | **DGAP-12 reconciliation:** F2 → PARTIAL (F-W07-04 open); G → PARTIAL (BL-B1-01 + L9 NOT READY); F3 stub PLANNED (DGAP-11 reopened — UI-1…UI-7 never delivered); L9 checklist cross-ref added | Agent (Lead-supervised docs) |
| 1405/04/24 | F2 `employee-auth-ui` W-01…W-08 CLOSED (Lead W-07/W-08 acceptance); UI entry not authorized | Agent (Lead closeout) |
| 1405/04/24 | DOC hygiene: W-02 CLOSED (DGAP-07 A); W-07 IN EXECUTION (scope YES+AMEND) | Agent (Lead-authorized batch) |
| 1405/04/24 | Option B reconciliation: F2 ACTIVE notes W-01/W-06 impl accepted; W-07/W-08 remain open | Lead |
| 1405/04/24 | L3 spec authored for W-01/W-03; W-03 closed without action (DG-03/Option A) | Lead |
| 1405/04/24 | Initial roadmap created; Phase F split into F1/F2 per DG-02; program-level scope declared | Lead |

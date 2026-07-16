# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Does NOT grant implementation authorization.
> Canonical sources override this file on conflict.
>
> | Domain | Canonical source |
> |--------|------------------|
> | Phases / F3 catalog | `docs/governance/roadmap.md` |
> | Decision gates | `docs/governance/open-decisions.md` |
> | Risks / BL items | `docs/governance/risk-register.md` |
> | Bounded contexts / entities | `.specify/docs/context-map.md` |
> | Spec roadmap | `.specify/docs/spec-catalog.md` |
> | L9 merge checklist (Phase G) | `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md` |

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/25 | 2026-07-16 | Sprint: F3-Sprint-A | Session: AUTH-011 Band 1 bootstrap_

**Authority note:** Lead `AUTH-011` (2026-07-16) — Bands 1–4 in sequence.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Bootstrap | `docs/governance/project-state.md` | absent → created | AUTH-011 Band 1 |
| Gap register | §8 Gap Registry | absent → populated | cross-doc audit |
| Cursor rule | `.cursor/rules/project-state-update.mdc` | absent → created | AUTH-011 Band 1 |

---

## 1. Layers & Entities (active F3 scope)

> Full inventory: `.specify/docs/context-map.md`

| Entity | Module | Layer | Table / Model | Cross-module ref | Notes |
|--------|--------|-------|---------------|------------------|-------|
| User | Auth | Presentation | `users` | — | Legacy `web` guard principal |
| UserModel | Identity | Infrastructure | `identity_users` | `employees.identity_id` (UUID, no FK) | CD-012; dual-guard |
| Role / Permission | Identity | Infrastructure | Spatie tables | guard `identity` + `web` | SEC-G-01 mitigated |
| Employee | Employee | Domain/Infra | `employees` | — | spec03 |
| Dormitory hierarchy | Dormitory | Infrastructure | `dormitories`, buildings, floors, rooms, beds | — | spec04 |
| Manager assignment | DormitoryAdmin | Infrastructure | `dormitory_manager_assignments` | FK `user_id` → `identity_users` | DGAP-09 RE-FROZEN |
| Unit-manager assignment | DormitoryAdmin | Infrastructure | `dormitory_unit_manager_assignments` | FK `user_id` → `identity_users` | UI-M2 scope |
| IdentityRoleGuard | Shared kernel | Application | — | consumed by DormitoryAdmin + Auth UI | DG-03 RESOLVED → `app/Shared/Auth/` |

---

## 2. Active Feature Map

> Program phases: `docs/governance/roadmap.md`

| ID | Title | Status | Stage | Blocker | Canonical artifact |
|----|-------|--------|-------|---------|-------------------|
| UI-M1 | Manager Dashboard — wire data | OBSERVED-L8-COMPLETE | L9-pending | — | `docs/features/ui-m1/l3-spec.md`; branch `release/f2-employee-auth-ui-l9` @ `a42dc99` |
| UI-M2 | Unit-Manager Dashboard — wire data | IN-PROGRESS | L3-not-started | — | component exists; no `l3-spec.md` |
| UI-A1 | Auth layout / dual-guard | OBSERVED-COMPLETE | L8-done | — | `docs/features/ui-a1/l8-closeout.md` |
| RM-BL-B1 | Assignment schema restore | RESOLVED | committed-on-branch | — | `369a106` |

**Status vocabulary:** `NOT-STARTED` | `IN-PROGRESS` | `BLOCKED` | `READY-FOR-L{n}` | `OBSERVED-L8-COMPLETE` | `MERGED` | `CLOSED` | `DONE`

---

## 3. Spec Registry (in-flight / F3)

> Full catalog: `.specify/docs/spec-catalog.md`

| Spec | Feature / Phase | Path | Status | Depends on |
|------|-----------------|------|--------|------------|
| ui-m1 | UI-M1 | `docs/features/ui-m1/l3-spec.md` | APPROVED (L3) | BL-B1-01, UI-A1 |
| ui-m2 | UI-M2 | — | NOT-STARTED | BL-B1-01 (sibling) |
| ui-a1 | UI-A1 | `docs/features/ui-a1/l8-closeout.md` | DELIVERED | F2 auth |
| spec04 | Dormitory | `specs/004-dormitory-context/` | DELIVERED | — |
| spec07 | Allocation + CheckIn | `specs/007-allocation-checkin/` | DELIVERED | spec04, spec05 |

---

## 4. Work Items (open / recent)

> Full risk register: `docs/governance/risk-register.md`

| WI ID | Parent | Type | Status | Owner | Notes |
|-------|--------|------|--------|-------|-------|
| BL-B1-01 | Phase G | remediation | RESOLVED | Tech Lead | on branch; roadmap still says pending commit |
| F-W07-04 | Phase F2 | security | OPEN | Lead | carried to F3 Sprint A |
| N-11 | UI-M1-COV | hygiene | OPEN | Cursor | S-4 grep CI + doc lag (AUTH-011 Band 3) |
| BL-01..04 | Phase G L9 | backlog | OPEN | Lead | E2 tracker incomplete per L9 checklist |

---

## 5. Open Decisions (mirror — summary only)

> **Do not edit decisions here.** Canonical: `docs/governance/open-decisions.md`

- [ ] **DGAP-08:** OPEN / PARKED — root blocker for DGAP-03/05/06 (Spec04 Auth)
- [ ] **DGAP-03/05/06:** OPEN / PARKED — Spec04 Auth packet
- [ ] **DGAP-09:** RE-FROZEN — no assignment schema without new unfreeze
- [ ] **SGAP-07:** BACKLOG + PARKED — Spec04 PENDING_RESIDUAL
- [x] **UI-M1-COV:** ACCEPTED — S-2/S-4/S-5 residual; N-11 deferred
- [x] **DGAP-11:** CLOSED — F3 catalog UI-M1/M2/A1

---

## 6. Lifecycle Matrix (UI delivery track)

> L-stages = UI delivery labels. Program lifecycle: `.specify/governance/program-architecture-lifecycle.md`

| Feature | L0 | L1 | L2 | L3 | L5 | L6 | L7 | L8 | L9 |
|---------|----|----|----|----|----|----|----|----|-----|
| UI-M1 | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ |
| UI-M2 | ✅ | ✅ | ✅ | — | — | — | — | — | — |
| UI-A1 | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ |

**Cell symbols:** `✅` done | `⏳` in-progress | `❌` blocked | `—` not started

---

## 7. Next Step (rewritten every prompt)

**Action:** Complete AUTH-011 Band 2 — analyze L9 merge readiness for UI-M1, run full test suite (exit 0), execute merge to `origin/011-reporting-projections` if green.  
**Owner:** Cursor (execution) → Lead (merge sign-off if checklist gaps remain)  
**Gate:** AUTH-011 Band 2 (Lead, 2026-07-16)  
**Target files:** `docs/governance/project-state.md`; merge `release/f2-employee-auth-ui-l9` → `011-reporting-projections`  
**Done when:** Suite exit 0; merge commit SHA recorded; UI-M1 → MERGED/CLOSED in §2 and §6  
**Blocker:** L9 checklist D1/E2 stale; unstaged `composer.json` + `tests.yml` on working tree

**Suggested user prompt:**
> @docs/governance/project-state.md — گزارش شواهد Band 2 AUTH-011

---

## 8. Gap Registry (spec / governance / doc)

| Gap ID | Cluster | Description | Status | Canonical ref |
|--------|---------|-------------|--------|---------------|
| GAP-PS-01 | Governance | `project-state.md` absent until AUTH-011 Band 1 | **CLOSING** | this file |
| GAP-DOC-01 | Doc lag | `roadmap.md` UI-M1 still "READY FOR L3/L6" after L8 closeout | OPEN | `docs/governance/roadmap.md:39` |
| GAP-DOC-02 | Doc lag | `risk-register.md` BL-B1-01 "pending Lead commit" while on branch | OPEN | `docs/governance/risk-register.md:13` |
| GAP-DOC-03 | Doc lag | `ui-a1/l8-closeout.md` lists UI-M1/M2 PENDING — superseded | OPEN | `docs/features/ui-a1/l8-closeout.md:107` |
| GAP-DOC-04 | Doc lag | L9 checklist assessed 2026-07-15; branch evolved (BL-B1-01, UI-M1 L8) | OPEN | `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md` |
| GAP-GOV-01 | L9 scope | Checklist A1 FAIL — PR includes Identity/auth beyond dormitory-admin-ui | OPEN | L9 §A1 |
| GAP-GOV-02 | L9 ownership | Checklist A2 FAIL — `IdentityRoleGuard` in `App\Support\Auth` | OBSERVED-SUPERSEDED | DG-03 RESOLVED → Shared Kernel |
| GAP-GOV-03 | L9 closure | E2 — BL-01..04 no dedicated tracker/issues | OPEN | L9 §E2 |
| GAP-GOV-04 | Domain | DGAP-08 BO designation blocks Spec04 Auth | OPEN / PARKED | `open-decisions.md` |
| GAP-GOV-05 | Domain | DGAP-03/05/06 parked (org/approver binding) | OPEN / PARKED | `open-decisions.md` |
| GAP-GOV-06 | Spec | SGAP-07 Spec04 PENDING_RESIDUAL | BACKLOG | `open-decisions.md` |
| GAP-GOV-07 | Security | F-W07-04 carried forward from F2 | OPEN | `risk-register.md` |
| GAP-N11-01 | Hygiene | S-4 raw-query grep CI deferred (UI-M1-COV) | DEFERRED → Band 3 | `open-decisions.md` UI-M1-COV |
| GAP-UI-M2-01 | Feature | No `docs/features/ui-m2/l3-spec.md` | OPEN | AUTH-011 Band 4 |
| GAP-UI-M1-01 | Feature | No dedicated `ui-m1/l8-closeout.md` artifact (closeout in commit + open-decisions) | OPEN | commit `a42dc99` |

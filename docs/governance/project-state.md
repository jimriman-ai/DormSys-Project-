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
_Last updated: 1405/04/25 | 2026-07-16 | Sprint: F3-Sprint-A | Session: AUTH-011 Bands 1–2 evidence_

**Authority note:** Lead `AUTH-011` (2026-07-16) — Bands 1–4 in sequence.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Band 1 rule | `.cursor/rules/project-state-update.mdc` | absent (commit msg only) → created on disk | this session |
| Band 1 restore | `docs/governance/project-state.md` | deleted in WD → restored | `git restore` from `1ce4087` |
| Band 2 analysis | UI-M1 L9 merge readiness | pending → analyzed | merge-base `5a13365`; FF eligible; no conflicts |
| Band 2 tests | Full suite | run 1 exit 2 (DB env) → run 2 in progress | `DB_HOST=pgsql REDIS_HOST=redis` |
| Band 2 merge | UI-M1 → `011-reporting-projections` | pending → **STOP** (await suite exit 0) | AUTH-011 precondition |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle — UI-M1 L9 merge** (AUTH-011 Band 2) — suite verification in progress; merge blocked until exit 0.

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
| UI-M1 | Manager Dashboard — wire data | OBSERVED-L8-COMPLETE | L9-pending | suite exit 0 | `docs/features/ui-m1/l3-spec.md`; `a42dc99` |
| UI-M2 | Unit-Manager Dashboard — wire data | IN-PROGRESS | L3-not-started | Band 4 gate | component exists; no `l3-spec.md` |
| UI-A1 | Auth layout / dual-guard | OBSERVED-COMPLETE | L8-done | — | `docs/features/ui-a1/l8-closeout.md` |
| RM-BL-B1 | Assignment schema restore | RESOLVED | on branch | — | `369a106` |

---

## 3. Spec Registry (in-flight / F3)

> Full catalog: `.specify/docs/spec-catalog.md`

| Spec | Feature / Phase | Path | Status | Depends on |
|------|-----------------|------|--------|------------|
| ui-m1 | UI-M1 | `docs/features/ui-m1/l3-spec.md` | APPROVED (L3) | BL-B1-01, UI-A1 |
| ui-m2 | UI-M2 | — | NOT-STARTED | BL-B1-01 (sibling) |
| ui-a1 | UI-A1 | `docs/features/ui-a1/l8-closeout.md` | DELIVERED | F2 auth |

---

## 4. Work Items (open / recent)

| WI ID | Parent | Type | Status | Owner | Notes |
|-------|--------|------|--------|-------|-------|
| BL-B1-01 | Phase G | remediation | RESOLVED | Tech Lead | on branch |
| F-W07-04 | Phase F2 | security | OPEN | Lead | carried to F3 Sprint A |
| N-11 | UI-M1-COV | hygiene | OPEN | Cursor | AUTH-011 Band 3 |
| AUTH-011-B2 | UI-M1 | L9 merge | IN-PROGRESS | Cursor | awaiting suite |

---

## 5. Open Decisions (mirror — summary only)

> Canonical: `docs/governance/open-decisions.md`

- [ ] **DGAP-08:** OPEN / PARKED
- [ ] **DGAP-03/05/06:** OPEN / PARKED
- [ ] **DGAP-09:** RE-FROZEN
- [x] **UI-M1-COV:** ACCEPTED — S-2/S-4/S-5 residual
- [x] **DGAP-11:** CLOSED — F3 catalog

---

## 6. Lifecycle Matrix (UI delivery track)

| Feature | L0 | L1 | L2 | L3 | L5 | L6 | L7 | L8 | L9 |
|---------|----|----|----|----|----|----|----|----|-----|
| UI-M1 | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ |
| UI-M2 | ✅ | ✅ | ✅ | — | — | — | — | — | — |
| UI-A1 | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ |

---

## 7. Next Step (rewritten every prompt)

**Action:** Await AUTH-011 Band 2 full-suite result (`DB_HOST=pgsql`); if exit 0 → fast-forward merge `release/f2-employee-auth-ui-l9` into `011-reporting-projections` (docs-only delta in merge commit message); if fail → STOP per AUTH-011.  
**Owner:** Cursor  
**Gate:** AUTH-011 Band 2 — suite exit 0 mandatory  
**Target files:** `storage/logs/auth011-band2-junit.xml`; `011-reporting-projections` branch  
**Done when:** Suite exit 0 recorded; merge SHA or STOP report to Lead  
**Blocker:** Run 1 failed exit 2 — `SQLSTATE[08006]` connection refused (`phpunit.xml` `DB_HOST=127.0.0.1` inside Sail mid-run)

**Suggested user prompt:**
> @docs/governance/project-state.md — نتیجه suite Band 2 و ادامه merge یا STOP

---

## 8. Gap Registry (spec / governance / doc)

| Gap ID | Cluster | Description | Status | Canonical ref |
|--------|---------|-------------|--------|---------------|
| GAP-PS-01 | Governance | `project-state.md` bootstrap | **CLOSED** | commit `1ce4087` |
| GAP-PS-02 | Governance | Cursor rule referenced but not in `1ce4087` tree | **CLOSING** | `.cursor/rules/project-state-update.mdc` |
| GAP-DOC-01 | Doc lag | `roadmap.md` UI-M1 status stale post-L8 | OPEN | `roadmap.md:39` |
| GAP-DOC-02 | Doc lag | `risk-register.md` BL-B1-01 pending commit wording | OPEN | `risk-register.md:13` |
| GAP-DOC-03 | Doc lag | `ui-a1/l8-closeout.md` UI-M1/M2 PENDING superseded | OPEN | `ui-a1/l8-closeout.md` |
| GAP-DOC-04 | Doc lag | L9 checklist stale (2026-07-15) | OPEN | `l9-merge-checklist-dormitory-admin-ui.md` |
| GAP-GOV-01 | L9 scope | Checklist A1 broad file set | OPEN | Lead AUTH-011 supersedes for UI-M1 |
| GAP-GOV-02 | L9 ownership | IdentityRoleGuard namespace | OBSERVED-SUPERSEDED | DG-03 Shared Kernel |
| GAP-GOV-03 | L9 closure | BL-01..04 tracker | OPEN | L9 §E2 |
| GAP-GOV-04 | Domain | DGAP-08 BO designation | OPEN / PARKED | `open-decisions.md` |
| GAP-GOV-05 | Domain | DGAP-03/05/06 | OPEN / PARKED | `open-decisions.md` |
| GAP-GOV-06 | Spec | SGAP-07 PENDING_RESIDUAL | BACKLOG | `open-decisions.md` |
| GAP-GOV-07 | Security | F-W07-04 | OPEN | `risk-register.md` |
| GAP-N11-01 | Hygiene | S-4 grep CI | DEFERRED → Band 3 | UI-M1-COV |
| GAP-UI-M2-01 | Feature | No `ui-m2/l3-spec.md` | OPEN | Band 4 |
| GAP-UI-M1-01 | Feature | No `ui-m1/l8-closeout.md` file | OPEN | `a42dc99` |
| GAP-TEST-01 | CI/env | Sail runs need `DB_HOST=pgsql` override vs `phpunit.xml` 127.0.0.1 | OPEN | Band 2 evidence |

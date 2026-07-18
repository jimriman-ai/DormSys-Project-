# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/27 | 2026-07-18 | Sprint: F3 Sprint B | Session: Phase 3 Controlled Execution SB-D1..D5 (WT-only; pending Lead commit)_

**Authority note:** SB-D1..D5 = A (closed). Phase 3 execution complete in working tree — Sprint B **closure** remains Lead action.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| SB-D1=A | Stage1ApproverConsoleActionsTest + IdentityRoleSeeder | Negative helper → employee-only; ROLE_DEPT_MGR `@deprecated` alias docblock | `tests/.../Stage1ApproverConsoleActionsTest.php:75–145`; `database/seeders/IdentityRoleSeeder.php:33–39` |
| SB-D2=A | Spec05 | + OA-05-00 dual-model note (stage ≠ auth role) | `specs/005-request-management/spec.md:208–216` |
| SB-D3=A | UI-M2 L3 | **authored** `docs/features/ui-m2/l3-spec.md` (STOP not hit) | new file; route evidence `routes/web.php:32–34` |
| SB-D4=A | F-W07-04 | candidate catalog authored | `docs/features/f-w07-04-candidate-catalog.md` |
| SB-D5=A | (no code) | Shared `dormitory-manager` accepted — verification report only | `governance-log.md` SB-D5 row (Lead); code: `IdentityRoleGuard.php:20`, `routes/web.php:28–30,:43–44` |
| Tests | Sail full suite | **1895 passed** (5454 assertions) | `docker exec … php artisan test` |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision — Sprint B Lead closure** (L3 review for `ui-m2/l3-spec.md`; F-W07-04 slug-naming deferred; WT commit)

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| RESIDUAL-01 | Runtime id `ROLE_DEPT_MGR` | **DONE** (SB-D1=A WT) | Sprint B Phase 3 | Lead commit |
| RESIDUAL-02 | Dual-model stage vocab | **DONE** (SB-D2=A docs WT) | Sprint B Phase 3 | Lead commit |
| UI-M2 | Unit-Manager Dashboard L3 | **L3 authored — awaiting Lead review** | Sprint B Phase 3 | L3 review gate |
| F-W07-04 | Next UI slug / product gate | **CATALOG ONLY** (SB-D4=A) | Sprint B Phase 3 | Future human slug decision; F2 PARTIAL |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| UI-M2 | ⏳ (authored, review pending) | — | — | — | SB-D3=A; L6+ out of scope |
| UI-M1 | ✅ | ✅ | ✅ | ⏳ | unchanged this prompt |

---

## 7. Next Step

**Action:** Lead Sprint B closure — review `docs/features/ui-m2/l3-spec.md`; decide when to name F-W07-04 slug; commit Phase 3 WT.  
**Owner:** Lead  
**Gate:** L3 review (UI-M2); product decision (F-W07-04)  
**Target files:** `docs/features/ui-m2/l3-spec.md`; `docs/features/f-w07-04-candidate-catalog.md`; Phase 3 code/docs listed in §0  
**Done when:** Lead accepts L3 and/or closes Sprint B explicitly  
**Blocker:** none for Phase 3 execution (complete)  

**Suggested user prompt:**
> Review `docs/features/ui-m2/l3-spec.md` (L3 gate). Commit Sprint B Phase 3 WT when ready. Name or park F-W07-04 separately.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| Sprint A | CLOSED | Lead CONFIRM |
| Sprint B Phase 3 | **EXECUTED (WT)** | SB-D1..D5=A; closure = Lead |
| RESIDUAL-01 | RESOLVED (WT) | SB-D1=A |
| RESIDUAL-02 | RESOLVED (WT docs) | SB-D2=A dual-model |
| UI-M2 L3 | CLOSING | authored; Lead review |
| F-W07-04 | OPEN (cataloged) | SB-D4=A; slug NOT named |
| SB-D5 | ACCEPTED-AS-IS | no execute payload |
| GAP-GOV-02 | OPEN | tip SHA pending Lead commit |
| DGAP-14 | DECIDED | residuals addressed in Sprint B |

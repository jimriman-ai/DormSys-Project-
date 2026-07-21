# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/30 | 2026-07-21 | Session: T3 G-REQ-01 delivered — 22 passed, uncommitted_

**Authority note:** OBSERVED. G-REQ-01 `RequestTransitionGuardTest` added (test-only). **22 passed**. Awaiting Lead for commit / next guardrail (G-REQ-02).

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| G-REQ-01 | `RequestTransitionGuardTest.php` | created — illegal OA-05-03 matrix | tests/ only |
| Verify | G-REQ-01 | **22 passed** (44 assertions) | compose exec |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle** — T3 Guardrails: **G-REQ-01 done**; next **G-REQ-02** on Lead order

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| T3 next | G-REQ-02 / commit G-REQ-01 |
| HD-02/HD-03/DBT-3 | Frozen |

Canonical: `docs/governance/open-decisions.md`

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| T2 | ✅ COMPLETE |
| T3 G-REQ-01 | ✅ VERIFIED (uncommitted) |
| T3 remaining | G-REQ-02… G-MUT-01 pending |

---

## 7. Next Step

**Action:** Lead commit G-REQ-01 or proceed G-REQ-02.  
**Owner:** Lead → Agent  
**Gate:** Lead order  
**Done when:** G-REQ-01 committed / G-REQ-02 scoped  
**Blocker:** none  
**Suggested user prompt:**
> Commit G-REQ-01 then implement G-REQ-02

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| G-REQ-01 | VERIFIED | 22 illegal edges → InvalidRequestTransitionException |

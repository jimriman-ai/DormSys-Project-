# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/30 | 2026-07-21 | Session: B2a RequestReadContractTest VERIFIED 6 passed — uncommitted_

**Authority note:** OBSERVED. Cluster B2a test-only Stage-1 bind applied; **6 passed**. Awaiting Lead commit order.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Fix | `RequestReadContractTest.php` | Stage-1 bind + forgetInstance Assign/Create | tests/ only |
| Verify | RequestReadContractTest | **6 passed** (was FAIL in snapshot) | compose exec |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle** — B2a **VERIFIED**; pending Lead commit then next triage cluster

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| Commit B2a | Lead order for commit message |
| HD-02/HD-03/DBT-3 | Frozen |

Canonical: `docs/governance/open-decisions.md`

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| T2-4 FilterTest | ✅ COMPLETED (`f8cec6b`) |
| B2a RequestReadContractTest | ✅ **VERIFIED** (uncommitted) |

---

## 7. Next Step

**Action:** Lead commit B2a; then next non-frozen triage cluster.  
**Owner:** Lead → Agent  
**Gate:** Lead commit approval  
**Done when:** committed  
**Blocker:** none  
**Suggested user prompt:**
> Commit B2a RequestReadContractTest then continue T2 triage

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| B2a RequestReadContract Stage-1 | VERIFIED | bind + forgetInstance; 6 passed |

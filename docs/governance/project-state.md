# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/30 | 2026-07-21 | Session: T3 G-REQ-02 VERIFIED — Architecture 3 passed; Request suite 1 HD-02 fail_

**Authority note:** OBSERVED. G-REQ-02 guard green. Markers added to 5 DBT-3 whitelist files. Request Feature regression: **161 passed, 1 failed** (`LotteryRegistrationRequestTest` — HD-02 / Stage-1 snapshot). Uncommitted.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Markers | 5 Request api-guard files | `@allowed-api-guard: DBT-3` | tests/ |
| G-REQ-02 | `RequestTestApiGuardTest.php` | created — **3 passed** | Architecture |
| Regression | Request Feature | 161 passed / 1 fail LotteryRegistration | HD-02 |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle** — T3 G-REQ-02 done; Lead: commit + decide LotteryRegistration fail (out of scope)

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| Commit G-REQ-02 | Lead order |
| LotteryRegistrationRequestTest | HD-02 — not fixed in T3 |
| HD-02/HD-03/DBT-3 | Frozen |

Canonical: `docs/governance/open-decisions.md`

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| G-REQ-01 | ✅ DONE |
| G-REQ-02 | ✅ VERIFIED (uncommitted) |

---

## 7. Next Step

**Action:** Lead commit G-REQ-02 (+ markers); next T3 candidate or accept LotteryRegistration as known HD-02 fail.  
**Owner:** Lead  
**Gate:** Lead  
**Done when:** committed  
**Blocker:** none for G-REQ-02  
**Suggested user prompt:**
> Commit G-REQ-02 then proceed G-REQ-03

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| G-REQ-02 | VERIFIED | guard + markers |
| LotteryRegistrationRequestTest | FROZEN HD-02 | Stage-1 snapshot missing |

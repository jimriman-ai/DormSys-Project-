# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/31 | 2026-07-22 | Session: HD-02 remediation planning — gap table + plan; AWAITING Lead approval_

**Authority note:** OBSERVED. Discovery/planning only for `lottery_programs` / `lottery_results` / `lottery_eligible_snapshots`. Items needing `lottery_registrations` marked **BLOCKED**. No migrations written. **STOP for Lead approval.**

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Discovery | 3 allowed lottery tables + models | gap assessed | migrations 000001/3/4 + Eloquent models |
| Plan | remediation | drafted in chat — **no migration files** | Lead gate |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision** — approve/reject HD-02 allowed-scope remediation plan

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| HD-02 remediations | Await Lead on gap plan |
| Frozen | lottery_registrations, ProposedAllocation, DBT-3, T2-A2 |

Canonical: `docs/governance/open-decisions.md`

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| Option B Phase 1 | ✅ CLOSED |
| HD-02 schema remediation | ⏳ planning — approval gate |

---

## 7. Next Step

**Action:** Lead approve subset of remediation migrations (or reject all).  
**Owner:** Lead  
**Gate:** explicit approval before any migration file  
**Done when:** approved IDs listed  
**Blocker:** Lead approval  
**Suggested user prompt:**
> APPROVE HD-02 remediations: P1, P2, S1, R1 (reject BLOCKED items)

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| Results→registrations FK | BLOCKED | frozen parent table |
| Programs/snapshots CHECKs | PROPOSED | see chat plan |

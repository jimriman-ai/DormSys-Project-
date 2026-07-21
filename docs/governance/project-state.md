# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/30 | 2026-07-21 | Session: DEBT-W3-01 CLOSED — CheckIn→Request stay lifecycle_

**Authority note:** OBSERVED. CheckIn/CheckOut Actions advance Request `checked_in`/`checked_out` via port+bridge for request-sourced allocations.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| DEBT-W3-01 | CheckIn→Request SM | OPEN → **CLOSED** | Lead resolution |
| Port | `RequestStayLifecycleCommandPort` | created | CheckIn Application |
| Bridge | `RequestStayLifecycleCommandBridge` | created | Integrations/CheckIn |
| Actions | CheckInAction / CheckOutAction | call port after persist | Application Services |
| Tests | CheckIn feature suite | pass (+ request-sourced case) | Sail |
| Frozen | HD-02/HD-03/DBT-3 | untouched | constraint |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle** — DEBT-W3-01 **CLOSED** (Agent SUSPENDED pending next Lead command)

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| DEBT-W3-01 | **CLOSED** |
| W3-WP-WF-04-RISK | KNOWN-RISK |
| HD-02 / HD-03 / DBT-3 | Frozen untouched |

Canonical: `docs/governance/open-decisions.md`

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| Wave 3 W3-B | ✅ COMPLETE |
| DEBT-W3-01 CheckIn consumer | ✅ **CLOSED** |
| Request OA-05-03 stay path | ✅ allocated→checked_in→checked_out |

---

## 7. Next Step

**Action:** Lead accepts DEBT-W3-01 closeout; issue next WP/wave.  
**Owner:** Lead  
**Gate:** none  
**Target files:** n/a  
**Done when:** Lead accepts  
**Blocker:** none  
**Suggested user prompt:**
> Accept DEBT-W3-01 closeout OR `BEGIN WAVE 4`

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| DEBT-W3-01 | CLOSED | CheckIn→Request wired |
| W3-WP-WF-04-RISK | KNOWN-RISK | baseline transitions |
| DEBT-W1-01 | FROZEN | DBT-3 |

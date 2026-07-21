# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/30 | 2026-07-21 | Session: Wave 3 W3-B COMPLETE — OA-05-03 + Session Handoff_

**Authority note:** OBSERVED. Wave 3 Option W3-B delivered. Request owns OA-05-03 states. CheckIn consumer = DEBT-W3-01.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| STOP-3A/B | APPROVED + W3-B | HARD STOP → executed | Lead prompt |
| OA-05-03 states | Request Spatie + entity | added 5 states | Domain/States/* |
| Adapter | `RequestLifecycleCommandAdapter` | no-op → wired | Allocation Infrastructure |
| Known-risk | WP-WF-04 baseline | registered | `wave3-wp-wf-04-known-risk.md` |
| Debt | DEBT-W3-01 | OPEN | CheckIn→Request |
| Session Handoff | `wave3-session-handoff.md` | created | docs/audit |
| Scoped tests | 42 passed | green | Sail artisan test |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle** — Completion Wave 3 · **COMPLETE** (W3-B; Agent SUSPENDED)

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| DEBT-W3-01 | CheckIn→Request checked_in/out — OPEN |
| W3-WP-WF-04-RISK | KNOWN-RISK |
| HD-02 / HD-03 / DBT-3 | Frozen untouched |
| DEBT-W1-01 | STOP-F |

Canonical: `docs/governance/open-decisions.md`

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| Wave 1 | ✅ COMPLETE |
| Wave 2 | ✅ COMPLETE |
| Wave 3 W3-B OA-05-03 | ✅ **COMPLETE** |
| RequestLifecycleCommandAdapter | ✅ wired |
| DEBT-W3-01 CheckIn consumer | ⏳ OPEN |

---

## 7. Next Step

**Action:** Lead reviews Session Handoff; optionally authorize DEBT-W3-01 (CheckIn→Request) or `BEGIN WAVE 4`.  
**Owner:** Lead  
**Gate:** none for Wave 3 (closed)  
**Target files:** n/a  
**Done when:** Lead accepts handoff  
**Blocker:** none for W3-B  
**Suggested user prompt:**
> Accept Wave 3 Session Handoff OR `BEGIN` CheckIn→Request WP (DEBT-W3-01)

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| DEBT-W3-01 | OPEN | CheckIn consumer |
| W3-WP-WF-04-RISK | KNOWN-RISK | baseline transitions |
| DEBT-W1-01 | FROZEN | DBT-3 |
| W2-SOFT-COUPLING-01 | ACCEPTED | — |

# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/30 | 2026-07-21 | Session: Wave 3 STOP-3A/B discovery — HARD STOP_

**Authority note:** OBSERVED. Wave 3 discovery only. No State Machine code. Path `app/Domain/Dormitory/` absent; ownership conflict flagged.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Wave 3 start | State Machine + Session Handoff | SUSPENDED → discovery ACTIVE | Lead Wave 3 packet |
| STOP-3A | State inventory | complete | `docs/audit/wave3-stop3-discovery.md` |
| STOP-3B | Blocking decisions review | complete | same + open-decisions / known-fail |
| Path check | `app/Domain/Dormitory/` | **does not exist** | glob 0 |
| Ownership conflict | Prompt SM under Dormitory | vs CD-010 Request owns state | STOP-3B |
| Implementation | SM / events / design md | **blocked** HARD STOP | awaiting Lead |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision** — STOP-3A/B: disposition W3-A \| B \| C \| D (module home + OA-05-03 scope)

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| STOP-3A/B | Await Lead approve + W3 disposition |
| CD-010-A1 | Request owns state; Workflow owns rules — constrains SM home |
| HD-02 / HD-03 / DBT-3 | Frozen — do not touch |
| W2-SOFT-COUPLING-01 | ACCEPTED |

Canonical: `docs/governance/open-decisions.md`

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| Wave 1 | ✅ COMPLETE |
| Wave 2 Isolation | ✅ COMPLETE (`927ee6a`) |
| Wave 3 SM + Session Handoff | ⏳ STOP-3A/B HARD STOP |
| Request Spatie lifecycle | ✅ exists (approval chain) |
| OA-05-03 post-approval states | ❌ gap (stub adapter) |

---

## 7. Next Step

**Action:** Lead `APPROVE STOP-3A` + `APPROVE STOP-3B` + choose **W3-A|B|C|D** (or refined scope).  
**Owner:** Lead  
**Gate:** STOP-3A + STOP-3B  
**Target files:** none until approval  
**Done when:** disposition issued  
**Blocker:** module-home / scope ambiguity (Dormitory vs Request; OA-05-03)  
**Suggested user prompt:**
> `APPROVE STOP-3A` + `APPROVE STOP-3B` + Option **W3-B** (extend Request Spatie for OA-05-03) | **W3-A** (docs-only) | **W3-C** (facade) | **W3-D** (override ownership HD)

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| OA-05-03 / RequestLifecycle stub | OPEN | no-op adapter; states absent |
| Prompt path `app/Domain/Dormitory` | CONFLICT | needs Lead disposition |
| DEBT-W1-01 / DBT-3 | FROZEN | untouched |
| W2-SOFT-COUPLING-01 | ACCEPTED | — |

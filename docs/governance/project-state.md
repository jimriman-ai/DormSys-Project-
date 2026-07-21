# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/30 | 2026-07-21 | Session: DEBT-W3-01 committed + T2 triage STOP_

**Authority note:** OBSERVED. DEBT-W3-01 CLOSED (`2274352`). T2 triage docs-only; no remediation until Lead confirms cluster.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Commit | DEBT-W3-01 wiring | committed | `2274352` |
| Session Handoff | `debt-w3-01-session-handoff.md` | created | `b06259e` |
| Ledger | DEBT-W3-01 | CLOSED ✓ | open-decisions |
| T2 triage | baseline clusters | listed + re-checked | `docs/audit/t2-baseline-triage.md` |
| Remediation code | — | **none** | awaiting Lead |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision** — T2: confirm first cluster (**T2-1** Unit Request recommended)

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| DEBT-W3-01 | **CLOSED** |
| T2 first cluster | Awaiting Lead confirm |
| HD-02 / HD-03 / DBT-3 | Frozen |

Canonical: `docs/governance/open-decisions.md`

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| DEBT-W3-01 | ✅ CLOSED (`2274352`) |
| Session Handoff | ✅ `b06259e` |
| T2 baseline remediation | ⏳ STOP — confirm cluster |

---

## 7. Next Step

**Action:** Lead confirms first T2 cluster (recommend **T2-1** `SubmitDateValidationTest`).  
**Owner:** Lead  
**Gate:** T2 scope confirm before write  
**Target files:** if T2-1 — `tests/Unit/Modules/Request/Application/SubmitDateValidationTest.php` only  
**Done when:** cluster confirmed  
**Blocker:** awaiting Lead  
**Suggested user prompt:**
> `APPROVE T2-1` — fix SubmitDateValidationTest ctor (StartRequestApprovalWorkflowAction mock)

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| DEBT-W3-01 | CLOSED | — |
| T2 Unit Request | OPEN | SubmitDateValidationTest |
| T2 Architecture adapter | OPEN | RequestLifecycleCommandAdapter location (W3-B) |
| Lottery | FROZEN | HD-02 |

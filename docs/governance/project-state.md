# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/28 | 2026-07-19 | Sprint: F3 Sprint B | Session: Stage1 re-verify PASS (tests + PHPStan)_

**Authority note:** WP-RQ-W2-01 Lead-accepted DONE (closure report). SHA **UNVERIFIED** until Lead commit.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Verify | `--filter=Stage1` | **15 passed** (42 assertions) | Sail `EXIT:0` |
| Verify | PHPStan Stage1 surfaces | **0 errors** | `php vendor/bin/phpstan analyse --no-progress` `EXIT:0` |
| Code | — | **unchanged** | notification-only |

---

## 0.1 Current Work Level (سطح کاری فعلی)

📄 **Spec — `docs/features/stage1-approver-console/`** (Wave 2 DONE per Lead; pending Lead commit)

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| F-W07-04 | stage1-approver-console | Wave 1+2 **DONE** (Lead ACCEPTED WP-RQ-W2-01) | Sprint B | Lead commit |
| UI-M2 | Unit Manager Dashboard | Lock COMPLETED (SB-D7) | Sprint B | Lead commit |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| stage1-approver-console | ✅ | ✅ Wave 1+2 | ⏳ | — | re-verify green |
| UI-M2 | ✅ SB-D6 | ✅ Lock | — | — | unchanged |

---

## 7. Next Step

**Action:** Lead commit WP-RQ-W2-01 (+ related WT). Record SB-D10 in governance if Lead wants ID for exempt entry (code already present; ID absent in docs — closure STOP).  
**Owner:** Lead  
**Gate:** Manual commit  
**Target files:** Stage1 Wave 2 surfaces + ExemptMutationActionRegistry  
**Done when:** SHA recorded  
**Blocker:** none for verify  

**Suggested user prompt:**
> Commit WP-RQ-W2-01. Optionally record SB-D10 for ListPending exempt registry entry.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| WP-RQ-W2-01 | **DONE** (Lead) | SHA UNVERIFIED |
| SB-D9 | ISSUED | Wave 2 auth |
| SB-D10 | CONTEXT ISSUED / **docs ABSENT** | closure STOP discrepancy |
| G7 | DEFERRED | requester-name filter |

# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/30 | 2026-07-21 | Session: WP-WF-05 notifications implemented (A1/B3/C1/D2) — pending Lead commit_

**Authority note:** OBSERVED. Decision Lock applied; WP-WF-05 code delivered. No WP-DORM-04.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Decision Lock | WP-WF-05 A/B/C/D | locked A1/B3/C1/D2 | Lead GO |
| NotificationType | enum | +`request_approval_pending` | A1 |
| Integration | `app/Integrations/Notification/` | subscriber + delivery | B3/C1/D2 |
| Tests | RequestApprovalWorkflowNotificationTest | 4 passed | Sail |
| PHPStan | Integration + type | 0 errors | scoped analyse |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle** — WP-WF-05 **OBSERVED complete**; WP-WF sequence 00–05 done; WP-DORM-04 still HOLD until Lead GO

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| WP-WF-05 lock | A1/B3/C1/D2 ACCEPTED |
| WP-DORM-04 | HOLD (sequence after WP-WF-05) |

---

## 6. Lifecycle Matrix (affected)

| WP | Status |
|----|--------|
| WP-WF-00…04 | OBSERVED done |
| WP-WF-05 | **OBSERVED DONE** |
| WP-DORM-04 | HOLD |

---

## 7. Next Step

**Action:** Lead review WP-WF-05 STOP/completion; commit; authorize WP-DORM-04 only when ready.  
**Owner:** Lead  
**Gate:** WP-WF-05 accepted  
**Suggested user prompt:**
> ACCEPT WP-WF-05 — commit Workflow sequence; GO WP-DORM-04 when authorized

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| WP-WF-05 | **OBSERVED DONE** | pending Lead commit |
| S2–S4 pending fan-out | DEFERRED | C1 scope |
| WP-DORM-04 | HOLD | after WP-WF-05 |

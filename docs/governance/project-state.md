# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/29 | 2026-07-20 | Session: Fixed CI `composer run arch -- --ansi` separator_

**Authority note:** Bug fix only — workflow arg forwarding. No ledger writes.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| CI arch step | `.github/workflows/tests.yml` | `composer run arch --ansi` → `composer run arch -- --ansi` | Match `composer run test -- --ansi` pattern |

---

## 0.1 Current Work Level (سطح کاری فعلی)

📄 **Spec / CI** — workflow argument-separator hygiene for `arch` script

---

## 7. Next Step

**Action:** Lead review/commit CI one-liner if desired.  
**Owner:** Lead  
**Gate:** Manual commit  
**Done when:** Workflow uses `--` before script args for arch  
**Suggested user prompt:**
> Commit the tests.yml arch `--` fix.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| CI arch `--ansi` forwarding | CLOSED | separator added |

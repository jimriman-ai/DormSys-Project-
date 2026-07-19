# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/28 | 2026-07-19 | Session: fix CI triggers — all push/PR; pending Lead commit + push_

**Authority note:** OBSERVED process fix — workflow `on:` expanded for solo push-on-any-branch.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Fix | `.github/workflows/tests.yml` | branch allowlist → **all push/PR** + dispatch | file |
| Fix | `.github/workflows/code-quality.yml` | branch allowlist → **all push/PR** + dispatch | file |
| Fix | `.github/workflows/governance-guard.yml` | branch filter removed; **paths kept** | file |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle — CI trigger fix** (workflow `on:` only)

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| CI push filters | Actions on any branch | **IN-PROGRESS** | push YAML | pending Lead commit/push |
| WP-UI-C-DASH-SEED | Dev identity roles | **DONE** | Verify PASS | DASH-00 ledger gap |
| WP-UI-C-01-B | listSites | NOT-STARTED | — | empty select |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| CI any-branch push | — | — | — | — | Tests + Code Quality unrestricted push |
| Governance Guard | — | — | — | — | path-filtered only |

---

## 7. Next Step

**Action:** Commit + push workflow YAML so next push runs Actions on `release/*`.  
**Owner:** Cursor (push) / Lead if commit deferred  
**Gate:** none for CI yaml process fix (user asked solve)  
**Target files:** `.github/workflows/{tests,code-quality,governance-guard}.yml`  
**Done when:** Actions run visible for this branch after push  
**Blocker:** none if push succeeds  

**Suggested user prompt:**
> Confirm Actions tab shows Tests + Code Quality on latest push.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| CI push filter vs release/* | **CLOSING** | allowlist removed |
| PR #12 closed unmerged | **OBSERVED** | push path now sufficient without PR |
| Default branch `spec02-baseline` | **OBSERVED** | not `main` |
| DASH-00 ledger | **OPEN** | not in open-decisions.md |
| WP-UI-C-01-B listSites | **OPEN** | reserved |

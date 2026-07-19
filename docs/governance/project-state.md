# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/28 | 2026-07-19 | Session: verify Bug1 pint composer script (already fixed)_

**Authority note:** Verify-only; no code change. Pint script already `@php vendor/bin/pint`.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Verify | Bug1 `composer.json` scripts.pint | **NOT PRESENT** — already `@php vendor/bin/pint` | `composer.json:74`; `composer run pint -- --test` invokes binary |

---

## 0.1 Current Work Level (سطح کاری فعلی)

📄 **Spec — CI/script hygiene verify** (pint script OK; WP-UI-C-01-LEGACY-CLEANUP-01 DONE)

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| WP-UI-C-01-LEGACY-CLEANUP-01 | Legacy wire retirement | **DONE** | — | none |
| WP-UI-C-01-B | `listSites()` | NOT-STARTED | — | empty select |
| composer pint script | Bug1 alleged | **CLOSED** (already fixed) | — | none |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| WP-UI-C-01-LEGACY-CLEANUP-01 | — | ✅ | ✅ | — | unchanged |

---

## 7. Next Step

**Action:** Lead authorize next WP (`WP-UI-C-01-B` or dashboard). Optional: run `composer run pint` for unrelated style debt.  
**Owner:** Lead  
**Gate:** none  
**Target files:** n/a  
**Done when:** next WP authorized  
**Blocker:** none for pint script  

**Suggested user prompt:**
> Authorize WP-UI-C-01-B listSites().

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| Bug1 pint script | **CLOSED** | already `@php vendor/bin/pint`; `--test` forwarded |
| WP-UI-C-01-B listSites | **OPEN** | reserved |
| Mixed auth:api / auth:identity | **OPEN** (debt) | observed |

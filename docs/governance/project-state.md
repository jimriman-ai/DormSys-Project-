# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/28 | 2026-07-19 | Session: DBT-5 CLOSED — NOT-A-GAP; Hard STOP before DASH-01 lifted_

**Authority note:** Docs ledger only. No infra/phpunit change.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Close | DBT-5 (DGAP-15) | OPEN → **CLOSED — NOT-A-GAP (config/operational)** | `open-decisions.md` DBT-5 Decision Record |
| Lift | Hard STOP before DASH-01 | active → **lifted** | Lead mitigation: single-suite / exclusive CI |
| Note | WP write | open-decisions.md | no phpunit/compose/tests |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle — Sprint C** — next authorized track step: **DASH-01** (shell), pending separate WP auth

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| DGAP-15 / DASH-00 | Dashboard Decision Register | **CLOSED** | ledger | pending Lead commit |
| DBT-5 / TEST-ISO-01 | Test DB Isolation | **CLOSED — NOT-A-GAP** | disposition | — |
| DASH-01…05 | Dashboard track | **NOT-STARTED** | planned | DASH-01 WP auth; DBT-1 for DASH-03 |
| WP-UI-C-01-B / DBT-1 | `listSites()` | **OPEN** | gap | blocks DASH-03 |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| DBT-5 Test DB Isolation | — | — | — | — | CLOSED NOT-A-GAP; concurrent-suite residual accepted |
| DASH-00 / DGAP-15 | — | — | — | — | CLOSED; sequence unblocked for DASH-01 |

---

## 7. Next Step

**Action:** Lead authorize / issue DASH-01 (dashboard shell `components.layouts.dashboard`).  
**Owner:** Lead → Cursor  
**Gate:** none from DBT-5 (lifted); DASH-01 needs its own WP  
**Target files:** layout + route smoke (per DASH-01 WP)  
**Done when:** DASH-01 WP issued and executed  
**Blocker:** none for starting DASH-01 auth  

**Suggested user prompt:**
> Issue WP-UI-C-DASH-01 — Dashboard Shell (layouts.dashboard).

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| DASH-00 ledger | **CLOSED** | DGAP-15 |
| DBT-5 Test DB Isolation | **CLOSED — NOT-A-GAP** | concurrent suites on `testing`; mitigation operational |
| WP-UI-C-01-B / DBT-1 listSites | **OPEN** | blocks DASH-03 |
| DBT-2 Role-aware redirect | **OPEN** | DASH-05 |
| DBT-3 Mixed auth:api / identity | **OPEN** | Hard STOP debt |
| DBT-4 Admin on web | **OPEN** | debt |
| DBT-6 transitional route | **OPEN** | cleanup |
| DBT-7 RequestCreatePage docs | **OPEN** | DOC-SYNC |

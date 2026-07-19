# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/28 | 2026-07-19 | Session: Lead confirm Q1–Q3 + DASH-01 closeout; parallel DASH-02 ‖ WP-UI-C-01-B_

**Authority note:** DASH-01 CLOSED in ledger. Commit pending / in-progress this turn.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Confirm | Lead Q1–Q3 | all **APPROVED** | this prompt |
| Record | DBT-3 HOTFIX drift | stub was `auth:api` pre-DASH-01 | `git show HEAD` vs DASH-01 diff |
| Close | WP-UI-C-DASH-01 | DONE → **CLOSED** | `open-decisions.md` closeout block |
| Next | parallel WPs | DASH-02 ‖ WP-UI-C-01-B | Session A / B v1.1 |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle — Sprint C** — parallel ACTIVE: **DASH-02** (Session A) ‖ **WP-UI-C-01-B** (Session B)

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| WP-UI-C-DASH-01 | Dashboard shell | **CLOSED** | historical | — |
| WP-UI-C-DASH-02 | Role-aware nav | **ACTIVE** | Session A | — |
| WP-UI-C-01-B | listSites() DBT-1 | **ACTIVE** | Session B (parallel) | — |
| DASH-03…05 | landings / redirect | **NOT-STARTED** | planned | DBT-1 for DASH-03 |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| DASH-01 Shell | ✅ | — | ✅ | — | CLOSED; Feature 739 green |
| DASH-02 Nav | ⏳ | — | — | — | ACTIVE Session A |
| WP-UI-C-01-B | ⏳ | — | — | — | ACTIVE Session B |

---

## 7. Next Step

**Action:** Run Session A (DASH-02) and Session B (WP-UI-C-01-B) in separate chats; do not cross-touch files.  
**Owner:** Cursor (per session)  
**Gate:** none between them (nav is role-only; listSites is contract — collision only if A reaches for listSites → STOP)  
**Target files:** A: layout/composer/nav; B: DormitoryReadContract + bridge + form select  
**Done when:** each WP green in its session  
**Blocker:** none  

**Suggested user prompt:**
> Paste Session A or Session B v1.1 prompt into a dedicated chat.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| DASH-01 | **CLOSED** | Q1–Q3 confirmed |
| DBT-5 | **CLOSED — NOT-A-GAP** | — |
| DBT-3 | **OPEN** | HOTFIX narrative drift noted; migrate Hard STOP |
| DBT-1 listSites | **OPEN** | Session B ACTIVE |
| DBT-2 role redirect | **OPEN** | DASH-05 |
| DBT-4 / DBT-6 / DBT-7 | **OPEN** | debt |

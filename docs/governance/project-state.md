# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/28 | 2026-07-19 | Session: confirm hotfix verify (dashboard + ReportingApiTest)_

**Authority note:** WP-UI-C-01-HOTFIX-01 verify confirm; no new AUTH.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Confirm | `route:list --name=dashboard` | resolves | Sail clear + list |
| Confirm | ReportingApiTest alone | **10 passed** (prior suite flake) | terminal 690691 |
| Confirm | `route('dashboard')` | `http://localhost/dashboard` | tinker |

---

## 0.1 Current Work Level (سطح کاری فعلی)

📄 **Spec — WP-UI-C-01-HOTFIX-01** (`dashboard` named route **DONE**; verify green)

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| WP-UI-C-01-HOTFIX-01 | Named route `dashboard` | **DONE** | Verify | pending Lead commit |
| WP-UI-C-DASH-SEED | Dev identity roles | **DONE** | Verify PASS | DASH-00 ledger gap |
| WP-UI-C-01-B | listSites | NOT-STARTED | — | empty select |

---

## 5. Open Decisions (mirror)

| ID | Summary | Status |
|----|---------|--------|
| DASH-00 | Role-based dashboard (ledger gap) | **OPEN** — absent from open-decisions.md |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| WP-UI-C-01-HOTFIX-01 | — | ✅ | ⏳ suite 2 unrelated FAIL | — | route only |
| WP-UI-C-DASH-SEED | — | ✅ | ✅ | — | absorbs DASH-06 |

---

## 7. Next Step

**Action:** Lead commit hotfix `routes/web.php`; next WP as authorized.  
**Owner:** Lead  
**Gate:** commit; DASH-00 still absent from open-decisions  
**Target files:** `routes/web.php`  
**Done when:** hotfix committed  
**Blocker:** none for hotfix verify  

**Suggested user prompt:**
> Commit WP-UI-C-01-HOTFIX-01, then authorize WP-UI-C-01-B or record DASH-00.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| DASH-00 ledger | **OPEN** | not in open-decisions.md |
| ReportingApiTest permissions | **OBSERVED** | `relation "permissions" does not exist` mid-suite — not dashboard |
| welcome `/dashboard` via url() | **OBSERVED** | now served by named redirect |
| WP-UI-C-01-B listSites | **OPEN** | empty select |
| Mixed auth:api / auth:identity | **OPEN** | Hard STOP — untouched |

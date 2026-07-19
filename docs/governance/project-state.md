# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/28 | 2026-07-19 | Session: confirm DevelopmentUserSeederTest re-run 8 passed_

**Authority note:** WP-UI-C-DASH-SEED verify confirm; DASH-00 still absent from open-decisions.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Confirm | DevelopmentUserSeederTest | first run **40P01** → re-run **8 passed** | terminals 690682 / 690683 |

---

## 0.1 Current Work Level (سطح کاری فعلی)

📄 **Spec — WP-UI-C-DASH-SEED** (local identity role seeding **DONE**; verify green)

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| WP-UI-C-DASH-SEED | Dev identity roles | **DONE** | Verify PASS | DASH-00 ledger gap (docs only) |
| Host suite triage | ReleaseAllocationTest etc. | OBSERVED | env | `DB_HOST=pgsql` on host |
| WP-UI-C-01-B | listSites | NOT-STARTED | — | empty select |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| WP-UI-C-DASH-SEED | — | ✅ | ✅ tests | — | absorbs DASH-06 |
| Host `php artisan test` Allocation | — | — | ❌ host | — | pgsql hostname; Sail previously green |

---

## 7. Next Step

**Action:** Record DASH-00 in open-decisions **or** authorize next WP (WP-UI-C-01-B listSites / dashboard UI WPs).  
**Owner:** Lead  
**Gate:** DASH-00 ledger + dashboard WP AUTH  
**Target files:** `docs/governance/open-decisions.md` (Lead); optional listSites contract  
**Done when:** DASH-00 row exists **or** next WP authorized  
**Blocker:** DASH-00 absent from open-decisions; dashboard Phase 3 awaiting Lead  

**Suggested user prompt:**
> Record DASH-00 in open-decisions, then authorize WP-UI-C-01-B (listSites) or dashboard shell WPs.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| DASH-00 ledger | **OPEN** | not in open-decisions.md |
| Host DB_HOST=pgsql | **OBSERVED** | ReleaseAllocationTest 4 errors — connection, not assertions |
| AssignRoleToUserAction web-only | **DEBT** | identity roles via Spatie in DevelopmentUserProvisioner |
| WP-UI-C-01-B listSites | **OPEN** | reserved |
| Mixed auth:api / auth:identity | **OPEN** | observed |

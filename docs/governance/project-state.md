# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Does NOT grant implementation authorization.
> Canonical sources override this file on conflict.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/27 | 2026-07-18 | Sprint: F3-Sprint-A | Session: AUTH-011 Band 2 scope correction — docs only_

**Authority note:** Lead `AUTH-011` Band 2 + scope correction (2026-07-18): **all git commit/merge are Lead-owned**. Cursor: report + doc writes only.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Scope | AUTH-011 Band 2 | Cursor merge/commit → **Lead-owned VCS** | Lead AUTH-011 Band 2 Scope Correction |
| D2 | Unstaged files | action pending → **report-only R1/R2/R3** | classification table § below |
| G1 | L9 checklist | confirmed still PASS/WAIVED | refreshed 2026-07-16 |
| G3 | `bootstrap/app.php` | confirmed CONFLICT (read-only) | `git diff 011-reporting-projections...HEAD` |
| §7 | Next Step | Cursor merge → **Lead manual commit + conflict resolve + merge** | this session |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision — Lead VCS** — commit unstaged docs + resolve `bootstrap/app.php` conflict + merge `release/f2-employee-auth-ui-l9` → `011-reporting-projections`.

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker | Canonical artifact |
|----|-------|--------|-------|---------|-------------------|
| UI-M1 | Manager Dashboard | OBSERVED-L8-COMPLETE | L9-**BLOCKED** | G3 conflict + Lead merge | `l9-merge-checklist-dormitory-admin-ui.md` |
| UI-M2 | Unit-Manager Dashboard | SCHEDULED-W3 | L3-not-started | W1/W2 gate | — |
| UI-A1 | Auth layout / dual-guard | OBSERVED-COMPLETE | L8-done | — | W1-A1 waiver active |

---

## 6. Lifecycle Matrix

| Feature | L0 | L1 | L2 | L3 | L5 | L6 | L7 | L8 | L9 |
|---------|----|----|----|----|----|----|----|----|-----|
| UI-M1 | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| UI-M2 | ✅ | ✅ | ✅ | — | — | — | — | — | — |
| UI-A1 | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ |

---

## 7. Next Step (rewritten every prompt)

**Action:** Lead manually (1) commit unstaged W1 docs + R2 config files, (2) resolve `bootstrap/app.php` when merging into `011-reporting-projections`, (3) record merge SHA.  
**Owner:** Lead  
**Gate:** G3 clean merge (Lead-owned); Cursor does **not** run git write commands  
**Target files:** unstaged list below; `bootstrap/app.php`  
**Done when:** Lead merge SHA recorded; UI-M1 → MERGED/CLOSED  
**Blocker:** G3 — `CONFLICT (content): bootstrap/app.php` (observed dry-run 2026-07-16; still present on branch tip)

**Suggested user prompt:**
> (Lead) After committing WD and resolving bootstrap/app.php, update project-state with merge SHA → then W2.

---

## 8. Gap Registry (selected)

| Gap ID | Status | Notes |
|--------|--------|-------|
| GAP-DOC-04 | **RESOLVED** | Checklist refreshed 2026-07-16 |
| GAP-GOV-01 | **RESOLVED** | W1-A1 recorded |
| GAP-GOV-02 | OPEN | merge blocked — Lead-owned |
| GAP-GOV-03 | **CLOSED** | BL-01…04 in `risk-register.md` |
| GAP-MERGE-01 | **OPEN** | `bootstrap/app.php` conflict |

### Unstaged files — D2 classification (report only; no Cursor action)

| File | Rule | Classification note |
|------|------|---------------------|
| `composer.json` | **R2** | CI/runtime config — phpstan `--no-progress` |
| `.github/workflows/tests.yml` | **R2** | CI config — `composer run test` |
| `docs/governance/open-decisions.md` | **R2** | AUTH-013 + W1-A1 |
| `docs/governance/project-state.md` | **R2** | session snapshot |
| `docs/governance/roadmap.md` | **R2** | wave sequencing |
| `docs/governance/risk-register.md` | **R2** | BL-01…04 tracker |
| `.specify/docs/spec-catalog.md` | **R2** | AUTH-013 dispositions |
| `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md` | **R2** | refreshed L9 checklist |

No **R1** (generated/cache/IDE). No **R3** (ambiguous).

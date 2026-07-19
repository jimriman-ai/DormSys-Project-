# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/28 | 2026-07-19 | Sprint: F3 Sprint B | Session: PA-01 DOC sync (F2 PASS / F-W07-04 Wave 1 COMPLETED)_

**Authority note:** SB-D1..D5 = A (closed). F-W07-04-D1…D3 recorded. PA-01 = DOC-ONLY sync (no new decision).

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| DOC sync | `roadmap.md` F2 / F-W07-04 | PARTIAL+CARRIED → **PASS** / Wave 1 **COMPLETED** | **F-W07-04-D2**, **D3** |
| DOC sync | `open-decisions.md` F-W07-04 § + DG-02 notes | CARRIED/PARTIAL → COMPLETED/PASS; DGAP-14 status **unchanged DECIDED** | PA-01; `governance-log.md:16–17` |
| DOC sync | `employee-auth-ui` brief + WBD + W07 + L3 | PARTIAL/CARRIED → **PASS** / COMPLETED | **D2** |
| Hygiene | `open-decisions.md` trailing orphan | corrupt diff fragment → removed | PA-01 |

---

## 0.1 Current Work Level (سطح کاری فعلی)

📄 **Spec — PA-01 Documentation Sync** (`docs/governance/project-audit-01.md` → PA-01); next proposed: PA-02 UI-M2 L3 accept

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| F-W07-04 | `stage1-approver-console` Wave 1 | **DONE** (D3) | Sprint B | Wave 2 UX tests deferred |
| F2 | employee-auth-ui / F-W07-04 gate | **PASS** (D2) | Program | Spec04 Auth still not authorized |
| UI-M2 | Unit-Manager Dashboard L3 | **L3 authored — awaiting Lead review** | Sprint B | L3 accept (PA-02) |
| DGAP-14 | Residual DeptMgr refs | **DECIDED** (unchanged) | Parked residuals | Runtime residuals via SB-D1/D2 |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| stage1-approver-console | ✅ (FC) | ✅ Wave 1 | ⏳ Wave 2 tests | — | D2–D3; PA-01 docs synced |
| UI-M2 | ⏳ (authored, review pending) | — | — | — | PA-02 |
| UI-M1 | ✅ | ✅ | ✅ | ⏳ | unchanged |

---

## 7. Next Step

**Action:** Lead L3 accept for `docs/features/ui-m2/l3-spec.md` (PA-02) and/or commit PA-01 DOC WT.  
**Owner:** Lead  
**Gate:** UI-M2 L3 review; optional Stage-1 Wave 2 tests (PA-04)  
**Target files:** `docs/features/ui-m2/l3-spec.md`; PA-01 synced docs (pending Lead commit)  
**Done when:** Lead accepts UI-M2 L3 and/or commits PA-01  
**Blocker:** none for PA-01 (complete)

**Suggested user prompt:**
> PA-02 — Accept or revise `docs/features/ui-m2/l3-spec.md` (L3 review gate).

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| PA-01 | **DONE** (WT) | DOC sync F2/F-W07-04; pending Lead commit |
| F-W07-04 | **DONE** Wave 1 | D1–D3; docs synced PA-01 |
| F2 (program gate) | **PASS** | D2; not Spec04 Auth authorization |
| UI-M2 L3 | CLOSING | authored; Lead review (PA-02) |
| DGAP-14 | DECIDED | PA-01: not reopened |
| GAP-GOV-02 | OPEN | tip SHA pending Lead commit |
| Stage-1 Wave 2 UX tests | OPEN | deferred per D3 (PA-04) |

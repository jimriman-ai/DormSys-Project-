# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/29 | 2026-07-20 | Session: WP-DEBT-05 v2.2 HALT STOP-1.1 — dirty tree_

**Authority note:** DEC-ARCH-POLICY-01 DECIDED; boundary-rules amended. WP-DEBT-05 code NOT started (porcelain dirty).

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| WP-DEBT-05 v2.2 | STOP-1.1 | re-issue → **HALT** | `git status --porcelain` non-empty |
| STOP-1.2 | DEC-ARCH-POLICY-01 | would PASS (present) | `open-decisions.md` |
| STOP-1.3 | Policy rule | would PASS at `docs/architecture/boundary-rules.md` (prompt path `docs/governance/boundary-rules.md` does not exist) | Grep |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision / Ops** — Lead Manual Commit (groups 1–4) → clean porcelain → re-issue WP-DEBT-05 v2.2

---

## 7. Next Step

**Action:** Lead commit pending debt/governance/CI; then re-issue WP-DEBT-05 v2.2 from STOP-1.  
**Owner:** Lead  
**Gate:** `git status --porcelain` empty  
**Done when:** STOP-1.1–1.3 all PASS  
**Blocker:** Dirty working tree (WP-DEBT-01/03/04 + CI + governance uncommitted)  
**Suggested user prompt:**
> After clean porcelain: execute WP-DEBT-05 v2.2 from STOP-1.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| WP-DEBT-05 | BLOCKED H-1.1 | await clean tree |
| DEC-ARCH-POLICY-01 | DECIDED | Option A; not yet IMPLEMENTED (code pending WP-05) |

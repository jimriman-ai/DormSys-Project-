# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/05/01 | 2026-07-23 | ARCH-MODULE-BOUNDARY-C2-01: C2 closed; Arch suite green (0 fail)_

**Authority note:** C1–C5 Architecture failure clusters closed. Full Pest 1995 passed; arch:scan green. pending Lead commit of multi-wave dirty tree.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Workflow Application exceptions | `app/Modules/Workflow/Application/Exceptions/*` | +3 types | ARCH-MODULE-BOUNDARY-C2-01 |
| Decide translate | `DecideRequestApprovalStageAction` | Domain→Application wrap | same |
| Request catch | Approve/Reject stage actions | catch Application exceptions | same |
| Full Pest | Sail | 3 fail → **0 fail** / 1995 pass | terminal 488707 |
| `arch:scan` | Sail | RED → **passed** | same |
| PHPStan | `php vendor/bin/phpstan analyse --memory-limit=1G --no-progress` | **OK** | same |
| Progress log | `.dormSys/progress-log.md` | +ARCH-MODULE-BOUNDARY-C2-01 | progress-log |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision** — Architecture Stop-Gate clusters C1–C5 **CLOSED**; Lead commit / next product wave

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| Arch C1–C5 | CLOSED |
| Spec07 adapter path doc vs Integrations | DRIFT (docs only; earlier wave) |

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| ARCH-MODULE-BOUNDARY-C2-01 | DONE |
| Arch suite baseline | **GREEN** (0 fail) |
| Stop-gate Arch clusters | ALL CLOSED |

---

## 7. Next Step

**Action:** Lead commit multi-wave dirty tree; choose next product/auth wave.  
**Owner:** Lead  
**Gate:** Arch C1–C5 green  
**Target files:** git commit of session waves  
**Done when:** Lead commits  
**Blocker:** none Arch  
**Suggested user prompt:**
> Review and commit ARCH waves C1–C5 (pending Lead commit)

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| C1 Persistence Option C | CLOSED | |
| C3 adapter / C4 boot | CLOSED | |
| C5 Mutation registry | CLOSED | |
| C2 Request→Workflow Domain | CLOSED | Application exception boundary |

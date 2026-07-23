# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/05/01 | 2026-07-23 | ARCH-ADAPTER-PROVIDER-01: C3∥C4 closed; suite 8→5 Arch (C2+C5)_

**Authority note:** C3 Integrations move + C4 register-only provider applied. Spec07 adapter-path text drifts (reported). Next: C5 then C2 per sequencing. pending Lead commit.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| C3 lifecycle bridge | `app/Integrations/Allocation/RequestLifecycleCommandBridge.php` | moved from Allocation Infra adapter | ARCH-ADAPTER-PROVIDER-01 |
| C3 bind | `IntegrationServiceProvider` + remove from `AllocationServiceProvider` | port at composition root | same |
| C4 | `IntegrationServiceProvider` | removed `boot()`; `Event::listen` in `register()` | same |
| Guard list | `architectureIntegrationPortClasses()` | +`RequestLifecycleCommandPort` | architecture.php |
| Full Pest | Sail | 8 fail → **5** stable Arch (+1 intermittent RequestRead) | terminal 488700 |
| PHPStan | `php vendor/bin/phpstan analyse --memory-limit=1G --no-progress` | **OK** | terminal 488699 |
| Progress log | `.dormSys/progress-log.md` | +ARCH-ADAPTER-PROVIDER-01 | progress-log |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision** — Next per sequencing: **C5** then **C2** (C1 + C3∥C4 done)

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| Arch C1 Persistence Option C | CLOSED |
| Arch C3 adapter / C4 composition | CLOSED (this wave) |
| Arch C2 / C5 | OPEN |
| Spec07 adapter path text vs Integrations | DRIFT (docs only; not rewritten) |

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| ARCH-ADAPTER-PROVIDER-01 | DONE |
| Suite baseline | RED **5** Arch (C2+C5) |
| C5 next | PENDING Lead AUTH |

---

## 7. Next Step

**Action:** Lead AUTH ARCH-C5 (mutation registry) then C2 (Request→Workflow Domain exceptions) per sequencing.  
**Owner:** Lead  
**Gate:** C3∥C4 done; residual C2+C5  
**Target files:** MutationAuthorization registries / Request↔Workflow boundary  
**Done when:** Lead chooses C5 Fix wave  
**Blocker:** C2, C5  
**Suggested user prompt:**
> AUTH ARCH-MUTATION-REGISTRY-01 (C5) from arch-triage-01

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| C1 Persistence Option C | CLOSED | |
| C3 Allocation→Request adapter | CLOSED | Integrations bridge |
| C4 Integration boot() | CLOSED | register-only |
| C2 Request→Workflow Domain | OPEN | |
| C5 Mutation auth registry | OPEN | |
| Spec07 adapter path doc drift | OPEN | report only |

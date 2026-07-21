# Wave 2 — Dormitory Module Isolation (Discovery)

**Command:** `BEGIN WAVE 2` (Lead — module Isolation scope)  
**Date:** 2026-07-21  
**Gate:** **STOP-2** — no refactor until `APPROVE STOP-2`  
**Excluded (from Wave 1 handoff):** DEBT-W1-01 (DBT-3), DEBT-W1-03 (allowlist), DEBT-W1-06 (`users` PK)

---

## Scope (Lead)

| In | Out |
|----|-----|
| `app/Modules/Dormitory/**` must not depend on `Dashboard` | Lottery / Reporting / DBT-3 |
| DormitoryServiceProvider dormitory-scoped binds only | Auth Foundation allowlist changes |
| Livewire → Action only (entrypoint) | Merging dormitory-admin-ui into Dashboard |

---

## Audit results

### A. PHP import: Dormitory → Dashboard

| Check | Result |
|-------|--------|
| `use App\Modules\Dashboard` under `app/Modules/Dormitory/**` | **0 hits** |
| `use App\Modules\Dashboard` under `app/Modules/DormitoryAdmin/**` | **0 hits** |

**Verdict:** Goal “zero PHP imports from dashboard” is **already satisfied**.

### B. Soft coupling (Blade layout name)

| File | Coupling |
|------|----------|
| `DormitoryIndexPage.php` L17 | `#[Layout('components.layouts.dashboard')]` |
| `DormitoryShowPage.php` L20 | same |

| Related | Path |
|---------|------|
| Layout view | `resources/views/components/layouts/dashboard.blade.php` (shared `resources/`, not under `app/Modules/Dashboard`) |
| Composer | `DashboardPresentationServiceProvider` registers `DashboardNavComposer` on that view name |

**Verdict:** No PHP module import; **string dependency** on the shared layout component name `dashboard`. Protocol “never depend on dashboard” is ambiguous for Blade names — needs Lead disposition.

### C. Entrypoint Rule (Livewire → Action)

| Component | Injects | Repository direct? |
|-----------|---------|-------------------|
| `DormitoryIndexPage` | `ListEmployeeAssignedDormitoriesAction` | **No** |
| `DormitoryShowPage` | `GetEmployeeAssignedDormitoryAction` | **No** |

**Verdict:** **PASS**.

### D. DormitoryServiceProvider

Binds only Dormitory Application/Domain/Infrastructure contracts and policies (`DormitoryServiceProvider.php`). No Dashboard types.

**Verdict:** **PASS**.

### E. Other cross-module (informational, not Dashboard)

| Location | Import | Note |
|----------|--------|------|
| `DormitoryStructureAuthorizationGate` | `Identity` Application contracts | Auth catalog/read — not Wave 2 Dashboard isolation |

---

## Proposed dispositions (Lead chooses)

| Option | Meaning | Files to change | Shared contracts |
|--------|---------|-----------------|------------------|
| **A — ACCEPT-AS-IS** | PHP isolation done; Blade layout name is shared UI shell, not module dependency | **none** | **none** |
| **B — Rename layout alias** | Introduce neutral layout e.g. `components.layouts.app-shell`; point Dormitory (+ optionally Dashboard) at it; keep Blade file in `resources/views` | Livewire attributes; optionally Dashboard composer registration key | **none** (view rename only) |
| **C — Extract Shared layout package** | Move layout + nav port to `app/Shared` / shared views; Dashboard becomes consumer | More files; composer relocate | Optional `NavItemsPort` if nav must stay modular |

**Recommendation (Agent, non-binding):** **A** if Lead’s “zero imports” means PHP only; **B** if string `dashboard` in Dormitory Presentation is unacceptable.

---

## STOP-2 GATE

```
STOP-2 GATE
─────────────────────────────────────
Files to change     : <none | pending Lead option B/C>
Shared contracts    : <none | pending Lead option C>
PHP Dormitory→Dashboard imports : 0
Entrypoint violations           : 0
Provider Dashboard binds        : 0
Soft layout coupling            : 2 Livewire files (see §B)
DEBT exclusions honored         : W1-01 / W1-03 / W1-06 not touched
─────────────────────────────────────
AWAITING: APPROVE STOP-2
  + disposition A | B | C
```

**Agent status:** Wave 2 **ACTIVE** for discovery only · **SUSPENDED for writes** until `APPROVE STOP-2`.

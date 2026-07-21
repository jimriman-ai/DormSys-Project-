# DEBT-DISCOVERY — Completion Wave 2 (Module Isolation)

**Registered:** 2026-07-21 · **Source wave:** Completion Wave 2  
**Parent discovery:** `docs/audit/wave2-isolation-discovery.md`  
**STOP-2:** Option A approved by Lead — PHP isolation sufficient; soft coupling accepted.

---

## Register

| ID | Description | File(s) | Decision |
|----|-------------|---------|----------|
| W2-SOFT-COUPLING-01 | `#[Layout('components.layouts.dashboard')]` in Dormitory Livewire components | Index.php, Show.php (Dormitory) | Accepted — PHP isolation sufficient; layout string is not a PHP dependency |

---

## W2-SOFT-COUPLING-01 — Blade layout string (accepted)

| Field | Value |
|-------|--------|
| Status | **ACCEPTED** (STOP-2 Option A) |
| Classification | ACCEPTED DEBT — soft coupling / naming only |
| Evidence | `app/Modules/Dormitory/Presentation/Livewire/DormitoryIndexPage.php` (`#[Layout('components.layouts.dashboard')]`) |
| | `app/Modules/Dormitory/Presentation/Livewire/DormitoryShowPage.php` (same) |
| Why accepted | Zero PHP imports from `App\Modules\Dashboard`; layout view lives under shared `resources/views`; string name is UI shell, not a module dependency |
| Closure | Optional later: Option B (neutral layout rename) or C (Shared extract) — requires new Lead HD; not Wave 2 work |

---

## Wave 2 closeout

| Field | Value |
|-------|--------|
| Status | **COMPLETE** |
| PHP Dormitory → Dashboard imports | 0 |
| Entrypoint / Provider | PASS |
| Frozen scope | Lottery / Reporting / DBT-3 untouched |
| Livewire / layout files | unchanged |

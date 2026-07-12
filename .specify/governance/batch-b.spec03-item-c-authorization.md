# Spec03 Item C Authorization — Phase 8 Polish

**Artifact type:** Implementation Authorization (scoped polish / quality)  
**Spec:** `003-employee-context` / catalog `spec03`  
**Authorized item:** Item C — Phase 8 polish (T053–T058, Scenario 9 neutralized)  
**Authorization date:** 2026-07-12  
**Checkpoint:** `batch-b.spec03-item-c-authorization`

**Governing plan:** `.specify/governance/batch-b.spec03-closure-plan.md` (`SPEC03_CLOSURE_PLAN_APPROVED`)  
**Preconditions met:**  
- Item A — `.specify/governance/batch-b.spec03-item-a-execution-report.md` (`SPEC03_ITEM_A_COMPLETED`)  
- Item B — `.specify/governance/batch-b.spec03-item-b-resolution.md` (`SPEC03_ITEM_B_DEFERRED`)

This record authorizes **exactly** Item C execution. It does **not** authorize Item D, EmployeeRead, UI, Request Dependent live, or live Allocation. Execution must not start until this artifact exists (now satisfied for subsequent execute prompts).

---

## 1. Authorization Decision

| Field | Value |
| ----- | ----- |
| **Decision** | **`SPEC03_ITEM_C_AUTHORIZED`** |
| **authorization-status** | **active** |
| **Execution started?** | **No** (authorization only) |
| **Authority class** | Implementation Authorization — Phase 8 polish only |
| **Item B deferral** | **Preserved** — T049–T052 remain deferred; must not be marked complete |

---

## 2. Scope Authorized

**authorized-scope** (verbatim):

| ID | Authorized work |
| -- | --------------- |
| **T053** | Re-run / extend `tests/Architecture/EmployeeSupplierBoundaryTest.php` for post-US3/US4 Employee files — **BT-05**. Do **not** add EmployeeRead paths (Item B deferred). |
| **T054** | Update `app/Modules/Employee/README.md` — module boundaries; CD-012/CD-013; ActiveAllocation → Null + PendingRequest → live bridge (per Item A DOC-OPT); state **EmployeeRead deferred at Spec03 close** (not “open Spec03 obligation”). |
| **T055** | Pint across Employee module. Prefer Windows-safe: `php vendor/bin/pint …` on `app/Modules/Employee` (and related Employee test paths if dirty). Fix formatting only. |
| **T056** | PHPStan on Employee paths with explicit PHP: `php vendor/bin/phpstan analyse --no-progress` (memory flag allowed) targeting `app/Modules/Employee` — **0 errors** (SC-005). |
| **T057** | Record pass/fail for `quickstart.md` Scenarios **1–8** via named existing tests and/or a short scenario table. **Scenario 9 = N/A — deferred** (Item B). Neutralize any Spec03 Phase 8 expectation that Scenario 9 must pass for closure. |
| **T058** | Scope-audit statement (in Item C execution report and/or minimal Phase 8 note): no Spec03 tasks inventing Request/Allocation modules; no Identity Infrastructure imports in Employee; no FK `identity_id` → `identity_users`. |

**Scenario 9 neutralization (mandatory under this IA):**

- Do **not** require `EmployeeReadContract` or quickstart Scenario 9 for Item C DoD.
- In any T057 evidence table: Scenario 9 = **`N/A — deferred (SPEC03_ITEM_B_DEFERRED)`**.
- Do **not** implement T049–T052 to “make Scenario 9 pass.”

**Minimal Phase 8 completion markers allowed (not Item D):**

- Mark **only** T053–T058 as complete when evidence exists, **or** record completion solely in the Item C execution report.
- T057 scenario table may appear in the execution report; a **minimal** Phase 8-only note in `tasks.md` is allowed **only** for T053–T058 / Scenario 9 N/A — **not** wholesale US3+/US4/catalog/`spec.md` reconciliation.

---

## 3. Scope Explicitly Excluded

**blocked-scope:**

| Forbidden | Reason |
| --------- | ------ |
| EmployeeRead T049–T052 implementation | Item B deferred; do not mark complete |
| `employee-context-ui` / Livewire / Feature Contracts / Main UI | Frozen / out of Spec03 Phase 8 |
| Request Dependent live / stub replacement | Frozen (D-01) |
| Employee Dependent Application read surface | Frozen (D-03) |
| Live Allocation adapter / Null PendingRequest reversion | Frozen |
| New feature work beyond T053–T058 polish | Closure plan scope out |
| **Item D** — `spec.md` status rewrite, catalog Spec03 hold cleanup, wholesale `tasks.md` Phase 6/7/header reconciliation | Separate authorization later |
| Declaring `SPEC03_CLOSED` | Final gate after C + D |
| Rewriting Item B deferral to “delivered” | Forbidden |

---

## 4. Dependencies / Preconditions

| Precondition | Status |
| ------------ | ------ |
| Closure plan approved | **Met** |
| Item A DOC-OPT completed | **Met** |
| Item B formally deferred | **Met** (`SPEC03_ITEM_B_DEFERRED`) |
| Scenario 9 disposition known | **Met** — N/A deferred |
| Item D complete | **Not required** to start Item C |
| Product IA for EmployeeRead | **Not issued; not required** |

**Ready for bounded execution:** **Yes** — next prompt may execute Item C only.

---

## 5. Execution Guardrails

1. Execute **only** after this authorization artifact exists (**now true**).
2. Preserve Item B deferral — no EmployeeRead code; no T049–T052 `[x]` as complete.
3. Do **not** start Item D during Item C (no catalog / `spec.md` / wholesale status sync).
4. No scope expansion beyond T053–T058 as defined in §2.
5. Prefer evidence over claim: name exact Pint/PHPStan commands and test paths in the execution report.
6. Windows: use `php vendor/bin/pint` and `php vendor/bin/phpstan …` (not bare `vendor/bin/phpstan` without `php`).
7. If PHPStan/Pint surfaces defects **outside** Employee module paths, do **not** expand into unrelated modules under this IA — report blocker instead.
8. HALT after Item C completion evidence — do not auto-start Item D.

---

## 6. Expected Completion State

Item C is **complete** when an execution report records **`SPEC03_ITEM_C_COMPLETED`** and all of the following exist:

| Evidence | Named artifact / criterion |
| -------- | -------------------------- |
| BT-05 | `tests/Architecture/EmployeeSupplierBoundaryTest.php` pass (command named) |
| Pint | Exact command + clean Employee-path result |
| PHPStan | Exact `php vendor/bin/phpstan …` command + **0 errors** on `app/Modules/Employee` |
| README | `app/Modules/Employee/README.md` updated incl. EmployeeRead **deferred at Spec03 close** + current port bindings |
| T057 | Scenarios **1–8** pass evidence; Scenario **9 = N/A — deferred** |
| T058 | Scope-audit statement in execution report (and optional minimal Phase 8 note) |
| Negative | No EmployeeRead implementation; no UI; no Item D catalog/`spec.md` sync |

**After completion:**

| Field | Expected |
| ----- | -------- |
| Item C | Completed; this IA exhausted for Phase 8 polish |
| Item B | Still deferred |
| Item D | Still unauthorized until separate authorization |
| Next | Authorize / execute Item D status sync only after Item C completion report |
| `SPEC03_CLOSED` | **Not** yet declareable |

---

## Explicit Non-Authorization

This artifact does **not** authorize Item D, EmployeeRead, UI work, Request Dependent live, live Allocation, or Spec03 closure declaration.

---

## Document Control

- Version: 1.0.0  
- Status: **`SPEC03_ITEM_C_AUTHORIZED`**  
- Selected item: Item C — Phase 8 polish (T053–T058; Scenario 9 N/A)  
- Owner: Governance Review  
- Last Updated: 2026-07-12  
- Checkpoint: `batch-b.spec03-item-c-authorization`

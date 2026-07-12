# Scope Redefinition — Request List Detail Navigation

**Artifact type:** Executable scope redefinition (non-authorizing)  
**Decision date:** 2026-07-11  
**Checkpoint:** `request-list-detail-navigation-scope-redefinition`

This artifact redefines scope after repeated Implementation Authorization denials. It does **not** authorize implementation, create implementation tasks, or modify code.

Immediate governing input: [request-list-detail-navigation-authorization-denial-analysis.md](./request-list-detail-navigation-authorization-denial-analysis.md) (v2 — IA v4 denial: owner In Scope mapped to already-satisfied core; empty `authorized-scope`; no invented polish).

---

## 1. Status

`REQUEST_LIST_DETAIL_NAVIGATION_SCOPE_REDEFINITION_REQUIRED`

---

## 2. Decision Basis

`D-01 = CONTINUE_WITH_SCOPE_REDEFINITION`

Product value is **not** rejected. The work item remains relevant to the core Request lifecycle. Prior denials were authorization-safety failures of **scope form** (outcome language without a distinct, gap-bound executable change set), not rejection of list→detail navigation as a capability.

---

## 3. Redefinition Question

`What is the minimal executable scope for Request List Detail Navigation that can be evaluated safely for implementation authorization?`

**Answer:** A **gap-fill-only** Presentation package that (a) defines a fixed end-to-end **capability inventory** for Request List → Request Detail read navigation, and (b) authorizes coding **only** for inventory items proven missing or broken at Implementation Authorization time — never for speculative polish or for re-implementing already-complete surfaces.

---

## 4. Approved Objective

Establish and/or restore a complete, read-only Request List → Request Detail navigation journey for the owning employee, using only existing approved Request read data and existing domain/Application boundaries, by implementing **only** the minimal Presentation (and strictly necessary supporting) changes required to close proven inventory gaps — without mutation, workflow, integration reopen, or UI polish invention.

---

## 5. Executable In-Scope Boundary

Minimal items that must exist for the capability end-to-end (**capability inventory**). Future Implementation Authorization may activate coding **only** against proven gaps in this inventory.

### 5.1 Capability inventory (must exist)

1. **List entry action** — On the authenticated Request List surface, each eligible request row exposes an explicit navigation control that targets Request Detail (historical/current form: **مشاهده** → `requests.show` via `wire:navigate` or equivalent approved Livewire navigation).  
2. **Detail destination** — An authenticated Request Detail / Show route and page exist and accept the list-provided request identifier (existing `requests.show` / `RequestShowPage` or equivalent already-approved destination).  
3. **Read-only detail presentation** — Detail renders using existing approved Request read data (`RequestReadContract` / equivalent prepared summary + ownership assertion already used by show). No new read models required for this package.  
4. **List read-only discipline** — List remains free of request mutation affordances (submit / cancel / approve / reject / edit) other than navigation (and existing non-mutating list UX such as refresh/filter/sort/pagination already outside this package’s change mandate).  
5. **Regression evidence** — Feature coverage exists (or is added only if missing) that asserts owned list→detail navigation and that the list does not introduce mutation for this capability.

### 5.2 Executable change authority (authorization-safe)

Allowed **only if** Implementation Authorization later records a non-empty `authorized-scope` that names **concrete missing or broken** inventory items:

- Add or repair the list-row navigation control and its target wiring strictly as needed for §5.1.1–§5.1.2.  
- Add or repair read-only detail consumption wiring strictly as needed for §5.1.3 (reuse existing contracts; no new business APIs).  
- Add or repair feature tests strictly as needed for §5.1.5.  
- Touch only files/components strictly necessary for those gap fills (typically Request List Blade / list Livewire presentation and, only if broken, show presentation wiring — not Domain redesign).

**Not executable under this redefinition:** Restating “implement list→detail navigation” as a blank mandate when §5.1 is already complete in-repo. That form produced empty `authorized-scope` and is forbidden.

---

## 6. Explicit Out-of-Scope Boundary

Excluded from this redefined package:

- request mutation  
- create/edit/update behavior  
- workflow transitions  
- approval actions  
- notifications  
- allocation changes  
- employee integration changes  
- dependent integration changes  
- new business rules  
- write-side behavior  
- unrelated UI improvements  
- speculative refactoring  
- speculative UI polish (whole-row click, stronger CTA redesign, mobile/a11y polish, visual redesign) unless separately selected and verbatim-scoped outside this package  
- Request Show layout/field/authorization redesign beyond existing frozen read destination  
- new Domain / Application / Infrastructure contracts or DTOs  
- reopening Spec03 Batch 1b, Spec04–Spec07, EmployeeRead, Dependent live, Allocation live  
- inventing residual work to fill an empty change set  

---

## 7. Authorization-Relevant Constraints

For any future Implementation Authorization Decision against this redefined scope:

- **Read-only only** — no mutation, no write-side APIs.  
- **Existing data only** — consume existing approved Request read contracts / prepared summaries; no new ownership/capability authority in UI.  
- **No domain expansion** — Domain and Application mutation boundaries unchanged.  
- **Minimal supporting changes only** — Presentation gap-fill; no speculative refactoring.  
- **Gap-bound `authorized-scope`** — If granted, `authorized-scope` must list concrete missing/broken inventory items and intended file/component targets; if inventory is fully satisfied, do **not** activate coding (`authorization-status` not active / deny or close-as-satisfied disposition).  
- **No polish invention** — polish remains Out of Scope.  
- **Feature Contract** — prior `FEATURE_CONTRACT_NOT_REQUIRED` for presentation/read navigation remains applicable unless a future residual invents contractable new behavior (not intended here).

---

## 8. Residual Risks

| Risk | Why it can still block authorization |
| ---- | ------------------------------------ |
| Inventory already complete | Feature Analysis / closeout evidence may show §5.1 already satisfied → IA may correctly deny coding or require close/defer rather than grant empty scope |
| Reselection vs closeout tension | Manual reselection claimed incomplete journey while repo evidence shows completion — IA must not invent polish to resolve tension |
| Ambiguous “residual gap” language | Owner residual intent without naming a **distinct** missing inventory item recreates empty `authorized-scope` |
| Duplicate contract/lock paths | Historical `docs/features` vs `docs/ui` paths may confuse residual governance; not resolved by this redefinition |
| Misreading readiness as grant | Authorization Review readiness ≠ Implementation Authorization |

---

## 9. Next Governance Step

`Prepare Authorization Review for redefined executable scope`

---

## 10. Explicit Non-Authorization

`This artifact redefines scope only and does not authorize implementation.`

---

## 11. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Only this scope-redefinition artifact was created:

- `.specify/docs/handoff/request-list-detail-navigation-scope-redefinition.md`

---

## Traceability

| Artifact | Role |
| -------- | ---- |
| Denial analysis v2 | Immediate governing input — empty scope / already-satisfied core / no polish |
| IA decision v4 | Denial source — no safe new `authorized-scope` |
| Owner scope revision `D-01 = APPROVE_RESIDUAL_SCOPE` | Product residual intent preserved; prior form insufficient for IA |
| This decision `D-01 = CONTINUE_WITH_SCOPE_REDEFINITION` | Continue via executable gap-fill scope form |
| Feature Analysis / Review / Contract Decision | Inventory evidence + boundaries preserved |

---

## Document Control

- Version: 1.0.0  
- Status: **`REQUEST_LIST_DETAIL_NAVIGATION_SCOPE_REDEFINITION_REQUIRED`**  
- Decision basis: **`D-01 = CONTINUE_WITH_SCOPE_REDEFINITION`**  
- Work item: `Request List Detail Navigation`  
- Owner: Governance Review  
- Last Updated: 2026-07-11  
- Checkpoint: `request-list-detail-navigation-scope-redefinition`

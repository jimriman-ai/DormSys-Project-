---
artifact: next_work_selection
status: SELECTION_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
selection_decision: NEXT_WORK_SELECTED
selected_work_item: Spec04 Auth integration residual readiness review
selection_round: post_spec04_checkin_retirement
date: 2026-07-12
---

# Next Work Selection (Revisit)

**Artifact type:** Work selection (non-authorizing)  
**Status:** `SELECTION_RECORDED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

This artifact replaces the prior selection that chose Check-in ↔ Dormitory readiness (now executed and closed with `NO_FURTHER_ACTION_RECOMMENDED` / residual retired). It does **not** authorize implementation, reopen closed Spec04 residuals, start UI coding, or begin a governance-repair cycle.

---

## A. Current Project State

| Topic | Posture |
| ----- | ------- |
| Spec04 Allocation Assignability | **CLOSED** (`SPEC04_RESIDUAL_CLOSED` / `FULLY_CLOSED`) |
| Spec04 Check-in ↔ Dormitory residual | **RETIRED_FROM_ACTIVE_SPEC04_TRACKING** / `CLOSED_NO_FURTHER_ACTION` |
| Spec04 repair loop | **Finished** — no further Spec04 assignability/check-in residual execution |
| Spec04 Backend | **CLOSED** (`SPEC04_BACKEND_CLOSED`) |
| Spec04 Product | **PENDING_RESIDUAL** for remaining open items only (Auth, UI, and other deferred non-retired items) |
| Prior selection outcome | Check-in readiness completed → reconciliation retired Spec04 Check-in tracking; Spec07 not reopened |
| Operating mode | `FEATURE_AND_SPEC_COMPLETION_MODE` |

Spec04 is no longer in a repair loop. Remaining Spec04 Product residuals that still signal open deferred work are primarily **Auth integration** and **Dormitory UI** (plus lower-priority deferred exclusions such as workflow/events/HTTP that lack a current packet).

---

## B. Candidate Work Items

| Candidate | Source artifacts | Dependency / readiness | Expected value | Key risks / blockers | Frozen / sensitive? |
| --------- | ---------------- | ---------------------- | -------------- | -------------------- | ------------------- |
| **Spec04 Auth integration residual readiness review** | Ownership D3 (`SPEC02_IDENTITY`); Spec04 residual table Auth row `DEFERRED_TO_FUTURE_WAVE`; catalog open residuals Auth/UI; nomination Auth `NEEDS_ANALYSIS` | Ownership **resolved** (D3); Spec02 Frozen Wave 1A; packet/IA **missing** | High — clarifies Dormitory-surface auth vs Identity foundation; unlocks later UI intake | Spec02 freeze; must not smuggle OA-02-01 / Livewire admin / Spec02 unfreeze | **Yes** — Spec02 Identity freeze |
| Spec04 Dormitory UI feature intake | Ownership D4 (`INDEPENDENT_UI_FEATURE`); UI triage `dormitory-admin-ui` **blocked** (no product auth) | Product UI authorization **absent**; Auth residual still open | High user visibility | This selection forbids starting UI implementation; triage requires product auth | **Yes** — UI Anti-Leak / presentation |
| Post-Spec03 EmployeeRead (T049–T052) | Spec03 closure; Item B deferred; nomination `DEFERRED` | No evidenced in-app consumer mandate + separate IA | Medium (integration) | Closed Spec03 reopen without mandate | Spec03 closed |
| Request Dependent live / Notification mark-all | IRG/UI triage | **BLOCKED** (backend + product auth) | Variable | Explicit blockers | Multiple |
| Lottery `dormitory_id`→`bedId` debt | Assignability non-blocking note | Explicitly **not** automatic next; Spec06 hold | Medium | Spec06 authority gap | Spec06 hold |
| Spec06 / Spec11 new implementation | Catalog holds | **BLOCKED** | n/a | Must not reopen regularization | Authority gaps |
| Closed Spec04 Assignability / Check-in residuals | Closeout + reconciliation | **Closed / retired** | n/a | Must not reopen | Closed |

**Not candidates:** reopening Assignability; recreating Check-in↔Dormitory Spec04 work; inventing “Dormitory Runtime”; Spec07 silent reopen.

---

## C. Selection Assessment

With Assignability closed and Check-in Spec04 tracking retired, the highest-value **remaining Spec04 Product residual** that can advance safely as **non-authorizing discovery** is Auth integration:

- Ownership Decision **D3** already assigns Auth to `SPEC02_IDENTITY` (foundation), without authorizing Spec02 unfreeze or coding.
- Catalog and Spec04 residual table still list Auth as open/`DEFERRED_TO_FUTURE_WAVE`.
- Dormitory UI (D4) is higher visibility but **blocked** on product authorization and remains unsafe to select for execution in this step; Auth readiness is the natural prerequisite path.
- EmployeeRead / Dependent / lottery debt / Spec06–11 lack mandate or are held.

Same entry pattern as prior successful residual readiness reviews (Assignability, Check-in): **readiness/discovery first**, no contract/IA yet.

---

## D. Final Selection Decision

`NEXT_WORK_SELECTED`

| Field | Value |
| ----- | ----- |
| Selected work item | **Spec04 Auth integration residual readiness review** (Spec02-owned residual per D3; discovery/readiness only) |
| Reason | Ownership resolved (D3); residual still open in Spec04 Product PENDING_RESIDUAL; highest safe product-residual continuation after Check-in retirement; discovery can determine whether a real Spec04/Spec02 gap remains vs status-only deferral — without UI start or Spec02 unfreeze |
| Not selected — Dormitory UI | No product auth; selection step must not start UI; Auth residual still open |
| Not selected — EmployeeRead | Deferred at Spec03 close; no consumer mandate |
| Not selected — Dependent / mark-all / Spec06–11 / lottery debt | Blocked, held, or explicitly non-automatic |
| Not selected — Assignability / Check-in Spec04 residuals | **CLOSED / RETIRED** — must not reopen |

---

## E. Immediate Next Step

**Create a residual readiness review / discovery artifact** for Spec04 Auth integration (non-authorizing), analogous to:

- `.specify/docs/discovery/spec04-allocation-residual-readiness-review.md`
- `.specify/docs/discovery/spec04-checkin-dormitory-residual-readiness-review.md`

That review must:

- treat Spec02 Identity as access-control foundation owner (D3)  
- not unfreeze Spec02 or authorize OA-02-01 / Livewire admin  
- not start Dormitory UI  
- determine whether Auth residual is a real unresolved gap, already satisfied, documentation/status only, or blocked on Spec02 product/reopen decisions  

No contract or Implementation Authorization in that step.

---

## Required Final Decision Block

```text
NEXT_WORK_SELECTION

Decision:
NEXT_WORK_SELECTED

Selected Work Item:
Spec04 Auth integration residual readiness review

Selection Basis:
After Assignability close and Check-in Spec04 residual retirement, Auth remains the primary open Spec04 Product residual with ownership D3 (SPEC02_IDENTITY); discovery-only next step mirrors prior residual readiness pattern and avoids UI start, Spec02 unfreeze, and closed Spec04 reopen.

Immediate Next Step:
Create Spec04 Auth integration residual readiness review (non-authorizing discovery)

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Guardrails Confirmation

- No code changes  
- No Spec04 Assignability / Check-in artifact reopen  
- No contract / IA creation  
- No UI implementation  
- No broad planning cleanup  

---

## No-Change Confirmation

`No application, test, migration, catalog, contract, authorization, review, closeout, or Spec04 closed/retired residual files were modified.`

Only this artifact was updated:

- `.specify/docs/planning/next-work-selection.md`

---

## Document Control

- Version: 2.0.0 (post–Check-in retirement revisit)  
- Status: **`SELECTION_RECORDED`** / **`NEXT_WORK_SELECTED`**  
- Selected: Spec04 Auth integration residual readiness review  
- Last Updated: 2026-07-12

---
artifact: next_work_selection_gate
status: SELECTION_GATE_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
selection_round: post_spec02_structure_authorization_binding_reconciliation
recommended_next_gate: CORE_COMPLETION_WAVE_PLAN
date: 2026-07-13
---

# Next Work Selection Gate — Post Spec02 Structure Binding Reconciliation

**Artifact type:** Work selection / readiness gate (non-authorizing)  
**Status:** `SELECTION_GATE_RECORDED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

This gate identifies the next governance-valid planning step after Spec02 Dormitory Structure Authorization Binding completed its full lifecycle (implementation → review → limited closeout → catalog reconciliation). It does **not** authorize implementation, reopen Spec02, or convert candidates into execution.

---

## 1. Current Planning Baseline

| Topic | Posture |
| ----- | ------- |
| Spec02 bounded packet | `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` |
| Implementation / Review / Closeout | `COMPLETED` / `IMPLEMENTATION_ACCEPTED` / `RECORDED` |
| Catalog reconciliation | Performed — catalog v1.0.19 |
| Overall Spec02 | **Frozen — Wave 1A Complete** (not unfrozen) |
| Spec03 | `SPEC03_CLOSED` — not a reopen candidate |
| Spec04 Backend / Assignability / Check-in | CLOSED / CLOSED / RETIRED |
| Spec04 Product | `PENDING_RESIDUAL` — Auth UI/role-mapping remainder + UI still open |
| Main UI Feature Execution | Deferred; no product-authorized new intake candidate |
| Prior Auth readiness (2026-07-12) | Already executed; led to Spec02 binding chain now closed |

**Must not reopen:** Spec02 bounded packet; Spec04 Phases 1–4; Assignability; Check-in Spec04 tracking; Spec07.

---

## 2. Candidate Next Workstreams

| ID | Candidate |
| -- | --------- |
| C1 | Core Completion Wave Plan (formalize accepted roadmap) |
| C2 | Spec04 Auth residual post-binding status refresh (UI/role-mapping remainder) |
| C3 | Workflow activate vs defer decision (CD-010) |
| C4 | Dormitory UI readiness / product-auth path (`dormitory-admin-ui`) |
| C5 | Request Dependent live-path decision (IRG-conditioned) |
| C6 | Spec06 governance continuation (if Lottery remains product goal) |
| C7 | Spec03 reopen / Dependent entity delivery | 
| C8 | Spec02 further implementation / review / closeout |

---

## 3. Eligibility Assessment

| ID | Eligibility | Rationale |
| -- | ----------- | --------- |
| C1 | **Eligible now** | Roadmap already `CORE_COMPLETION_WAVE_ROADMAP_READY`; no formal plan artifact yet; sequencing lock needed before stream selection |
| C2 | **Conditionally eligible** | Structure Application PEP closed; Auth residual remainder (UI/Presentation/HTTP + role mapping) still open — refresh after Wave Plan (or as early Wave Plan item), not Spec02 reopen |
| C3 | **Conditionally eligible** | Catalog activation criteria unmet; requires explicit activate/defer decision — decision gate, not coding |
| C4 | **Blocked** | No product UI authorization; triage ineligible; Auth remainder + product auth prerequisites |
| C5 | **Conditionally eligible** | Only if first UI/product path requires Family live Dependent; IRG blocked otherwise; Spec03 itself closed |
| C6 | **Conditionally eligible** | Only if Lottery remains in Core Wave product goal; authority gap holds new Lottery impl |
| C7 | **Out of current scope** | Spec03 already `SPEC03_CLOSED`; do not reopen as if incomplete |
| C8 | **Out of current scope / Rejected** | Packet closed; review/closeout re-run prohibited |

---

## 4. Dependency / Decision Gates Still Required

| Decision | Needed before |
| -------- | ------------- |
| Formal Core Completion Wave Plan | Ordered evaluation of C2–C6 |
| Workflow activate vs defer | Any Workflow UI / engine work |
| Product auth for a named UI slug | Dormitory UI or other Main UI Feature Execution |
| Lottery in / out of Core Wave | Spec06 regularization continuation priority |
| Family-live Dependent required for first UI? | Request Dependent IRG re-entry |
| Role mapping / UI auth packets | Separate Spec02-owned or UI-feature IA (not this closed structure packet) |

---

## 5. Recommended Next Gate

```text
CORE_COMPLETION_WAVE_PLAN
```

**Why this gate (not implementation, not Spec02 reopen):**

1. Spec02 structure-binding lifecycle is fully settled — next action must be wave sequencing, not another Spec02 packet action.  
2. Prior Spec04 Auth readiness already ran and produced the now-closed Spec02 packet; re-selecting “Auth readiness” as if missing would be stale.  
3. Multiple conditionally eligible streams (Auth remainder refresh, Workflow decision, Spec06, Dependent-live) need an ordered plan before any single stream is selected for execution prep.  
4. Dormitory UI remains blocked — must not be next execution selection.

---

## 6. Rejected Immediate Actions

| Action | Reason |
| ------ | ------ |
| Spec02 implementation / review / closeout re-run | Packet closed; prohibited |
| Spec02 unfreeze / OA-02-01 / role mapping / UI auth coding | Outside closed packet; not authorized |
| Spec04 backend Phases 1–4 reopen | Backend CLOSED |
| Spec07 reopen | Fully Closed; Check-in retired without reopen |
| Spec03 reopen | Already `SPEC03_CLOSED` |
| Dormitory UI Feature Contract / implementation | No product auth; blocked |
| Main UI Feature Execution start | Deferred; triage has no authorized NEW_CANDIDATE |
| Spec06/Spec11 new implementation | Authority gaps; held |
| Treating Auth readiness as never-run | Already executed 2026-07-12 |

---

## 7. Follow-on Sequence (non-authorizing)

After `CORE_COMPLETION_WAVE_PLAN`:

1. Record Workflow activate vs defer decision (plan item).  
2. Spec04 Auth residual **post-binding** status refresh (structure PEP closed vs UI/role-mapping remainder).  
3. Spec06 in-scope decision for Core Wave (continue regularization vs defer).  
4. Dependent-live path only if plan asserts first UI needs Family live.  
5. Spec01/Spec05/catalog hygiene as P1 plan items.  
6. Re-run UI Candidate Readiness Triage only after product-auth prerequisites exist.  
7. Then a later `NEXT_WORK_SELECTION` may pick one stream for discovery/IA prep — **not** automatic execution.

---

## Required Final Decision Block

```text
NEXT_WORK_SELECTION_GATE

Baseline:
SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED
Catalog Reconciliation PERFORMED
Spec02 overall Frozen — Wave 1A Complete

Recommended Next Gate:
CORE_COMPLETION_WAVE_PLAN

Execution Authority:
NONE

Spec02 Reopen:
PROHIBITED

Implementation Start:
NOT AUTHORIZED
```

---

## Explicit Non-Authorization

This gate does **not** authorize coding, contracts, UI intake, Spec02 expansion, Spec04 residual implementation, Spec06/Spec11 new work, or Spec03 reopen.

---

## No-Change Confirmation

`No application, test, contract, or authorization implementation files were modified.`

Only this selection-gate artifact was created:

- `.specify/docs/planning/next-work-selection-gate-post-spec02-structure-binding.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`SELECTION_GATE_RECORDED`**  
- Recommended next gate: **`CORE_COMPLETION_WAVE_PLAN`**  
- Last Updated: 2026-07-13

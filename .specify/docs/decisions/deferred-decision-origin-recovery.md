---
artifact: deferred_decision_origin_recovery
status: DEFERRED_ORIGIN_RECOVERY_COMPLETED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
recommended_next_gate: PRODUCT_SURFACE_AUTHORIZATION_DECISION
date: 2026-07-13
---

# Deferred Decision Origin Recovery

**Artifact type:** Governance investigation (non-authorizing)  
**Status:** `DEFERRED_ORIGIN_RECOVERY_COMPLETED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Purpose:** Recover the **original decision reason** behind deferred, blocked, and residual items.  
**Principle:** `DEFERRED` ≠ `INCOMPLETE`. Missing implementation is not automatically a bug.

Does **not** authorize implementation, UI, Workflow, Lottery, Feature Contracts, or closed-spec reopen. Does **not** activate any stream.

**Upstream context:** Deferred portfolio disposition; Spec04 Auth residual requires product authority; product authorization gap triage — `NO_NAMED_PRODUCT_SURFACE_AUTHORIZED`.

---

## 1. Investigation Baseline

| Topic | Posture |
| ----- | ------- |
| Core Completion Wave | Active — product-auth gap path |
| Spec02 structure binding | Completed (bounded); Spec02 Frozen — Wave 1A Complete |
| Spec03 / Spec07 | Closed |
| Spec04 Assignability / Check-in | Closed / Retired |
| Spec04 Auth residual | Open — requires product authority |
| Workflow / Spec06 | Deferred / authority-held |

---

## 2. Deferred Origin Register

| Item | Current status | Original reason for deferral / blocking | Decision source | Still valid? | Human/product decision still required? | Recommended disposition |
| ---- | -------------- | ---------------------------------------- | --------------- | ------------ | -------------------------------------- | ----------------------- |
| **Workflow** | `WORKFLOW_REMAINS_DEFERRED` | Reusable orchestration deferred until duplication proven; CD-010 splits Request approval **state** from Workflow transition **rules**; catalog activation criteria (≥2 multi-stage workflows + shared transition behavior) unmet | **Architecture decision** (CD-010 + catalog Deferred Components) | **Yes** | No for keep-deferred; Yes only to **activate** later | `KEEP_DEFERRED` |
| **Spec06 Lottery** | Wave deferred; `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN`; new work held | Implementation ahead of map-backed Nomination/DA/IA; Option B documented exception; `AUTHORITY_NOT_AVAILABLE` | **Missing authority** (governance debt) + later **product** (wave inclusion deferred) | **Yes** | **Yes** — authority resolution + any new Lottery priority | `BLOCKED_PENDING_AUTHORITY` |
| **Spec04 Auth residual** | Open; `REQUIRES_PRODUCT_AUTHORITY` | Spec04 backend Phases 1–4 **explicitly excluded** Authorization/policies/roles/guards from delivered scope; residual tracked as deferred product concern (not cancelled) | **Architecture / Spec04 IA exclusion** (backend closeout §6) | **Yes** | **Yes** — named product surface + Auth packet scope | `REQUIRE_PRODUCT_DECISION` |
| **Role mapping** (`dormitory.structure.*`) | Deferred (keys registered, no grants) | Structure-binding packet **explicitly excluded** role grants; Spec02 Frozen; grants need separate Spec02 IA + product role model | **Architecture** (binding lock/closeout) + **Missing authority** for grants | **Yes** | **Yes** — after product surface / role audience defined | `MERGE_INTO_OTHER_STREAM` (Auth residual) / deferred until surface |
| **UI Authorization** (Presentation/Livewire/Blade) | Blocked | No product-authorized Dormitory UI surface; UI Anti-Leak forbids UI-invented authority; Presentation excluded from Spec04 backend closeout | **Missing authority** (product) + **Architecture** (UI Anti-Leak / closeout exclusion) | **Yes** | **Yes** — product surface authorization | `BLOCKED_PENDING_AUTHORITY` |
| **Dormitory UI** (`dormitory-admin-ui`) | Blocked / NOT_READY | Spec04 Phase H Livewire deferred from MVP; UI triage: not product-authorized; discovery `REQUEST_PRODUCT_AUTHORIZATION`; Employee UI grant consumed | **Architecture** (Phase H deferral) then **Missing authority** / **Product decision** (no successor auth) | **Yes** | **Yes** — product authorization for named slug | `BLOCKED_PENDING_AUTHORITY` |
| **OA-02-01** (auth UX / login) | Deferred (Spec02 Wave 1A) | Wave 1A scope explicitly out: no Fortify/session/login; auth UX deferred by OA-02-01; freeze record | **Architecture / Spec02 scope** (plan §8.1, freeze record) | **Yes** | **Yes** to reopen Spec02 auth UX | `KEEP_DEFERRED` |
| **Livewire admin (T035–T037)** | Deferred | Optional Wave 1A tail deferred at freeze; reopen requires catalog decision | **Architecture / Spec02 freeze** | **Yes** | **Yes** to unfreeze Spec02 presentation | `KEEP_DEFERRED` |
| **Full RBAC / Full Spec02 authorization** | Not complete | Wave 1A delivered RBAC **baseline** only; Spec02 Frozen; only bounded structure PEP packet closed later — not full platform auth | **Architecture** (Wave 1A freeze + bounded packet) | **Yes** | **Yes** for successive packets / unfreeze | `KEEP_DEFERRED` |
| **Spec11 Reporting** | `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN`; new work held | Claimed Design Approval not recoverable; authority treated as exception; UI (Operator Explorer/KPI) excluded from Spec11 IA | **Missing authority** (evidence gap) | **Yes** | **Yes** for new Reporting / Reporting UI | `BLOCKED_PENDING_AUTHORITY` (separate track from Spec04 Auth) |
| **HTTP / Policy / middleware (Dormitory)** | Deferred / blocked pending surface | Same Spec04 backend exclusion as Auth residual; no authorized admin HTTP surface | **Architecture** (closeout) + **Missing authority** (surface) | **Yes** | **Yes** after product surface | Merge into Auth residual after surface auth |
| **EmployeeRead (T049–T052)** | Deferred at Spec03 close | Explicitly deferred Post-Spec03; Spec03 closed without requiring it | **Architecture / Spec03 closure** | **Yes** | **Yes** for Post-Spec03 selection | `KEEP_DEFERRED` |
| **Request Dependent live path** | IRG-blocked / conditional | Spec03 closed; live stub replacement needs Integration Readiness; not required unless Family-live asserted | **Technical dependency** (IRG) + **Architecture** (Spec03 close) | **Yes** | **Yes** if product asserts Family-live need | `KEEP_DEFERRED` |
| **Main UI Feature Execution** | Deferred | No authorized NEW_CANDIDATE after Employee UI closeout | **Missing authority** / **Product decision** | **Yes** | **Yes** | `BLOCKED_PENDING_AUTHORITY` |
| **Spec03 reopen** | Closed | Spec03 closed as complete for its scope | **Architecture / governance closure** | **Yes** (remain closed) | Only if explicit reopen | `KEEP_CLOSED` |
| **Spec04 Assignability / Check-in** | Closed / Retired | Residuals completed or retired by readiness | **Architecture / residual closeout** | **Yes** | No | `KEEP_CLOSED` / `RETIRE` |

---

## 3. Decision Evidence

| Item | Primary evidence paths |
| ---- | ---------------------- |
| Workflow | `catalog-decisions.md` CD-010; `spec-catalog.md` Deferred Components (activation criteria); `.specify/docs/decisions/workflow-activate-vs-defer-decision.md` |
| Spec06 | `spec06-regularization-decision.md` Option B; `spec06-regularization-completion-notice.md` `AUTHORITY_NOT_AVAILABLE`; Spec06 wave inclusion decision |
| Spec04 Auth residual | `handoff/spec04-backend-closeout.md` §6; Auth residual readiness review; post-binding refresh; product decision; ownership D3 |
| Role mapping | Spec02 structure-binding lock/closeout (no role grants); catalog Spec02 notes |
| UI auth / Dormitory UI | Spec04 plan/tasks Phase H deferral; `governance-next-candidate-triage.md`; `next-ui-feature-authorization-discovery.md`; UI Anti-Leak contract |
| OA-02-01 / Livewire admin | `specs/002-identity-access/plan.md` §8.1 + Freeze Record |
| Full RBAC | Spec02 Wave 1A freeze + bounded structure packet non-claims |
| Spec11 | `decision/spec11-authority-resolution-decision.md`; catalog Spec11 notes |
| EmployeeRead / Dependent | Spec03 closure handoff; Core Completion Wave Plan W6 |
| Main UI | Product authorization discovery after Employee closeout |

---

## 4. Unknown Decision Debt

| Gap | What is unknown | Severity | Notes |
| --- | --------------- | -------- | ----- |
| Next product-surface **owner** | Who may authorize the next Auth/UI slug | Medium | Catalog Authority Map historically incomplete for next-spec selection; gap triage already flagged |
| Original “why Phase H first vs Auth first” product ranking | Exact product priority ordering at Spec04 planning time | Low | Architecture MVP exclusion is clear; product ranking not required to keep Dormitory UI blocked |
| Spec06 SPEC06-C06 residual unknowns | Alternate authority chain remnants | Low | Regularization completed; new work still held by `AUTHORITY_NOT_AVAILABLE` |
| Exact first Auth packet contents after surface auth | Role-only vs Presentation vs HTTP | Medium | **Expected** — blocked until `PRODUCT_SURFACE_AUTHORIZATION_DECISION` |

No critical item has **Unknown** as the sole origin class. Origins are recoverable; remaining unknowns are **forward product decisions**, not lost history.

---

## 5. Human Decision Required List

| Decision needed | Why | Blocks |
| --------------- | --- | ------ |
| **Product surface authorization** (name + authorize or refuse a slug, e.g. Dormitory admin / Auth-readiness surface) | No named product surface authorized | Auth residual packet; UI authorization; Dormitory UI; role mapping scope |
| Product audience / role boundary for that surface | Role mapping cannot be designed without audience | Role grants |
| Spec06 Domain Authority resolution (separate) | `AUTHORITY_NOT_AVAILABLE` | New Lottery work |
| Spec11 authority / Reporting UI (separate) | Exception posture + UI exclusions | New Reporting / Reporting UI |
| Spec02 OA-02-01 / Livewire admin reopen (separate) | Wave 1A freeze | Identity auth UX |

**Not required to keep current deferrals valid:** Workflow activation decision (already deferred validly); Spec03/Assignability/Check-in reopen.

---

## 6. Synthesis

1. Most deferrals are **intentional architecture or scoped exclusions**, not abandoned incompleteness.  
2. The **active Core Completion Wave blocker** is **missing product authority for a named surface**, which freezes Spec04 Auth residual remainder, UI authorization, Dormitory UI, and practical role mapping.  
3. Spec06 and Spec11 are **separate missing-authority tracks**, not Spec04 Auth packet members.  
4. Workflow remains a **valid architecture deferral** under unmet activation criteria.

---

## 7. Recommended Next Governance Gate

```text
PRODUCT_SURFACE_AUTHORIZATION_DECISION
```

Aligns with product authorization gap triage. Origin recovery does **not** change that sequence or authorize Auth packet preparation.

---

## 8. Decision

```text
DEFERRED_ORIGIN_RECOVERY_COMPLETED
```

Origins for required portfolio items were recovered from repository governance evidence. Remaining human decisions are **forward product/authority decisions**, listed in §5 — not failures to recover historical reasons.

---

## Explicit Non-Authorization

This investigation does **not** authorize:

- application, test, contract, UI, workflow, lottery, or authorization implementation  
- stream activation; Feature Contracts; Spec reopen  
- invention of product surfaces or product authority  

---

## No-Change Confirmation

`No application, test, contract, UI, workflow, lottery, or authorization implementation files were modified.`

Only this governance artifact was created:

- `.specify/docs/decisions/deferred-decision-origin-recovery.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`DEFERRED_ORIGIN_RECOVERY_COMPLETED`**  
- Recommended next gate: **`PRODUCT_SURFACE_AUTHORIZATION_DECISION`**  
- Last Updated: 2026-07-13  
- Checkpoint: `deferred-decision-origin-recovery`

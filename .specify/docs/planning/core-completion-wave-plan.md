---
artifact: core_completion_wave_plan
status: WAVE_PLAN_COMPLETED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
upstream_gate: next-work-selection-gate-post-spec02-structure-binding.md
recommended_next_gate: HUMAN_OWNER_DESIGNATION_REQUIRED
wave_membership_outcomes: "Workflow DEFERRED; Spec06 DEFERRED; Surface employee-request-self-service AUTHORIZED; OWNER PENDING_HUMAN_DESIGNATION; Spec04 Auth BLOCKED → HUMAN_OWNER_DESIGNATION_REQUIRED next"
hygiene_artifact: .specify/docs/planning/core-completion-wave-hygiene-pass.md
deferred_portfolio_artifact: .specify/docs/planning/deferred-portfolio-review-and-disposition.md
auth_residual_product_decision: .specify/docs/spec04/spec04-auth-residual-product-decision.md
product_authorization_gap_triage: .specify/docs/decisions/product-authorization-gap-triage.md
date: 2026-07-13
---

# Core Completion Wave Plan

**Artifact type:** Wave-level planning / sequencing (non-authorizing)  
**Status:** `WAVE_PLAN_COMPLETED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Upstream:** `.specify/docs/planning/next-work-selection-gate-post-spec02-structure-binding.md` (`RECOMMENDED_NEXT_GATE: CORE_COMPLETION_WAVE_PLAN`)

This plan defines Core Completion Wave membership, eligibility, dependencies, and non-executing gate sequence. It does **not** authorize implementation, reopen closed packets, create product scope, or convert candidates into approved execution streams.

---

## 1. Planning Baseline

| Topic | Settled posture |
| ----- | --------------- |
| Spec02 bounded packet | `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` |
| Spec02 lifecycle | Implementation `COMPLETED`; Review `IMPLEMENTATION_ACCEPTED`; Closeout `RECORDED`; Catalog reconciliation `COMPLETED` (v1.0.19) |
| Overall Spec02 | **Frozen — Wave 1A Complete** — not unfrozen; not full RBAC / UI auth / role mapping / OA-02-01 |
| Spec03 | `SPEC03_CLOSED` — reopen out of wave |
| Spec04 Backend | `SPEC04_BACKEND_CLOSED` — Phases 1–4 not reopenable |
| Spec04 Assignability | `CLOSED` / `FULLY_CLOSED` |
| Spec04 Check-in residual | `ALREADY_SATISFIED` / `RETIRED_FROM_ACTIVE_SPEC04_TRACKING` |
| Spec04 Auth remainder | Still pending at **UI / Presentation / HTTP / role-mapping** scope (Application structure PEP packet closed under Spec02) |
| Spec04 UI | Deferred; `dormitory-admin-ui` blocked (no product auth) |
| Main UI Feature Execution | Deferred; no authorized NEW_CANDIDATE |
| Spec06 / Spec11 | `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` — new impl held |
| Spec07 | Fully Closed — do not reopen |
| Entry recommendation | `RECOMMENDED_NEXT_GATE: CORE_COMPLETION_WAVE_PLAN` (now executed by this artifact) |

**Hard non-claims preserved:** no full Spec02 authorization completion; no full RBAC; no UI auth completion; no role mapping completion; no OA-02-01 completion.

---

## 2. Core Completion Wave Candidates

| ID | Candidate stream | Wave membership |
| -- | ---------------- | --------------- |
| W1 | Workflow activate vs defer decision (CD-010) | **In wave** (decision membership) |
| W2 | Spec04 Auth residual post-binding status refresh | **In wave** (status/readiness) |
| W3 | Spec06 Core Wave inclusion / continuation decision | **In wave** (conditional membership decision) |
| W4 | Wave hygiene (Spec01 closure review; Spec05 final closeout readiness; catalog alignment) | **In wave** (P1 hygiene) |
| W5 | Dormitory UI readiness / product-auth path | **Tracked in wave; blocked for execution** |
| W6 | Request Dependent live-path decision | **Conditional membership only** |
| W7 | Spec03 reopen / Dependent entity “completion” | **Out of wave** |
| W8 | Spec02 further packet actions (impl/review/closeout) | **Out of wave** |
| W9 | Spec04 Assignability / Check-in reopen; Spec07 reopen; backend Phases 1–4 | **Out of wave** |
| W10 | Main UI Feature Execution / Feature Contract drafting | **Out of wave until product auth + triage** |

---

## 3. Eligibility Matrix

| ID | Status | Reason | Required prior decision(s) | May advance to later readiness gate? |
| -- | ------ | ------ | -------------------------- | ------------------------------------ |
| W1 | **Eligible now** | Pure governance/product decision; catalog activation criteria unmet but decision can still be **defer** | None beyond this Wave Plan | Yes — decision gate next |
| W2 | **Conditionally eligible** | Structure PEP closed; Auth remainder (UI/role-mapping/HTTP) still open in Spec04 Product | Wave Plan (this artifact); should follow W1 if Workflow outcome changes Auth/UI shape | Yes — status refresh / disposition gate |
| W3 | **Conditionally eligible** | Lottery may or may not be Core Wave product goal; authority gap holds new Lottery impl | Explicit product “Lottery in Core Wave?” decision | Yes — only as regularization/decision, not impl |
| W4 | **Eligible now** (parallel after W1–W3 decisions) | Hygiene does not unblock UI by itself | Prefer after membership decisions so catalog mirrors decisions | Yes — editorial/status only |
| W5 | **Blocked** | No product UI authorization; triage ineligible | Product auth for named slug; Auth remainder disposition; UI Anti-Leak compliance path | Readiness only after product auth — **not** execution |
| W6 | **Conditionally eligible** | Spec03 closed; Dependent live still IRG-blocked; needed only if first chosen UI/product path requires Family live | Product asserts Family-live needed for chosen stream | Yes — IRG/decision only if asserted |
| W7 | **Out of scope** | Spec03 already closed | n/a | No |
| W8 | **Out of scope** | Bounded packet closed; re-run prohibited | n/a | No |
| W9 | **Out of scope** | Closed/retired residuals; Spec07 fully closed | n/a | No |
| W10 | **Out of scope for now** | Main UI deferred; no authorized candidate | Product auth + triage PASS | Later wave / later gate only |

---

## 4. Dependency and Authority Map

| Authority / decision | Owner class | Required before |
| -------------------- | ----------- | --------------- |
| Workflow activate vs defer | Product / Architecture (CD-010) | Any Workflow engine or Workflow UI stream |
| Spec04 Auth remainder disposition | Spec02 Identity foundation (D3) + Spec04 residual tracking | Role-mapping packet, Presentation/HTTP auth packet, or Auth residual retirement claim |
| Product authorization for UI slug | Product | Dormitory UI or any Main UI Feature Execution |
| Lottery in Core Wave? | Product | Spec06 regularization continuation priority inside this wave |
| Family-live Dependent required? | Product / Architecture | Request Dependent IRG re-entry |
| Role mapping IA | Separate Spec02-owned human IA | Any `givePermissionTo` / role→`dormitory.structure.*` work |
| UI Feature Contract / IA | UI governance + product auth | UI implementation |
| Spec06/Spec11 new implementation IA | Separate authorization (authority gaps) | Any new Lottery/Reporting coding |
| Integration Readiness Gate | Architecture / Integration | Request Dependent live stub replacement |

**This Wave Plan grants none of the above.**

---

## 5. Recommended Wave Sequence

Non-executing gates only (ordered):

```text
0. CORE_COMPLETION_WAVE_PLAN                    ← this artifact (COMPLETED)
1. WORKFLOW_ACTIVATE_VS_DEFER_DECISION          ← COMPLETED → WORKFLOW_REMAINS_DEFERRED
2. SPEC04_AUTH_RESIDUAL_POST_BINDING_STATUS_REFRESH ← COMPLETED (actual order: after Workflow)
3. SPEC06_CORE_WAVE_INCLUSION_DECISION          ← COMPLETED → SPEC06_REMAINS_DEFERRED
4. CORE_COMPLETION_WAVE_HYGIENE_PASS            ← COMPLETED (see hygiene artifact)
5. DEFERRED_PORTFOLIO_REVIEW_AND_DISPOSITION    ← COMPLETED (stream selection deferred until Auth product decision)
6. SPEC04_AUTH_RESIDUAL_PRODUCT_DECISION        ← COMPLETED → SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY
7. PRODUCT_AUTHORIZATION_GAP_TRIAGE             ← COMPLETED → NO_NAMED_PRODUCT_SURFACE_AUTHORIZED
7a. DOMAIN_AUTHORITY_AND_ORGANIZATION_MODEL_DISCOVERY ← COMPLETED
7b. DOMAIN_STRUCTURE_EVIDENCE_CONSOLIDATION_GATE_DECISION ← ACCEPTED (mandatory intermediate)
8. DOMAIN_STRUCTURE_AND_RELATIONSHIP_EVIDENCE_CONSOLIDATION ← COMPLETED
9. HUMAN_DOMAIN_AUTHORITY_CLARIFICATION         ← PARTIAL / superseded for surface naming (owner still open)
9a. BUSINESS_OWNER_FORMALIZATION_AND_ARCHITECTURE_REVIEW ← COMPLETED → CONFLICTING_TERM; owner UNRESOLVED
10. PRODUCT_SURFACE_AUTHORIZATION_DECISION      ← COMPLETED → employee-request-self-service; owner UNRESOLVED
10a. PRODUCT_SURFACE_REFINEMENT_REQUIRED        ← refined to owner-authority clarification
10b. PRODUCT_SURFACE_OWNER_AUTHORITY_CLARIFICATION ← COMPLETED → OWNER_DECISION_REQUIRED_TO_PROCEED
10c. HUMAN_OWNER_DESIGNATION_REQUIRED           ← NEXT
11. CORE_COMPLETION_WAVE_STREAM_SELECTION       ← after owner designation + Auth readiness (still non-executing)
   (pick at most one eligible stream for discovery/IA-prep — still non-executing)
12. (Only if selected stream requires it)
   REQUEST_DEPENDENT_LIVE_PATH_DECISION
13. (Only after product auth exists)
   UI_CANDIDATE_READINESS_TRIAGE
14. Later: stream-specific readiness → Implementation Authorization (separate authority)
```

**Portfolio note:** Stream selection and Spec04 Auth packet prep must not run while owner field is `PENDING_HUMAN_DESIGNATION`. Named surface `employee-request-self-service` remains authorized. **Next** is `HUMAN_OWNER_DESIGNATION_REQUIRED`.

**As-executed note:** Original plan listed Spec06 before Auth refresh. After Workflow deferral, Auth residual refresh ran next (sequence-sensitivity), then Spec06 inclusion (deferred), then hygiene. Membership outcomes are unchanged in meaning.

**Sequence principles preserved:**

- Wave planning before stream selection before execution prep  
- Product/governance membership decisions before hygiene and stream selection  
- Readiness refreshes do **not** become implementation  
- Stream selection ≠ Implementation Authorization  
- UI triage only after product auth  

---

## 6. Deferred / Rejected Immediate Actions

| Action | Why rejected now |
| ------ | ---------------- |
| Spec02 reopen / impl / review / closeout re-run | Packet closed |
| Spec02 unfreeze; OA-02-01; Livewire admin; role mapping coding | Outside closed packet; separate authority required |
| Spec03 reopen | `SPEC03_CLOSED` |
| Spec04 Phases 1–4 / Assignability / Check-in reopen | Closed or retired |
| Spec07 reopen | Fully Closed |
| Dormitory UI Feature Contract / implementation | No product auth; blocked |
| Main UI Feature Execution | Deferred; no authorized candidate |
| Spec06/Spec11 new implementation | Authority gaps; Spec06 **out of Core Wave** (`SPEC06_REMAINS_DEFERRED`) |
| Workflow engine / Workflow UI | `WORKFLOW_REMAINS_DEFERRED` |
| Request Dependent live coding | IRG blocked; only if later stream asserts need |
| Treating Auth readiness (2026-07-12) as never-run | Already produced closed Spec02 structure binding |
| Claiming full RBAC / UI auth / role mapping complete | Explicit non-claims |

---

## 7. Wave Planning Conclusion (historical at plan creation)

```text
WAVE_PLAN_STATUS: COMPLETED
RECOMMENDED_NEXT_GATE_AT_PLAN_CREATION: WORKFLOW_ACTIVATE_VS_DEFER_DECISION
```

**Rationale (historical):** After formalizing wave membership and order, the first required gate was Workflow activate vs defer. That gate and subsequent membership/status gates are now completed — see §8.

---

## 8. Post-plan execution trail (hygiene mirror)

| Gate | Outcome | Artifact |
| ---- | ------- | -------- |
| Workflow activate vs defer | `WORKFLOW_REMAINS_DEFERRED` | `.specify/docs/decisions/workflow-activate-vs-defer-decision.md` |
| Spec04 Auth residual post-binding refresh | `AUTH_RESIDUAL_REFRESH_STATUS: COMPLETED` — Auth residual **not** closed | `.specify/docs/spec04/spec04-auth-residual-post-binding-status-refresh.md` |
| Spec06 Core Wave inclusion | `SPEC06_REMAINS_DEFERRED` | `.specify/docs/decisions/spec06-core-wave-inclusion-decision.md` |
| Hygiene pass | `CORE_COMPLETION_WAVE_HYGIENE_STATUS: COMPLETED` | `.specify/docs/planning/core-completion-wave-hygiene-pass.md` |
| Deferred portfolio disposition | `DEFERRED_PORTFOLIO_DISPOSITION_COMPLETED` | `.specify/docs/planning/deferred-portfolio-review-and-disposition.md` |
| Spec04 Auth residual product decision | `SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY` | `.specify/docs/spec04/spec04-auth-residual-product-decision.md` |
| Product authorization gap triage | `PRODUCT_AUTHORIZATION_REQUIRES_HUMAN_DECISION` / `NO_NAMED_PRODUCT_SURFACE_AUTHORIZED` | `.specify/docs/decisions/product-authorization-gap-triage.md` |
| Domain authority / org model discovery | `DOMAIN_MODEL_DISCOVERY_COMPLETED` / `ORG_MODEL_STATUS: REQUIRES_HUMAN_CLARIFICATION` | `.specify/docs/discovery/domain-authority-and-organization-model-discovery.md` |
| Domain structure evidence consolidation gate | `Accepted` — mandatory intermediate before human clarification / product-surface auth | `.specify/docs/decisions/domain-structure-evidence-consolidation-gate.md` |
| Domain structure / relationship evidence consolidation | `DOMAIN_STRUCTURE_EVIDENCE_CONSOLIDATION_COMPLETED` — entity map COMPLETED; org/visibility/actor binding remain open | `.specify/docs/discovery/domain-entity-relationship-authority-map.md` |
| Business owner formalization review | `CONFLICTING_TERM` / `BLOCK_PENDING_HUMAN_AUTHORITY` / owner field `UNRESOLVED` | `.specify/docs/decisions/business-owner-formalization-review.md` |
| Product surface authorization decision | `PRODUCT_SURFACE_AUTHORIZED` — surface `employee-request-self-service`; Spec04 Auth residual `REQUIRES_MORE_PRODUCT_AUTHORITY` | `.specify/docs/decisions/product-surface-authorization-decision.md` |
| Product surface owner authority clarification | `OWNER_DECISION_REQUIRED_TO_PROCEED` — owner `PENDING_HUMAN_DESIGNATION`; Spec04 Auth **BLOCKED** | `.specify/docs/decisions/product-surface-owner-authority-clarification.md` |

**Current recommended next gate (post–owner authority clarification):**

```text
RECOMMENDED_NEXT_GATE: HUMAN_OWNER_DESIGNATION_REQUIRED
```

**Wave membership summary (authoritative):**

- Workflow: **deferred** (out of execution-preparation path)  
- Spec06: **deferred** (out of Core Completion Wave path)  
- Spec04 Auth: Application PEP closed under Spec02; remainder **open** — Auth packet **BLOCKED** until human owner designation; named surface `employee-request-self-service`  
- Spec11 Reporting: **separate authority track** — not Spec04 Auth residual  
- Dormitory UI: **blocked** (no product auth for admin path; self-service surface does not authorize dormitory admin)  

---

## Required Final Decision Block

```text
CORE_COMPLETION_WAVE_PLAN

Wave Plan Status:
COMPLETED

Execution Authority:
NONE

Spec02 Bounded Packet:
CLOSED — DO NOT REOPEN

Overall Spec02:
Frozen — Wave 1A Complete

Spec03:
CLOSED — DO NOT REOPEN

Spec04 Closed Residuals:
DO NOT REOPEN

Workflow / Spec06 Wave Membership:
BOTH DEFERRED

Recommended Next Gate (current):
HUMAN_OWNER_DESIGNATION_REQUIRED

Authorized Surface (recorded):
employee-request-self-service (Owner field: PENDING_HUMAN_DESIGNATION; Spec04 Auth BLOCKED)

Implementation / UI / Role Mapping / OA-02-01:
NOT AUTHORIZED
```

---

## Explicit Non-Authorization

This plan does **not** authorize:

- application, test, contract, or authorization implementation changes  
- Spec02 expansion beyond the closed structure-binding packet  
- Spec04 residual coding or Dormitory UI intake  
- Spec06/Spec11 new implementation  
- Request Dependent live integration  
- Main UI Feature Execution  
- Auth packet preparation while Business Owner remains `UNRESOLVED`  

---

## No-Change Confirmation

Wave plan is a **status mirror** for Core Completion Wave sequencing. Application files are not modified by wave-plan updates.

---

## Document Control

- Version: 1.8.0 (owner authority clarification; next gate human owner designation)  
- Status: **`WAVE_PLAN_COMPLETED`**  
- Recommended next gate: **`HUMAN_OWNER_DESIGNATION_REQUIRED`**  
- Last Updated: 2026-07-13  
- Checkpoint: `core-completion-wave-plan`

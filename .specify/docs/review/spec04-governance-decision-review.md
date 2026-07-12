---
artifact_type: governance_decision_review
review_scope: spec04
validation_record_ref: wave-02-spec04-validation.record.md
authority_level: decision_preparation
execution_authority: none
mutation_permission: none
timestamp: 2026-07-12
---

# Spec04 Governance Decision Gate Review

## 1. Artifact Metadata

| Field | Value |
| ----- | ----- |
| Artifact path | `.specify/docs/review/spec04-governance-decision-review.md` |
| Artifact type | `governance_decision_review` |
| Review scope | `spec04` |
| Validation record | `.specify/governance/wave-02-spec04-validation.record.md` |
| Authority level | Decision preparation — structures options for the Decision Gate; **does not** finalize decisions |
| Execution authority | None |
| Mutation permission | None |
| Recorded | 2026-07-12 |

**Role of this artifact:** Prepare structured Decision Gate input for Spec04 lifecycle authority, status representation, Floor domain evolution, and residual scope. It does **not** authorize alignment, implementation, status edits, or catalog changes.

**Primary evidence basis:** Validation Record §§3–8; closeout boundary evidence cited therein; Authority Map ownership facts from `.specify/docs/catalog-decisions.md` (IA owner path; catalog as status mirror) referenced only as existing repository governance context — not as a new rule.

---

## 2. Summary of Conflicts

Spec04 currently presents **four overlapping lifecycle layers** that do not share a single synchronized label:

| Layer | What repository evidence shows | Conflict character |
| ----- | ------------------------------ | ------------------ |
| **Documentation / mirror** | `spec.md`, `plan.md`, contracts, `spec-catalog.md` still say Planning / implementation not authorized | Lag vs execution handoffs (`CONFIRMED`) |
| **Execution** | `IMPLEMENTATION_AUTHORIZED` → phase acceptances → `SPEC04_BACKEND_CLOSED` | Execution advanced; mirrors did not (`CONFIRMED`) |
| **Task progress** | `tasks.md` all unchecked; Status still “Implementation not authorized” | Contradicts closeout claims (`CONFIRMED`) |
| **Domain model** | OA-04-01 / FR-003–004: Floor as Room attribute; backend IA/closeout: Floor aggregate | Potential domain evolution without package amendment (`SUSPECTED`) |
| **Product scope** | Closeout closes backend Phases 1–4 only; Auth/UI/Allocation/CheckIn/etc. excluded | Partial closure vs whole-spec “planning hold” wording (`CONFIRMED`) |

**Core problem for the Decision Gate:** Without an agreed authoritative status model and residual-ownership rule, any later “alignment” edit risks either (a) implying full product closure that closeout disclaims, or (b) preserving a false “never authorized” posture that contradicts IA and `SPEC04_BACKEND_CLOSED`.

Validation Record decision-type IDs retained for traceability: `D-W02-04-01` … `D-W02-04-08`.

---

## 3. Decision Areas & Options

### Decision 1: Authoritative Artifact for Spec04 Status

**Question:** Which repository artifact(s) should be considered the authoritative source for Spec04’s *overall* lifecycle status?

#### Options

| ID | Option |
| -- | ------ |
| **A** | `spec.md` + `spec-catalog.md` (planning / initial spec state as written today) |
| **B** | Handoff chain (`IMPLEMENTATION_AUTHORIZED`, phase reviews, `SPEC04_BACKEND_CLOSED`) as execution-completion truth |
| **C** | A synthesized state combining A and B, reflecting different lifecycle phases (mirrors lag; handoffs own execution/closure facts) |
| **D** | Other — e.g., Authority Map–aligned rule: IA/closeout own execution truth; catalog/`spec.md` are non-owning status mirrors that must be updated only under an explicit alignment authorization |

#### Supporting evidence (Validation Record)

- Conflict 1: package/catalog Planning vs IA → `SPEC04_BACKEND_CLOSED` (§3)
- Authority Mapping (§4): Planning auth, IA, and backend closeout are documented in different artifact classes; no reconciled overall status statement
- Authority Map context (repository): Implementation Authorization owned under handoff path; `spec-catalog.md` described as status mirror / non-owning — relevant when weighing A vs B/C/D
- Wave 01 exclusion: Spec04 was outside baseline alignment (`CONFIRMED`)

#### Recommendation input (pros / cons — not a decision)

| Option | Pros | Cons / risks |
| ------ | ---- | ------------ |
| **A** | Matches current literal package/catalog text; zero interpretive synthesis | Treats stale mirrors as authority; contradicts IA + closeout; blocks honest residual planning |
| **B** | Matches execution evidence; aligns with handoff-based IA ownership pattern | “Overall” Spec04 is not fully closed; B alone can over-read backend closeout as product closure |
| **C** | Preserves both planning history and execution facts; supports layered labeling (Decision 2) | Requires explicit synthesis rules; maintainability depends on documenting which layer each artifact updates |
| **D** | Ties Spec04 to existing Authority Map vocabulary (IA owns execution; mirrors sync later) | Needs Decision Gate to state the synthesis/labeling rule; may still need Decision 2 for display form |

**Gate note:** Choosing A as *authority* (not merely as current text) would conflict with Validation Record Conflict 1 unless Decision Gate also voids or reinterprets IA/closeout — which closeout and IA do not invite.

---

### Decision 2: Lifecycle Labeling Strategy

**Question:** How should Spec04’s multi-faceted lifecycle be represented to avoid ambiguity?

#### Options

| ID | Option |
| -- | ------ |
| **A** | Single overarching status (e.g., “Partially Closed,” “Backend Complete”) |
| **B** | Differentiated statuses for lifecycle layers (e.g., Planning: Complete; Backend: Closed; Product feature / residual: Open) |
| **C** | Other — e.g., single catalog Status plus mandatory “Scope boundary” note pointing at closeout §6; or dual fields (`execution_status` / `product_status`) |

#### Supporting evidence (Validation Record)

- Conflict 7: partial backend closure vs whole-spec planning hold (§3, §5)
- Closeout §6 exclusions and §7 stop boundary: backend closed ≠ product closed (§5)
- Decision types `D-W02-04-02`, `D-W02-04-03`, `D-W02-04-08` (§8)

#### Recommendation input (pros / cons — not a decision)

| Option | Clarity | Consistency | Automation / mirror potential |
| ------ | ------- | ----------- | ----------------------------- |
| **A** | Simple for scanners; one cell in catalog | Weak — compresses residuals into one word; easy to misread as full closure or as “still planning” | Easier single-field sync; higher semantic error risk |
| **B** | Highest fidelity to Validation Record layers | Matches Wave 01 lesson (freeze ≠ all related work closed) if layers are named | Needs multi-field or structured note convention; harder to automate unless schema agreed |
| **C** | Depends on design | Can bridge A and B (one Status + mandatory residual pointer) | Catalog already uses Notes column — may reuse without new schema |

**Gate note:** Decision 2 should be chosen *with* Decision 1. If Decision 1 selects B alone without layered labels, residual exclusions may be erased in display. If Decision 1 selects C/D, Decision 2-B or 2-C is the natural companion.

---

### Decision 3: Floor Aggregate Evolution Resolution

**Question:** How should the discrepancy between OA-04-01 (Floor as Room attribute; no Floor aggregate) and the explicit Floor aggregate in backend design/closeout be addressed?

#### Options

| ID | Option |
| -- | ------ |
| **A** | Formally supersede OA-04-01 (and FR-003/FR-004 / US1 wording) in `spec.md` to reflect Floor aggregate |
| **B** | Maintain OA-04-01 as written; treat backend Floor aggregate as independent evolution not amending the spec decision |
| **C** | Document the refinement as domain evolution within Spec04 history/notes (without yet rewriting OA body — or with a dated “refined by” note pointing to design/closeout) |
| **D** | Other — e.g., reopen OA-04-01 as OQ; or amend `data-model.md`/contracts first, then OA |

#### Supporting evidence (Validation Record)

- Domain Evolution Trace §6: OA-04-01 DECIDED vs IA/closeout Floor hierarchy
- Conflict 3: catalog still “Open (planning): building/floor hierarchy” vs closed design
- Conflict 4: `SUSPECTED` package lag / evolution
- Open questions §7 items 2, 4

#### Recommendation input (impact — not a decision)

| Option | Domain clarity | Consistency risk |
| ------ | -------------- | ---------------- |
| **A** | Spec becomes authoritative with implemented hierarchy | Requires careful amendment of OA, FRs, entities, possibly `data-model.md`; must not silently reopen closed backend phases |
| **B** | Preserves historical OA text | Spec and code/design diverge permanently; Allocation/consumers may not know which model is normative |
| **C** | Preserves audit trail of evolution | Interim clarity depends on how strongly the “refined by” note is marked normative |
| **D** | Flexible | Adds process cost; may delay labeling Decisions 1–2 |

**Gate note:** Validation Record forbids treating Floor as already superseded. Decision 3 is required before any Spec04 package amendment that claims domain truth.

---

### Decision 4: Residual Scope Ownership & Tracking

**Question:** How should out-of-scope items from `SPEC04_BACKEND_CLOSED` (Auth, UI, Allocation integration, CheckIn wiring, events, HTTP, etc.) be managed going forward?

#### Options

| ID | Option |
| -- | ------ |
| **A** | Assign ownership to a future Wave / Spec (named or to-be-nominated) |
| **B** | Mark as deferred / unplanned (recorded hold without owner Spec) |
| **C** | Re-evaluate only when future feature development needs them (on-demand; no standing inventory beyond closeout §6) |
| **D** | Other — e.g., keep exclusions as Spec04 residual checklist under Spec04 id; or split (Allocation→spec07, UI→UI governance, Auth→Identity/Dormitory policy track) |

#### Supporting evidence (Validation Record)

- Backend Closeout Boundary Analysis §5 (exclusion list + stop boundary)
- Conflict 7: residuals exist but package/catalog do not distinguish them
- Open questions §7 items 5, 8
- Decision type `D-W02-04-05`

#### Recommendation input (clarity for remaining work — not a decision)

| Option | Clarity of remaining work | Future planning |
| ------ | ------------------------- | --------------- |
| **A** | High if targets named | Enables Discovery/IA chains; risk of wrong Spec assignment without dependency review |
| **B** | Medium — known deferred, no owner | Prevents false “fully closed”; may orphan work |
| **C** | Low standing inventory | Matches closeout “future specs / phases if needed”; risk of rediscovery loops |
| **D** | Potentially highest fidelity | Requires Decision Gate to approve split map; more artifacts |

**Gate note:** Closeout already states residuals are out of scope and not authorized. Decision 4 chooses *tracking ownership*, not authorization to implement residuals.

---

## 4. Proposed Next Steps Post-Decision

Conditional paths only — **not** authorized by this review:

| If Decision Gate selects… | Then a subsequent authorized step may include… |
| ------------------------- | ---------------------------------------------- |
| **1-C or 1-D** + **2-B or 2-C** | Documentary Alignment Phase for Spec04 mirrors (`spec-catalog.md`, `spec.md` Status/notes, optionally `plan.md` / Governance Traceability) under separate mutation authority — without reopening Phases 1–4 |
| **1-A** as authority | Further targeted discovery / HALT on mirror-as-authority vs IA conflict; do not treat package text as voiding closeout without explicit gate language |
| **1-B** alone without **2** layered labels | Alignment that sets a single “backend closed” style Status **plus** mandatory residual pointer (else product-closure ambiguity remains) |
| **2-A** | Catalog/spec single Status string selection; still need Decision 4 so residuals are not implied closed |
| **3-A** | Spec amendment task (OA-04-01 / FR-003–004 / entities / possibly data-model) — separate from status alignment; does not reopen backend implementation |
| **3-B** | Explicit Decision Gate statement that package OA remains normative and backend Floor is non-normative for product meaning (high conflict with closeout acceptance language — gate must confront that) |
| **3-C** | History/notes amendment task; optional later full supersession |
| **4-A** | Initiate Discovery / nomination for named future Spec(s) or Wave residual packets |
| **4-B** | Record deferred residual inventory (catalog note or Spec04 residual register) without IA |
| **4-C** | Leave closeout §6 as sole residual list; no standing Spec04 residual track |
| **4-D** | Produce residual ownership split map, then Discovery per owner |
| **Conflicts remain unresolvable at Gate** | Initiate further targeted discovery (e.g., codebase vs closeout inventory — Validation Record `UNKNOWN`; Floor supersession evidence hunt) before any alignment mutation |
| **Any path** | Do **not** treat this review as Implementation Authorization, Batch Permission, or mutation permission for Spec04 package/catalog |

### Coupling checklist for the Decision Gate session

Before closing the gate, record answers (or deferrals) for:

1. Authoritative status artifact / chain (Decision 1 → `D-W02-04-01`)
2. Labeling form (Decision 2 → `D-W02-04-02`, `D-W02-04-03`)
3. Floor OA disposition (Decision 3 → `D-W02-04-04`, `D-W02-04-08`)
4. Residual ownership (Decision 4 → `D-W02-04-05`)
5. Whether `tasks.md` / `governance_phase` are in-scope for a later alignment phase (`D-W02-04-06`, `D-W02-04-07`) — listed for completeness; not expanded as separate Decision sections above

---

## 5. Explicit Non-Decisions

This document:

- Does **not** select Options A–D for any Decision 1–4
- Does **not** change Spec04, catalog, handoffs, or tasks status metadata
- Does **not** authorize alignment, implementation, or residual work
- Does **not** create Implementation Authorization, Design Approval, or Batch Execution Permission
- Does **not** supersede OA-04-01 or claim full Spec04 product closure

---

## Document Control

| Field | Value |
| ----- | ----- |
| Version | 1.0.0 |
| Status | Decision preparation — awaiting Decision Gate |
| Upstream | `.specify/governance/wave-02-spec04-validation.record.md` |
| Downstream consumer | Spec04 Governance Decision Gate (human / governance review) |
| Last updated | 2026-07-12 |

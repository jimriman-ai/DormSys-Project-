---
artifact_type: governance_decision_record
decision_scope: spec04
authority_level: authoritative_decision
execution_authority: pending_alignment_plan
mutation_permission: none
status: DECISION_FINALIZED
review_ref: spec04-governance-decision-review.md
validation_record_ref: wave-02-spec04-validation.record.md
timestamp: 2026-07-12
---

# Spec04 Governance Decision Record (GDR)

## 1. Artifact Metadata

| Field | Value |
| ----- | ----- |
| Artifact path | `.specify/docs/decision/spec04-governance-decision.md` |
| Artifact type | `governance_decision_record` |
| Decision scope | `spec04` |
| Authority level | `authoritative_decision` |
| Execution authority | `pending_alignment_plan` |
| Mutation permission | `none` |
| Status | `DECISION_FINALIZED` |
| Upstream review | `.specify/docs/review/spec04-governance-decision-review.md` |
| Upstream validation | `.specify/governance/wave-02-spec04-validation.record.md` |
| Recorded / finalized | 2026-07-12 |

**Nature of this artifact:** This file is a **record** of Decision Gate outcomes for Spec04. It does **not** itself mutate Spec04 package files, the catalog, or the repository. Repository drift identified in the validation record remains present until a separately authorized alignment plan is issued and executed.

---

## 2. Executive Summary

This Governance Decision Record **records** how Spec04’s multi-layer status conflicts are **identified and understood** for governance purposes, based on the Wave 02 validation record and the Spec04 Governance Decision Gate Review.

It identifies Spec04 status as a composition of layered lifecycle states, records Spec04-local differentiated labels, identifies OA-04-01 as superseded by accepted backend domain evolution (with normative documentation update deferred to a future alignment phase), and identifies residual items as deferred scope for future waves/specs.

This record does **not** authorize or perform alignment, package amendment, catalog updates, or residual implementation. Next execution posture: `pending_alignment_plan`.

---

## 3. Final Decisions

### Decision 1 — Authoritative Artifact for Spec04 Status

| Field | Value |
| ----- | ----- |
| Review question | Which repository artifact(s) should be considered the authoritative source for Spec04’s *overall* lifecycle status? |
| Review options | A / B / C / D (see review) |
| **Selected option** | **C** (composition; Spec04-local recognition) |
| Trace ID | `D-W02-04-01` |

**Decision text (Safe Mode):**

Spec04 status is identified as a composition of layered lifecycle states. For the purpose of this specific Spec, the following mapping is recognized:

| Recognized layer (Spec04-local) | Identified source of understanding | Role |
| ------------------------------- | --------------------------------- | ---- |
| Backend / execution | Handoff chain culminating in `SPEC04_BACKEND_CLOSED` (and preceding `IMPLEMENTATION_AUTHORIZED` / phase acceptances) | Identifies authorized backend completion for Phases 1–4 |
| Product / residual scope | Closeout exclusions (§6) together with Decision 4 of this record | Identifies work not within backend closeout |
| Documentation / mirror | `spec.md`, `plan.md`, contracts, `spec-catalog.md` (current text) | Identifies presentational / inventory wording; recognized as lagging execution evidence |
| Task progress checklist | `tasks.md` (current text) | Identifies checklist state; recognized as lagging phase-acceptance evidence |
| Domain model (normative package vs accepted evolution) | Decision 3 of this record; accepted backend design/closeout hierarchy | Identifies domain-evolution disposition for Floor |

This composition model is a **Spec04-local recognition**. It does **not** define a new global metadata schema for all specs.

---

### Decision 2 — Lifecycle Labeling Strategy

| Field | Value |
| ----- | ----- |
| Review question | How should Spec04’s multi-faceted lifecycle be represented to avoid ambiguity? |
| Review options | A / B / C (see review) |
| **Selected option** | **B** (differentiated labels; Spec04-local) |
| Trace IDs | `D-W02-04-02`, `D-W02-04-03` |

**Decision text (Safe Mode):**

For Spec04 specifically, differentiated lifecycle labels are recognized to reflect its current state:

| Spec04-local label | Recognized value |
| ------------------ | ---------------- |
| Planning | Complete (planning artifacts authored; planning authorization historically issued) |
| Backend | CLOSED (`SPEC04_BACKEND_CLOSED` — Phases 1–4) |
| Product | PENDING_RESIDUAL (residuals identified under Decision 4; not full product closure) |
| Documentation / mirrors | DRIFT_PRESENT (package/catalog wording still reflects pre-IA / planning-hold text) |

These labels remain a **local observation for Spec04** until a global policy is established in Phase 5. This decision does **not** mandate a new global `governance_phase` or `product_status` metadata field for all specs.

---

### Decision 3 — Floor Aggregate Evolution Resolution

| Field | Value |
| ----- | ----- |
| Review question | How should the discrepancy between OA-04-01 (Floor as Room attribute) and the Floor aggregate in backend design/closeout be addressed? |
| Review options | A / B / C / D (see review) |
| **Selected option** | **A-oriented disposition** (supersession identified; package update deferred to alignment) |
| Trace IDs | `D-W02-04-04`, `D-W02-04-08` |

**Decision text (Safe Mode):**

OA-04-01 is identified as superseded by the accepted backend domain evolution (Floor Aggregate). A future alignment phase MUST update normative documentation to reflect this evolution.

This record does **not** amend `spec.md`, FR-003/FR-004, or related package text. The identification of supersession preserves decision-chain integrity relative to accepted backend design/closeout evidence; normative documentation update is deferred to a separately authorized alignment phase.

---

### Decision 4 — Residual Scope Ownership & Tracking

| Field | Value |
| ----- | ----- |
| Review question | How should out-of-scope items from `SPEC04_BACKEND_CLOSED` (Auth, UI, Allocation integration, CheckIn wiring, etc.) be managed going forward? |
| Review options | A / B / C / D (see review) |
| **Selected option** | **A** (future waves/specs ownership) |
| Trace ID | `D-W02-04-05` |

**Decision text (Safe Mode):**

Residual items (Auth, UI, Allocation integration, CheckIn wiring, HTTP/API surfaces, events/jobs unless separately approved, and other exclusions listed in `spec04-backend-closeout.md` §6) are identified as deferred scope. Ownership is transferred to future waves/specs; these items are not considered cancelled.

This record does **not** authorize residual implementation and does **not** name a specific successor Spec id (successor nomination remains a separate governance act).

---

### Deferred Decision Fields (review coupling checklist)

| Field | Recorded disposition |
| ----- | -------------------- |
| `tasks.md` progress representation (`D-W02-04-06`) | Identified as Documentation/task-progress drift relative to phase acceptances; update deferred to a future alignment phase under `pending_alignment_plan`. Not mutated by this record. |
| `governance_phase` metadata completeness (`D-W02-04-07`) | No Spec04-local mandate to invent a global `governance_phase` field. Differentiated labels under Decision 2 are the recognized Spec04 observation until Phase 5 global policy. |

---

## 4. Authority Mapping

Recognized Source-of-Truth understanding for Spec04 lifecycle layers (composition model — Decision 1). Identifying only; no repository mutation.

| Recognized Spec04 layer | Identified Source of Truth (understanding) | Notes |
| ----------------------- | ------------------------------------------ | ----- |
| Backend / execution | `.specify/docs/handoff/spec04-backend-closeout.md` (`SPEC04_BACKEND_CLOSED`); preceding IA and phase-acceptance handoffs | Backend CLOSED under Decision 2 |
| Product / residual | Closeout §6 exclusions + Decision 4 of this GDR | Product PENDING_RESIDUAL |
| Documentation / mirror | Current `specs/004-accommodation-resource/spec.md`, `plan.md`, contracts; `.specify/docs/spec-catalog.md` | DRIFT_PRESENT — mirrors lag; not mutated here |
| Task progress | Current `specs/004-accommodation-resource/tasks.md` | Drift identified; alignment pending |
| Domain model (evolution disposition) | Decision 3 of this GDR (OA-04-01 identified as superseded by Floor Aggregate); accepted backend hierarchy in IA/closeout | Normative package text update pending alignment |

| Display concern | Recognized understanding |
| --------------- | ------------------------ |
| Overall Spec04 status | Composition of the recognized layers above (Decision 1) |
| Labeling form | Spec04-local differentiated labels (Decision 2) |

---

## 5. Next Phase Authorization

| Statement | Binding? |
| --------- | -------- |
| This document **records** finalized Decision Gate outcomes for Spec04 | Yes (`DECISION_FINALIZED`) |
| `execution_authority` | `pending_alignment_plan` — next step is **planning** alignment, not acting on package/catalog/tasks |
| `mutation_permission` | `none` — this record does not mutate the repository |
| This document **does NOT** authorize implementation alignment of `spec.md`, `spec-catalog.md`, `tasks.md`, `plan.md`, or contracts | **Yes — explicit** |
| This document **does NOT** authorize Spec04 residual implementation | **Yes — explicit** |
| This document **does NOT** itself amend OA-04-01 or any Spec04 package content | **Yes — explicit** |
| Normative documentation update for Floor Aggregate | Identified as required in a **future alignment phase** (Decision 3); not performed by this record |
| Alignment plan issuance | Requires a **separate** alignment-plan artifact before any Spec04 documentary mutation |

---

## 6. Structural Linkage (Review → GDR)

| Review section | GDR section |
| -------------- | ----------- |
| §1 Artifact Metadata | §1 Artifact Metadata |
| §2 Summary of Conflicts (lifecycle layers) | §2 Executive Summary; §4 Authority Mapping |
| §3 Decision 1–4 | §3 Final Decisions 1–4 (finalized) |
| §3 coupling checklist | §3 Deferred Decision Fields |
| §4 Proposed Next Steps Post-Decision | §5 Next Phase Authorization (`pending_alignment_plan`) |
| §5 Explicit Non-Decisions | §5 (mutation_permission: none) |

---

## Document Control

| Field | Value |
| ----- | ----- |
| Version | 1.0.0 |
| Status | `DECISION_FINALIZED` |
| Owner | Governance Decision Gate |
| Last updated | 2026-07-12 |

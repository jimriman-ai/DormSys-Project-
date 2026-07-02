# Governance Invariants
Status: Canonical Constraint Reference Candidate
Purpose: Define the non-negotiable rules that must remain true across all DormSys governance activity
Scope: Program Governance, Design Governance, Implementation Authorization, Execution Governance

## 1. Intent

This document records governance invariants.
An invariant is a rule that does not depend on workflow preference, local interpretation, or temporary operating style.

Invariants are not suggestions.
They are the fixed control truths that all governance interpretation, review, approval, execution, and remediation must respect.

If any process, proposal, review path, prompt, or AI-generated recommendation conflicts with these invariants, the invariant prevails.

## 2. Invariant Set

### INV-001: Authority Comes From Canonical Authority Records Only

Authority may be read only from the canonical authority source defined by governance.
No artifact, summary, note, prompt, or derived document may create authority by implication.

Operational effect:
- Authority must resolve to the canonical authority map.
- If authority ownership cannot be resolved unambiguously, the process must halt.

### INV-002: Descriptive Artifacts Are Not Authority Artifacts

`spec.md`, `plan.md`, `tasks.md`, status headers, progress notes, and working summaries are descriptive.
They do not grant authority, execution permission, approval state, or governance ownership.

Operational effect:
- Existence of planning artifacts does not authorize implementation.
- Content inside planning artifacts does not define execution scope by inference.

### INV-003: Artifact Existence Does Not Equal Approval

The presence of a document, checklist, review note, design file, or task list does not imply:
- design approval
- implementation authorization
- batch execution permission
- blocker resolution
- governance phase completion

Operational effect:
- Approval must be explicit.
- Approval must be read from the canonical authority mechanism.

### INV-004: AI Is Advisory Only

AI may assist analysis, drafting, classification, and review.
AI may not create lifecycle stages, gates, authority, approval, blockers, or governance facts on its own.

Operational effect:
- AI output is proposal until adopted by human governance.
- AI-generated terminology has no governance force until canonically adopted.

### INV-005: Undefined Terms Have No Governance Force

Any label not explicitly defined in canonical governance has no authority effect.

Examples:
- unofficial phases
- ad hoc readiness labels
- derived review states
- convenience shorthand not formally adopted

Operational effect:
- Undefined labels may be described as observations only.
- Undefined labels may not be used to block, authorize, or route work.

### INV-006: New Governance Stages Require Formal Adoption

No new stage, gate, approval state, or governance layer may be used operationally unless formally adopted into canonical governance.

Operational effect:
- Naming a stage does not create the stage.
- Repeated informal use does not create validity.

### INV-007: Review Is Not Automatically a Gate

A review becomes a governance gate only when governance explicitly defines:
- trigger
- scope
- criteria
- approver
- continuation condition

Operational effect:
- Informational review and governance gate must be distinguished explicitly.
- Reviews without gate definition are non-authoritative.

### INV-008: Freeze Changes Governance Scope, Not Governance Existence

Freeze may stabilize a particular governance area.
Freeze does not terminate governance globally unless canonical governance explicitly states so.

Operational effect:
- Program governance may narrow after freeze.
- Execution governance continues where governance defines it.

### INV-009: No Cross-Layer Re-entry Without Authority

A completed governance layer may not be re-entered operationally unless canonical governance explicitly defines:
- trigger
- owning authority
- required record
- exit condition

Operational effect:
- Open concerns do not justify informal re-entry.
- Anxiety, uncertainty, or AI advice do not justify reopening a completed layer.

### INV-010: Blocking Requires Explicit Record

No dependency, blocker, or partial authorization may be treated as valid unless its record explicitly states:
- blocked scope
- blocking reason
- owner
- exit condition
- lifecycle update rule where applicable

Operational effect:
- Undefined blockers are not governance blockers.
- Broad or fuzzy blockers are invalid until explicitly recorded.

### INV-011: Evidence Before Interpretation

Evidence must be collected before governance interpretation.
Interpretation without identified evidence is a proposal, not a fact.

Operational effect:
- Unsupported conclusions must be marked non-grounded.
- Derived governance claims must point to canonical evidence.

### INV-012: Single Canonical Authority Source Per Decision

Each governance decision must resolve to exactly one canonical authority source.
Supporting documents may provide context but may not compete with the authority source.

Operational effect:
- Mixed authority basis is invalid.
- If multiple sources appear to compete, canonical conflict resolution must be applied before proceeding.

### INV-013: No Stop Condition Without Exit Condition

No governance stop, hold, block, freeze, or partial restriction may be used without a defined exit condition.

Operational effect:
- Every stop must have a release rule.
- Open-ended review loops are invalid governance behavior.

### INV-014: Human Adoption Is Required For Governance Change

Governance rules, stages, gates, control records, and adoption states require explicit human review and approval.

Operational effect:
- AI output may prepare a proposal.
- Only human-approved governance records may change operational governance.

## 3. Enforcement Rule

If any document, review, recommendation, or execution plan conflicts with one or more invariants:
1. mark the conflict explicitly,
2. halt any authority-dependent progression,
3. resolve the issue using canonical governance,
4. do not proceed by interpretation.

## 4. Output Rule

Any governance analysis must distinguish clearly among:
- canonical fact
- grounded interpretation
- proposed hardening
- advisory suggestion
- historical lesson

No output may merge these categories into a single authority layer.

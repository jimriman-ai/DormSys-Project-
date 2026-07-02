# Process Retrospective
Status: Governance Learning Record
Purpose: Convert real process failures into traceable institutional knowledge
Scope: Governance incidents, lifecycle drift, authority confusion, blocker misuse, stage creep, review loops

## 1. Intent

This document captures what happened, why it happened, what damage it caused, and how it was resolved.

It is not a rule document.
It is not an authority source.
It is not an approval record.

Its purpose is to preserve the incident-to-learning chain so future rules can be justified by real evidence.

## 2. Retrospective Record Format

Each entry must contain:

- Incident ID
- Date or Period
- Subject Area
- Lifecycle Position
- Incident Summary
- Observed Symptoms
- Root Cause
- Canonical Evidence
- Supporting Evidence
- Governance Impact
- Delivery Impact
- Resolution Used
- Residual Risk
- Candidate Rule IDs
- Notes

## 3. Entry Template
```text
Incident ID:
RET-XXX

Date or Period:
...

Subject Area:
...

Lifecycle Position:
...

Incident Summary:
...

Observed Symptoms:
...

Root Cause:
...

Canonical Evidence:
...

Supporting Evidence:
...

Governance Impact:
...

Delivery Impact:
...

Resolution Used:
...

Residual Risk:
...

Candidate Rule IDs:
...

Notes:
...

## 4. Normalized Root Cause Categories

Use one or more of the following categories:

- Stage Mixing
- Authority Inference
- AI Stage Fabrication
- Governance Layer Drift
- Evidence-Free Interpretation
- Review/Gate Confusion
- Blocker Without Record
- Missing Exit Criteria
- Mixed Authority Basis
- Rule Creep
- Documentation Mirror Misused as Authority

## 5. DormSys Incident Baseline

### RET-001
Incident ID: `RET-001`

Date or Period:
spec07 governance stall period

Subject Area:
DormSys governance and execution transition

Lifecycle Position:
Post-freeze / pre-implementation and early execution control ambiguity

Incident Summary:
Program governance concepts, design governance decisions, implementation authorization checks, and execution control were repeatedly mixed together, preventing stable transition into execution.

Observed Symptoms:
- repeated requests for new readiness checks
- informal re-entry into earlier governance reasoning
- uncertainty about whether execution could proceed
- repeated reinterpretation of existing documents for authority

Root Cause:
Stage Mixing

Canonical Evidence:
- authority must resolve through the canonical authority map
- planning artifacts cannot grant authority
- review gate exists only where governance defines it

Supporting Evidence:
repeated use of non-canonical readiness language and lifecycle restatement

Governance Impact:
execution path became ambiguous

Delivery Impact:
implementation stalled and decision velocity decreased

Resolution Used:
reality check and governance hardening analysis

Residual Risk:
same pattern may reappear in later specs unless encoded as rules

Candidate Rule IDs:
G-001, G-003, G-006, G-007, G-013

Notes:
Primary foundational incident

### RET-002
Incident ID: `RET-002`

Date or Period:
spec07 control analysis period

Subject Area:
AI interaction with governance workflow

Lifecycle Position:
Review and interpretation loop

Incident Summary:
AI-generated labels such as readiness or re-approval concepts began to function like unofficial lifecycle stages.

Observed Symptoms:
- new labels repeated across analysis
- terms treated as meaningful routing states
- governance discussion drifted toward invented phase logic

Root Cause:
AI Stage Fabrication

Canonical Evidence:
undefined terminology has no explicit authority unless canonically adopted

Supporting Evidence:
informal terms used in operational reasoning

Governance Impact:
non-canonical stages gained accidental influence

Delivery Impact:
additional review loops and process hesitation

Resolution Used:
rule extraction and invariant definition

Residual Risk:
future AI sessions may reproduce the same pattern

Candidate Rule IDs:
G-002, G-004, G-008, G-012

Notes:
Requires permanent control around AI-generated lifecycle language

### RET-003
Incident ID: `RET-003`

Date or Period:
spec07 execution-readiness ambiguity period

Subject Area:
Authority and permission interpretation

Lifecycle Position:
Design-to-implementation transition

Incident Summary:
Presence of planning artifacts and task structures was treated as partial evidence of execution readiness or implied authority.

Observed Symptoms:
- document existence used as signal
- task structure treated as authority hint
- implementation readiness discussed without authority resolution

Root Cause:
Authority Inference

Canonical Evidence:
planning artifacts cannot grant authority; scope may not be inferred from spec/plan/tasks

Supporting Evidence:
workflow reasoning repeatedly anchored to descriptive artifacts

Governance Impact:
approval source became blurred

Delivery Impact:
uncertain execution start conditions

Resolution Used:
single-source authority model and invariants

Residual Risk:
artifact-driven authority drift remains likely without explicit controls

Candidate Rule IDs:
G-003, G-004, G-013, G-014

Notes:
Core control failure

### RET-004
Incident ID: `RET-004`

Date or Period:
post-freeze interpretation period

Subject Area:
Governance continuity model

Lifecycle Position:
Program freeze to execution governance transition

Incident Summary:
Freeze was sometimes treated as the end of governance, and sometimes as reason to restart program-level review, causing inconsistent governance behavior.

Observed Symptoms:
- uncertainty about whether governance still applied
- uncertainty about whether earlier governance could be reopened
- transition to execution control lacked a stable model

Root Cause:
Governance Layer Drift

Canonical Evidence:
freeze does not end governance globally; governance continues with formal update requirements

Supporting Evidence:
conflicting interpretations of post-freeze responsibilities

Governance Impact:
execution governance and program governance boundaries blurred

Delivery Impact:
stalled transition and analysis paralysis

Resolution Used:
invariants plus decision-tree separation

Residual Risk:
future freezes may be misread without explicit control model

Candidate Rule IDs:
G-001, G-006, G-007, G-010

Notes:
Cross-layer control issue

### RET-005
Incident ID: `RET-005`

Date or Period:
blocker classification ambiguity period

Subject Area:
Dependency and partial authorization handling

Lifecycle Position:
Authorization and execution boundary

Incident Summary:
Potential blockers were discussed without explicit blocked scope, owner, or exit condition.

Observed Symptoms:
- blockers named informally
- uncertainty whether dependency was architecture-blocking or execution-only
- no explicit lifecycle record for partial state

Root Cause:
Blocker Without Record

Canonical Evidence:
partial status requires explicit blocked scope and blocking reason

Supporting Evidence:
dependency claims without formal record basis

Governance Impact:
blocking logic became subjective

Delivery Impact:
unnecessary waiting and escalation loops

Resolution Used:
explicit blocker control schema

Residual Risk:
future dependencies may again be over-classified or under-classified

Candidate Rule IDs:
G-009, G-015, G-016

Notes:
Needs owner and exit-condition enforcement


**4. `process-guardrails.md`**
```md
# Process Guardrails
Status: Governance Hardening Rule Set
Purpose: Convert recurring governance failure modes into operational control rules
Scope: All DormSys governance interpretation, approval, authorization, execution, and remediation activity

## 1. Intent

This document defines the rule layer that sits between retrospective learning and operational governance behavior.

It exists to prevent recurrence of:
- stage mixing
- authority inference
- AI-generated phase drift
- review/gate confusion
- blocker ambiguity
- governance re-entry without authority
- rule creep

This document does not become authoritative by existence alone.
Each rule must be classified and adopted before it is treated as binding.

## 2. Rule Classification

Each rule must be assigned exactly one level:

- L1 Canonical Rule
- L2 Approved Hardening Rule
- L3 Recommended Practice
- L4 Historical Lesson

No unclassified rule may be used operationally.

## 3. Rule Record Schema

- Rule ID
- Title
- Level
- Current Disposition
- Source Incident
- Problem Solved
- Rule Statement
- Canonical Evidence
- Supporting Evidence
- Applies To
- Governance Layer
- Owner
- Approval Authority
- Approval Date
- Review Date
- Notes

## 4. Rule Set

### G-001: No Unauthorised Re-entry
Problem Solved:
Prevents completed governance layers from being reopened informally.

Rule Statement:
No completed governance layer may be re-entered unless canonical governance explicitly defines the trigger, owner, record, and exit condition.

Recommended Level:
L1

### G-002: AI Is Advisory Only
Problem Solved:
Prevents AI-generated outputs from becoming governance by repetition.

Rule Statement:
AI outputs are advisory only until explicitly adopted by human governance.

Recommended Level:
L1

### G-003: Planning Artifacts Do Not Grant Authority
Problem Solved:
Prevents authority inference from descriptive artifacts.

Rule Statement:
`spec.md`, `plan.md`, `tasks.md`, and similar planning artifacts may not grant or imply design approval, implementation authorization, execution permission, or scope authority.

Recommended Level:
L1

### G-004: Document Creation Does Not Create Governance
Problem Solved:
Prevents false governance stages from appearing because a file exists.

Rule Statement:
Creating a document, checklist, review note, prompt, or task breakdown does not create a lifecycle stage, review gate, approval, or governance state.

Recommended Level:
L1

### G-005: Review Must Be Classified
Problem Solved:
Prevents review/gate confusion.

Rule Statement:
Every review used in governance must be explicitly classified as either:
- Informational Review
- Governance Gate

No third category may be used operationally.

A Governance Gate is valid only when canonical governance defines trigger, criteria, approver, and continuation rule.

Recommended Level:
L2

### G-006: Freeze Changes Scope, Not Governance Existence
Problem Solved:
Prevents freeze from being misread as governance termination.

Rule Statement:
Freeze narrows or stabilizes a governance scope but does not terminate governance globally unless canonical governance explicitly says so.

Recommended Level:
L1

### G-007: Existing Governance First
Problem Solved:
Prevents unnecessary process invention.

Rule Statement:
When process ambiguity appears, resolution must first be attempted inside existing governance before any governance-affecting change is proposed.

Recommended Level:
L2

### G-008: Undefined Terminology Has No Authority
Problem Solved:
Prevents shorthand, AI labels, and informal wording from becoming governance inputs.

Rule Statement:
Any undefined term, readiness label, or unofficial lifecycle phrase has no authority effect and may not be used to authorize, block, or reroute work.

Recommended Level:
L1

### G-009: Blocking Requires Explicit Record
Problem Solved:
Prevents informal blockers.

Rule Statement:
A blocker is governance-valid only when it includes explicit blocked scope, blocking reason, owner, and exit condition.

Recommended Level:
L1

### G-010: Governance Change Before Operational Use
Problem Solved:
Prevents unofficial process changes from being used live.

Rule Statement:
No governance-affecting process change may be used operationally before explicit approval or canonical incorporation.

Recommended Level:
L2

### G-011: Human Adoption Required
Problem Solved:
Prevents automated governance mutation.

Rule Statement:
No governance rule, gate, control, or authority interpretation becomes operationally valid without explicit human approval.

Recommended Level:
L1

### G-012: Proposal Classification Required
Problem Solved:
Prevents advisory content from being mistaken for binding rule.

Rule Statement:
Every governance proposal must be explicitly classified as canonical, hardening, recommended, historical, or rejected before use.

Recommended Level:
L2

### G-013: Single Source of Authority
Problem Solved:
Prevents mixed authority basis and local reinterpretation.

Rule Statement:
Every governance decision must identify exactly one canonical authority source.
Supporting documents may explain the decision but may not replace the authority source.

Recommended Level:
L1

### G-014: Evidence Before Interpretation
Problem Solved:
Prevents governance conclusions from outrunning evidence.

Rule Statement:
Evidence must be collected before governance interpretation.
Any interpretation without identified evidence must be treated as proposal only.

Recommended Level:
L1

### G-015: Every Blocker Must Have an Owner
Problem Solved:
Prevents blockers from becoming ownerless waiting states.

Rule Statement:
No blocker, dependency hold, or partial restriction is valid unless one accountable owner is explicitly identified.

Recommended Level:
L2

### G-016: Every Blocker Must Have an Exit Condition
Problem Solved:
Prevents indefinite stop conditions.

Rule Statement:
No blocker, pause, hold, or partial state may remain active without an explicit exit condition and release criterion.

Recommended Level:
L2

### G-017: Program Governance Does Not Repeat Inside Execution
Problem Solved:
Prevents execution from being trapped by repeated upstream governance questioning.

Rule Statement:
Once work is inside an authorized execution path, program-governance questions may not be reintroduced operationally unless canonical governance explicitly requires re-entry.

Recommended Level:
L2

## 5. Output Discipline

Any governance artifact derived from these rules must distinguish clearly among:
- grounded canonical rule
- approved hardening rule
- advisory recommendation
- historical lesson
- rejected proposal

## 6. Final Control

A governance system that turns every lesson into an unreviewed rule will reproduce drift at the rule layer.

Therefore:
- incidents create candidates
- candidates require evidence
- evidence requires classification
- classification requires human adoption
- only adopted rules may govern work

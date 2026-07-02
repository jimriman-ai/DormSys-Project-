# Governance Adoption Contract
Status: Rule Adoption Control
Purpose: Define how candidate governance rules become authoritative, provisional, advisory, archival, or rejected
Scope: All rules extracted from retrospectives or governance incidents

## 1. Intent

This contract prevents rule creep.
No candidate rule may become authoritative merely because it is sensible, repeated, or useful.

## 2. Adoption Outcomes

Each candidate rule must end in exactly one outcome:

- Canonical Rule
- Approved Hardening Rule
- Recommended Practice
- Historical Lesson
- Rejected

## 3. Required Evaluation Questions

For every candidate rule answer:

- What incident created the need for this rule?
- What exact failure mode does it prevent?
- What canonical evidence supports it?
- Is it already present in canonical governance?
- If not, is temporary hardening justified?
- Does it conflict with any canonical source?
- Does it add governance cost disproportionate to control value?
- Who owns this rule?
- What review date applies?

## 4. Adoption Constraints

### AC-001
No rule without incident trace

### AC-002
No rule without evidence

### AC-003
No implicit canonicalization by repetition or use

### AC-004
No mixed authority basis

### AC-005
No operational use before classification

### AC-006
No AI-only governance mutation

### AC-007
No blocker-related rule without owner and exit-condition logic

## 5. Rule Record Template
```text
Rule ID:
Title:
Status Level:
Current Disposition:
Origin:
Incident Reference:
Problem Solved:
Rule Statement:
Canonical Evidence:
Supporting Evidence:
Applies To:
Governance Layer:
Owner:
Approval Authority:
Approval Date:
Review Date:
Notes:

## 6. Adoption Decision Record

text
Decision ID:
Rule ID:
Decision Outcome:
Decision Basis:
Canonical Source:
Conflicts Found:
Owner:
Effective Date:
Next Review:
Notes:


**8. `governance-auditor-prompt.md`**
```md
# Governance Auditor Prompt
```text
You are acting as a Governance Hardening Auditor.

You are not a lifecycle designer.
You are not a process inventor.
You are not an authority source.

Your job is to audit governance artifacts, incidents, candidate rules, and execution-routing logic against canonical governance only.

MISSION

Determine whether each claim, rule, blocker, stage, or routing step is:
- canonically grounded,
- traceable to real evidence,
- correctly classified,
- non-conflicting,
- and safe for operational use.

NON-NEGOTIABLE RULES

1. Do not infer authority from `spec.md`, `plan.md`, `tasks.md`, status headers, progress notes, summaries, or prompts.
2. Do not infer scope from task structure or planning content.
3. Do not treat a review as a gate unless canonical governance explicitly defines it.
4. Do not treat document creation as governance creation.
5. Do not treat AI-generated labels as lifecycle states.
6. Do not use undefined terms as blockers, approvals, or routing states.
7. If a conclusion lacks canonical evidence, mark it `Not Grounded`.
8. If a rule is useful but not canonically established, mark it `Proposed Hardening Only`.
9. Every governance decision must identify exactly one canonical authority source.
10. If multiple sources appear to compete, mark `Authority Conflict`.

AUDIT FLOW

For each candidate item, perform:

Step 1: Identify the claim
Step 2: Identify the incident trace
Step 3: Identify canonical evidence
Step 4: Identify supporting but non-canonical evidence
Step 5: Identify exact authority source
Step 6: Check classification
Step 7: Check conflict risk
Step 8: Check drift risk
Step 9: Return adoption recommendation

VALID RECOMMENDATIONS

- Adopt as L1
- Approve as L2
- Retain as L3
- Archive as L4
- Reject
- Escalate for Human Clarification

OUTPUT FORMAT

# Governance Hardening Audit

## Canonical Documents Reviewed
- ...

## Audit Scope
- ...

## Rule-by-Rule Audit

### Rule: [ID / Title]

**Claim**
- ...

**Incident Trace**
- Status:
- Incident Reference:
- Notes:

**Evidence Review**
- Canonical Evidence:
- Supporting Evidence:
- Grounding Status: Grounded | Proposed Hardening Only | Not Grounded

**Authority Check**
- Single Canonical Authority Source:
- Authority Conflict Risk:
- Result:

**Classification Check**
- Recommended Level:
- Reason:

**Conflict Check**
- Status:
- Notes:

**Drift Check**
- Drift Type:
- Severity:
- Notes:

**Adoption Recommendation**
- Decision:
- Rationale:

## Cross-Rule Conflicts
- ...

## Items Missing Incident Support
- ...

## Items Missing Canonical Evidence
- ...

## Items Unsafe for Operational Use
- ...

## Final Verdict
- Adoption-ready | Partially ready | Not ready

STRICT FAILURE MODES TO FLAG

- stage creep
- rule creep
- authority inference
- review/gate confusion
- blocker without explicit record
- missing exit criteria
- mixed authority basis
- AI output treated as authority
- governance layer drift

FINAL BEHAVIOR

If evidence is insufficient, say so clearly.
If a rule is helpful but not grounded, keep it out of canonical status.
If the item creates new governance without authority, reject it.


**9. `governance-reality-check-prompt.md`**
```md
# Governance Reality Check Prompt
```text
You are performing a Governance Reality Check.

Your task is to determine the real governance position of the subject using canonical evidence only.

You must not propose remediation.
You must not create rules.
You must not invent lifecycle stages.
You must not infer authority from descriptive artifacts.

MANDATORY RULES

- Use canonical governance evidence only for authority conclusions.
- `spec.md`, `plan.md`, `tasks.md`, status headers, progress notes, summaries, and AI outputs are not authority sources.
- If a lifecycle label is not canonically defined, list it as non-canonical.
- If a blocker is not explicitly recorded with blocked scope and blocking reason, do not treat it as governance-valid.
- If authority ownership cannot be resolved to one canonical source, halt.

RETURN EXACTLY THIS STRUCTURE

# Governance Reality Check

## Canonical Documents Reviewed
- ...

## Subject
- ...

## Last Official Completed Stage
- ...

## Current Active Stage
- ...

## Next Authorized Step
- ...

## Canonical Authority Source
- ...

## Confirmed Blockers
- ...

## Confirmed Partial Constraints
- ...

## Non-Canonical Labels in Circulation
- ...

## Claims Rejected as Not Grounded
- ...

## Prohibited Assumptions
- ...

## Final Verdict
- Grounded and Clear
- Grounded but Blocked
- Partially Grounded
- Not Grounded
- Authority Conflict - Halt
- Lifecycle Ambiguity - Halt


**10. `governance-execution-gate-prompt.md`**
```md
# Governance Execution Gate Prompt
```text
You are validating whether execution may proceed.

You are not deciding architecture.
You are not reopening program governance.
You are not inventing readiness checks.

You must answer only whether the next execution step is authorized.

CHECKS

1. Is Design Approval explicitly present from the canonical authority source?
2. Is Implementation Authorization explicitly present from the canonical authority source?
3. If authorization is partial, are blocked-scope and blocking-reason explicitly recorded?
4. Is the requested work inside authorized-scope?
5. Has the current batch satisfied the required review gate and human approval condition for continuation?

RULES

- Do not infer authority from planning artifacts.
- Do not infer scope from task content.
- Do not classify a dependency as blocking without explicit record support.
- Do not introduce a readiness phase.

OUTPUT

# Execution Gate Check

## Subject
- ...

## Design Approval
- Present | Missing | Not Grounded

## Implementation Authorization
- Present | Missing | Not Grounded

## Authorization Shape
- Full | Partial | Not Grounded

## Authorized Scope Match
- Yes | No | Not Grounded

## Review Gate Continuation Status
- Satisfied | Missing | Not Applicable | Not Grounded

## Final Execution Decision
- Proceed
- Proceed Within Partial Scope
- Halt
- Halt - Authority Not Grounded
- Halt - Scope Not Authorized
- Halt - Review Gate Incomplete


**11. `governance-blocker-record-template.md`**
```md
# Governance Blocker Record Template
```text
Blocker ID:
BLK-XXX

Subject:
...

Lifecycle Position:
...

Status:
Active | Resolved | Rejected

Blocked Scope:
...

Blocking Reason:
...

Canonical Evidence:
...

Owner:
...

Exit Condition:
...

Resolution Condition:
...

Related Authorization State:
Full | Partial | Not Yet Authorized

Recorded By:
...

Approval Authority:
...

Recorded Date:
...

Resolved Date:
...

Notes:
...


**12. `governance-rule-register-template.md`**
```md
# Governance Rule Register Template
```text
Rule ID:
G-XXX

Title:
...

Status Level:
L1 | L2 | L3 | L4

Current Disposition:
Canonical | Approved Hardening | Recommended | Historical | Rejected

Origin:
...

Incident Reference:
...

Problem Solved:
...

Rule Statement:
...

Canonical Evidence:
...

Supporting Evidence:
...

Applies To:
...

Governance Layer:
Program | Design | Authorization | Execution | Cross-Layer

Owner:
...

Approval Authority:
...

Approval Date:
...

Review Date:
...

Conflicts:
...

Notes:
...


**13. `governance-review-classification.md`**
```md
# Governance Review Classification
Status: Review Control Standard
Purpose: Remove ambiguity between informational review and governance gate
Scope: All reviews referenced in DormSys governance or execution control

## 1. Rule

Every review must be classified as exactly one of the following:

- Informational Review
- Governance Gate

No third operational category exists.

## 2. Informational Review

Definition:
A review performed for visibility, quality input, or discussion.

Authority Effect:
None by default.

It may:
- surface concerns
- recommend changes
- suggest follow-up
- improve clarity

It may not:
- stop work by itself
- authorize work by itself
- reopen governance by itself
- create a lifecycle state by itself

## 3. Governance Gate

Definition:
A review with explicit governance-defined stop/go effect.

A Governance Gate is valid only when the following are defined:
- trigger
- scope
- criteria
- approver
- continuation condition

Authority Effect:
Operational only within the governance definition that creates it.

## 4. Misclassification Rule

If a review is referenced operationally but lacks the fields required for Governance Gate classification, it must be treated as Informational Review.

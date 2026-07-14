# Governance Artifact Discipline Method

## Purpose

This document is the operational method guide for how DormSys manages governance gaps, canonical decision records, supporting evidence, evaluator outputs, efficient human decision questions, question dependencies, and evidence-grounded answer options.

It does not replace the constitution. It does not resolve any open governance decision. It does not authorize Auth, UI, Spec completion, or Implementation.

## Core Rule

One unresolved governance decision boundary = one authoritative canonical resolution record.  
Supporting evidence may be distributed; decision ownership must have one authoritative record.  
Indexes and catalogs are pointers only.  
No unresolved decision may be resolved by inference.  
`STALE_BLOCKER` is escalation only, not authorization.  
Human decision questions should be optimized to close the maximum number of related dependent gaps without reducing clarity.  
The gap-to-decision relationship is many-to-many.  
One question may close multiple gaps.  
One gap may require multiple decisions.  
Questions may depend on earlier answers.  
Options should be grounded in project evidence without becoming inferred decisions.

## Source Of Truth

| Kind | Path | Role |
| --- | --- | --- |
| Binding governance rules | `.specify/memory/constitution.md` | Source of truth for Artifact Discipline and related rules |
| Operational method (this file) | `.specify/docs/governance/artifact-discipline-method.md` | How agents apply the rules |
| Canonical record for a gap | One file per unresolved decision boundary | Authority for status, pending human decision, and resolution |
| Supporting evidence | Clarifications, decisions, discovery, reviews | Distributed evidence only |
| Indexes / catalogs | e.g. `.specify/governance/decision-index.md` | Pointers only |

Do not create `.specify/memory/governance-rules.md`. Do not treat this method document as a competing constitution.

## Key Definitions

| Term | Meaning |
| --- | --- |
| Decision boundary | The single question that must be answered to resolve a governance uncertainty |
| Governance gap | Observed incompleteness that may contain one or more decision boundaries |
| Canonical record | The one authoritative file for an unresolved decision boundary |
| Supporting evidence | Any other artifact that informs, but does not own, the decision |
| Human authority | Explicit designation recorded by a human in the canonical record |
| Inference | Treating circumstantial or supporting evidence as a resolution |
| Gap bundling | Asking one structured question that closes multiple related gaps |
| Gap decomposition | Splitting one gap into distinct decision boundaries before asking |
| Question dependency | A question whose validity or options depend on a prior answer |
| `STALE_BLOCKER` | Escalation classification when a blocker persists past the no-change threshold |

## Method Overview

1. Identify the governance gap; apply Authority Layer Separation; then decompose into decision boundaries.  
2. Map primary gap, dependent gaps, root decision boundary, question dependencies, and downstream gates.  
3. Ensure exactly one canonical record owns the root (or each split) decision boundary.  
4. Gather supporting evidence as references only.  
5. Design MECE, evidence-grounded options with explicit governance impact.  
6. Build a question dependency graph; batch only independent questions.  
7. Ask humans using the optimized request format.  
8. Record answers (including partial answers) only in the canonical record.  
9. Re-run evaluator logic; do not authorize downstream work by inference.  
10. Escalate with `STALE_BLOCKER` when the threshold is met — still without authorizing progression.

## Canonical Record Rule

For each unresolved governance decision boundary there must be exactly one authoritative canonical resolution record.

- Supporting evidence may live in many files.  
- Decision ownership, current status, pending human decision, and final resolution are tracked only in the canonical record.  
- Agents must not create a parallel clarification, decision, or resolution artifact for the same unresolved decision boundary unless a human maintainer explicitly instructs it.  
- When new evidence appears, update or reference the existing canonical record instead of creating a competing file.

## Required Canonical Sections

Canonical records should use this section structure:

```text
# {ID} Canonical Decision Record

## Status
## Decision Boundary
## Current Evidence
## Supporting Evidence References
## Human Decision Required
## Resolution
## Impact
```

Keep the file concise. Put long-form analysis in supporting evidence, not in the canonical record.

## Status Rules

- Status remains `UNRESOLVED` until an explicit authoritative human decision is recorded in the canonical record.  
- Classifications such as `NO_CHANGE` or `STALE_BLOCKER` describe evaluator posture; they do not change the decision outcome.  
- Non-authorizing notices must remain clear while the decision is unresolved.

## Resolution Rules

- Resolution remains `PENDING` (or equivalent) until explicit human authority is present.  
- Agents must not write a resolved owner, scope, or authority value by inference.  
- Partial answers must be recorded; the canonical record must state which sub-decisions remain open.  
- A gap is fully closed only when all decision boundaries inside it are explicitly resolved.

## Human Authority Rule

Only an explicit human answer recorded in the canonical record may resolve a governance decision.

Presenting evidence-derived candidates as selectable options is not inference and does not resolve anything. Circumstantial participation (manager involvement, workflow presence, prior insufficient answers) never substitutes for designation.

## STALE_BLOCKER Rule

`STALE_BLOCKER` is an escalation signal only.

It does not:

- resolve the decision  
- unblock dependent gaps  
- authorize Auth, UI, Spec completion, or Implementation  
- reset or erase the need for explicit human authority  

When classification is `STALE_BLOCKER`, the next action belongs to a human: escalate and obtain an explicit authoritative answer.

## Baseline And No-Change Evaluation

Evaluators compare current evidence and canonical status against a known baseline.

- If the blocker and substantive evidence are unchanged, classification is typically `NO_CHANGE` (until the stale threshold).  
- Artifact reshapes, index pointer hygiene, and method-document edits that do not change the decision outcome do not count as substantive resolution and must not reset the stale-blocker count.  
- Track `consecutive_no_change_count` honestly across evaluations.

## Changed-State Rule

A changed state requires substantive difference in decision-relevant evidence or an explicit status/resolution update in the canonical record (for example, a new human answer). Cosmetic edits alone do not produce a changed-state classification for the purpose of clearing `STALE_BLOCKER`.

## Artifact Creation Rule

- Do not create a new file for the same unresolved decision boundary.  
- Do not create designation or resolution success files before a sufficient human answer exists.  
- Do not create a competing governance-rules memory file that duplicates the constitution.  
- Create a new artifact only for a genuinely new decision boundary, or when a human maintainer explicitly requests a split.

## Supporting Evidence Rule

Supporting files may hold discovery, response intake, change requests, reviews, and historical narrative.

They must not:

- become a second canonical owner of the same boundary  
- be collapsed wholesale into the canonical record  
- be deleted merely because a canonical reshape occurred  

The canonical record lists them as references only.

## Index And Catalog Rule

Decision indexes, catalogs, and spec catalogs act as pointers only.

They must not duplicate canonical decision content (full options, full evidence, resolution narrative). If a pointer is wrong, apply a minimal pointer-only fix. If it already points correctly, leave it alone.

## Non-Authorization Rule

Unresolved governance records and method documents are non-authorizing.

While ownership, re-authorization, Auth readiness, Feature Contract readiness, or related gates remain blocked:

- `may_proceed_to_impl` remains false  
- Implementation remains unauthorized  
- UI remains unauthorized  
- Auth work remains unauthorized  
- Spec completion that depends on unresolved gates remains blocked  

No evaluator classification alone flips these permissions.

## Question Efficiency And Gap Bundling Rule

Governance should not ask one question per gap by default.

Before preparing a human decision request, map:

```text
Primary gap
Dependent gaps
Root decision boundary
Downstream gates affected
Gates that remain blocked regardless of answer
```

Rules:

- A question that closes only one gap is acceptable only when that gap is genuinely independent.  
- A question that closes one gap while leaving obvious dependent gaps unasked is suboptimal.  
- Efficiency must not reduce clarity.  
- Do not combine unrelated gaps just to reduce the number of questions.  
- The real optimization target is minimum human round-trips with maximum clarity, not merely minimum question count.

## Authority Layer Separation Rule

Before decomposing or escalating any governance gap, the agent MUST test whether the boundary mixes distinct authority layers. A single question that spans multiple layers is not a valid governance question and MUST be narrowed or split before any human decision request.

This rule replaces the former nested `Decision Boundary Layer Test` and is applied before Gap Decomposition.

### The Four Layers

| # | Layer | Question form | Decision type | Resolved by |
|---|-------|---------------|---------------|-------------|
| 1 | Real-world organizational authority | "Who in the organization holds this authority?" | Human governance decision | Human only |
| 2 | Product governance / accountability | "Who is accountable for this product boundary?" | Human governance decision | Human only |
| 3 | Software / domain authority model | "What roles/permissions/authority model does the system encode?" | Domain / spec decision | Spec + design, post-auth |
| 4 | Implementation workflow design | "How is this enforced in workflow/UI/code?" | Implementation decision | Impl, post-auth |

### Classification Test

For each pending governance question, the agent MUST label every sub-part against the four layers:

- Layers 1 and 2: route to the human as a governance decision.
- Layers 3 and 4: defer to spec/design/implementation; MUST NOT be asked as a governance question and MUST NOT be treated as resolved by a governance-owner answer.
- Mixed questions across the Layer 1-2 / Layer 3-4 divide are INVALID until narrowed or decomposed.

### Enforcement

- A human decision request MUST contain only Layer 1 and/or Layer 2 content.
- Layer 3 and Layer 4 concerns are recorded as downstream gaps or impact notes, marked BLOCKED pending the relevant Layer 1-2 resolution and authorization gate.
- If a proposed question cannot be cleanly separated, the decision boundary is not precise enough to escalate.
- Evidence-derived candidate roles may be suggested, but they MUST NOT be treated as inferred authority.

### Simple Statement

- "Who has authority in the organization / for this product?" → human governance decision.
- "What role / workflow / permission should the system have?" → domain/spec/design decision.
- Both mixed together → fix before escalation.

## Gap Decomposition Rule

A single governance gap may contain multiple distinct decision boundaries.

Before preparing a question:

1. Decompose the gap.  
2. List every distinct decision hidden inside the gap.  
3. Determine whether all decisions share the same human authority.  
4. If they share one authority, bundle them into one structured question.  
5. If they require different authorities, split them into separate questions ordered by dependency.  

A gap is fully closed only when all decision boundaries inside it are explicitly resolved.  
Partial answers must be recorded.  
The canonical record must state which sub-decisions remain open.  

The gap-to-decision relationship is many-to-many:

- One question may close multiple gaps.  
- One gap may require multiple decisions.  
- Both directions must be mapped before asking.

## Gap Ordering Rule

Preferred order when sequencing human questions:

1. Origin and validity gaps  
2. Authority and ownership gaps  
3. Conflict-resolution gaps  
4. Authorization gaps  
5. Workflow gaps  
6. UI and interaction gaps  
7. Implementation-detail gaps  

Authority and ownership gaps must be asked before workflow, UI, auth, or implementation questions that depend on them.

## Question Dependency Rule

Human decision questions may depend on each other.

Before sending questions:

1. Build a question dependency graph.  
2. Never send a dependent question before its parent question is answered.  
3. Never send two questions in one batch if the second question's validity depends on the first question's answer.  
4. Independent questions may be batched to reduce human round-trips.  
5. After each answer, re-evaluate the graph.  
6. Some questions may become unnecessary.  
7. Some questions may be created.  
8. Some questions may need changed options.  

The goal is minimum human round-trips, not minimum questions on paper.  
A batch of independent questions in one message is one round-trip.  
A premature dependent question creates ambiguity.

Illustrative example:

```text
Q1 Business Owner and Authority Structure
- If Option A Single Owner: tie-breaker question becomes N/A.
- If Option B Dual Owner: tie-breaker scope may become askable if not already explicit.
- If Option C Owner not designated but designation authority known: designation follow-up becomes active.
- If Option D No owner and no designation authority: dependent questions freeze and escalation is required.
```

## Decision Option Design Rule

Options must be MECE:

- Mutually Exclusive  
- Collectively Exhaustive  

Each option must state:

- selected authority or owner  
- scope of authority  
- conflict-resolution path  
- downstream gaps that may be evaluated next  
- gates that remain blocked  

Deferral options must include:

- who is responsible for the future decision  
- what remains blocked  
- what trigger, deadline, or escalation path causes re-evaluation  

Reject ambiguous options such as:

- business side owns it  
- manager and supervisor are both involved  
- use the previous answer  
- infer from workflow  
- follow normal process  
- decide later without owner, authority, trigger, or escalation path  

## Option Relevance Rule

Options must be grounded in actual project evidence, not generic templates.

Before finalizing options:

1. Extract candidate roles or answers from existing supporting evidence.  
2. Pre-fill options with realistic candidates found in evidence where possible.  
3. Still allow an explicit Other option when needed.  
4. Remove options that no evidence supports and no stakeholder could realistically select.  
5. Keep exactly one explicit deferral or escalation option when the decision may legitimately be unavailable.  

A blank placeholder like `[role/title]` invites ambiguous answers.  
A pre-filled candidate list with an explicit override field produces faster, more precise human decisions.  

Evidence-derived candidates are suggestions only.  
Presenting a candidate in an option is not inference and does not resolve anything.  
Only an explicit human answer recorded in the canonical record may resolve the decision.

## Multi-Gap Question Rule

A human decision request may resolve multiple related gaps when those gaps share the same root decision boundary.

Before sending a question, identify:

```text
Primary gap
Dependent gaps
Root decision boundary
Downstream gates affected
Gates that remain blocked regardless of answer
Question dependencies
```

A multi-gap question is valid only if:

- all included gaps share the same root decision boundary or authority path  
- the answer options are clear and non-overlapping  
- each option states its governance impact  
- no implementation authorization is implied  
- unresolved downstream gates remain explicitly blocked  

Do not combine unrelated gaps only to reduce the number of questions.

## Question Impact Matrix

Example:

| Question | Primary Gap | Related Gaps | Depends On | If Answered, Can Evaluate | Still Blocked |
|---|---|---|---|---|---|
| Business Owner and Authority Structure | HDAC-05 | HDAC-06, conflict resolution, authority boundary | — root | HDAC-05, then HDAC-06 | implementation, auth, UI until downstream gates resolve |
| Manager Reauthorization Scope | HDAC-06 | approval scope | HDAC-05 answer | HDAC-06 | implementation, auth, UI until downstream gates resolve |

Use the matrix to detect:

- over-bundled unrelated questions  
- premature dependent questions  
- single-gap questions that could have closed dependent gaps  
- gaps that need decomposition before asking  
- questions that can be batched because they are independent  

## Standard Workflow For An Unresolved Governance Gap

1. Name the primary gap and list dependent gaps.  
2. Decompose into decision boundaries; map many-to-many links.  
3. Confirm or create exactly one canonical record for the root unresolved boundary (create only if none exists).  
4. Attach supporting evidence as references.  
5. Design MECE evidence-grounded options and a dependency graph.  
6. Issue the human decision request (escalation if `STALE_BLOCKER`).  
7. Record the explicit answer or partial answer in the canonical record only.  
8. Re-evaluate dependent questions and downstream gates.  
9. Do not authorize Auth / UI / Spec completion / Implementation until gates allow it.

## Standard Workflow For Existing Duplicate Or Messy Artifacts

1. Do not delete historical supporting evidence.  
2. Identify the intended single canonical record.  
3. If duplicates appear to compete as canonical for the same boundary, report `DUPLICATE_CANONICAL_RECORD_DETECTED` and identify competing files.  
4. Prefer in-place reshape of the chosen canonical file over creating a new one.  
5. Keep indexes as pointer-only updates.  
6. Leave unresolved status unchanged unless an explicit human resolution already exists.

## Canonical Reshape Rule

A reshape rewrites an existing canonical file in place to the required section structure.

- It must not resolve the decision.  
- It must not invent owners or authorities.  
- It must not unblock dependent gaps.  
- It must not reset `STALE_BLOCKER` counts.  
- Supporting evidence stays referenced, not pasted as long-form narrative.

## Human Decision Request Rule

Human requests must:

- state the decision boundary clearly  
- present MECE options with governance impact  
- reject known ambiguous answer patterns  
- name what remains blocked regardless of answer  
- name which gaps become evaluable if answered sufficiently  
- assign `responsible_party = human` while the authority designation is missing  

## Optimized Human Decision Request Format

```text
Decision boundary:
<one clear question, possibly covering related sub-decisions that share authority>

Primary gap / Related gaps / Depends on:
<root mapping>

Acceptable options (MECE):
Option A — ...
  Authority / scope / conflict path / next evaluable / still blocked
Option B — ...
...
Other explicit role/title or authority: ______
Deferral/escalation option (if legitimate): ...

Rejected answer patterns:
- ...

Non-authorization notice:
This answer does not by itself authorize Auth, UI, Spec completion, or Implementation.
```

## Evaluator Output Rule

Evaluators should emit a machine-readable posture, for example:

```json
{
  "hdac_05_status": "UNRESOLVED",
  "hdac_06_status": "BLOCKED_PENDING_BUSINESS_OWNER",
  "may_proceed_to_impl": false,
  "classification": "STALE_BLOCKER",
  "current_blocker": "...",
  "next_action": {
    "responsible_party": "human",
    "action_description": "..."
  }
}
```

Status values must follow explicit evidence. Classification may escalate to `STALE_BLOCKER` without changing resolution.

## Responsible Party Rule

| Situation | Responsible party | Typical action |
| --- | --- | --- |
| Unresolved ownership / authority designation | human | Provide explicit designation or escalate |
| `STALE_BLOCKER` | human | Escalate stale unresolved decision; obtain authoritative answer |
| Parent resolved; dependent gap now evaluable | agent | Evaluate next dependent gap using recorded authority |
| Canonical shape / pointer hygiene only | agent | Reshape or fix pointers without changing resolution |

## HDAC-05 Example

This example is illustrative of method quality. It does **not** resolve HDAC-05 and does not change live statuses.

Example: A Business Owner question may ask who is accountable for a product surface and, if dual ownership is selected, who the named tie-breaker is. It must not ask the human to design system roles, workflow stages, permissions, approval-chain mechanics, or enforcement behavior. Those are downstream Layer 3-4 concerns.

**Optimized human-facing question (Layer 2)**

> Who is the formal Business Owner/accountable business authority for the authorized product surface `employee-request-self-service`, for approval of scope, change requests, and owner-bound governance wording?

**Evidence-derived candidate roles (suggestions only; not inferred decisions)**

From existing supporting requery / architecture / role evidence, realistic candidates may include:

- مدیر منابع انسانی (`hr_manager` role label in architecture inventory)  
- مدیر خوابگاه (dormitory-manager title used in HDAC requery options)  

These are candidates for human selection only. Presenting them does not assign ownership.

**Options**

**Option A — Single Accountable Business Owner**

- Select one role/title (candidate list or Other).  
- Scope: sole authority for approval and change decisions on this product surface.  
- Conflict path: N/A (single owner).  
- Next evaluable: HDAC-05 resolution recording; then HDAC-06 dependency evaluation.  
- Still blocked: Auth, UI, Spec completion, Implementation until downstream gates resolve.

**Option B — Dual Ownership With Named Tie-Breaker**

- Name both owners and exactly one tie-breaker role/title.  
- Scope: shared ownership with explicit dispute resolution.  
- Conflict path: tie-breaker decides. Without a named tie-breaker, answer remains insufficient.  
- Next evaluable: HDAC-05 if complete; then HDAC-06.  
- Still blocked: Auth, UI, Spec completion, Implementation until downstream gates resolve.

**Option C — Owner Not Yet Designated, But Designation Authority Is Known**

- State who has authority to designate the Business Owner.  
- Scope: designation process ownership, not surface Business Owner yet.  
- Conflict path: as specified by the designation authority.  
- Next evaluable: designation follow-up question becomes active; HDAC-05 remains unresolved until designation completes.  
- Still blocked: HDAC-06, Auth, UI, Spec completion, Implementation.

**Option D — No Owner And No Designation Authority Currently Exists**

- Explicit deferral/escalation: name who must create designation authority, what remains blocked, and the trigger/escalation path for re-evaluation.  
- Downstream gaps freeze.  
- Still blocked: HDAC-05, HDAC-06, Auth, UI, Spec completion, Implementation.

**Override field**

```text
Other explicit role/title: ______
```

**Hard constraints in the example**

- HDAC-05 may only be resolved by explicit human authority recorded in the canonical record.  
- HDAC-06 remains blocked until HDAC-05 is resolved or otherwise evaluable under explicit governance rules.  
- Implementation, auth, UI, and spec completion remain unauthorized until downstream gates are resolved.  
- Evidence-derived candidate roles may be shown as suggestions, but they are not inferred decisions.

Live canonical file for this boundary (do not replace with a second file):  
`.specify/docs/clarifications/hdac-05-business-owner-requery.md`

## Pre-Flight Checklist

- [ ] Authority layers classified (1-2 human governance only; 3-4 deferred)  
- [ ] No mixed Layer 1-2 / Layer 3-4 question pending escalation  
- [ ] Decision boundaries decomposed  
- [ ] Primary / dependent gaps mapped  
- [ ] Root decision boundary named  
- [ ] Question dependency graph built  
- [ ] Exactly one canonical record owns the unresolved boundary  
- [ ] Supporting evidence listed as references only  
- [ ] Options are MECE and evidence-grounded  
- [ ] Each option states authority, scope, conflict path, next evaluable, still blocked  
- [ ] No premature dependent question in the same batch  
- [ ] Non-authorization notice present  
- [ ] Index remains pointer-only  
- [ ] Constitution remains source of truth  

## Done Criteria

A method-guided human decision cycle is complete only when:

1. The canonical record reflects explicit human answers for all sub-decisions in scope (or records which remain open).  
2. Evaluator output matches repository evidence.  
3. Dependent questions are re-graphed.  
4. No Auth / UI / Spec completion / Implementation authorization is implied by documentation alone.  
5. Indexes still point without duplicating canonical content.

## Anti-Patterns

- One new artifact per clarification round for the same boundary  
- Resolving ownership from workflow participation  
- Copying full historical narratives into the canonical record  
- Duplicating canonical content into indexes/catalogs  
- Asking Auth / UI / workflow questions before unresolved ownership  
- Sending a dependent question before its parent is answered  
- Bundling unrelated gaps only to reduce question count  
- Blank `[role/title]` options that invite ambiguous free text  
- Treating `STALE_BLOCKER` as permission to proceed  
- Creating `.specify/memory/governance-rules.md` as a competing rule host  
- Declaring implementation readiness while `may_proceed_to_impl` must remain false  

## Summary

Use one canonical record per unresolved decision boundary, keep evidence distributed and indexes as pointers, never resolve by inference, treat `STALE_BLOCKER` as escalation only, decompose gaps before asking, order by dependency, design MECE evidence-grounded options, and optimize for minimum clear human round-trips—not minimum superficial question count—without authorizing Auth, UI, Spec completion, or Implementation until downstream gates explicitly allow it.

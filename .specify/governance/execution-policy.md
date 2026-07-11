# Execution Policy

## Purpose

This document defines how batches are discovered, loaded, executed, and reviewed.

It orchestrates the delivery pipeline without containing the detailed implementation rules
(those live in `coding-rules.md`, `batch-strategy.md`, and `review-checklist.md`).

This document governs **execution behavior only**.

It does not:

- own, grant, imply, restore, or revoke operational authority
- redefine governance decision authority ownership
- serve as an authorization record

For governance authority ownership, do not rely on this document.

Refer exclusively to:

`.specify/docs/catalog-decisions.md`  
`## Governance Decision Authority Map`

---

## Authority Ownership

Canonical Governance Decision Authority ownership is defined **only** in:

`.specify/docs/catalog-decisions.md`  

See:

`## Governance Decision Authority Map`

This document does not redefine that ownership.

Authorization vocabulary and lifecycle are defined in:

`.specify/governance/_meta/authority-model.md`

Do not infer authority from:

- `spec.md`
- `plan.md`
- `tasks.md`
- status headers
- progress notes
- governance state or snapshot artifacts (per `catalog-decisions.md` § `Governance state / snapshot artifacts`)
- Nomination Records (per `.specify/governance/_meta/authority-model.md` — evidence-only; non-operational)
- checkpoint summaries, status reports, or audit records
- handoff directory placement or filename similarity to authorization artifacts
- this document

Authorization checks apply **only** to artifacts that match the canonical authorization artifact classes and instance paths in `## Governance Decision Authority Map`. Artifacts outside the authorization record lifecycle (per `authority-model.md` § `Artifacts outside the authorization record lifecycle`) **cannot** satisfy authorization checks.

Nomination Records **cannot** satisfy authorization checks and **cannot** substitute for Design Approval, Implementation Authorization, or Batch Execution Permission.

Exactly **three** operational authority types exist per `authority-model.md` §2. Next Spec Transition Nomination is a **non-operational** governance decision class; it is **not** an operational authority type.

---

## Pre-Execution Requirements

Before any implementation work begins for the active specification:

1. Verify Design Approval is satisfied **per the canonical map**.
2. Design Approval is **not** Implementation Authorization.
3. Verify Implementation Authorization is satisfied **per the canonical map**;  
   confirm `authorization-status` is `active` or `partial` per  
   `.specify/governance/_meta/authority-model.md` §5.  
   The instance record MUST match the Implementation Authorization path pattern in the canonical map.  
   Governance state / snapshot artifacts (per `catalog-decisions.md` § `Governance state / snapshot artifacts`) **cannot** satisfy this check.
4. Verify the intended batch's wave and tasks appear verbatim in the record's `authorized-scope`.  
   Do not infer scope from spec, plan, or task content.
5. Classify authorization failure per **§ HALT Classification (Authorization vs Transition vs Governance Precondition)** before reporting.
   If Case A applies, **HALT** and report:

   > `Missing or invalid implementation authorization record.`

   If Case B applies, **HALT** and report:

   > `No authorized implementation exists. Governance transition decision required.`

   If Case C applies, **HALT** and report:

   > `Governance precondition failure: transition nomination record required.`

Nomination Records **cannot** satisfy steps 1–4. A valid Nomination Record does not remove the requirement for operational authority in steps 1–4.

This document does not fix or override invalid authorization; it only detects and HALTs.

---

## Integration Implementation Authorization Issuance

This section constrains **issuance and request** of cross-module Integration Implementation Authorization. It does **not** add a fourth operational authority type. Authority ownership remains only in `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`.

Before issuing Integration Implementation Authorization, apply:

`.specify/governance/patterns/integration-readiness-gate.md`

Authorization must be blocked unless the following chain is proven:

```text
Consumer -> Required Capability -> Accepted Application Contract -> Thin Adapter Mapping
```

Allowed readiness outcomes only:

- `READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION`
- `INTEGRATION_AUTHORIZATION_BLOCKED`

The gate itself does **not** authorize implementation. It only determines whether implementation authorization may be requested or issued.

Apply this gate **before**:

- Integration Implementation Authorization
- cross-module adapter creation
- replacing Null/Stub adapters with live implementations
- provider-consumer Application bindings

Every Integration Implementation Authorization artifact must include section `## Integration Readiness Gate` with fields:

Consumer; Required Capability; Accepted Provider Contract; Mapping; Adapter Type; Behavior Invented; Authorization Result.

Use the reusable template (do not duplicate the full pattern):

`.specify/templates/integration-implementation-authorization-template.md`

If readiness is `INTEGRATION_AUTHORIZATION_BLOCKED`, do not issue Integration Implementation Authorization and do not begin live adapter implementation.

---

## Governance Transition State

**Governance Transition** is an operational state — **not** a decision class with canonical authority ownership in `## Governance Decision Authority Map`.

It occurs when **all** of the following are true:

1. The active specification's authorized implementation scope is **complete** (all tasks in the authorized scope are done, or the final authorized batch has passed its review gate), **or** no specification has been nominated for execution; **and**
2. No valid Implementation Authorization exists for any specification or batch that execution is attempting to start; **and**
3. No existing governance decision has selected or authorized a next specification or batch for implementation.

This state means **a governance decision is required** — not a framework defect.

Enforcement must **not**:

- infer which specification or batch should come next,
- use catalog ordering, dependencies, or informational status mirrors as authorization,
- auto-advance to any next specification or batch.

---

## Nomination and Execution Policy

This section defines how **Next Spec Transition Nomination** and **Nomination Records** interact with execution policy.

Vocabulary and classification are defined in `.specify/governance/_meta/authority-model.md` §2 (Non-Operational Governance Decision Classes):

- **Next Spec Transition Nomination** — a **non-operational** governance decision class (per `authority-model.md` §2).
- **Nomination Record** — an **evidence-only**, **non-authorizing** artifact instance of that decision class (per `authority-model.md` §2).

### Role

A Nomination Record records **which specification is nominated as the program's next focus** after a governance transition boundary. It is selection evidence only.

A Nomination Record:

- **MUST NOT** grant Design Approval, Implementation Authorization, or Batch Execution Permission.
- **MUST NOT** be treated as an Authorization Record.
- **MUST NOT** be used to satisfy enforcement checks that require an operational authority type.
- **MUST NOT** reclassify a HALT caused by missing operational authority into a non-HALT state.
- **MAY** be required by this policy as a **governance precondition** before certain next-spec processes are initiated.

### Governance precondition (normative)

For a specification treated as the **next spec** after a governance transition boundary, this policy **requires** a valid Nomination Record nominating that specification **before**:

- initiating Design Approval for that specification, or
- any other next-spec governance flow that **this policy** identifies as requiring transition nomination.

**Starting Design Approval for a next spec without a required Nomination Record** **SHOULD** be classified as **Case C** (governance precondition failure).

Once a required Nomination Record exists for that specification:

- enforcement **MAY** proceed to evaluate normal operational authority preconditions (Design Approval, Implementation Authorization, Batch Execution Permission) per the canonical map;
- if operational authority is missing, the situation **remains** a **HALT** — nomination does not clear it.

### Separation from operational authority

| Layer | Governs | Satisfies implementation preconditions? |
| --- | --- | --- |
| Nomination Record | Program selection evidence (non-operational) | **No** |
| Design Approval | Design readiness (operational authority type) | Partially (step 1 only) |
| Implementation Authorization | Implementation execution (operational authority type) | Yes (steps 3–4) |
| Batch Execution Permission | Batch progression (operational authority type) | Batch gate only |

Do not infer a Nomination Record from `spec-catalog.md` ordering, status mirrors, or progress notes.

A **transition nomination** (Nomination Record) is **not** the same as an **implementation execution target** (a specification or batch selected for implementation under operational authority workflow). Only the latter engages Case A when Implementation Authorization is missing or invalid.

---

## HALT Classification (Authorization vs Transition vs Governance Precondition)

When execution cannot proceed for authorization or governance-precondition reasons, classify the HALT as **Case A**, **Case B**, or **Case C** before reporting.

Apply the tests in order. Use the **first** case that matches.

### Case A — Authorization defect

Use when execution attempts to start or continue **implementation work** that **requires** a valid Implementation Authorization record per the canonical map, and that record is:

- missing for the **implementation execution target** specification or batch when operational authority workflow has already selected that target for implementation,
- duplicated,
- ambiguous,
- `revoked`,
- `superseded` without a single active replacement,
- or does not cover the requested scope.

**HALT message (exact):**

> `Missing or invalid implementation authorization record.`

Case A indicates an **error or invalid artifact** relative to an implementation execution target already under operational authority workflow.

Do **not** use Case A when:

- the only reason execution cannot proceed is that no next specification or batch has been selected or authorized for implementation yet, or
- a Nomination Record exists but Implementation Authorization is missing — unless implementation work is actually being attempted for that target.

A Nomination Record alone does **not** establish an implementation execution target for Case A.

### Case B — Governance Transition

Use when:

- authorized implementation work for the current specification is **complete**, **and**
- no valid Implementation Authorization exists for a next specification or batch, **and**
- no governance artifact has selected or authorized a next specification or batch for implementation.

**HALT message (exact):**

> `No authorized implementation exists. Governance transition decision required.`

Case B does **not** authorize any specification, batch, or workflow step. It only reports that human governance must decide what to authorize next.

A Nomination Record does **not** clear Case B when Implementation Authorization is still missing for the work being attempted. Case B addresses program-boundary transition; missing operational authority for implementation remains a HALT regardless of nomination.

### Case C — Governance precondition failure (transition nomination)

Use when:

- execution or governance workflow is initiating a **next-spec process** that this policy requires to be preceded by a Nomination Record (see § Nomination and Execution Policy), **and**
- no valid Nomination Record exists nominating that specification, **or**
- the Nomination Record is missing, duplicated, ambiguous, or superseded without a single active replacement.

**HALT message (exact):**

> `Governance precondition failure: transition nomination record required.`

Case C is a **governance-precondition failure**, not an authorization defect (Case A) and not a program-boundary transition report (Case B).

Case C does **not** authorize any specification, batch, or workflow step. It does **not** substitute for Design Approval or Implementation Authorization.

Do **not** use Case C when operational authority is missing but the required Nomination Record already exists — classify per Case A or remain halted on operational preconditions instead.

### Detection procedure

1. Determine whether execution is seeking the **next** work after a governance transition boundary, initiating **next-spec governance** (e.g., Design Approval for a next spec), or attempting **implementation** for a specific specification or batch.
2. If step 1 involves a next-spec process that requires a Nomination Record per § Nomination and Execution Policy, load the Nomination Record for that specification. If required and missing, duplicated, ambiguous, or superseded without replacement → **Case C**.
3. Load Implementation Authorization instance(s) required by the canonical map for the **implementation execution target** (not merely a transition-nominated specification).
4. If an implementation execution target exists and its authorization record is missing, duplicated, ambiguous, `revoked`, `superseded` without replacement, or scope-invalid → **Case A**.
5. If authorized scope for the active specification is complete and no valid Implementation Authorization exists for any next specification or batch, and no governance artifact has selected or authorized a next specification or batch for implementation → **Case B**.
6. If multiple cases could apply, apply this precedence: **Case C** (missing required nomination for the engaged next-spec process) before **Case A** (implementation authorization defect for an execution target) before **Case B** (program-boundary transition).

Never guess which specification or batch should come next. Never treat a Nomination Record as Implementation Authorization.

---

## Governance Transition Follow-Up

When the system HALTs with:

> `No authorized implementation exists. Governance transition decision required.`

the next step is a **human governance action**, not an automated step.

Required follow-up:

1. **Stop.** Do not implement, plan, or batch-execute any unauthorized specification or batch.
2. A governance body must:
   - consult `.specify/docs/spec-catalog.md` for ordering guidance, dependency information, and informational status (status mirrors are **not** authority),
   - decide which specification is eligible to be the program's next focus,
   - create a **Nomination Record** for that specification as an evidence-only artifact per `authority-model.md` §2 (Non-Operational Governance Decision Classes) and this policy § Nomination and Execution Policy,
   - then create the appropriate **operational** artifacts per `## Governance Decision Authority Map` in `catalog-decisions.md` (Design Approval and/or Implementation Authorization as applicable).
3. A Nomination Record records selection only. It does **not** replace Design Approval or Implementation Authorization. Case B HALT persists for implementation until operational authority exists, even after a Nomination Record is created.
4. Next Spec Transition Nomination is a **non-operational** governance decision class per `authority-model.md` §2. It is **not** an operational authority type and **does not** have an entry in `## Governance Decision Authority Map`. Operational authority ownership remains defined only in that map per `catalog-decisions.md`.
5. After the appropriate **operational** authorization artifacts exist and satisfy Pre-Execution Requirements, execution may resume for the newly authorized scope only.

---

## Batch Discovery

When a batch execution request arrives:

1. Determine the active specification identifier (e.g., `spec05`).
2. Load the batch map from:

   `.specify/governance/batches/<spec>.md`

3. Locate the requested batch identifier (e.g., `B1`) in the map.
4. Extract:

   - Task range (e.g., `T008-T012`)
   - Wave assignment (e.g., `Wave1A`)
   - Batch type (e.g., `SCHEMA`, `FOUNDATION`)

**Never guess or infer batch composition.**

If:

- the batch file is missing, or
- the batch ID is not found,

**HALT**.

Batch definitions do not grant authority; they only describe execution grouping.

---

## Execution Boundaries

### One Batch at a Time

- Execute exactly **ONE** batch per execution cycle.
- A batch ends when all tasks in the batch are implemented, tested, verified, and reported.
- **HALT at the review gate.** Never auto-continue to the next batch.

Batch sequencing is an execution discipline, not an authority tier.

### Scope Lock

- Modify only the files required by the tasks in the current batch.
- Do not touch files outside the active specification's module unless `tasks.md` explicitly requires it (e.g., shared contracts).
- **No cross-module foreign keys** (AP-04).
- **No new package or stack changes** without an ADR (AP-01).

Scope Lock is an execution constraint; it does not redefine module ownership.

### Wave Gating

- Determine the batch's wave from `.specify/governance/batches/<spec>.md`.
- Wave 1A batches may proceed as foundational work when the active authorization record's
  `authorized-scope` explicitly permits that wave.
- Wave 1B/1C batches require those waves in `authorized-scope`.
- If a wave is absent from `authorized-scope` or listed in `blocked-scope`,
  **HALT** and report:

  > `Wave <X> not yet authorized. Awaiting checkpoint.`

Clarifications:

- Design Approval is **not** Implementation Authorization.
- Wave gating enforces **how** authorized work is sequenced;  
  it does not create new authority levels.

---

## Implementation Rules

During batch execution, apply the rules defined in:

- `.specify/governance/coding-rules.md`
  - references `constitution.md`, `specification-playbook.md`, `catalog-decisions.md`
- `.specify/governance/batch-strategy.md`
  - defines batch types, risk model, and execution strategy

This document does not duplicate those rules.  
It ensures they are **loaded and applied** during execution.

---

## Failure Policy

If tests, PHPStan, or Pint fail during batch execution:

1. **STOP immediately**
2. Report the root cause.
3. Propose the minimal scoped fix.
4. **Wait for approval** before applying the fix.
5. Do not start changing files on your own.

If the same failure repeats after two attempts:

- Diagnose the architectural or environmental root cause.
- Propose a fundamentally different approach.
- Explain tradeoffs and confirm before proceeding.

Failure handling follows this execution policy and the authority model;  
this document does not grant fix authorization.

---

## Review Gate

At the end of every batch:

1. Generate a batch report using:

   `.specify/governance/reporting-template.md`

2. Run the architecture review checklist from:

   `.specify/governance/review-checklist.md`

3. **HALT** — classify per § HALT Classification (Authorization vs Transition vs Governance Precondition) and report the exact message for Case A, Case B, or Case C when authorization or governance preconditions block progression; otherwise halt per Review Gate discipline.
4. Wait for human approval before starting the next batch.

Clarifications:

- The review gate controls **batch progression only**.
- Human approval after the review gate is required before the next batch may start.
- Review Gate approval is **not** Implementation Authorization.
- If Implementation Authorization is `revoked`, `superseded`, or otherwise invalid,
  review-gate approval does **not** restore it.

Do not infer authority from:

- `spec.md`
- `plan.md`
- `tasks.md`
- status headers
- progress notes
- governance state or snapshot artifacts
- Nomination Records
- checkpoint summaries, status reports, or audit records
- handoff directory placement or filename similarity to authorization artifacts

or from this review-gate section.

Nomination Records do not satisfy review-gate authority requirements.

---

## Conflict Resolution

If a conflict arises between:

- a task requirement and an architectural principle,
- a spec detail and a catalog decision,
- two sources at the same precedence tier,

**HALT immediately** and report the conflict with references to:

- `.specify/governance/file-precedence.md`
  - to determine which source prevails
- `.specify/governance/decision-index.md`
  - to confirm the exact location of each decision ID

This document does not resolve conflicts by itself;  
it enforces HALT and proper escalation.

---

## Required Inputs

Before executing a batch, **load and apply**:

- `.specify/docs/catalog-decisions.md` § Governance Decision Authority Map
- `.specify/governance/_meta/authority-model.md`
  - vocabulary and authorization-record lifecycle
- `.specify/governance/file-precedence.md`
  - tier precedence
- `.specify/governance/batches/<spec>.md`
  - batch composition and wave
- Authorization record instance required by the canonical map for the active specification (must match a canonical authorization artifact class; evidence-only artifacts **cannot** substitute)
- `.specify/governance/KNOWN_DEBT.md`
  - required for full-suite test runs

If a required input is missing or unreadable, **HALT**.

Required Inputs describe **what must be loaded**;  
they do not change authority ownership.

---

## Document Control

- Version: 1.5.0
- Last Updated: 1405/04/20 | 2026/07/11
- Change: Added Integration Implementation Authorization Issuance section referencing Integration Readiness Gate pattern
- Owner: DormSys Architecture Team

This ownership line controls document maintenance only.  
It does not grant operational authority.

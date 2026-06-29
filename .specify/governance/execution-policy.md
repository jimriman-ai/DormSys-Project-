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
- this document

---

## Pre-Execution Requirements

Before any implementation work begins for the active specification:

1. Verify Design Approval is satisfied **per the canonical map**.
2. Design Approval is **not** Implementation Authorization.
3. Verify Implementation Authorization is satisfied **per the canonical map**;  
   confirm `authorization-status` is `active` or `partial` per  
   `.specify/governance/_meta/authority-model.md` §5.
4. Verify the intended batch's wave and tasks appear verbatim in the record's `authorized-scope`.  
   Do not infer scope from spec, plan, or task content.
5. If authorization is missing, duplicated, ambiguous, `revoked`, `superseded`
   without a single active replacement, or does not cover the batch scope,
   **HALT** and report:

   > `Missing or invalid implementation authorization record.`

This document does not fix or override invalid authorization; it only detects and HALTs.

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

3. **HALT**
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

or from this review-gate section.

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
- Authorization record instance required by the canonical map for the active specification
- `.specify/governance/KNOWN_DEBT.md`
  - required for full-suite test runs

If a required input is missing or unreadable, **HALT**.

Required Inputs describe **what must be loaded**;  
they do not change authority ownership.

---

## Document Control

- Version: 1.2.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team

This ownership line controls document maintenance only.  
It does not grant operational authority.

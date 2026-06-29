# Execution Policy

## Purpose

This document defines how batches are discovered, loaded, executed, and reviewed. It orchestrates the delivery pipeline without containing the detailed implementation rules (those live in `coding-rules.md`, `batch-strategy.md`, and `review-checklist.md`).

This document defines batch execution **process** only. It does not own, grant, imply, restore, or revoke any operational authority.

---

## Authority Model Reference

The three operational authority types — Design Approval, Implementation Authorization, and Batch Execution Permission — are distinct. Their authoritative source classes are defined exclusively in `.specify/governance/_meta/authority-model.md` §3.

| Authority Type | Authoritative Source (pointer only) |
|---|---|
| Design Approval | `.specify/docs/handoff/<spec>-design-approved.md` |
| Implementation Authorization | `.specify/docs/handoff/<spec>-implementation-authorization.md` |
| Batch Execution Permission | This document + `.specify/governance/batches/<spec>.md` + recorded human review outcome |

Do not infer authority from `spec.md`, `plan.md`, `tasks.md`, status headers, or progress notes.

---

## Pre-Execution Requirements

Before any implementation work begins for the active specification:

1. Confirm Design Approval via `.specify/docs/handoff/<spec>-design-approved.md`.
2. Design Approval alone does not authorize implementation.
3. Confirm Implementation Authorization via `.specify/docs/handoff/<spec>-implementation-authorization.md` with `authorization-status` of `active` or `partial` per authority-model §5.
4. Verify the intended batch's wave and tasks appear verbatim in the record's `authorized-scope`. Do not infer scope from spec, plan, or task content.
5. If the record is missing, duplicated, ambiguous, `revoked`, `superseded` without a single active replacement, or does not cover the batch scope, HALT and report: `Missing or invalid implementation authorization record.`

---

## Batch Discovery

When a batch execution request arrives:
1. Determine the active specification identifier (e.g., `spec05`)
2. Load the batch map from `.specify/governance/batches/<spec>.md`
3. Locate the requested batch identifier (e.g., `B1`) in the map
4. Extract:
   - Task range (e.g., `T008-T012`)
   - Wave assignment (e.g., `Wave1A`)
   - Batch type (e.g., `SCHEMA`, `FOUNDATION`)

**Never guess or infer batch composition. If the batch file is missing or the batch ID is not found, HALT.**

---

## Execution Boundaries

### One Batch at a Time
- Execute exactly ONE batch per execution cycle
- A batch ends when all tasks in the batch are implemented, tested, verified, and reported
- **HALT at the review gate.** Never auto-continue to the next batch.

### Scope Lock
- Modify only the files required by the tasks in the current batch
- Do not touch files outside the active specification's module unless `tasks.md` explicitly requires it (e.g., shared contracts)
- **No cross-module foreign keys** (AP-04)
- **No new package or stack changes** without an ADR (AP-01)

### Wave Gating
- Determine the batch's wave from `.specify/governance/batches/<spec>.md`.
- Wave 1A batches may proceed as foundational work when the active Implementation Authorization record's `authorized-scope` explicitly permits that wave.
- Wave 1B/1C batches require the Implementation Authorization record to include those waves in `authorized-scope`. If a wave is absent from `authorized-scope` or listed in `blocked-scope`, HALT and report: `Wave <X> not yet authorized. Awaiting checkpoint.`
- Design Approval must not be interpreted as Implementation Authorization.

### Decision Separation
- Design Approval confirms design readiness only.
- Implementation Authorization permits implementation execution under declared scope.
- Batch Execution Permission permits progression to the next eligible batch under the current authorization and review state.
- These three decisions are distinct and must not be inferred from one another.

---

## Implementation Rules

During batch execution, apply the rules defined in:
- `.specify/governance/coding-rules.md` (references constitution, playbook, catalog decisions)
- `.specify/governance/batch-strategy.md` (batch types, risk model)

Reference governance decisions by ID **only when they influence the implementation or review**. Avoid unnecessary citations.

---

## Failure Policy

If tests, PHPStan, or Pint fail during batch execution:
1. **STOP immediately**
2. Report the root cause
3. Propose the minimal scoped fix
4. **Wait for approval** before applying the fix
5. Do not start changing files on your own

If the same failure repeats after two attempts:
- Diagnose the architectural or environmental root cause
- Propose a fundamentally different approach
- Explain tradeoffs and confirm before proceeding

---

## Review Gate

At the end of every batch:
1. Generate a batch report using `.specify/governance/reporting-template.md`
2. Run the architecture review checklist from `.specify/governance/review-checklist.md`
3. **HALT**
4. Wait for human approval before starting the next batch

Batch Execution Permission (authority-model §3) requires this document's review rules, the batch map, **and** a recorded human review outcome.

Clarifications:
- Human approval after the review gate is required before the next batch may start.
- Review-gate approval is Batch Execution Permission for next-batch progression only, not Implementation Authorization.
- If Implementation Authorization is `revoked`, `superseded`, or otherwise invalid, review-gate approval does not restore it.

---

## Conflict Resolution

If a conflict arises between:
- A task requirement and an architectural principle
- A spec detail and a catalog decision
- Two sources at the same precedence tier

**HALT immediately** and report the conflict with references to:
- `.specify/governance/file-precedence.md` (to determine which source prevails)
- `.specify/governance/decision-index.md` (to confirm the exact location of each decision ID)

---

## Required Inputs

Before executing a batch, load and apply:

- `.specify/governance/_meta/authority-model.md` (vocabulary and authority sources)
- `.specify/governance/file-precedence.md` (tier precedence)
- `.specify/governance/batches/<spec>.md` (batch composition and wave)
- `.specify/docs/handoff/<spec>-implementation-authorization.md` (implementation authority instance)
- `.specify/governance/KNOWN_DEBT.md` (required for full-suite test runs)

If a required input is missing or unreadable, HALT.

---

**Document Control**
- Version: 1.1.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team

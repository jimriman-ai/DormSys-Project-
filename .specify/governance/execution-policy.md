# Execution Policy

## Purpose

This document defines how batches are discovered, loaded, executed, and reviewed. It orchestrates the delivery pipeline without containing the detailed rules (those live in `coding-rules.md`, `batch-strategy.md`, and `review-checklist.md`).

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

### Wave Authorization
- Wave 1A batches may execute immediately (foundational work)
- Wave 1B/1C batches require explicit authorization checkpoint
- If a batch belongs to a non-authorized wave, HALT and report: `"Wave <X> not yet authorized. Awaiting checkpoint."`

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

**Document Control**
- Version: 1.0.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team

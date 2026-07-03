# Program Architecture Lifecycle

## Purpose

This document defines the lifecycle used to prepare and execute the remaining DormSys program scope after the closure of spec06.

DormSys is no longer in broad spec-generation mode. The remaining work should follow a controlled sequence that separates architecture alignment from implementation execution.

This lifecycle exists to reduce architectural drift, avoid cross-spec rework, and preserve strict module boundaries during downstream delivery.

## Current Program State

- spec06 is closed and verified.
- Lottery is treated as a stable upstream module.
- The project is positioned at the handoff into spec07.
- Remaining scope:
  - spec07 Allocation
  - spec08 Voucher
  - spec09 Notification
  - spec10 Audit
  - spec11 Reporting

## Lifecycle

The remaining specs should follow this sequence when entering a new implementation stream or when cross-spec dependencies materially change:

1. Program Architecture Alignment
2. Cross-Spec Review
3. Program Architecture Review (PAR)
4. Architecture Freeze
5. Task Freeze
6. Execution
7. Verification

This lifecycle is intended to guide implementation readiness and downstream execution control. It should be applied proportionally to the scope and risk of the work.

## Program Architecture Alignment

Program Architecture Alignment is required before a new implementation stream or when cross-spec dependencies materially change.

Before implementation begins for spec07, spec07 through spec11 should be aligned at architecture level.

This phase defines or validates for each relevant spec:

- scope
- module boundary
- ownership
- upstream dependencies
- downstream consumers
- contracts and interfaces
- domain events where applicable
- integration points
- architecture-level acceptance conditions

This phase is analysis and refinement only. It is not an implementation phase.

## Cross-Spec Review

Cross-Spec Review evaluates the relevant specs as a connected program.

Review should cover:

- dependency ordering
- contract consistency
- ownership clarity
- event duplication
- integration overlap
- circular dependencies
- responsibility leakage across modules

## Program Architecture Review (PAR)

PAR is an advisory architecture gate between alignment and implementation readiness.

Its purpose is to support clear architectural decision-making before downstream execution begins. It is not intended as administrative ceremony.

No implementation for a new downstream stream should begin until PAR has reviewed the aligned architecture package at a level appropriate to the scope and risk.

PAR should determine:

- whether contracts are complete enough for downstream execution
- whether ownership boundaries are explicit
- whether event definitions are coherent and non-duplicative
- whether the dependency graph is valid
- whether unresolved circular dependencies remain
- whether any module has absorbed responsibility that belongs elsewhere
- whether blockers are documented clearly enough to determine freeze readiness

## Architecture Freeze

Architecture Freeze occurs after PAR indicates sufficient readiness.

Architecture Freeze locks:

- module boundaries
- ownership
- contract direction
- event responsibilities
- integration points
- dependency graph assumptions

Architecture Freeze is controlled, not absolute. If implementation reveals a real architectural defect, revision must occur through explicit review rather than silent code-level drift.

## Task Freeze

After Architecture Freeze, execution proceeds one spec at a time.

Only the next implementation target should enter Task Freeze.

For the current program state:

- Architecture Alignment applies to spec07..spec11 when opening the next downstream implementation stream
- Task Freeze applies only to spec07 before implementation begins

spec08 through spec11 may be architecture-ready without being task-frozen.

## Execution

Implementation must proceed spec-by-spec and batch-by-batch.

Execution must remain within:

- approved architecture boundaries
- task-frozen scope
- existing governance constraints

## Verification

Verification is required after implementation batches and after spec-level completion.

Verification should confirm:

- boundary compliance
- contract compliance
- no unintended architecture drift
- no ownership leakage
- readiness for the next spec handoff

## Success Criteria

The architecture preparation phase is complete only if:

- each relevant remaining spec has a clear architectural boundary
- cross-spec contracts are aligned
- dependency order is validated
- ownership is unambiguous
- no unresolved circular dependency remains
- architectural blockers are explicitly documented
- the project is ready to enter PAR

## Operating Principle

DormSys should prefer dependency-aware freeze over premature implementation.

The objective is to stabilize architecture just enough to support controlled execution without overextending planning depth.

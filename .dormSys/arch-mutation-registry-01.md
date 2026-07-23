# ARCH-MUTATION-REGISTRY-01 (C5)

> 1405/05/01 (2026-07-23). Mutation registry only. No C1–C4, schema, or CLOSED-decision edits.

## Pre-check

| Check | Result |
|-------|--------|
| Freeze on mutation registry | **None** observed |
| Failing tests (current) | `MutationAuthorizationBoundaryTest` ×2 |
| Unaccounted classes | `ApplyRequestApprovalAutoApprovalsAction`, `DecideRequestApprovalStageAction`, `StartRequestApprovalWorkflowAction` |
| Root cause | Workflow `*Action` classes discovered by arch scan; neither MPEP-invoking nor listed in Exempt/Pending registries |

## Drift

| Expected | Actual (pre-fix) | Disposition |
|----------|-------------------|-------------|
| Every discovered `*Action` is exempt, pending, or invokes MPEP (`MutationAuthorizationBoundaryTest`) | Three Workflow actions unaccounted | **Fixed** via Pending registry |
| No CLOSED decision pins pending vs exempt vs adopt-MPEP for these three | Classification open | **Applied Pending** per registry docstring (“business mutation … until domain adoption waves add MPEP”). Did **not** wire MPEP into Workflow actions (out of registry scope). |

## Change

`PendingMutationAuthorizationRegistry::PENDING` += three Workflow actions (alphabetical).

## Advisor

| | |
|--|--|
| **Current (applied)** | Register all three as **Pending** |
| **Alternative A** | **Exempt** (trusted nested / Stage1-like) |
| **Alternative B** | Wire MPEP + Workflow gate in the three actions |
| **Reason** | Scope = registry only; Pending matches registry contract for business mutations without MPEP; Request callers already enforce MPEP at Approve/Reject/Submit |
| **Risks** | Pending leaves direct Workflow-action calls outside MPEP until a Workflow adoption wave; Exempt would permanently waive that |

## Validation

| Gate | Result |
|------|--------|
| PHPStan | `php vendor/bin/phpstan analyse --memory-limit=1G --no-progress` → **OK** |
| C5 Pest | `MutationAuthorizationBoundaryTest` → **3 passed** |
| Full Pest | **3 failed / 1992 passed** |
| `arch:scan` | RED — **C2 MATRIX only** (no UNREGISTERED) |

### Failure delta (of the prior 5)

| Failure | Cluster | After |
|---------|---------|-------|
| `MutationAuthorizationBoundaryTest` ×2 | **C5** | **RESOLVED** |
| `ForbiddenImportsScanTest` | C2 | unchanged |
| ModuleBoundary Request→Workflow Domain | C2 | unchanged |
| `ModuleInventoryParityTest` | C2 | unchanged |

**Suite:** 5 → **3** (C2 only). C1/C3/C4 not regressed.

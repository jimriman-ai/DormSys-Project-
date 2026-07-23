# ARCH-MODULE-BOUNDARY-C2-01

> 1405/05/01 (2026-07-23). Request↔Workflow Application boundary only.

## Pre-check

| Check | Result |
|-------|--------|
| Freeze/STOP on boundary | **None** in `.dormSys/open-decisions.md` |
| 3 C2 failures | `ForbiddenImportsScanTest`, `ModuleInventoryParityTest`, ModuleBoundary `Request Application` ↛ `Workflow Domain` |
| Root cause | `ApproveRequestStageAction` / `RejectRequestAction` catch Workflow **Domain** exceptions |
| Expected | `boundary-rules.md` ENFORCED: Application → foreign Domain **forbidden**; foreign **Application** allowed |
| Actual (pre-fix) | Request Application imported Workflow Domain exceptions |

## Drift

Actual violated ENFORCED Expected (same as failing tests). No competing Spec that authorizes foreign Domain exception imports. Aligned Actual → Expected (not an allowlist weaken).

## Change

| Action | Path |
|--------|------|
| Add | `Workflow/Application/Exceptions/{UnauthorizedWorkflowStageActor,WorkflowInstanceNotFound,InvalidWorkflowTransition}Exception` |
| Translate | `DecideRequestApprovalStageAction::execute` wraps Domain → Application exceptions |
| Catch | Request Approve/Reject catch Workflow **Application** exceptions |

## Advisor

| | |
|--|--|
| **Current (applied)** | Application-boundary exception types + translate at Decide |
| **Rejected** | Arch allowlist for Domain exception imports |
| **Reason** | Matches ENFORCED boundary-rules; allowlist would weaken matrix |
| **Risks** | Other Workflow Application entrypoints (`Start*`, `Apply*`) still throw Domain types to in-module callers (allowed). Only cross-module Decide path wrapped |

## Validation

| Gate | Result |
|------|--------|
| PHPStan | `php vendor/bin/phpstan analyse --memory-limit=1G --no-progress` → **OK** |
| Full Pest | **1995 passed** / 0 failed (5790 assertions) |
| `arch:scan` | **passed** |

### Failure delta (prior 3 → 0)

| Failure | Cluster | After |
|---------|---------|-------|
| `ForbiddenImportsScanTest` | C2 | **RESOLVED** |
| `ModuleInventoryParityTest` | C2 | **RESOLVED** |
| ModuleBoundary Request→Workflow Domain | C2 | **RESOLVED** |

**Residual Arch failures: 0.** C1/C3/C5 not regressed (suite green; prior C5/C3/C1 surfaces remain passing).

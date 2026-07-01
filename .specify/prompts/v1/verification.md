# Verification Prompt

## Goal

Verify that the completed implementation batch or spec-level delivery remains compliant with architecture, contracts, and governance constraints.

## Current Phase

Verification

## Source of Truth

- .specify/governance/program-architecture-lifecycle.md
- approved PAR output
- Architecture Freeze decisions
- execution-policy.md
- review-checklist.md
- coding-rules.md
- batch-strategy.md
- relevant active spec artifacts
- implementation outputs under review

## Scope

- verify boundary compliance
- verify contract compliance
- verify ownership consistency
- detect implementation drift
- determine readiness for next batch or next spec

## Non-Goals

- no new feature implementation
- no speculative redesign
- no unrelated cleanup

## Required Output

1. verification findings
2. drift or violation list, if any
3. remediation requirements, if any
4. verdict:
   - Pass
   - Pass with conditions
   - Fail

## Success Criteria

Verification is complete only if:

- implementation has been checked against architecture boundaries
- contract compliance has been assessed
- ownership leakage has been evaluated
- readiness decision is explicit

## Stop Condition

Stop after issuing the verification verdict.

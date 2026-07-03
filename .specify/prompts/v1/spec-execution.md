# Spec Execution Prompt

## Goal

Execute the approved implementation scope for one task-frozen spec within approved architectural boundaries.

## Current Phase

Execution

## Source of Truth

- .specify/governance/program-architecture-lifecycle.md
- approved PAR output
- Architecture Freeze decisions
- task-frozen spec artifacts
- execution-policy.md
- batch-strategy.md
- coding-rules.md
- file-precedence.md
- review-checklist.md
- relevant spec.md / plan.md / tasks.md for the active spec

## Scope

- implement only the approved batch for the active spec
- remain within frozen task scope
- preserve architecture boundaries and ownership rules
- produce implementation-ready outputs for review and verification

## Non-Goals

- no cross-spec architecture redesign
- no unrelated refactors
- no speculative implementation beyond batch scope
- no expansion into future-spec tasks without approval

## Required Output

1. completed implementation for the approved batch
2. concise change summary
3. risks or deviations detected during execution
4. verification notes for handoff

## Success Criteria

Execution is complete only if:

- approved batch scope is implemented
- no architecture boundary is crossed
- no unapproved responsibility shift is introduced
- outputs are ready for verification

## Stop Condition

Stop after completing the approved batch and preparing verification handoff.

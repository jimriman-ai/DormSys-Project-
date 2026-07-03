# Program Architecture Review Prompt

## Goal

Review the architecture alignment package for spec07..spec11 and determine whether the program is ready for Architecture Freeze and spec07 Task Freeze.

## Current Phase

Program Architecture Review (PAR)

## Source of Truth

- .specify/governance/program-architecture-lifecycle.md
- all outputs from Program Architecture Alignment
- execution-policy.md
- specification-playbook.md
- review-checklist.md
- context-map.md
- dormsys-architecture.md
- authority-model.md
- decision-index.md
- catalog-decisions.md

## Scope

- review architecture summaries
- validate dependency graph
- validate ownership boundaries
- validate contract coherence
- identify unresolved blockers
- issue a formal readiness verdict

## Non-Goals

- no implementation
- no migrations
- no schema changes
- no services
- no repositories
- no infrastructure adapters
- no tests

## Required Output

1. PAR findings
2. unresolved issues list
3. remediation requirements, if any
4. freeze readiness verdict:
   - Pass
   - Pass with conditions
   - Fail

## Success Criteria

PAR is complete only if:

- contracts are reviewable
- ownership boundaries are explicit
- dependency ordering is valid
- no unresolved circular dependency remains
- major architecture blockers are either resolved or explicitly conditioned

## Stop Condition

Stop after issuing the PAR verdict. Do not begin task planning or implementation.

# Program Alignment Prompt

## Goal

Prepare spec07 through spec11 for architecture-level execution readiness before any implementation begins for the next downstream stream.

## Current Phase

Program Architecture Alignment

## Source of Truth

- .specify/governance/program-architecture-lifecycle.md
- spec-catalog.md
- specification-playbook.md
- decision-index.md
- catalog-decisions.md
- context-map.md
- dormsys-architecture.md
- system-flow.md
- authority-model.md
- coding-rules.md
- execution-policy.md
- file-precedence.md
- batch-strategy.md
- review-checklist.md
- relevant spec.md / plan.md / tasks.md for spec07..spec11
- spec06 material if upstream handoff context is required

## Scope

- review the intended scope and dependency order for spec07..spec11
- refine architecture-level definition for each remaining spec
- identify cross-spec contracts, dependencies, ownership, and integration points
- surface architectural ambiguities, conflicts, and blockers
- prepare output for Program Architecture Review

## Non-Goals

- no implementation
- no database schema changes
- no migrations
- no application services
- no repositories
- no infrastructure adapters
- no tests
- no task freeze for spec08..spec11
- no unrelated document rewrites

## Required Output

1. architecture summary for each of spec07..spec11
2. dependency and contract matrix across spec07..spec11
3. ownership and integration notes
4. blocker and ambiguity list
5. proposed PAR checklist
6. Architecture Freeze readiness verdict:
   - Ready
   - Ready with conditions
   - Not ready

## Success Criteria

This phase is complete only if:

- every remaining spec has a clear architectural boundary
- cross-spec contracts are aligned
- dependency order is validated
- ownership is unambiguous
- no unresolved circular dependency exists
- all blockers are explicitly documented
- the project is ready to enter PAR

## Stop Condition

Stop after producing review-ready architecture outputs. Do not begin implementation.

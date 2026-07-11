# Governance File Precedence

## Purpose

This document defines the precedence order for governance-related artifacts and the protocol for resolving conflicts between them.

It governs conflict resolution only.

It does not:

- create governance authority,
- transfer authority ownership,
- grant implementation permission,
- substitute for approval or authorization records.

For authority ownership, see the Governance Decision Authority Map in:

`.specify/docs/catalog-decisions.md`

---

## Precedence Order

When two governance artifacts conflict, the higher-precedence source wins.

Precedence resolves document conflicts only. It does not create, grant, or transfer governance decision authority.

### Tier 1 — Canonical Governance Authority

1. `.specify/docs/catalog-decisions.md`

Use this tier to locate:

- governance decision authority ownership (`## Governance Decision Authority Map`),
- canonical ownership resolution for Design Approval, Implementation Authorization, and Batch Execution Permission.

Authority ownership is defined only in that section. This document assigns precedence position only.

### Tier 2 — Governance Resolution and Execution Control

2. `.specify/governance/file-precedence.md`
3. `.specify/governance/execution-policy.md`
4. `.specify/governance/governance-enforcer.md`

Use this tier for:

- document conflict resolution,
- execution orchestration behavior,
- enforcement sequencing,
- halt behavior,
- governance validation flow.

These documents control procedure, not ownership.

### Tier 3 — Planning and Implementation Constraints

5. `.specify/governance/batch-strategy.md`
6. `.specify/governance/coding-rules.md`
7. `.specify/governance/review-checklist.md`
8. `.specify/governance/reporting-template.md`
9. `.specify/governance/patterns/` (including `integration-readiness-gate.md` and `patterns/README.md`)

Use this tier for:

- batch composition,
- execution sequencing strategy,
- implementation constraints,
- review criteria,
- reporting format,
- reusable governance patterns (for example Integration Readiness Gate before cross-module Integration Implementation Authorization).

These documents do not override higher-tier ownership or execution rules. Patterns do not grant operational authority.

### Tier 4 — Spec and Task Artifacts

10. `spec.md`
11. `spec05.md`
12. `tasks.md`
13. `.specify/governance/batches/<spec>.md`

Use this tier for:

- feature scope,
- specification detail,
- task decomposition,
- batch mapping for execution order and grouping.

These artifacts are operational inputs, not governance authority sources.

### Tier 5 — Informational / Supporting Inputs

14. `decision-index.md`
15. `constitution.md`
16. `ai-prompts.md`
17. `rules-dorm-sys.mdc`
18. `KNOWN_DEBT.md`

Use this tier as supporting context unless a higher-tier document explicitly delegates a specific interpretive role.

---

## Conflict Resolution Protocol

When a conflict is detected:

1. Identify all conflicting statements.
2. Determine the source file of each statement.
3. Compare source files using the precedence order defined above.
4. Apply the higher-tier statement.
5. If the conflict involves ownership authority, confirm the authoritative source via `.specify/docs/catalog-decisions.md`.
6. If the conflict cannot be resolved unambiguously, halt execution.

---

## Non-Transfer Rule

Referencing a higher-tier rule inside a lower-tier document does not transfer authority ownership to the lower-tier document.

Examples:

- A coding rule may cite a governance decision, but does not become the owner of that decision.
- A batch map may describe ordering, but does not become the owner of execution authority.
- An enforcer may validate authorization, but does not become the source of authorization.

---

## Halt Conditions

Execution must halt when:

- two sources conflict and precedence does not resolve the issue unambiguously,
- a lower-tier source attempts to replace a higher-tier source,
- authority ownership cannot be resolved from the canonical source,
- an artifact implies authority it does not own.

---

## Document Control

- Version: 1.3.0
- Last Updated: 1405/04/20 | 2026/07/11
- Change: Added `.specify/governance/patterns/` to Tier 3 (Integration Readiness Gate)
- Owner: DormSys Architecture Team

This ownership line is for document maintenance only.

It does not grant operational authority.

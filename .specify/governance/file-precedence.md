# File Precedence and Conflict Resolution

## Purpose

This document defines the precedence order among tiered governance documents and the protocol for resolving conflicts between them.

It does **not** define authoritative sources for Design Approval, Implementation Authorization, or Batch Execution Permission. For authority ownership, see the Governance Decision Authority Map in `.specify/governance/catalog-decisions.md`.

---

## Source of Truth Order

When documents conflict, use this order from highest to lowest:

### Tier 0 — Supreme Authority
1. `.specify/docs/catalog-decisions.md`
   - Architectural principles
   - Non-negotiable constraints
   - Domain language authority

**Rule:** If a lower document conflicts with the Constitution, the Constitution wins.

### Tier 1 — Boundary and Process Governance
2. `.specify/docs/catalog-decisions.md`
   - Boundary decisions
   - Entity ownership
   - Cross-context contracts
   - Governance Decision Authority Map

3. `.specify/governance/specification-playbook.md`
   - Evidence rules
   - Conflict process
   - Freeze and pipeline rules

4. `.specify/governance/execution-policy.md`
   - Batch discovery and execution process
   - Activation and halt conditions
   - Wave gating checkpoints
   - Review gate procedures

**Rule:** Catalog decisions override provisional assumptions. The playbook governs process only. The execution policy governs when implementation may start, continue, pause, or halt. None of these files may override the Constitution or catalog decisions.

### Tier 2 — Specification Layer
5. `specs/<spec>/spec.md`
   - Feature requirements
   - Use cases
   - Operational agreements
   - Descriptive design-readiness status

6. `specs/<spec>/plan.md`
   - Implementation strategy
   - Dependency graph
   - Execution waves

7. `specs/<spec>/data-model.md`
   - Schema contracts

**Rule:** Specs must follow higher tiers. Operational agreements cannot override constitutional or boundary decisions. Status text in spec-scoped artifacts is descriptive only and must not be treated as operational authority.

### Tier 3 — Execution Layer
8. `specs/<spec>/tasks.md`
   - Task breakdown
   - Acceptance criteria
   - Status tracking

9. `.specify/governance/batches/<spec>.md`
   - Batch definitions
   - Wave mapping
   - Review gates
   - Batch progression status

**Rule:** Tasks and batches implement the spec. They cannot create, replace, or imply operational authority, and cannot change higher-tier requirements or constraints.

---

## Conflict Resolution Protocol

When a conflict is detected:

1. **HALT** execution immediately.
2. Identify the conflicting sources and their tier levels.
3. Apply the higher-precedence source.
4. Report the conflict with references to both sources.
5. If both sources are in the same tier, wait for human decision.

**Defect vs conflict:** A statement that violates `.specify/governance/_meta/authority-model.md` is a governance defect, not a precedence conflict. Defects are corrected by fixing the violating document, not by applying this protocol.

If a conflict appears to involve operational authority ownership, resolve the document-content conflict using the steps above, then confirm the authoritative source via `.specify/governance/catalog-decisions.md`.

---

## Extension Policy

If new governance documents are added:
- Update this file to assign their precedence tier.
- Do not modify `.cursor/rules/dormsys.mdc` unless the loading mechanism changes.
- New tier assignments must be documented before the document takes effect.

---

**Document Control**
- Version: 1.1.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team

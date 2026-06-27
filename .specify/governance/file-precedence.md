# File Precedence and Authority Hierarchy

## Source of Truth Order

When resolving conflicts or ambiguities during specification execution, follow this precedence order from highest to lowest:

### Tier 0 — Supreme Authority
1. `.specify/governance/constitution.md`
   - Architectural principles (AP-*)
   - Technology stack
   - Non-negotiable constraints
   - Domain language authority

**Rule**: Constitution is the supreme governance document. Every technical artifact must align with it. If any lower-level artifact conflicts with the Constitution, the Constitution prevails.

### Tier 1 — Boundary & Process Governance
2. `.specify/governance/catalog-decisions.md`
   - Boundary decisions (CD-*)
   - Entity ownership resolutions
   - Cross-context contracts

3. `.specify/governance/specification-playbook.md`
   - Evidence management rules
   - Conflict resolution process
   - Freeze and pipeline policy

**Rule**: Catalog decisions supersede provisional assumptions. Playbook governs process only, not architecture decisions.

### Tier 2 — Specification Layer
4. `specs/<spec>/spec.md`
   - Feature requirements
   - Use cases
   - Operational agreements (OA-*)

5. `specs/<spec>/plan.md`
   - Implementation strategy
   - Dependency graph
   - Execution waves

6. `specs/<spec>/data-model.md`
   - Aggregate designs
   - State machines
   - Schema contracts

**Rule**: Specs must conform to higher tiers. Operational agreements (OA-*) cannot override architectural principles (AP-*) or boundary decisions (CD-*).

### Tier 3 — Execution Layer
7. `specs/<spec>/tasks.md`
   - Task breakdown
   - Acceptance criteria
   - Status tracking

8. `.specify/governance/batches/<spec>.md`
   - Batch definitions
   - Wave mapping
   - Review gates

**Rule**: Tasks implement the specification. Batch maps organize execution. Neither can alter requirements or architectural constraints defined in higher tiers.

## Conflict Resolution Protocol

When a conflict is detected:

1. **HALT execution immediately**
2. Identify the conflicting sources and their tier levels
3. Apply the higher-precedence source
4. Report the conflict with references to both sources
5. Wait for human decision if both sources are in the same tier

## Extension Policy

If new governance documents are added (ADR, RFC, Architecture Notes, Decision Log):
- Update this file to define their precedence tier
- Do not modify `.cursor/rules/dormsys.mdc` unless the governance loading mechanism changes
- Tier assignments must be explicitly documented before the new document takes effect

---

**Document Control**
- Version: 1.0.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team

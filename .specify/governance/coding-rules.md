# Coding Rules

## Purpose

This document consolidates all coding, architecture, and implementation constraints that govern DormSys development. **It does not duplicate governance text.** Instead, it references the authoritative sources by decision ID.

---
## Technology

Technology stack is governed exclusively by AP-01
(constitution.md).

Do not duplicate framework versions or tooling
requirements in this document.
## Architectural Constraints

Apply the following principles during implementation:

| Constraint | Reference | Key Rule |
|---|---|---|
| Modular Monolith | AP-02 (constitution.md § 2.2) | Bounded modules, no shared domain logic |
| Clean Architecture | AP-03 (constitution.md § 2.3) | Dependency direction: Presentation/Infra → Application → Domain |
| Shared Database | AP-04 (constitution.md § 2.4) | No cross-module FKs; isolated table ownership |
| State Machines | AP-05 (constitution.md § 2.5) | Explicit transitions; no direct status updates |

**Do not paraphrase these principles. Cite them by ID when they influence a design choice.**

---

## Boundary Decisions

When implementing entities or aggregates that cross module boundaries, consult:

| Decision | Reference | Summary |
|---|---|---|
| Dependent Entity Ownership | CD-009 (catalog-decisions.md) | Child entities owned by parent's module |
| Approval State vs Transition | CD-010 (catalog-decisions.md) | State in aggregate, transitions in domain service |
| Eligibility Invariant | CD-013 (catalog-decisions.md) | Eligibility module owns invariant validation |
| Allocation ↔ Occupancy | CD-014 (catalog-decisions.md) | Allocation = assignment; Occupancy = physical state |

**Do not reinterpret or simplify these decisions. Reference them by ID.**

---

## Evidence and Citation Rules

When writing code or making design decisions:
1. **Cite the source** (Rule 2, specification-playbook.md)
2. **Separate concept from implementation** (Rule 3, specification-playbook.md)
3. **Do not invent decisions** — if a required decision is missing, HALT (execution-policy.md)

---

## Forbidden Practices

The following are explicitly prohibited by governance:

| Prohibition | Reference | Rationale |
|---|---|---|
| Cross-module FKs | AP-04 | Violates module isolation |
| Direct status field updates | AP-05 | Bypasses state machine invariants |
| Paraphrasing governance text | Rule 2 | Introduces drift and ambiguity |
| Auto-continuing after failure | execution-policy.md | Prevents root-cause analysis |
| Modifying files outside active spec | execution-policy.md, Scope Lock | Uncontrolled blast radius |

---

## Code Quality Standards



---
Code quality requirements are defined by the project
Constitution and CI configuration.

This document references them but does not redefine them.
## Implementation Checklist

Before marking a task complete:
- [ ] All cited decisions are referenced by ID
- [ ] State transitions go through domain methods, not direct updates
- [ ] No cross-module FKs introduced
- [ ] Tests pass (Pest)
- [ ] Static analysis passes (PHPStan)
- [ ] Code style passes (Pint)
- [ ] Architectural review checklist confirms no violations

---

**Document Control**
- Version: 1.0.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team

# Coding Rules

## Purpose

This document defines mandatory coding practices and implementation discipline for DormSys development.

Its purpose is to ensure:

* consistent implementation practices
* maintainable and testable code
* alignment with approved architectural decisions
* visibility of governance rules during development

This document is not an authority source.

It does not define:

* architectural approval
* design decisions
* implementation authorization
* batch execution permission
* governance precedence

Referenced governance documents remain the authoritative sources for their respective rules.

---

## Technology

Technology decisions are governed exclusively by:

* AP-01 (`constitution.md`)

This document does not duplicate technology versions, framework choices, or tooling policies.

---

## Architectural Constraints

Apply approved architectural principles during implementation.

| Constraint            | Source                    | Rule                                             |
| --------------------- | ------------------------- | ------------------------------------------------ |
| Modular Monolith      | AP-02 (`constitution.md`) | Bounded modules with isolated domain ownership   |
| Clean Architecture    | AP-03 (`constitution.md`) | Dependencies flow toward domain logic            |
| Shared Database Rules | AP-04 (`constitution.md`) | No cross-module foreign keys                     |
| State Machines        | AP-05 (`constitution.md`) | State changes occur through explicit transitions |

Do not reinterpret architectural principles. Reference the original decision ID.

---

## Boundary Decisions

When implementation involves cross-module boundaries, consult approved decisions.

| Decision                     | Source                          | Rule                                                        |
| ---------------------------- | ------------------------------- | ----------------------------------------------------------- |
| Dependent Entity Ownership   | CD-009 (`catalog-decisions.md`) | Ownership follows module responsibility                     |
| Approval State vs Transition | CD-010 (`catalog-decisions.md`) | State belongs to aggregate; transitions follow domain rules |
| Eligibility Invariant        | CD-013 (`catalog-decisions.md`) | Eligibility validation ownership is explicit                |
| Allocation and Occupancy     | CD-014 (`catalog-decisions.md`) | Assignment and physical occupancy are separate concepts     |

Do not simplify or reinterpret these decisions.

---

## Evidence and Citation Rules

During implementation:

1. Cite the authoritative source when applying a decision.
2. Separate architectural concepts from implementation details.
3. Do not invent missing decisions.
4. If required governance information is unavailable, halt according to `execution-policy.md`.

---

## Forbidden Practices

The following violations must be detected during implementation.

The Source column identifies the authoritative document.

This table does not transfer authority ownership to this document.

| Prohibition                                        | Source                    | Rationale                     |
| -------------------------------------------------- | ------------------------- | ----------------------------- |
| Cross-module foreign keys                          | AP-04 (`constitution.md`) | Violates module isolation     |
| Direct status updates                              | AP-05 (`constitution.md`) | Bypasses state invariants     |
| Paraphrasing governance decisions                  | specification-playbook.md | Creates ambiguity             |
| Auto-continuing after failure                      | execution-policy.md       | Prevents root cause analysis  |
| Modifying files outside active specification scope | execution-policy.md       | Prevents uncontrolled changes |
| Treating coding rules as execution authority       | file-precedence.md        | Prevents authority confusion  |

---

## Code Quality Standards

Code quality requirements are enforced through project standards and CI configuration.

This document references those requirements but does not redefine them.

Required checks:

* Tests pass (Pest)
* Static analysis passes (PHPStan)
* Code formatting passes (Pint)
* Architectural review confirms compliance

---

## Implementation Checklist

Before completing implementation:

* [ ] All decisions are referenced by ID
* [ ] No architectural rules are violated
* [ ] No unauthorized scope changes are introduced
* [ ] Tests pass
* [ ] Static analysis passes
* [ ] Required review gates are completed

---

## Authority Boundary

This document governs coding behavior only.

It must not be interpreted as:

* a source of design approval
* a source of implementation authorization
* a source of execution permission
* a replacement for governance precedence rules

In case of conflict, the authoritative governance hierarchy defined by `file-precedence.md` applies.

---

**Document Control**

* Version: 1.1.0
* Last Updated: 1405/04/06
* Owner: DormSys Architecture Team

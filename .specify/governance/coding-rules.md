# Coding Rules

## Purpose

This document defines mandatory coding practices and implementation discipline for DormSys development.

It exists to constrain how code is changed, reviewed, and organized during implementation.

This document is not an authority source.

It does not define:

- architectural approval,
- design decisions,
- implementation authorization,
- batch execution permission,
- governance precedence.

Referenced governance documents remain the authoritative sources for the rules they own (AP-*, CD-*, playbook rules, and similar). Governance decision authority ownership is canonical only in `.specify/docs/catalog-decisions.md` § Governance Decision Authority Map.

For canonical governance decision authority ownership, refer only to:

`.specify/docs/catalog-decisions.md`  
`## Governance Decision Authority Map`

---

## Technology Baseline

| Concern | Source | Rule |
|--------|--------|------|
| Framework | `constitution.md` / `AP-01` | Laravel 13 is mandatory |
| UI Stack | `constitution.md` / `AP-01` | Livewire is mandatory |
| Database | `constitution.md` / `AP-01` | PostgreSQL is mandatory |

Implementation must not introduce conflicting technologies without explicit approval from the authoritative source.

---

## Architectural Constraints

| Concern | Source | Rule |
|--------|--------|------|
| Modular Monolith | `constitution.md` / `AP-02` | Preserve module boundaries |
| DDD Discipline | `constitution.md` / `AP-03` | Respect aggregates, repositories, domain services |
| No Boundary Leakage | `constitution.md` / `AP-04` | Do not bypass module ownership |
| Explicit Use Cases | `constitution.md` / `AP-05` | Application flow must remain use-case driven |

These rules constrain implementation behavior only.

They do not transfer architectural authority to this document.

---

## Boundary Decisions

| Concern | Source | Rule |
|--------|--------|------|
| Dependent Entity Ownership | `.specify/docs/catalog-decisions.md` / `CD-009` | Ownership follows module responsibility |
| Approval State vs Transition | `.specify/docs/catalog-decisions.md` / `CD-010` | State belongs to aggregate; transitions follow domain rules |
| Eligibility Invariant | `.specify/docs/catalog-decisions.md` / `CD-013` | Eligibility validation ownership is explicit |
| Allocation and Occupancy | `.specify/docs/catalog-decisions.md` / `CD-014` | Assignment and physical occupancy are separate concepts |

Boundary decision references do not transfer decision ownership to this document.

---

## Implementation Rules

Mandatory implementation behavior:

1. Keep changes within approved scope.
2. Respect module boundaries and ownership.
3. Prefer explicit domain naming over technical shortcuts.
4. Preserve aggregate invariants.
5. Avoid cross-module leakage through direct persistence coupling.
6. Keep application services thin and domain logic intentional.
7. Add tests proportional to implementation risk.
8. Do not invent architectural decisions to fill governance gaps.

---

## Cross-Module Integration Discipline

Before any of the following:

- Integration Implementation Authorization
- cross-module adapter creation
- replacing Null/Stub adapters with live implementations
- provider-consumer Application bindings

apply the mandatory Integration Readiness Gate:

`.specify/governance/patterns/integration-readiness-gate.md`

Authorization must be blocked unless the chain is proven:

`Consumer -> Required Capability -> Accepted Application Contract -> Thin Adapter Mapping`

Allowed readiness outcomes only:

- `READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION`
- `INTEGRATION_AUTHORIZATION_BLOCKED`

The gate does **not** authorize implementation. It only determines whether Integration Implementation Authorization may be requested or issued. Use the reusable template:

`.specify/templates/integration-implementation-authorization-template.md`

Do not invent live cross-module wiring from stub/Null adapters without an issued Integration Implementation Authorization that records `## Integration Readiness Gate`.

---

## Evidence and Citation Rules

1. Cite the authoritative source when applying a decision.
2. Separate architectural concepts from implementation details.
3. Do not invent missing decisions.
4. If required governance information is unavailable, halt according to `execution-policy.md`.

These rules govern implementation discipline, not authority ownership.

---

## Forbidden Practices

This table does not transfer authority ownership to this document.

| Forbidden Practice | Why Forbidden | Authority Source |
|-------------------|---------------|------------------|
| Crossing module boundaries without ownership justification | Breaks modular monolith discipline | `constitution.md` / `AP-04` |
| Hiding business rules in UI or infrastructure | Violates domain ownership clarity | `constitution.md` / `AP-05` |
| Treating inferred behavior as approved design | Creates governance drift | `specification-playbook.md` |
| Continuing after missing governance evidence | Violates halt policy | `execution-policy.md` |
| Resolving governance conflict by local guesswork | Violates precedence discipline | `file-precedence.md` |

---

## Closing Constraint

This document defines implementation constraints only.

It does not grant, modify, replace, or interpret governance authority.

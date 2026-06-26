# Architecture Decision Records (ADRs)

This directory contains Architecture Decision Records (ADRs) for DormSys.

---

## What is an ADR?

An ADR documents an important architectural decision made along with its context and consequences.

**Format:** We use the format described in [Michael Nygard's article](https://cognitect.com/blog/2011/11/15/documenting-architecture-decisions).

---

## Index

### Phase 0: Foundation Decisions (Spec01)

| ADR | Title | Status | Date |
|-----|-------|--------|------|
| [001](001-manual-service-provider-registration.md) | Manual Service Provider Registration | Accepted | 2026-06-22 |
| [002](002-module-boundary-enforcement.md) | Module Boundary Enforcement Mechanism | Accepted | 2026-06-22 |
| [003](003-migration-template-standards.md) | Migration Template Standards | Accepted | 2026-06-22 |
| [004](004-pre-commit-hooks.md) | Pre-Commit Hook Configuration | Accepted | 2026-06-22 |
| [005](005-laravel-version-selection.md) | Laravel Version Selection | Accepted | 2026-06-22 |
| [006](006-module-table-naming-convention.md) | Module-Scoped Table Naming Convention | Accepted | 2026-06-26 |

---

## Creating a New ADR

1. Copy the template from any existing ADR
2. Number it sequentially (ADR-005, ADR-006, etc.)
3. Fill in all sections
4. Update this README.md index
5. Commit with message: `docs: Add ADR-XXX [title]`

---

## ADR Status

- **Proposed:** Under discussion
- **Accepted:** Decision made, implementation can proceed
- **Deprecated:** No longer valid
- **Superseded:** Replaced by another ADR

# Batch Strategy

## Purpose

Defines the generic model for batch composition, risk assessment, and wave assignment. Spec-specific batch maps live in `.specify/governance/batches/<spec>.md`.

---

## Batch Composition Principles

### Batches Are Review Units, Not Task Counts

A batch groups tasks by:
- **Architectural risk** (schema changes, new aggregates, cross-module contracts)
- **Dependency boundaries** (prerequisites must complete in earlier batches)
- **Review focus** (schema, state machines, integrations)

**Batches are NOT defined by:**
- Arbitrary task counts
- Calendar milestones
- Developer assignment

---

## Batch Types

| Type | Description | Typical Size | Review Focus |
|---|---|---|---|
| SCHEMA | Database schema, migrations, initial models | 3–5 tasks | Table ownership, FK rules (AP-04) |
| FOUNDATION | Aggregates, repositories, basic domain services | 5–8 tasks | DDD boundaries, state machines (AP-05) |
| FEATURE | Use case implementation (commands, queries, UI) | 4–7 tasks | Business logic correctness, test coverage |
| INTEGRATION | Cross-module contracts, event handlers | 3–5 tasks | Boundary leaks (CD-*), contract stability |
| POLISH | Validation, error handling, edge cases | 2–4 tasks | Completeness, MVP readiness |

---

## Risk Model

### Low Risk (proceed immediately)
- Internal domain logic (no external dependencies)
- Unit tests
- Read-only queries

### Medium Risk (review after batch)
- New aggregates or state machines
- Application services with multi-step transactions
- New Livewire components

### High Risk (review before AND after batch)
- Schema changes affecting multiple modules
- Cross-module contracts or events
- Changes to shared governance (constitution, catalog decisions)

---

## Wave Model

### Wave 1A — Foundational (auto-authorized)
- Schema, core aggregates, primary use cases
- Must reach MVP gate before Wave 1B opens

### Wave 1B — Secondary Features (checkpoint required)
- Features dependent on external module readiness (e.g., spec03 contracts)
- Opens only after explicit authorization

### Wave 1C — Advanced Features (checkpoint required)
- Complex features (lottery, multi-stage approvals)
- Opens after Wave 1B completes

---

## Batch Numbering

Use clear batch identifiers:
- `B1`, `B2`, `B3`, … (not task ranges like `T008-T012`)
- Batch numbers follow execution order, not phase order
- Example: `B6` (Supplier Contracts, Phase 9) executes before `B8` (FamilyDirect, Phase 6) because Wave 1A closes before Wave 1B opens

---

## Spec-Specific Batch Maps

Each specification defines its batch map in `.specify/governance/batches/<spec>.md`:
- Maps batch IDs to task ranges
- Assigns wave authorization
- Notes dependencies on other specs or waves

**The batch map is the single source of truth for execution order.**

---

**Document Control**
- Version: 1.0.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team

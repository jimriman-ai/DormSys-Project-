# Batch Strategy

## Purpose

Defines the generic model for:

- batch composition,
- risk assessment,
- wave-based execution sequencing.

Spec-specific batch maps live in:

`.specify/governance/batches/<spec>.md`

This document governs **batch composition and execution strategy only**.

It does **not**:

- own, grant, imply, restore, or revoke operational authority,
- define or assign governance decision authority,
- serve as an authorization record,
- act as a source of truth for governance precedence.

For governance decision authority ownership, refer only to:

`.specify/docs/catalog-decisions.md`  
`## Governance Decision Authority Map`

For precedence rules, refer to:

`.specify/governance/file-precedence.md`

---

## Batch Composition Principles

### Batches Are Review Units, Not Task Counts

A batch groups tasks by:

- **Architectural risk**  
  (schema changes, new aggregates, cross-module contracts)
- **Dependency boundaries**  
  (prerequisites must complete in earlier batches)
- **Review focus**  
  (schema, state machines, integrations)

Batches are designed to:

- focus review on coherent slices of change,
- limit risk per execution cycle,
- make rollback and diagnostics manageable.

They are **not** defined by:

- arbitrary task counts,
- calendar milestones,
- developer assignment.

### Composition Constraints

When defining batches (in `.specify/governance/batches/<spec>.md`):

- Group tasks that share the same architectural concern (schema, domain model, integration).
- Avoid mixing unrelated high-risk items in a single batch.
- Ensure dependencies flow from earlier to later batches; do not introduce cycles.
- Keep batch sizes within typical ranges (see **Batch Types**) unless explicitly justified.

Batch composition is a planning discipline, **not an authority tier**.

---

## Batch Types

Typical batch types:

| Type        | Description                                   | Typical Size | Review Focus                                        |
|------------|-----------------------------------------------|-------------|-----------------------------------------------------|
| SCHEMA     | Database schema, migrations, initial models   | 3–5 tasks   | Table ownership, FK rules (AP-04)                  |
| FOUNDATION | Aggregates, repositories, basic domain services| 5–8 tasks  | DDD boundaries, state machines (AP-05)             |
| FEATURE    | Use case implementation (commands, queries, UI)| 4–7 tasks  | Business logic correctness, test coverage          |
| INTEGRATION| Cross-module contracts, event handlers        | 3–5 tasks   | Boundary leaks (CD-*), contract stability          |
| POLISH     | Validation, error handling, edge cases        | 2–4 tasks   | Completeness, MVP readiness                        |

Clarifications:

- Batch type influences **review focus** and **risk expectations**, not authority.
- Architectural principles (AP-*) and catalog decisions (CD-*) still prevail in conflicts:
  - see `.specify/governance/file-precedence.md`,
  - see `.specify/governance/decision-index.md`.

---

## Risk Model

### Purpose

The risk model categorizes batches so that:

- review intensity matches potential impact,
- high-risk changes receive early and late scrutiny,
- low-risk changes remain under discipline without unnecessary overhead.

### Low Risk (standard review)

Examples:

- Internal domain logic (no external dependencies)
- Unit tests
- Read-only queries

Characteristics:

- Localized impact
- Minimal architectural coupling
- Standard review at the **batch review gate**  
  (see `.specify/governance/execution-policy.md` → Review Gate)

### Medium Risk (review after batch)

Examples:

- New aggregates or state machines
- Application services with multi-step transactions
- New Livewire components

Characteristics:

- Changes to behavior or invariants within a module
- Possible non-trivial failure modes
- Require focused review of boundaries and invariants after the batch

### High Risk (review before AND after batch)

Examples:

- Schema changes affecting multiple modules
- Cross-module contracts or events
- Changes to shared governance inputs  
  (e.g., references to `constitution`, `catalog decisions`)

Characteristics:

- Wide impact across modules or governance-sensitive areas
- Require:
  - **pre-batch** review of design and impact, and
  - **post-batch** review of implementation and tests.

Risk classification does **not** grant or revoke authority:

- Authority must be validated via the canonical map and authorization records
  described in `.specify/docs/catalog-decisions.md` and
  `.specify/governance/_meta/authority-model.md`.

---

## Wave Model

### Purpose

Waves define a **phased execution sequence** for feature development.

They are an execution ordering tool, not an authority model.

Actual authorization for any wave or batch:

- must come from the canonical authority map and
- must be recorded in an authorization record instance.

### Wave 1A — Foundational (execution-first focus)

- Schema, core aggregates, primary use cases
- Establishes the minimum viable foundation
- Must reach the MVP gate before Wave 1B opens

Execution Notes:

- Wave 1A is intended for foundational work that **should be authorized early**,
  but it is **not auto-authorized by this document**.
- Whether a Wave 1A batch may run is determined by:
  - the canonical map in `.specify/docs/catalog-decisions.md`, and
  - the corresponding authorization record.

### Wave 1B — Secondary Features (checkpointed)

- Features dependent on external module readiness (e.g., spec03 contracts)
- Opens only after the relevant checkpoints are satisfied

Execution Notes:

- A checkpoint is a combination of:
  - satisfied design/implementation decisions, and
  - valid authorization records covering the batch scope.
- This document **describes** that Wave 1B is gated by checkpoints;
  it does **not** define or issue those checkpoints.

### Wave 1C — Advanced Features (checkpointed)

- Complex features (lottery, multi-stage approvals)
- Opens after Wave 1B completes and its checkpoints pass

Execution Notes:

- Wave 1C is sequenced last by design, to isolate advanced risk.
- Wave 1C execution still requires valid authorization:
  - this strategy does not add a new level of authority,
  - it only determines execution ordering under existing authority.

---

## Spec-Specific Batch Maps

Each specification defines its batch map in:

`.specify/governance/batches/<spec>.md`:

- Maps batch IDs to task ranges
- Records **wave membership** (which wave a batch belongs to; not authorization)
- Notes dependencies on other specs or waves

Clarifications:

- The batch map is the single source of truth for **execution order and grouping**.
- It does **not** create or own authority:
  - execution order must still comply with:
    - authorization records, and
    - the canonical authority map.

If a batch map conflicts with:

- `.specify/docs/catalog-decisions.md` § Governance Decision Authority Map, or
- `.specify/governance/file-precedence.md`,

then:

- **HALT** and escalate per `.specify/governance/execution-policy.md` → Conflict Resolution.

---

## RULE EXECUTION HIERARCHY

When executing a batch:

1. `execution-policy.md`  
   → MUST be enforced first (execution behavior, HALT rules, review gate).
2. `file-precedence.md`  
   → resolves conflicts between governance inputs.
3. `coding-rules.md`  
   → constrains code changes and implementation details.
4. `KNOWN_DEBT.md`  
   → defines allowed failure baseline and explicit technical debt.
5. `review-checklist.md`  
   → defines PASS/FAIL criteria at the batch review gate.

`batch-strategy.md` defines the **strategy** and risk model that these documents apply during execution.

It does **not** override their precedence or execution rules.

---

## EXECUTION ENTRY RULE

All batch execution MUST go through:

`governance-enforcer.md`

Direct execution of any batch without enforcer validation = **FORBIDDEN**.

Clarifications:

- `governance-enforcer.md` coordinates:
  - loading required inputs,
  - enforcing `execution-policy.md`,
  - applying `batch-strategy.md`,
  - running `review-checklist.md`.
- `governance-enforcer.md` is an **enforcement entry point**, not an authority owner.

---

## Document Control

- Version: 1.1.0
- Last Updated: 1405/04/08
- Owner: DormSys Architecture Team

This ownership line controls document maintenance only.  
It does not grant operational authority.

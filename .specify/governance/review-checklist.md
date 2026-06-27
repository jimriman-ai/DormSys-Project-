# Architectural Review Checklist

## Purpose

Run this checklist at the end of every batch before submitting the batch report. Focus on DDD boundaries, aggregate consistency, and governance violations. **This is not a CI checklist** — tests, PHPStan, and Pint must pass before reaching this stage.

---

## Domain Layer

- [ ] **Aggregates**: Each aggregate has a clear root, enforces invariants, and exposes no public setters
- [ ] **State Machines**: All state transitions go through domain methods (AP-05), not direct field updates
- [ ] **Entity Ownership**: Child entities are owned by the correct module (CD-009)
- [ ] **Invariants**: Business rules are enforced in the domain layer, not in application services or controllers

---

## Module Boundaries

- [ ] **No Cross-Module FKs**: Foreign keys do not reference tables outside the module (AP-04)
- [ ] **Dependency Direction**: Presentation/Infra → Application → Domain (AP-03)
- [ ] **Boundary Leaks**: Application services do not expose domain entities directly to the UI layer
- [ ] **Contract Stability**: Cross-module contracts (DTOs, events) are defined and documented

---

## Catalog Decision Compliance

- [ ] **CD-009**: Dependent entities (e.g., `RequestItem`, `ApprovalStep`) are owned by the parent aggregate's module
- [ ] **CD-010**: Approval state lives in the aggregate; approval transitions are handled by domain services
- [ ] **CD-013**: Eligibility checks reference the Eligibility module; invariants are not duplicated
- [ ] **CD-014**: Allocation and Occupancy are cleanly separated; no mixed responsibilities

---

## Evidence and Citation

- [ ] **Decision IDs**: All architectural choices cite the governing decision by ID (AP-*, CD-*, OA-*)
- [ ] **No Paraphrasing**: Governance text is referenced, not reinterpreted
- [ ] **No Invented Decisions**: If a decision is missing, the batch halted and escalated

---

## Testing and Verification

- [ ] **State Transition Tests**: Every state machine transition has a Pest test
- [ ] **Invariant Tests**: Aggregate invariants are tested with invalid data
- [ ] **Integration Tests**: Cross-module interactions are covered (if applicable to this batch)
- [ ] **PHPStan**: Level 8, zero errors
- [ ] **Pint**: Laravel preset, zero violations

---

## Scope and Risk

- [ ] **Scope Lock**: No files outside the active specification's module were modified (unless explicitly required by `tasks.md`)
- [ ] **Wave Authorization**: If this batch belongs to Wave 1B/1C, authorization was confirmed before execution
- [ ] **Failure Protocol**: If tests or static analysis failed, root cause was identified and minimal fix was approved before proceeding

---

## Pass Criteria

A batch passes review if:
- All checklist items are confirmed
- The batch report is complete and accurate
- No governance violations are present

If any item fails, the batch is **rejected** and must be reworked before continuing to the next batch.

---

**Document Control**
- Version: 1.0.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team

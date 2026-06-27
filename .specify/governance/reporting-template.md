# Batch Report Template

Use this template at the end of every batch execution. Fill in all sections accurately. Missing or incomplete reports block progression to the next batch.

---

## Header

**Specification**: `<spec-id>` (e.g., `spec05`)  
**Batch**: `<batch-id>` (e.g., `B1`)  
**Wave**: `<wave>` (e.g., `Wave1A`)  
**Date**: `<date>` (Jalali format)  
**Executor**: `<AI agent identifier or human name>`

---

## Completed Tasks

List all tasks in this batch and mark their status:

- [x] T008 — ...
- [x] T009 — ...
- [ ] T010 — *(if incomplete, explain why)*

**If any task is incomplete, this batch did not finish. Do not proceed.**

---

## Files Changed

### Created
- `path/to/file.php`

### Modified
- `path/to/file.php`

### Deleted *(if any)*
- `path/to/file.php`

---

## Tests

**Pest**:
- Total: `<count>`
- Passed: `<count>`
- Failed: `<count>`

**Coverage** *(if measured)*:
- Lines: `<percentage>`
- Critical paths (state transitions, invariants): `<status>`

---

## Static Analysis

**PHPStan**:
- Level: `<level>`
- Errors: `<count>`
- Status: `PASS | FAIL`

**Pint**:
- Violations: `<count>`
- Status: `PASS | FAIL`

---

## Decision Traceability

### Architectural Principles (AP-*)
- `AP-03`: Clean Architecture — enforced dependency direction in application services
- `AP-04`: No cross-module FKs — verified schema migrations
- `AP-05`: Explicit state machines — implemented `RequestLifecycle` domain service

### Catalog Decisions (CD-*)
- `CD-009`: Dependent entity ownership — `RequestItem` owned by Request module
- `CD-010`: Approval state vs transitions — state in aggregate, transitions in service

### Operational Agreements (OA-*)
- `OA-05-01`: *(if applicable, cite and explain)*

**Rule**: Only cite decisions that influenced this batch. Avoid unnecessary references.

---

## Architecture Impact

### New Domain Elements
- Aggregates: `<list>`
- Value Objects: `<list>`
- Domain Services: `<list>`
- State Machines: `<list>`

### New Application Elements
- Commands: `<list>`
- Queries: `<list>`
- DTOs: `<list>`

### Schema Changes
- Tables created: `<list>`
- Tables modified: `<list>`
- Foreign keys: `<none | list + justification for cross-module FKs>`

---

## Risks and Limitations

### Known Risks
- `<describe any technical debt, edge cases not yet handled, or assumptions that may not hold>`

### Deferred Items
- `<list any work deferred to a later batch, with reason>`

### Assumptions
- `<list any assumptions made during implementation that require validation>`

---

## Review Checklist Result

- [ ] Domain layer: aggregates, state machines, invariants verified
- [ ] Module boundaries: no cross-module FKs, correct dependency direction
- [ ] Catalog decision compliance: CD-009, CD-010, CD-013, CD-014 checked
- [ ] Evidence and citation: decision IDs referenced correctly
- [ ] Testing: state transitions, invariants, integration tests pass
- [ ] Scope lock: no files outside active spec modified
- [ ] Wave authorization: confirmed (if Wave 1B/1C)

**Status**: `PASS | FAIL`

If `FAIL`, describe the violation and required remediation.

---

## Next Batch

**Next Batch ID**: `<batch-id>` (e.g., `B2`)  
**HALT Reason**: `Review gate — awaiting approval`

---

**End of Report**

---

**Document Control**
- Version: 1.0.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team

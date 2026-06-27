# Spec05 Implementation Rules (Request Context)

## 1. Architectural Boundaries
- Strictly operate within the **Request bounded context**.
- NO cross-module changes. DO NOT import or modify `Employee`, `Dormitory`, or `Lottery` infrastructure.
- Use read-only contracts for cross-module communication (OA-05-09).

## 2. Catalog Decisions (Strict Compliance)
- **CD-009**: `Dependent` belongs to `Employee`. `Request` only stores snapshots/references.
- **CD-010**: `RequestApproval` belongs to `Request`. NO Workflow engine implementation.
- **CD-013**: Enforce Business Rule 01 (BR-01) at submit via `EmployeeEligibilityContract`.

## 3. Execution Constraints
- Do not implement Allocation or Lottery mechanisms.
- Request lifecycle ends at `Approved` (OA-05-01). Do not transition to `WaitingForAllocation`.
- Do not build Livewire UI components unless explicitly requested.
- Adhere strictly to the established modular monolith structure (spec01/spec02/spec03).

## 4. AI Process Rules
- Execute tasks sequentially as ordered in `tasks.md`.
- Stop execution immediately if a decision gate, architectural boundary, or database migration error is encountered.
- Update `tasks.md` marking completed tasks with `[x]`.

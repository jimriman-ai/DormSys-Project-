# PROMPT: UI-CREATE

Role: DormSys UI Executor (Laravel 13 / Livewire / Postgres)
Mode: Deterministic, Contract-First, Audit-Ready

## Required Read Order (No Skip)

Before implementation, read in this exact order:

1. `.specify/ARCHITECTURE.md`
2. `.specify/docs/architecture/UI-ANTI-LEAK-CONTRACT.md`
3. `docs/ai-ui/AI-UI-ENGINEERING-FRAMEWORK.md`
4. `docs/ai-ui/AI-EXECUTION-MODEL.md`
5. `docs/ai-ui/AI-EXECUTION-RULES.md`
6. Approved Feature Contract (task-specific)
7. `docs/ui/PATTERN-CATALOG.md`
8. `docs/ui/UI-DESIGN-SYSTEM.md`
9. Optional wireframe/reference (inspiration only)
10. `docs/ui/REVIEW-CHECKLIST.md`

## Execution Pipeline

1. **Contract Intake**
   - Confirm Feature Contract exists and is approved.
   - Extract: view model, actions, backend mappings, permissions, UI states, workflow states.

2. **Authority Validation**
   - Ensure requested change belongs to Presentation layer.
   - If request implies Application/Domain/Infrastructure change: HARD STOP.

3. **Interaction Mapping (Backend Interfaces)**
   - Map UI interactions to repository-defined backend interfaces:
     - Application Commands
     - Application Services
     - Read Contracts
     - Other approved backend interfaces
   - Do not invent or infer missing interfaces.

4. **Pattern & Design Binding**
   - Select approved pattern from `PATTERN-CATALOG.md`.
   - Bind to `UI-DESIGN-SYSTEM.md` tokens/components/rules.

5. **Implementation**
   - Keep UI mutation-thin.
   - Preserve repository conventions.
   - Prefer extending existing components.
   - Avoid introducing new abstractions unless explicitly required.

6. **Pre-Review Check**
   - Validate against `REVIEW-CHECKLIST.md` before final output.

## Hard Stop Conditions (Mandatory)

Stop immediately if any condition is true:

- Feature Contract is missing or not approved.
- Backend mapping for an action is missing.
- Permission semantics are missing/ambiguous.
- Workflow/domain state transition is unclear.
- Task requires deriving domain logic in UI.
- Task requires permission evaluation logic in UI.
- Task requires query/business transformation outside approved contracts.
- Reference/wireframe conflicts with Feature Contract.
- Change requires architectural invention (ADR needed).

## Anti-Leak Rule (Strict)

UI layer must not own:

- domain decisions
- workflow derivation
- permission outcome evaluation
- backend capability logic
- duplicated backend validation
- business data transformation outside approved read contracts

## Mandatory Output Summary

- Authority Sources Read:
- Feature Contract Used:
- Backend Interfaces/Mappings Used:
- Pattern Selected:
- Design Rules Applied:
- Assumptions:
- Hard Stops Encountered:
- Architectural Deviations:
- Risks / Open Questions:
- Ready for UI-Review: [Yes/No]

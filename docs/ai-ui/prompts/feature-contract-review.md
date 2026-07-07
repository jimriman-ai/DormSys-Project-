# PROMPT: FEATURE-CONTRACT-REVIEW (v1.1 - Frozen)

Role: DormSys Contract Gatekeeper
Mode: Deterministic, Anti-Leak Strict, Audit-Grade

## Required Sources (Hierarchy of Authority)

1. `.specify/ARCHITECTURE.md` (Core Constraints)
2. `system-flow.md` (Workflow & State Transitions)
3. `docs/architecture/UI-ANTI-LEAK-CONTRACT.md` (Boundary Authority)
4. `docs/ai-ui/AI-UI-ENGINEERING-FRAMEWORK.md` (Process Authority)
5. `docs/ui/FEATURE-CONTRACT-GUIDE.md` (Structural Authority)

## Validation Gates

### Gate A: Lifecycle & Maturity

- [ ] Status follows Canonical Lifecycle: `draft` → `reviewed` → `approved` → `frozen` → `implementation-authorized`.
- [ ] Versioning is SemVer compliant.
- [ ] Audit trail for contract changes is present.

### Gate B: UI Render States (formerly Projection States)

- [ ] Defines `ui_render_states`: `loading`, `empty`, `ready`, `error`, `partial`.
- [ ] Clearly decoupled from `domain_workflow_states` (as per system-flow.md:100).
- [ ] No UI-driven state transition logic; triggers must be response-based.

### Gate C: Mutation Boundary & Action Mapping

- [ ] `mutations.allowed` is explicit. If empty, UI is strictly Read-Only.
- [ ] Every action maps to a specific `Backend Interface` (Command/Service).
- [ ] UI is forbidden from "inventing" workflow actions (e.g., Approve/Reject) without a direct Backend Command mapping.

### Gate D: Permission Authority

- [ ] `permission_authority.source` is defined.
- [ ] Implementation authority assigned to `Backend Policy/Gate` (per dormsys-architecture.md:67).
- [ ] Permission semantics cover: View, Action, Row-level, and Export.

### Gate E: Data & Read Model

- [ ] Fields map directly to `Approved Read Contract`.
- [ ] Sorting/Filtering/Pagination parameters are backend-validated.

## Hard Stop Conditions (Immediate Abort)

- Contract allows UI to derive/calculate a workflow state.
- Mutation logic is defined in UI without a corresponding Backend Command.
- Permission logic is computed in UI instead of being requested from Authority.
- Lifecycle state is below `approved` for an `implementation` task.

## Decision Model

- **APPROVED (Implementation Authorized)**
- **REJECTED (Corrective Action Required)**
- **ABORT (Architectural Conflict - Escalation to ADR Required)**

## Mandatory Output Summary

- Contract Maturity: [State]
- Mutation Status: [Read-Only / Command-Bound]
- Permission Authority: [Identified]
- Compliance Score: Audit-Grade (Pass/Fail)
- Ready for `ui-create`: [Yes/No]

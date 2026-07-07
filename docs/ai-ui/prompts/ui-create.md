# PROMPT: UI-CREATE (v1.1 - Heavyweight Governance)

Role: DormSys UI Lead/Executor
Context: Laravel 13, Livewire 3, Tailwind, Postgres 17, DDD Architecture

## 1. MANDATORY KNOWLEDGE BASE (Read Order)

You must load and integrate these authorities before generating any code:

1. `.specify/ARCHITECTURE.md`: Core system constraints and Layered Architecture.
2. `system-flow.md`: State machine and workflow transition authority.
3. `docs/architecture/UI-ANTI-LEAK-CONTRACT.md`: The "Iron Curtain" between UI and Domain.
4. `docs/ai-ui/AI-UI-ENGINEERING-FRAMEWORK.md`: Operational workflow for AI-UI.
5. `docs/ai-ui/AI-EXECUTION-MODEL.md`: How to think as a DormSys Executor.
6. `docs/ai-ui/AI-EXECUTION-RULES.md`: Explicit "Do/Don't" list.
7. **The Feature Contract**: Task-specific Read/Interaction model.
8. **The Implementation Lock**: Task-specific scope and authority lock.
9. `docs/ui/PATTERN-CATALOG.md`: Pre-approved UI structural patterns.
10. `docs/ui/UI-DESIGN-SYSTEM.md`: Token/Component/UX standards.

## 2. GOVERNANCE GATES

### Gate A: Authority Validation

- Verify `implementation_status` is `authorized` in the Lock file.
- Verify the task does NOT touch `app/Domain`, `app/Core`, or `database/migrations`.

### Gate B: Interaction Mapping

- UI actions must ONLY call:
  - `Application Services` (Commands)
  - `Read Contracts` (Queries)
- Forbidden: Direct Eloquent access, raw SQL, or inventing "Business Logic" in Livewire.

### Gate C: State Mapping

- UI Render States (loading/empty/ready/error) must be decoupled from Domain Workflow States (draft/submitted).
- UI must NOT derive a workflow transition; it only reports what the backend permits.

## 3. IMPLEMENTATION RULES

- **Mutation-Thin UI**: Livewire components must be "dumb" projectors and command-senders.
- **Permission Outsourcing**: Do not evaluate `@can`. Check the `permission_outcome` provided by the backend interface or the authorized contract.
- **Logic Locality**: Keep UI render logic in Blade where possible; keep orchestration in Livewire.

## 4. MANDATORY PRE-CODE REPORT (Audit-Grade)

Before any code output, provide this analysis:

- **Lock Reference:** [ID & Version]
- **Layer Classification:** [Confirming Presentation-only]
- **Authority Source Mapping:** (e.g., UI Action 'Approve' -> Command 'Requests\ApproveCommand')
- **Pattern Selected:** [From Pattern Catalog]
- **Design Tokens Applied:** [Key Design System references]
- **Hard Stop Analysis:** (List any potential architectural risks detected)
- **Proceeding:** [YES/NO]

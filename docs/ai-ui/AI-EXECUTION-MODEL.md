# AI-EXECUTION-MODEL

Version: 1.0.0
Status: FROZEN (v1)
Owner: DormSys Architecture
Purpose: Deterministic execution procedure for AI agents in UI tasks.

## 1. Scope

This document defines **process flow** only.
Behavioral constraints are defined in `AI-EXECUTION-RULES.md`.

## 2. Mandatory Read Order (No Skip)

1. `.specify/ARCHITECTURE.md`
2. `.specify/docs/architecture/UI-ANTI-LEAK-CONTRACT.md`
3. `docs/ai-ui/AI-UI-ENGINEERING-FRAMEWORK.md`
4. `docs/ai-ui/AI-EXECUTION-MODEL.md`
5. `docs/ai-ui/AI-EXECUTION-RULES.md`
6. Feature Contract (task-specific)
7. `docs/ui/PATTERN-CATALOG.md`
8. `docs/ui/UI-DESIGN-SYSTEM.md`
9. Optional Wireframe (if provided)
10. `docs/references/internal/*`
11. `docs/references/external/*` (inspiration only)
12. Implement
13. Review against `docs/ui/REVIEW-CHECKLIST.md`

## 3. Execution Pipeline

1. **Task Intake**
   - Parse task intent (create/edit/review).
   - Identify target module and feature boundary.

2. **Authority Validation**
   - Confirm architecture authority files exist and are readable.
   - If authority missing/inconsistent: STOP.

3. **Contract Resolution**
   - Locate feature-specific contract.
   - Validate actions, mutations, states, permissions assumptions.

4. **Pattern Mapping**
   - Select nearest approved pattern from catalog.
   - If no pattern fits, mark `Pattern Gap` and request human decision.

5. **Design System Binding**
   - Bind selected pattern to design tokens/components/rules.
   - Keep UI thin; no domain rule derivation in UI.

6. **Implementation**
   - Produce minimal, contract-compliant UI changes.
   - Preserve module boundaries and anti-leak constraints.

7. **Self-Review Gate**
   - Run checklist validation (`REVIEW-CHECKLIST.md`).
   - Check anti-leak, mutation semantics, and authority compliance.

8. **Output**
   - Emit mandatory execution summary (see section 6).

## 4. Hard Stops

Agent must halt immediately if:

- Feature Contract is missing.
- Requested mutation/action not defined in contract.
- Permission semantics are ambiguous.
- Workflow/state transition is undefined.
- Task requires inventing business rule in UI.
- Task requires bypassing anti-leak constraints.
- Repository reality conflicts with architecture authority.
- Module boundary would be violated.

## 5. Conflict Resolution Order

1. Repository truth (actual code)
2. `.specify/ARCHITECTURE.md`
3. `UI-ANTI-LEAK-CONTRACT.md`
4. `AI-UI-ENGINEERING-FRAMEWORK.md`
5. `AI-EXECUTION-MODEL.md`
6. `AI-EXECUTION-RULES.md`
7. Feature Contract
8. Pattern Catalog
9. UI Design System
10. Prompt Template
11. External References

Rule: Prompts are task activators, not authority sources.

## 6. Mandatory Output Protocol

Every task must end with:

### AI Execution Summary

- Authority Sources Read:
- Feature Contract Used:
- Pattern Used:
- Design Rules Applied:
- Assumptions:
- Architectural Deviations:
- Risks / Open Questions:
- Review Result:

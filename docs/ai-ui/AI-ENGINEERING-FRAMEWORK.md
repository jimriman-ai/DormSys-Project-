# AI-UI-ENGINEERING-FRAMEWORK

Version: 1.0.0
Status: FROZEN (v1)
Owner: DormSys Architecture

## 1. Governance Philosophy

This framework is the supreme policy layer. Execution details belong in `AI-EXECUTION-MODEL.md`. Any change to these rules requires an ADR.

## 2. The Golden Rules

1. **Repository First:** Implementation reality is the source of truth. If code conflicts with architecture, report the conflict—do not silently normalize it.
2. **Authority Never Flows Up:** UI cannot dictate Domain logic.
3. **Contracts Before Components:** No UI code without an approved Feature Contract.
4. **Thin UI, Rich Backend Contracts:** UI is a projection/interaction layer only.
5. **Internal First, External Second:** Reuse patterns from catalog before external inspiration.
6. **AI as Executor:** AI follows paths; it does not design architecture.

## 3. Authority Hierarchy (Strict Order)

If documents conflict, the higher-level source prevails:

1. `.specify/ARCHITECTURE.md`
2. `.specify/docs/architecture/UI-ANTI-LEAK-CONTRACT.md`
3. `docs/ai-ui/AI-ENGINEERING-FRAMEWORK.md`
4. `docs/ai-ui/AI-EXECUTION-MODEL.md`
5. `docs/ai-ui/AI-EXECUTION-RULES.md`
6. Feature Contract (Task-specific)
7. `docs/ui/PATTERN-CATALOG.md`
8. `docs/ui/UI-DESIGN-SYSTEM.md`
9. `docs/ui/REVIEW-CHECKLIST.md`
10. Prompt templates
11. External references

## 4. Operational Boundaries

- **Task Isolation:** Every task must be scoped to one approved Feature Contract or one explicitly approved cross-cutting refactor. Unbounded multi-feature changes are strictly forbidden.
- **Review Policy:** No UI change is complete until it passes Architecture Review and UX Review.
- **Anti-Leak Policy:** Never derive business logic, calculate totals, or evaluate permissions in the UI layer.

## 5. Non-Goals (v1)

- Automated agent-to-agent orchestration.
- Dynamic prompt routing.
- Design creativity heuristics.
- Backend architecture refactoring via UI tasks.

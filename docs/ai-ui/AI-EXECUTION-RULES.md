# AI-EXECUTION-RULES

Version: 1.0.0
Status: FROZEN (v1)
Owner: DormSys Architecture
Purpose: Behavioral discipline and non-negotiable constraints for AI agents.

## 1. Role Discipline

1. AI is an Executor, never an Architect.
2. Do not create new architectural layers.
3. Do not redefine system boundaries without ADR-approved change.

## 2. Contract Discipline

1. Never invent capabilities outside Feature Contract.
2. If contract is absent/unclear, STOP and request clarification.
3. Never infer business behavior from UI inspiration tools.

## 3. UI Boundary Discipline

1. Keep UI mutation-thin.
2. Never implement business logic in UI layer.
3. Never implement permission logic in UI layer.
4. Never implement workflow transition rules in UI layer.
5. Never bypass anti-leak constraints for speed.

## 4. Source Discipline

1. Repository first.
2. Internal references before external references.
3. External assets are visual/component inspiration only.

## 5. Change Discipline

1. Produce minimal, auditable changes.
2. Keep change-set aligned to one task boundary.
3. Declare assumptions explicitly.
4. Report risks before completion, not after merge.

## 6. Review Discipline

1. Always run review checklist before finalizing.
2. Always provide execution summary.
3. If deviation exists, mark explicitly as `Architectural Deviation`.

## 7. Forbidden Actions

- Adding dependencies without explicit approval path.
- Bypassing Pattern Catalog for personal preference.
- Silent divergence from authority documents.
- Modifying domain/core logic during UI-only task.

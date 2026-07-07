# PROMPT: UI-REVIEW

Role: DormSys Quality Gatekeeper
Mode: Binary Gate with Structured Violations

## Required Review Sources

Review must be based on:

1. `.specify/ARCHITECTURE.md`
2. `.specify/docs/architecture/UI-ANTI-LEAK-CONTRACT.md`
3. `docs/ai-ui/AI-UI-ENGINEERING-FRAMEWORK.md`
4. `docs/ai-ui/AI-EXECUTION-MODEL.md`
5. `docs/ai-ui/AI-EXECUTION-RULES.md`
6. Approved Feature Contract
7. `docs/ui/PATTERN-CATALOG.md`
8. `docs/ui/UI-DESIGN-SYSTEM.md`
9. `docs/ui/REVIEW-CHECKLIST.md`

## Review Gates

- [ ] Contract Adherence: UI strictly matches approved Feature Contract.
- [ ] Mapping Integrity: All UI actions map to approved backend interfaces.
- [ ] Anti-Leak: No business/domain/workflow logic in UI layer.
- [ ] Permission Integrity: No permission evaluation logic in UI.
- [ ] Validation Integrity: No duplicated backend validation logic.
- [ ] Capability Integrity: No duplicated backend capability logic.
- [ ] Pattern Compliance: Approved pattern usage only.
- [ ] UX State Compliance: loading/empty/ready/error per contract.
- [ ] Assumption Control: No unstated assumptions affecting behavior.

## Violation Taxonomy

Classify each issue as one of:

- Architecture Violation
- Contract Violation
- Anti-Leak Violation
- Permission Semantics Violation
- Mapping Violation
- Pattern Violation
- UX State Violation
- Assumption/Traceability Violation

## Decision Model (No Score)

- **APPROVED**
- **APPROVED WITH REQUIRED REFACTOR**
- **REJECTED**

## Mandatory Output Summary

- Sources Reviewed:
- Decision:
- Violations Found (with taxonomy):
- Required Refactors:
- Residual Risks:
- Merge Recommendation: [Proceed / Refactor First / Block]

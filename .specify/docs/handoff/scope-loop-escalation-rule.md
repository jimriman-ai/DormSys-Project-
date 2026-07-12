# Scope Loop Escalation Rule

**Artifact type:** Governance escalation rule (non-authorizing)  
**Decision date:** 2026-07-11  
**Checkpoint:** `scope-loop-escalation-rule`

---

## Status

`SCOPE_LOOP_ESCALATION_RULE_ACCEPTED`

---

## Rule

`Scope Loop Escalation Rule`

---

## Purpose

When a work item completes a scope revision cycle and still cannot receive implementation authorization, the work item must not automatically return to scope revision or implementation authorization retry.

The next required step becomes:

`OWNER_CLOSURE_DECISION_REQUIRED`

This restriction applies unless materially new scope evidence or authority-changing governance input is introduced.

---

## Trigger Condition

A work item has:

- implementation authorization denial
- denial analysis
- scope revision
- another authorization review
- another implementation authorization denial

---

## Required Escalation

`OWNER_CLOSURE_DECISION_REQUIRED`

---

## Allowed Decisions

The owner must choose exactly one:

- `APPROVE_CONSTRAINED_CONTINUATION`
- `DEFER_WORK_ITEM`
- `CLOSE_WORK_ITEM`

---

## Non-Authorization

`This artifact defines governance escalation only and does not authorize implementation.`

---

## No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

---

## Governance Basis

| Artifact | Role |
| -------- | ---- |
| [authority-model.md](../../governance/_meta/authority-model.md) | Authority model vocabulary and non-authorization invariants |
| [catalog-decisions.md](../catalog-decisions.md) | Canonical Governance Decision Authority Map (not modified) |
| [spec-catalog.md](../spec-catalog.md) | Spec catalog reference (not modified) |

---

## Explicit Boundaries

This artifact:

- defines governance escalation only
- does **not** authorize implementation
- does **not** change any feature scope
- does **not** create implementation tasks
- does **not** modify application code or tests
- does **not** grant Design Approval, Implementation Authorization, or Batch Execution Permission

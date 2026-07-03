# spec08 Nomination Record

**Recorded:** 2026-07-01  
**Authority:** Product / Tech governance  
**Decision class:** Next Spec Transition Nomination (non-operational)

---

## Document classification

| Property | Value |
| -------- | ----- |
| **Type** | Nomination Record (evidence-only) |
| **Authority map role** | None — not listed in `## Governance Decision Authority Map` |
| **Grants Design Approval** | **No** |
| **Grants Implementation Authorization** | **No** |
| **Grants Batch Execution Permission** | **No** |
| **Grants execution authority** | **No** |

This record is an **evidence-only** instance of **Next Spec Transition Nomination** per `.specify/governance/_meta/authority-model.md` §2. It does **not** satisfy operational authority checks.

---

## Nomination header

| Field | Value |
| ----- | ----- |
| **nomination-status** | `active` |
| **nominated-spec** | spec08 — External Accommodation (Voucher) |
| **nominated-by** | Governance Review |
| **effective-date** | 2026-07-01 |
| **supersedes** | — |
| **superseded-by** | — |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §2; `.specify/governance/execution-policy.md` § Nomination and Execution Policy |

```text
nomination-status: active
nominated-spec: spec08
transition-trigger: spec07-implementation-closure
governance-transition-state: OPEN FOR AUTHORIZATION INITIATION
execution-state: NOT AUTHORIZED
```

---

## Trigger

spec07 is **FULLY CLOSED**.

| Item | State |
| ---- | ----- |
| spec07 program | **CLOSED** (T001–T074) |
| Wave 1A | **CLOSED** |
| Wave 1B | **CLOSED** (`revoked` — program closure) |
| Active execution scope | **NONE** |
| Closure evidence | [`spec07-implementation-authorization-wave1b.md`](./spec07-implementation-authorization-wave1b.md) § Program Closure Record |

---

## Nomination decision

**spec08** is formally nominated as the **NEXT spec candidate** for entry into the authorization pipeline.

| Transition | Value |
| ---------- | ----- |
| **Previous spec** | spec07 |
| **Previous spec status** | CLOSED |
| **Current active execution spec** | NONE |
| **Next spec candidate** | spec08 |

---

## Governance action

This record activates the governance transition for spec08 and **permits initiation of the spec08 authorization pathway** (governance precondition for next-spec processes per `execution-policy.md`).

### Authorized by this record

- Recognition of spec08 as nominated next-spec candidate
- Initiation of authorization-path preparation
- Creation of required spec08 governance records (e.g., planning authorization, design approval pathway artifacts)

### Not authorized by this record

- Execution
- Implementation
- Task breakdown
- Planning scope approval
- Design Approval
- Batch or wave authorization

---

## Constraints

- No execution authority is created
- No implementation scope is opened
- No planning artifact is treated as authorization unless explicitly approved by the required governance record
- spec07 remains **CLOSED** and unchanged
- Catalog ordering or status mirrors alone do **not** substitute for this record

---

## Governance effect

| Item | State |
| ---- | ----- |
| **spec08 status** | CANDIDATE → **NOMINATED FOR AUTHORIZATION** |
| **Governance transition state** | **OPEN FOR AUTHORIZATION INITIATION** |
| **Execution state** | **NOT AUTHORIZED** |

---

## Boundary context (informational)

| Item | State |
| ---- | ----- |
| **CD-016** | ACCEPTED — Voucher owns eligibility and issuance lifecycle |
| **OQ-07** | CLOSED |
| **context-map R8** | Lottery / Allocation → Voucher (upstream triggers) |
| **Catalog dependencies** | spec01, spec05, spec06 (informational — not execution authorization) |

Boundary closure does **not** imply implementation readiness.

---

## Final state

**TRANSITION RECORDED** — spec08 nominated for authorization initiation.

Next operational steps require separate governance records per the canonical authority map (Design Approval, Implementation Authorization as applicable). Missing operational authority remains **HALT** regardless of this nomination.

---

## References

- [`spec07-implementation-authorization-wave1b.md`](./spec07-implementation-authorization-wave1b.md)
- [`spec07-implementation-state.md`](./spec07-implementation-state.md)
- [`.specify/docs/spec-catalog.md`](../spec-catalog.md)
- [`.specify/docs/catalog-decisions.md`](../catalog-decisions.md) CD-016
- [`.specify/governance/execution-policy.md`](../../governance/execution-policy.md)

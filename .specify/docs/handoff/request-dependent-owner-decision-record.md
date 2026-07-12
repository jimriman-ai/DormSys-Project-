# Request Dependent Owner Decision Record

**Artifact type:** Owner decision record (non-authorizing)  
**Recorded:** 2026-07-11  
**Actors:** Product / Architecture (manual owner decision)  
**Checkpoint:** `request-dependent-owner-decision-record`

---

## 1. Decision Record Status

| Field | Value |
| ----- | ----- |
| **Status** | **`REQUEST_DEPENDENT_OWNER_DECISION_RECORDED`** |
| **Effect** | Resolves owner-input wait on D-01 / D-02 / D-03 for the **current** Completion Wave scope |
| **Implementation?** | **No** — this record does not authorize coding, adapters, contracts, or Integration IA |

---

## 2. Source Artifacts

| Artifact | Prior status |
| -------- | ------------ |
| [request-dependent-integration-readiness-gate.md](./request-dependent-integration-readiness-gate.md) | `REQUEST_DEPENDENT_INTEGRATION_BLOCKED_BY_MISSING_DECISION` |
| [request-dependent-integration-decision-package.md](./request-dependent-integration-decision-package.md) | `REQUEST_DEPENDENT_DECISION_PACKAGE_REQUIRES_OWNER_INPUT` |
| [request-dependent-owner-decision-request.md](./request-dependent-owner-decision-request.md) | `AWAITING_OWNER_DECISION` |
| [spec03-us3-completion-handoff.md](./spec03-us3-completion-handoff.md) | `SPEC03_US3_COMPLETED` — live Request Dependent integration not included |

Supporting references: `.specify/governance/execution-policy.md`, `.specify/governance/_meta/authority-model.md`, `specs/003-employee-context/spec.md`, `specs/005-request-management/spec.md`, `specs/005-request-management/contracts/employee-request-boundary.md`.

---

## 3. Recorded Decisions

### D-01 — Stub Timing

| Field | Value |
| ----- | ----- |
| **Decision** | **`DEFER_LIVE_INTEGRATION`** |
| **Meaning** | Request Dependent live integration is deferred to a later named gate. The approved `DependentSnapshotSourceStub` remains active for the current scope. Current scope does **not** require live Employee Dependent replacement. |

### D-02 — `eligible` Semantics

| Field | Value |
| ----- | ----- |
| **Decision** | **`ELIGIBLE_REQUIRES_EXPLICIT_PRODUCT_RULE`** |
| **Meaning** | `DependentSnapshotReadDTO.eligible` must not be mapped, derived, exposed, or implemented from Employee data until Product defines the explicit eligibility rule. No adapter, contract, or Request logic may invent this meaning. |

### D-03 — Employee Application Surface

| Field | Value |
| ----- | ----- |
| **Decision** | **`NO_EMPLOYEE_DEPENDENT_READ_SURFACE_NOW`** |
| **Meaning** | No Employee Application Dependent read contract is authorized for the current scope. If live integration is approved in a future gate, Architecture must authorize an Employee Application read contract before IRG can pass. Request-to-Employee repository access remains forbidden. |

---

## 4. Rationale

Live Request ↔ Employee Dependent integration is deferred because:

1. **Approved stub behavior** already satisfies Spec05 FamilyDirect Wave 1B (T039) for the current scope.
2. **No Product requirement** currently forces live replacement of `DependentSnapshotSourceStub` after Spec03 US3 completion.
3. **`eligible` has no approved semantics** on Employee Dependent (no domain field / product rule); mapping it now would invent behavior and fail Integration Readiness Gate thin-adapter rules.
4. **No accepted Employee Application Dependent read contract** exists; exposing repositories to Request is forbidden by `employee-request-boundary.md`.
5. Implementing live wiring **now** would require invented business meaning and unauthorized Application surface work.

This record does not reinterpret the owner choices; it preserves them verbatim for governance continuity.

---

## 5. Current Approved Behavior

**`DependentSnapshotSourceStub` remains the approved current behavior for Request Dependent snapshot support.**

- Request continues to own immutable dependent snapshots (CD-009).
- Employee continues to own Dependent lifecycle (Spec03 US3 complete).
- No live cross-module Dependent source binding is in force.

---

## 6. Explicit Non-Authorizations

This decision record does **NOT** authorize:

- live Request-to-Employee Dependent integration  
- adapter / Integrations bridge implementation  
- Employee Application Dependent read contract creation  
- Request module rewiring or provider rebinding away from the stub  
- Integration Implementation Authorization  
- Integration Readiness Gate **PASS** status  
- Spec03 US4, EmployeeRead (T049–T052), UI work, or Spec04/Spec07 reopen  

Authority ownership remains only in `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`.

---

## 7. Future Reopen Conditions

Request Dependent live integration may be reopened **only if** one or more of the following occur:

1. Product names a future gate or release requiring live Dependent integration.  
2. Product defines `eligible` semantics (or separately authorizes removal from the live path).  
3. Architecture authorizes an Employee Application Dependent read contract.  
4. A future Integration Readiness Gate is scheduled with those decisions available.

Until then, treat live integration as **out of current execution scope**.

---

## 8. Next Allowed Governance Step

**Proceed with other approved work that does not require Request Dependent live integration.**

**Do not re-run the Request Dependent IRG until Product and Architecture provide the required future integration inputs.**

Owner decision request status is satisfied for the current wave: wait-state `AWAITING_OWNER_DECISION` is closed by this record for D-01 / D-02 / D-03 as stated above.

---

## 9. No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

---

## Document Control

- Version: 1.0.0  
- Status: **`REQUEST_DEPENDENT_OWNER_DECISION_RECORDED`**  
- Owner: Product / Architecture (recorded by Governance)  
- Last Updated: 2026-07-11  
- Related request: [request-dependent-owner-decision-request.md](./request-dependent-owner-decision-request.md)

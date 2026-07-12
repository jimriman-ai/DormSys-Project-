# Request Dependent Owner Decision Request

**Artifact type:** Owner decision request (non-deciding / non-authorizing)  
**Request date:** 2026-07-11  
**Status:** **`AWAITING_OWNER_DECISION`**

**Upstream:**

| Artifact | Result |
| -------- | ------ |
| [request-dependent-integration-readiness-gate.md](./request-dependent-integration-readiness-gate.md) | `REQUEST_DEPENDENT_INTEGRATION_BLOCKED_BY_MISSING_DECISION` |
| [request-dependent-integration-decision-package.md](./request-dependent-integration-decision-package.md) | `REQUEST_DEPENDENT_DECISION_PACKAGE_REQUIRES_OWNER_INPUT` |
| [spec03-us3-completion-handoff.md](./spec03-us3-completion-handoff.md) | `SPEC03_US3_COMPLETED` — live Request Dependent integration **not** included |

This document **requests** Product and Architecture decisions. It does **not** make those decisions, authorize integration, create contracts/adapters, or change Request/Employee behavior.

---

## 1. Why this request exists

Integration Readiness Gate cannot PASS until owners record explicit answers to timing and `eligible` semantics. Spec03 US3 Employee Dependent CRUD is complete; Spec05 FamilyDirect remains on the **approved stub** (`DependentSnapshotSourceStub`).

**Current binding rule (unchanged until owners decide otherwise):**

- Keep stub  
- Do not issue Integration Implementation Authorization  
- Do not implement live Request ↔ Employee Dependent wiring  

---

## 2. Decision owners

| Role | Decisions owned in this request |
| ---- | ------------------------------- |
| **Product** | D-01 (stub timing), D-02 (`eligible` rule or removal intent) |
| **Architecture** | D-03 (Employee Application provider surface shape) — **only if** Product selects a live path in D-01 |
| **Governance** | Records outcomes; may authorize later contract definition / IRG re-run — **not** in this request |

---

## 3. Decisions requested (owners must choose)

### D-01 — Stub replacement timing (Product)

**Question:** Is live Request Dependent source replacement required now?

**Choose exactly one:**

| Option ID | Meaning |
| --------- | ------- |
| `REQUIRED_NOW` | Product mandates live Employee-backed Dependent source for FamilyDirect in the current completion wave |
| `DEFERRED_TO_LATER_GATE` | Stub remains; live path deferred to a **named** later gate/release (Product must name it) |
| `NOT_REQUIRED_FOR_CURRENT_SCOPE` | Stub remains for current product-core / Spec05 completed scope; no live path scheduled |

**Evidence note (non-binding):** Repository evidence supports deferral/stub remain more than `REQUIRED_NOW`. Product may still choose `REQUIRED_NOW` explicitly.

**If `DEFERRED_TO_LATER_GATE`, Product must also state:**

- Named gate / release identifier: `________________`

---

### D-02 — `DependentSnapshotReadDTO.eligible` semantics (Product)

**Question:** What does `eligible` mean for live FamilyDirect Dependent snapshot sourcing?

Request currently rejects submission when `eligible === false`. Employee Dependent has **no** `eligible` field in Spec03 data-model/entity.

**Choose exactly one primary classification:**

| Option ID | Meaning |
| --------- | ------- |
| `EMPLOYEE_SOURCED` | Product defines a rule that Employee domain/application must supply eligibility for FamilyDirect dependents (Architecture later places it on accepted Application contract) |
| `REQUEST_DERIVED` | Product defines an approved derivation rule from already-accepted Dependent attributes (must be written explicitly — no silent invention) |
| `REMOVE_FROM_LIVE_PATH` | Live integration must not rely on `eligible`; consumer/DTO change requires separate later authorization |
| `DEFER_WITH_STUB` | No live-path rule yet; keep stub seeding; resolve before any live IRG PASS |

**If `EMPLOYEE_SOURCED` or `REQUEST_DERIVED`, Product must write the rule in one sentence:**

> Rule: `________________`

---

### D-03 — Employee Application provider surface (Architecture) — conditional

**Activate only if D-01 = `REQUIRED_NOW`** (or Product names a live gate that is now being opened).

**Question:** Which accepted Employee Application surface will supply Dependent snapshot fields for Request?

**Choose exactly one direction (shape only — no API inventing in this request):**

| Option ID | Meaning |
| --------- | ------- |
| `DEDICATED_DEPENDENT_READ_CONTRACT` | New/accepted Employee Application Dependent read/supplier contract for snapshot source fields |
| `VIA_EMPLOYEE_READ_PHASE7` | Use Spec03 Phase 7 `EmployeeReadContract` (or extension) as the vehicle — requires separate Spec03 authorization beyond US3 |
| `BLOCKED_PENDING_PRODUCT` | Architecture declines to select until D-01/D-02 closed |

**Hard constraints Architecture must respect:**

- Request must not call Employee Dependent repositories directly  
- Live bridge belongs in `app/Integrations/` after IRG PASS + Integration IA  
- Thin adapter must not invent business behavior (especially `eligible`)  

---

## 4. Confirmed non-decisions (already evidence-backed)

Owners need **not** re-decide these for this request:

| Topic | Confirmed finding |
| ----- | ----------------- |
| Integration purpose (if live) | Read-only lookup for **snapshot materialization** at FamilyDirect create/submit |
| CD-009 | Employee owns Dependent lifecycle; Request owns immutable snapshots |
| Stub today | Approved Spec05 Wave 1B strategy; still bound |
| US3 | Complete for T035–T040; does not authorize Request live wiring |

---

## 5. Response template (owners fill)

```text
REQUEST_DEPENDENT_OWNER_DECISION_RESPONSE
Date:
Actors: Product: ________  Architecture: ________

D-01: [ REQUIRED_NOW | DEFERRED_TO_LATER_GATE | NOT_REQUIRED_FOR_CURRENT_SCOPE ]
Named gate (if deferred): ________

D-02: [ EMPLOYEE_SOURCED | REQUEST_DERIVED | REMOVE_FROM_LIVE_PATH | DEFER_WITH_STUB ]
Rule text (if needed): ________

D-03: [ DEDICATED_DEPENDENT_READ_CONTRACT | VIA_EMPLOYEE_READ_PHASE7 | BLOCKED_PENDING_PRODUCT | N/A ]

Notes:
________
```

Record the completed response as a separate handoff artifact (recommended path):  
`.specify/docs/handoff/request-dependent-owner-decision-response.md`  
(or catalog-decisions Change Log entry if Product prefers that register).

---

## 6. What happens after owners respond

| If Product chooses… | Next allowed governance step |
| ------------------- | ---------------------------- |
| `NOT_REQUIRED_FOR_CURRENT_SCOPE` or `DEFERRED_TO_LATER_GATE` (+ named gate) | Update Completion Wave sequencing; keep stub; **no** Integration IA; IRG remains non-PASS for live path |
| `REQUIRED_NOW` + D-02 rule closed | Architecture completes D-03 → Contract definition authorization (separate) → Re-run IRG → only then Integration IA |
| D-02 left open while D-01 = live | **HALT** — cannot accept Employee Application contract or IRG PASS |

**Still forbidden until Integration IA after IRG PASS:** adapters, provider rebinding, `DependentSnapshotSourceStub` replacement, Request/Employee production code for this edge.

---

## 7. Explicit non-authority

This request:

- Does **not** grant Design Approval, Implementation Authorization, or Batch Execution Permission  
- Does **not** substitute for Integration Readiness Gate PASS  
- Does **not** authorize Spec03 US4, EmployeeRead (T049–T052), UI, or Spec04/Spec07 reopen  

Authority ownership remains `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`.

---

## 8. No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

---

## Document Control

- Version: 1.0.0  
- Status: **`AWAITING_OWNER_DECISION`**  
- Package outcome reference: `REQUEST_DEPENDENT_DECISION_PACKAGE_REQUIRES_OWNER_INPUT`  
- Owner request checkpoint: `request-dependent-owner-decision-request`  
- Last Updated: 2026-07-11

# Completion Wave Plan — Core Completion Before UI

**Artifact type:** Program planning / governance (non-authorizing)  
**Decision date:** 2026-07-11  
**Upstream audit:** Spec Completion & Readiness Audit → `REQUIRES_SPEC_COMPLETION_BEFORE_UI`  
**Upstream roadmap:** Spec Completion Roadmap → `SPEC_COMPLETION_ROADMAP_READY`  
**Program decision:** UI Feature Execution is deferred until product-core readiness is established.

**This artifact does not:**

- grant Design Approval, Implementation Authorization, or Batch Execution Permission
- reopen Spec04, Spec07, or any closed Spec
- authorize UI Feature Contracts, UI implementation, or coding
- replace or modify existing Application contracts
- modify Request integration or any live cross-module adapter

Authority ownership remains only in `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`.  
Vocabulary / lifecycle: `.specify/governance/_meta/authority-model.md`.  
Execution behavior: `.specify/governance/execution-policy.md`.  
Cross-module live wiring: `.specify/governance/patterns/integration-readiness-gate.md`.

---

## 1. Goal

Establish a governed **Core Completion Wave** that closes the highest-impact **product-core backend gaps** before resuming UI Feature Execution.

Immediate focus of Batch 1: **Spec03 Employee Context** remaining hold scope (US3 Dependent), with US4 treated as a separate evidence-driven follow-on — not assumed work.

UI Feature Execution (including `audit-ui` feature-contract drafting and all other UI candidates) remains **deferred** relative to this wave’s product-core priority, except that general UI Framework work remains unblocked per the audit (framework ≠ feature contract readiness).

---

## 2. Scope Boundaries

### In scope (Completion Wave — planning)

| Item | Boundary |
| ---- | -------- |
| Spec03 US3 Dependent (T035–T040) | Employee-module Dependent domain, persistence, Application actions, Employee-local tests |
| Spec03 US4 eligibility (T041–T048) | Evidence assessment first; authorize only gaps proven missing or defective |
| Spec03 Phase 7 `EmployeeReadContract` (T049–T052) | Optional follow-on; only if product-core / downstream consumers require it |
| Spec03 Phase 8 polish for newly authorized slices | Quality gates scoped to authorized files |
| Integration Readiness Gate **evaluation** (planning only) | For future Request live Dependent source replacement — not implementation in Batch 1 |
| Catalog / handoff hygiene notes | Documentation only; does not reopen closed Specs |

### Out of scope (hard)

| Item | Rule |
| ---- | ---- |
| UI Feature Execution / Feature Contracts / Livewire feature work | Deferred |
| Spec04 backend Phases 1–4 | Closed — do not reopen (`SPEC04_BACKEND_CLOSED`) |
| Spec07 | Fully closed — do not reopen |
| Spec08 / Spec09 / Spec10 / Spec11 closures | Do not reopen |
| Workflow Engine activation | Deferred until catalog activation criteria (CD-010) |
| Request module integration changes | Forbidden in Batch 1; future live adapter requires IRG + Integration Implementation Authorization |
| Replacing `DependentSnapshotSourceStub` | Not Batch 1; IRG-gated |
| Replacing / rewriting accepted contracts | Forbidden — do not replace existing contracts |
| Allocation / CheckIn / Lottery / Voucher product UI paths | Separate product decisions |

---

## 3. Product-Core Blockers (Wave-Relevant)

| Priority | Blocker | Evidence-backed status | Blocks | Wave action |
| -------- | ------- | ---------------------- | ------ | ----------- |
| **P0** | Spec03 US3 Dependent | `PARTIALLY_COMPLETE` — US1/US2 closed; US3 hold; no `employee_dependents` table / entity / actions | Live Dependent ownership (CD-009); live Family Dependent source path | **Batch 1** — authorize then implement Employee-internal US3 |
| **P1** | Spec03 US4 Eligibility completeness | Partial code exists; tasks still open; contract drift vs `contracts/employee-eligibility-service.md` | Full CD-013 supplier fidelity; eligibility UX | **Batch 1b** — evidence gap analysis → scoped IA only for proven gaps |
| **P1** | Request Dependent live source | Stub (`DependentSnapshotSourceStub`) until US3 supplier exists | Live Family Dependent integration | **After US3 DoD** — IRG → Integration IA (not Spec03 reopen of Request) |
| **P2** | Spec04 Auth / UI readiness | Backend closed; Auth/UI unresolved | `dormitory-ui` | Later wave — no Phase 1–4 reopen |
| **P2** | Spec06 governance regularization | Code ahead of Nomination/DA/IA artifacts | Lottery UI | Parallel hygiene — not Batch 1 |
| **P3** | Workflow Engine | Deferred / not started | `workflow-ui` | Out of this wave unless activation criteria met |

Audit reminder: no Spec blocks **general UI Framework**; no open UI candidate is **contract-ready** today. This wave addresses product-core gaps that block Dependent / Family readiness specifically.

---

## 4. Non-Goals

1. Do not treat this plan as Implementation Authorization.
2. Do not start UI Feature Contracts or UI coding under this plan.
3. Do not reopen Spec04, Spec07, or closed Specs.
4. Do not modify Request integration, bindings, or adapters in Spec03 US3 Employee work.
5. Do not invent Application capabilities to “make IRG pass.”
6. Do not assume US4 / Phase 7 must be rewritten because tasks.md checkboxes are open — verify evidence first.
7. Do not activate Workflow Engine.
8. Do not expand Spec03 into Livewire HR admin (plan Phase F / R-15 remains deferred).

---

## 5. Execution Order

```text
Wave 0 — Governance (this package)
    → Completion Wave Plan (this file)
    → Spec03 Readiness Package
    → HALT for human Implementation Authorization of Batch 1 scope

Batch 1 — Spec03 US3 (Employee-internal only)
    → Implementation Authorization record (authorized-scope: T035–T040 verbatim)
    → Implement Dependent domain/persistence/application/tests
    → Review gate (execution-policy)
    → Do NOT touch Request adapters

Batch 1b — Spec03 US4 evidence + scoped completion (optional / separate IA)
    → Gap inventory vs contracts + runtime consumers (Request)
    → Implementation Authorization only for proven missing/defective items
    → Prefer align-to-accepted-consumer behavior over rewriting Request

Batch 2 — Integration readiness (separate program step)
    → Integration Readiness Gate for:
        Consumer: Request
        Capability: findSnapshotForDependent(employeeId, sourceDependentId)
        Provider: Employee Dependent read surface (must exist from Batch 1)
    → Only if READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION
    → Issue Integration Implementation Authorization
    → Thin live adapter replacing stub — no contract replacement

Later waves (not Batch 1)
    → Spec04 Auth/UI readiness (no backend reopen)
    → Spec06 auth regularization
    → UI Feature Execution resume (product choice; audit-ui remains shortest UI path when UI is re-enabled)
```

**One batch at a time** per `.specify/governance/execution-policy.md`. Review gate HALT between batches.

---

## 6. Authorization Requirements

| Step | Operational authority required? | Artifact pattern |
| ---- | ------------------------------- | ---------------- |
| This plan / Spec03 readiness package | No (planning only) | Handoff planning docs |
| Spec03 US3 coding | **Yes** — Implementation Authorization | `.specify/docs/handoff/spec03-implementation-authorization.md` (or successor superseding post-MVP hold) with verbatim `authorized-scope` |
| Spec03 US4 gap fill | **Yes** — separate or extended IA | Same map pattern; scope must list exact tasks |
| Request live Dependent adapter | **Yes** — Integration Implementation Authorization **after** IRG | Template: `.specify/templates/integration-implementation-authorization-template.md` |
| Batch progression after review | Batch Execution Permission / human review gate | Per Authority Map + execution-policy |
| UI Feature Contracts | Separate UI product + lock-review path | Out of this wave |

**Pre-execution checks (execution-policy):**

1. Design readiness for Spec03 US3 already exists in `specs/003-employee-context/` (spec/plan/tasks/contracts/data-model) — do not invent new contracts.
2. Current hold: `.specify/docs/handoff/spec03-post-mvp-authorization.md` explicitly **does not authorize** T035+.
3. Starting T035+ without a new active/partial Implementation Authorization covering those tasks is **Case A HALT**: `Missing or invalid implementation authorization record.`
4. Nomination Record is **not** required to resume held scope of Spec03 (already executed Wave 1A/1B); do not invent a transition nomination unless governance treats this as a new next-spec process.

---

## 7. Integration Readiness Gate Applicability

| Change | IRG required? | Notes |
| ------ | ------------- | ----- |
| Spec03 US3 Dependent inside Employee module | **No** | Intra-module domain/persistence/application |
| Binding Employee DI for Dependent repository | **No** | Module provider only |
| Replacing `DependentSnapshotSourceStub` with live Employee-backed adapter | **Yes** | Cross-module consumer→provider |
| Binding live adapter in Request / IntegrationServiceProvider | **Yes** | Provider-consumer Application binding |
| Changing Request `DependentSnapshotSourceContract` shape | **Blocked path** | Do not replace contracts; map to accepted consumer contract |
| Live Allocation port for Employee eligibility | **Yes** | When replacing null/stub with live Allocation read |
| Spec04 Allocation/CheckIn dormitory wires | **Yes** | Later wave; do not reopen Spec07 |

Gate outcomes only: `READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION` | `INTEGRATION_AUTHORIZATION_BLOCKED`.  
The gate does **not** authorize coding.

---

## 8. Completion Wave Decision

| Field | Value |
| ----- | ----- |
| **Decision** | **`COMPLETION_WAVE_READY`** |
| **First execution batch** | Spec03 US3 (T035–T040) after Implementation Authorization |
| **Immediate next human action** | Issue Spec03 US3 Implementation Authorization (see Spec03 Readiness Package) |
| **Clarifications deferred (non-blocking)** | Exact US4 / Phase 7 inclusion in Batch 1b; product timing for UI resume |

Non-blocking clarifications may be resolved when drafting Batch 1b authorization; they do **not** block Batch 1 US3 authorization drafting.

---

## 9. References

- `.specify/docs/spec-catalog.md` — Spec03 inventory / hold notes
- `.specify/docs/handoff/spec03-post-mvp-authorization.md` — US3+ hold
- `.specify/docs/handoff/spec04-backend-closeout.md` — `SPEC04_BACKEND_CLOSED`
- `.specify/docs/handoff/spec03-readiness-package.md` — companion package
- Spec Completion & Readiness Audit (2026-07-11) — `REQUIRES_SPEC_COMPLETION_BEFORE_UI`
- Spec Completion Roadmap (2026-07-11) — product-core blocker framing

---

## Document Control

- Version: 1.0.0
- Status: Planning — non-authorizing
- Owner: DormSys Architecture / Governance Review
- Last Updated: 2026-07-11

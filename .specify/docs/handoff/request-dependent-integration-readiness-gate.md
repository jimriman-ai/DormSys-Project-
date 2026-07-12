# Request Dependent Integration Readiness Gate

**Artifact type:** Integration Readiness Gate assessment (non-authorizing)  
**Gate date:** 2026-07-11  
**Pattern:** `.specify/governance/patterns/integration-readiness-gate.md`  
**Execution policy:** `.specify/governance/execution-policy.md` § Integration Implementation Authorization Issuance  
**Authority model:** `.specify/governance/_meta/authority-model.md`

This gate assesses whether Spec03 US3 completion unblocks **Request ↔ Employee Dependent** live integration planning. It does **not** authorize implementation, adapters, bindings, or contract creation.

---

## 1. Gate Decision

| Field | Value |
| ----- | ----- |
| **Decision** | **`REQUEST_DEPENDENT_INTEGRATION_BLOCKED_BY_MISSING_DECISION`** |
| **IRG chain result** | **`INTEGRATION_AUTHORIZATION_BLOCKED`** |
| **Integration Implementation Authorization** | **Must not be issued** |

**Outcome A not selected:** accepted Employee Application provider contract is missing; thin mapping cannot be proven without inventing behavior.  
**Outcome C not selected:** Spec05 documents a planned live supplier path after US3; this is not a “never integrate” deferral — it is blocked by missing decisions/artifacts required for IRG PASS.

---

## 2. Scope Under Review

Potential **Request Module** live consumption of **Employee-owned Dependents** after Spec03 US3 (T035–T040) completion, specifically for FamilyDirect snapshot source resolution via `DependentSnapshotSourceContract`.

Out of scope for this gate:

- US4 Employee eligibility completion
- UI / Livewire
- Spec04 / Spec07 reopen
- Any coding, adapter creation, or provider rebinding

---

## 3. Current Confirmed Baseline

| Fact | Evidence |
| ---- | -------- |
| Employee Dependent capability completed | `spec03-us3-completion-handoff.md` — `SPEC03_US3_COMPLETED`; T035–T040 delivered |
| Employee owns Dependent lifecycle | CD-009; Employee domain/persistence/application |
| Request owns immutable snapshots | `request_dependent_snapshots`; CD-009 / OA-05-04 |
| Request integration not performed | `DependentSnapshotSourceStub` still bound in `RequestServiceProvider` |
| Spec05 FamilyDirect shipped on stub | `tasks.md` T037–T039 complete; T039 documents stub when US3 unavailable |
| No UI / US4 expansion in US3 | US3 IA `blocked-scope`; completion handoff |

---

## 4. Evidence Reviewed

### Handoff / governance

- `.specify/docs/handoff/spec03-us3-completion-handoff.md`
- `.specify/docs/handoff/spec03-us3-implementation-batch1-review.md`
- `.specify/docs/handoff/spec03-implementation-authorization-us3.md`
- `.specify/docs/handoff/completion-wave-plan.md` (context)
- `.specify/governance/patterns/integration-readiness-gate.md`
- `.specify/governance/execution-policy.md`
- `.specify/governance/_meta/authority-model.md`
- `docs/architecture/integration-layer-policy.md`

### Specs

- `specs/003-employee-context/spec.md` (US3)
- `specs/003-employee-context/tasks.md` (T035–T040 complete; EmployeeRead T049–T052 hold)
- `specs/003-employee-context/data-model.md` (Dependent fields)
- `specs/005-request-management/spec.md` (US4 FamilyDirect / CD-009)
- `specs/005-request-management/plan.md` (Wave 1B stub gate)
- `specs/005-request-management/tasks.md` (T037–T039)
- `specs/005-request-management/contracts/employee-request-boundary.md`

### Code boundaries (read-only)

- Request: `DependentSnapshotSourceContract`, `DependentSnapshotReadDTO`, `DependentSnapshotSourceStub`, `CreateFamilyDirectRequestAction`, `RequestServiceProvider` binding
- Employee: `Dependent` entity, `DependentRepositoryContract` (module-internal repository — not a cross-module Application supplier contract)
- No Employee Application Dependent **read/supplier** contract present under `app/Modules/Employee/Application/Contracts/`

---

## 5. Integration Need Assessment

### Answers to assessment questions

| # | Question | Finding |
| - | -------- | ------- |
| 1 | Does Request require live Dependent integration **now**? | **Not proven.** Spec05 Wave 1B completed with **approved stub strategy** (T039). Stub remains the bound production/test source. US3 completion removes the “US3 unavailable” *blocker for a future live path*, but does **not** itself mandate stub replacement. |
| 2 | Explicit spec dependency Request → Employee Dependents? | **Yes (conditional).** Spec05 US4 / FR-010 / OA-05-04 / `employee-request-boundary.md`: FamilyDirect captures snapshots from Employee supplier **when live**, or fixtures/stub until then. |
| 3 | Exact integration purpose (if pursued)? | **Read-only lookup for snapshot materialization** at FamilyDirect create/submit: resolve `sourceDependentId` → immutable snapshot fields. Not Request ownership of Dependent lifecycle; not workflow enrichment as primary purpose. |
| 4 | Employee read capability required first? | **Yes — and missing.** Boundary forbids Request calling Employee Dependent repositories directly. No accepted Employee Application contract exposes a Dependent read projection suitable for mapping. `EmployeeReadContract` / Dependent supplier surface remains hold (Spec03 T049–T052). |
| 5 | Boundary shape required? | Per integration-layer policy: **thin bridge in `app/Integrations/`** implementing Request’s port by calling an **accepted Employee Application contract** — not module-to-module Infrastructure, not Request importing Employee Domain/Infrastructure. |
| 6 | Enough authority to authorize integration now? | **No.** |
| 7 | What blocks it? | See §7. |

### IRG chain status

```text
Consumer: Request Module
    ✓ evidenced (CreateFamilyDirectRequestAction + DependentSnapshotSourceContract)

Required Capability:
    DependentSnapshotSourceContract::findSnapshotForDependent(employeeId, sourceDependentId): ?DependentSnapshotReadDTO
    ✓ precisely defined on consumer port

Accepted Application Contract (Employee):
    ✗ MISSING — no accepted Employee Application Dependent read/supplier contract
    ✗ DependentRepositoryContract is internal persistence port, not an approved cross-module Application contract
    ✗ Request must not call Employee repositories directly (employee-request-boundary.md)

Thin Adapter Mapping:
    ✗ Cannot be proven — provider contract absent
    ✗ DependentSnapshotReadDTO.eligible has no counterpart on Employee Dependent entity/data-model
       → mapping would invent eligibility meaning (forbidden by IRG thin-adapter rule)
```

**Conclusion on need:** Live integration is a **documented future intent**, not a **current mandatory operational requirement**. Spec05 remains functional on stub. Advancing to Integration Implementation Authorization is **blocked** until missing decisions/contracts are resolved.

---

## 6. Boundary and Contract Assessment

| Topic | Assessment |
| ----- | ---------- |
| Read contract required? | **Yes.** Cross-module reads must go through an accepted Employee Application contract (or equivalent accepted supplier surface). Internal `DependentRepositoryContract` is insufficient and forbidden as a Request dependency. |
| Adapter / integration layer? | **Yes, when authorized.** Policy places bridge under `app/Integrations/` + `IntegrationServiceProvider::register()`, implementing Request’s `DependentSnapshotSourceContract`. |
| Policy already defines pattern? | **Yes** for *where* bridges live (`integration-layer-policy.md`). **No** accepted Employee Dependent Application contract exists to map from. |
| Exception model needed? | **Not for location** if Integrations path is used. **Do not** copy legacy in-module adapter anti-patterns. |
| Speculative design avoided | This gate does **not** invent the Employee Application API shape, `eligible` semantics, or DTO field mapping. Those require separate architecture/product decisions. |

---

## 7. Missing Prerequisites or Decisions

| ID | Missing item | Why it blocks |
| -- | ------------ | ------------- |
| **M-01** | Accepted **Employee Application Dependent read/supplier contract** | IRG requires Accepted Application Contract link; internal repository is not that link |
| **M-02** | Decision on **`eligible` field semantics** for live source | Consumer DTO requires `eligible`; Employee Dependent has no such field — thin adapter cannot invent it |
| **M-03** | Explicit **product/governance decision** that stub replacement is now required | Spec05 completed under stub; US3 completion alone is not Implementation Authorization for live wiring |
| **M-04** | Integration Implementation Authorization (after IRG READY) | Execution-policy: IRG PASS is precondition; this gate is not PASS |
| **M-05** | (Related hold) Spec03 **EmployeeRead / Phase 7** or equivalent Dependent supplier authorization | May be the vehicle for M-01, but is currently hold and out of US3 scope |

Non-blockers for this assessment (do not confuse with authorization):

- Spec03 US3 Employee-internal Dependent CRUD — **complete**
- Consumer port definition — **present**
- Integration layer policy for bridge placement — **present**

---

## 8. Next Allowed Governance Step

**Architecture + product decision package (not coding):**

1. Decide whether live Request Dependent source replacement is a **current** product requirement, or remain on stub until a named release/gate.
2. If live path is required, define and accept an **Employee Application Dependent read contract** (fields, nullability, ownership checks) that can map to `DependentSnapshotReadDTO` **without invented behavior** — including resolution of `eligible`.
3. Re-run this Integration Readiness Gate; only if outcome becomes readiness PASS may Integration Implementation Authorization be drafted (template: `.specify/templates/integration-implementation-authorization-template.md`).
4. Until then: keep `DependentSnapshotSourceStub`; do not modify Request bindings; do not create Integrations Dependent bridges.

**Not next:** Implementation of adapters, US4 eligibility, UI Feature Contracts, or Spec04/Spec07 reopen.

---

## 9. No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

---

## Document Control

- Version: 1.0.0  
- Status: Gate assessment complete  
- Decision: **`REQUEST_DEPENDENT_INTEGRATION_BLOCKED_BY_MISSING_DECISION`**  
- Owner: DormSys Architecture / Governance Review  
- Last Updated: 2026-07-11  
- Checkpoint: `request-dependent-integration-readiness-gate`

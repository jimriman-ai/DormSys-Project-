# Request Dependent Integration Decision Package

**Artifact type:** Product / architecture decision package (non-authorizing)  
**Package date:** 2026-07-11  
**Resolves blockers from:** [`.specify/docs/handoff/request-dependent-integration-readiness-gate.md`](./request-dependent-integration-readiness-gate.md)

This package structures missing decisions for Request ↔ Employee Dependent live integration. It does **not** implement adapters, define final contract code, issue Integration Implementation Authorization, or modify modules.

---

## 1. Decision Package Status

| Field | Value |
| ----- | ----- |
| **Status** | **`REQUEST_DEPENDENT_DECISION_PACKAGE_REQUIRES_OWNER_INPUT`** |
| **Why not READY** | Repository evidence structures the questions and options, but **Q1 timing** and **Q4 `eligible` semantics** lack a definitive recorded product/architecture decision — owners must choose |

---

## 2. Source Blocker Reference

| Field | Value |
| ----- | ----- |
| **IRG decision** | `REQUEST_DEPENDENT_INTEGRATION_BLOCKED_BY_MISSING_DECISION` |
| **IRG chain** | `INTEGRATION_AUTHORIZATION_BLOCKED` |

**Why blocked (summary):**

1. No accepted **Employee Application Dependent read/supplier contract** (internal `DependentRepositoryContract` is not a cross-module Application surface; Request must not call Employee repositories — `employee-request-boundary.md`).
2. No approved source/semantics for **`DependentSnapshotReadDTO.eligible`** (Employee Dependent has no `eligible` field; Request rejects when `eligible === false`).
3. No explicit product/governance decision that **stub replacement is required now** (Spec05 Wave 1B completed under approved stub T039).

---

## 3. Decision Questions

### Q1. Stub replacement necessity

**Question:** Is Request live Dependent source replacement required now, or should approved stub behavior remain until a later named release/gate?

| Allowed outcome | Evidence fit |
| --------------- | ------------ |
| `REQUIRED_NOW` | **Not supported.** No handoff/product authorization mandates stub replacement after US3. Spec05 T001–T052 complete with stub. |
| `DEFERRED_TO_LATER_GATE` | **Compatible.** Spec05 planned “Employee supplier when US3 live **or** fixtures”; fixtures/stub already satisfied Wave 1B. Live path remains future intent. |
| `NOT_REQUIRED_FOR_CURRENT_SCOPE` | **Compatible for current operational scope.** FamilyDirect works on stub; Completion Wave product-core US3 is Employee-only complete. |
| `REQUIRES_PRODUCT_DECISION` | **Required classification for timing.** Repository cannot choose “now” vs “later named gate” without Product. |

**Analysis:** Spec03 US3 removes the “US3 unavailable” *technical* blocker for a future live path. It does **not** equal a product mandate to replace the stub. Spec05 intentionally authorized and completed FamilyDirect under stub (T039).

**Structured answer pending Product:** choose among `DEFERRED_TO_LATER_GATE`, `NOT_REQUIRED_FOR_CURRENT_SCOPE`, or (only with explicit product mandate) `REQUIRED_NOW`.

**Recommended provisional classification (non-binding):** `REQUIRES_PRODUCT_DECISION` → prefer `DEFERRED_TO_LATER_GATE` unless Product names a release requiring live source.

---

### Q2. Integration purpose

**If live integration is required, what is the exact purpose?**

| Candidate | Evidence |
| --------- | -------- |
| **read-only lookup for snapshot materialization** | **Supported.** `CreateFamilyDirectRequestAction` resolves `findSnapshotForDependent` then appends immutable `DependentSnapshot` rows (CD-009 / OA-05-04 / FR-010). |
| validation support | **Partial / secondary.** Request validates ownership + `eligible` on the **read DTO** after lookup; validation is not a separate integration purpose. |
| workflow enrichment | **Not evidenced** for this edge. |
| other | **None evidenced.** |

**Evidence-backed purpose:** **read-only lookup for snapshot materialization** at FamilyDirect create/submit.

Not: Dependent lifecycle ownership by Request; not Employee repository exposure; not Workflow engine.

---

### Q3. Accepted provider surface

**What form of Employee provider surface would satisfy governance without violating boundaries?**

| Requirement | Decision shape (not implementation) |
| ----------- | ----------------------------------- |
| Employee Application read/supplier contract | **Required** before IRG can PASS — accepted Application contract in Employee (or equivalent accepted supplier API), not Infrastructure |
| Approved read DTO / projection | **Required** — fields must map to consumer need without inventing behavior |
| Ownership / authorization checks | **Required at boundary** — Request already checks `ownerEmployeeId`; provider must not expose cross-employee dependents as valid hits |
| Repository exposure to Request | **Forbidden** — `employee-request-boundary.md`: Request does not call Employee Dependent repositories directly |
| Bridge placement | **Policy already defined** — thin bridge in `app/Integrations/` implementing Request’s `DependentSnapshotSourceContract` by calling the accepted Employee Application contract (`integration-layer-policy.md`) |

**Do not invent API signatures in this package.** Contract text/DTO shape require a separate **contract definition authorization** after Product/Architecture decide Q1 and Q4.

**Related hold:** Spec03 Phase 7 `EmployeeReadContract` (T049–T052) is a candidate vehicle for general Employee reads, but Dependent snapshot supplier may be a dedicated Application surface — Architecture must decide which without expanding Spec03 US3.

---

### Q4. `eligible` semantics

**What decision is required for `DependentSnapshotReadDTO.eligible`?**

| Classification | Evidence |
| -------------- | -------- |
| sourced from Employee domain/application | **Not evidenced.** Spec03 Dependent data-model/entity fields: id, employeeId, names, relationship, age?, nationalCode? — **no `eligible`**. |
| derived in Request from approved inputs | **Possible in theory** but **no recorded rule** defining derivation from Dependent attributes. |
| removed from live integration requirement | **Possible product choice** (would require consumer/DTO/contract change authorization — out of this package’s implement scope). |
| **requires explicit product rule before any contract can be accepted** | **Best fit.** Request currently enforces `if (! $snapshot->eligible) throw …`. Stub tests seed `eligible: true/false`. No Spec03/Spec05 decision states what “dependent eligible for FamilyDirect” means in domain terms. |

**Structured answer:** **`requires explicit product rule before any contract can be accepted`**

Ambiguity must remain visible: filling `eligible` from Employee status, relationship type, age, or always-true would be **invented business logic** and fail IRG thin-adapter rules.

---

### Q5. Next governance path

| Option | Fit |
| ------ | --- |
| Product decision required | **Yes** — Q1 timing; Q4 `eligible` rule (or removal) |
| Architecture decision / ADR required | **Yes** — Employee Application Dependent supplier shape vs Phase 7 EmployeeRead; confirmation Integrations bridge pattern for this edge |
| Contract definition authorization required | **Yes, after** Q1 (if live) + Q4 + Architecture shape — not before |
| Re-run IRG after decisions | **Yes** — only after accepted provider contract + `eligible` decision + product “live now/later” |
| No action / remain deferred | **Valid interim** if Product selects deferral without opening contract work |

**Chosen next path (package recommendation):**  
**Product decision required** → **Architecture decision** (provider surface) → **Contract definition authorization** (if live path selected) → **Re-run IRG**.

---

## 4. Options Matrix

| Option | Benefit | Risk | Governance impact | Unblocks IRG? |
| ------ | ------- | ---- | ----------------- | ------------- |
| **A. Stub remains (current)** | Spec05 FamilyDirect stays green; zero cross-module churn; respects completed Wave 1B stub strategy | Live Employee Dependent data unused by Request; FamilyDirect tests stay fixture-driven | No Integration IA; IRG stays blocked or reclassified deferred by product | **No** (and OK if Product chooses deferral) |
| **B. Live integration later (named gate)** | Clear program sequence: decide `eligible` + Employee Application contract first; then IRG → Integration IA | Delay until named gate; must not silently start adapters | Requires Product naming of gate/release; Architecture + contract auth before IRG | **Not until** later gate completes decisions |
| **C. Live integration now after contract work** | Real Employee Dependents feed FamilyDirect snapshots | Highest complexity; `eligible` undefined; EmployeeRead/Dependent supplier still hold | Needs Product REQUIRED_NOW + Architecture + Contract definition IA + IRG PASS + Integration IA | **Only after** all missing decisions and accepted contract — not “now” as-is |

---

## 5. Required Decisions

| Decision | Owner | Input needed |
| -------- | ----- | ------------ |
| Q1: stub remain vs deferred named gate vs required now | **Product** | Explicit choice among allowed Q1 outcomes; if deferred, name the gate/release |
| Q4: meaning of Dependent `eligible` for FamilyDirect (or remove from live path) | **Product** (+ **Architecture** for layer ownership) | Written rule: Employee-sourced field, Request derivation rule, or DTO change authorization |
| Q3: Employee Application Dependent supplier vs Phase 7 EmployeeRead | **Architecture** / **Spec03–05 owners** | Which accepted Application surface supplies snapshot fields |
| Confirm Integrations bridge as only live wiring path | **Architecture** / **Governance** | Align with `integration-layer-policy.md` (already policy-default) |
| Authorize drafting Employee Application Dependent read contract | **Governance** (Implementation / Design style auth per map) | Only if Product selects live path (B or C) |
| Re-run IRG | **Governance / Architecture** | After accepted contract + Q4 closed |

---

## 6. Recommended Decision Path

**Safest path based on current evidence:**

1. **Product** records Q1 as **`DEFERRED_TO_LATER_GATE`** (or `NOT_REQUIRED_FOR_CURRENT_SCOPE`) unless a named release requires live FamilyDirect sourcing from Employee.
2. **Product + Architecture** open a short decision on Q4 `eligible` **before** any live contract work (do not invent always-true mapping).
3. Keep **`DependentSnapshotSourceStub`** bound; do not create Integrations Dependent bridges.
4. If/when Product flips to live path: Architecture defines Employee Application Dependent read surface → Governance authorizes **contract definition only** → accept contract → **re-run IRG** → only then Integration Implementation Authorization.

This avoids speculative contracts and preserves Spec05’s completed stub-backed FamilyDirect scope.

---

## 7. Next Allowed Governance Step

**Immediate next step:**  
**Product owner decision on Q1 and Q4** (record answers in a Product Decision / ADR / catalog decision as appropriate).

Until those owner answers exist:

- Do **not** issue Integration Implementation Authorization  
- Do **not** authorize adapter implementation  
- Do **not** treat Spec03 US3 completion as live Request integration authority  
- Optional: Architecture may draft **options-only** notes for Employee Application surface shapes — still no coding and no contract acceptance without Product Q1/Q4

After Product answers:

- If deferred → update IRG disposition / Completion Wave batch sequencing; no further Request Dependent IRG advancement  
- If live required → Architecture decision on provider surface → Contract definition authorization → Re-run IRG

---

## 8. No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

---

## Traceability

| Artifact | Role |
| -------- | ---- |
| `request-dependent-integration-readiness-gate.md` | Source blockers (M-01–M-05) |
| `spec03-us3-completion-handoff.md` | US3 complete; live Request integration still hold |
| Spec05 `tasks.md` T037–T039 | FamilyDirect + stub strategy complete |
| `employee-request-boundary.md` | No direct Employee Dependent repository use |
| `integration-layer-policy.md` | Bridge placement policy |
| This package | Structures Q1–Q5 for owner input |

---

## Document Control

- Version: 1.0.0  
- Status: **`REQUEST_DEPENDENT_DECISION_PACKAGE_REQUIRES_OWNER_INPUT`**  
- Owner: DormSys Architecture / Governance Review (awaits Product)  
- Last Updated: 2026-07-11  
- Checkpoint: `request-dependent-integration-decision-package`

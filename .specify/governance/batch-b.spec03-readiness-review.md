# Spec03 Integrity & Readiness Review (Batch B)

**Artifact type:** Evidence-based readiness review (non-authorizing)  
**Spec:** `003-employee-context` / catalog `spec03`  
**Program context:** Spec Completion Wave — integrity review before further implementation or UI gating  
**Review date:** 2026-07-12  
**Checkpoint:** `batch-b.spec03-readiness-review`

**This artifact does not:**

- authorize implementation, Integration Authorization, or Batch Execution Permission
- authorize or reopen UI Feature Contracts / Main UI Wave
- invent new product scope
- modify application code, contracts, or bindings

Authority ownership remains only in `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`.  
Execution behavior: `.specify/governance/execution-policy.md`.

---

## 1. Current State Evidence

### 1.1 Implemented components (evidenced)

| Slice | Scope | Evidence |
| ----- | ----- | -------- |
| **US1 — Employee + Identity attachment** | Domain entity, immutable `identity_id`, `CreateEmployeeAction`, migrations `employee_employees`, BT-01–BT-05 path | `app/Modules/Employee/**` (Entity/Action/Repo); `tests/Feature/Modules/Employee/EmployeeIdentityBoundaryTest.php`; `tests/Architecture/EmployeeSupplierBoundaryTest.php`; Wave 1A close in `spec03-post-mvp-authorization.md` |
| **US2 — Department** | Department CRUD + assignment + inactive guard | Actions/repos/models; `tests/Feature/Modules/Employee/DepartmentTest.php`; Wave 1B complete per `tasks.md` / post-MVP handoff |
| **US3 — Dependent records** | `employee_dependents` migration; Dependent entity/model/repo; `AddDependentAction` / `UpdateDependentAction`; Employee-local tests | Migration `2026_07_11_000003_create_employee_dependents_table.php`; `DependentTest.php`; handoff `SPEC03_US3_COMPLETED` |
| **US4 — Eligibility (Batch 1b gap fill)** | `EligibilityReasonCode`, `EligibilityCalculator`, `ActiveAllocationReadPort` + `NullActiveAllocationReadAdapter`, gap-filled `EmployeeEligibilityService`, `EmployeeEligibilityContractTest` (4 scenarios) | Code under `app/Modules/Employee/`; completion handoff `SPEC03_US4_BATCH1B_IMPLEMENTATION_COMPLETED` |
| **Auth seam (mutations)** | `EmployeeMutationAuthorizationGate` on create/assign/deactivate/dependent mutations | Application services inject gate; Identity-active principal check |
| **Pending-request port (live bridge)** | `PendingRequestReadPort` bound to `PendingRequestReadBridge` | `app/Providers/IntegrationServiceProvider.php` — preserved by Batch 1b (not Null) |
| **Presentation (separate UI feature)** | `EmployeeHubPage` + hub route/tests | Closed as `employee-context-ui` (`FEATURE_CLOSED`) — **not** Spec03 Completion Wave deliverable (see §4) |

### 1.2 Missing components (Spec03-owned, still open)

| Item | Tasks / refs | Status |
| ---- | ------------ | ------ |
| **`EmployeeReadContract` supplier** | T049–T052 | **Missing** — no `EmployeeReadContract` / `EmployeeReadService` / `EmployeeSummaryDTO` in `app/`; README still lists as “later waves” |
| **Phase 8 polish checkboxes** | T053–T058 | **Open / incomplete as Spec03 tasks** — BT-05 MVP exists; full post-US3/US4 regression polish, README refresh, quickstart Scenarios 1–9 formal pass record not evidenced as closed Spec03 DoD |
| **DOC-OPT — eligibility contract markdown sync** | Batch 1b optional | **Deferred** — `contracts/employee-eligibility-service.md` still documents Wave 1A `EmployeeId`-only signature; runtime uses `string` + `excludingRequestId` (accepted consumer truth) |
| **Null PendingRequest adapter / greenfield T043–T044 full stub pair** | Original Phase 6 | **Intentionally not delivered** — Batch 1b preserved live PendingRequest bridge; Null Pending path blocked as reversion |
| **Live Allocation eligibility adapter** | Spec07 / IRG | **Out of Spec03 Batch 1b** — Null ActiveAllocation remains |

### 1.3 Ambiguous / stale documentation components

| Artifact | Drift | Impact |
| -------- | ----- | ------ |
| `specs/003-employee-context/spec.md` **Status** line | Still: “US3+ not authorized” | Misleading vs US3 + US4 Batch 1b completion handoffs |
| `specs/003-employee-context/tasks.md` Phase 6 checkboxes | T041–T048 still `[ ]` while Batch 1b delivered subset | Task board lag; must not be read as “US4 not implemented” without handoff evidence |
| `.specify/docs/spec-catalog.md` Spec03 row | Still “US3+ hold” in snapshot language | Catalog hygiene lag vs handoffs / catalog-decisions §2.8.3–2.8.4 |
| `spec03-readiness-package.md` | Pre-US3 inventory (US3 “Missing”, US4 “partial”) | Historical Batch 1 package — superseded by US3/US4 Batch 1b completion artifacts for those slices |
| Eligibility contract markdown vs runtime | Signature / stub narrative outdated | Editorial only; runtime + Request consumer are authoritative per US4 review decision R1 |

### 1.4 Evidence references (primary)

| Kind | Path |
| ---- | ---- |
| Spec / tasks / plan | `specs/003-employee-context/spec.md`, `tasks.md`, `plan.md`, `contracts/*`, `data-model.md` |
| US3 completion | `.specify/docs/handoff/spec03-us3-completion-handoff.md` |
| US4 Batch 1b completion | `.specify/docs/handoff/spec03-us4-batch1b-completion-handoff.md` |
| Post–Batch 1b transition | `.specify/docs/handoff/spec03-us4-post-batch-governance-transition-decision.md` |
| Completion Wave plan | `.specify/docs/handoff/completion-wave-plan.md` |
| Request Dependent owner decisions | `.specify/docs/handoff/request-dependent-owner-decision-record.md` (D-01–D-03) |
| Catalog IA log | `.specify/docs/catalog-decisions.md` §2.8.3–2.8.4 |
| Code | `app/Modules/Employee/**`; migrations under `database/migrations/modules/employee/` |
| Tests | `tests/Feature/Modules/Employee/*`, `tests/Unit/Modules/Employee/*` |
| UI (out of Spec03 completion) | `docs/ui/closeout/employee/employee-context-ui.closeout.md` |

---

## 2. US3 / US4 Boundary Analysis

### 2.1 US3 boundary — Dependent Records (P2)

**Owns (Employee):**

- Dependent lifecycle under Employee aggregate (CD-009)
- Persistence `employee_dependents` with FK to `employee_employees` only (no `request_id`)
- Application mutations: add / update / list via repository
- Employee-local feature tests; mutation authorization gate for dependent ops

**Does not own:**

- Request snapshot tables / FamilyDirect submission
- Live Request ← Employee Dependent source adapter
- Employee Application **Dependent read** projection for cross-module consumers (D-03: `NO_EMPLOYEE_DEPENDENT_READ_SURFACE_NOW`)
- Livewire / Feature Contract UI for dependents
- Eligibility computation (US4)

**Independent test:** Dependent CRUD without Request module — satisfied by `DependentTest.php`.

**Disposition:** **Complete** under US3 IA (`SPEC03_US3_COMPLETED`). Coding authority exhausted.

### 2.2 US4 boundary — Eligibility Computation (P3)

**Owns (Employee):**

- `EmployeeEligibilityContract` computation (CD-013)
- Domain `EligibilityCalculator` + stable reason codes
- Internal ports: ActiveAllocation (Null in Employee), PendingRequest (live bridge outside Employee provider)
- Deterministic fixture outcomes (SC-004) via `EmployeeEligibilityContractTest`

**Does not own:**

- Request submission **enforcement** (already Request-owned)
- Live Allocation truth (Allocation / Spec07 when IRG-authorized)
- Rewriting accepted runtime signature to Wave 1A `EmployeeId`-only markdown
- Forcing Null PendingRequest (would reverse live bridge)
- EmployeeRead (Phase 7)
- UI / Feature Contracts

**Batch 1b authorized subset (delivered):** T041, T043-AA, T044-NA, T045, T047-AA, T048; DOC-OPT deferred optional.

**Disposition:** **Batch 1b complete** (`SPEC03_US4_BATCH1B_IMPLEMENTATION_COMPLETED`). Remaining original Phase 6 greenfield items that conflict with accepted consumer truth stay **blocked**, not “incomplete US4 product gap.”

### 2.3 Dependency / overlap / ambiguity

| Relation | Finding |
| -------- | ------- |
| US3 ↔ US4 | **Orthogonal.** Dependent ownership ≠ eligibility supplier. US3 completion does not imply US4; US4 Batch 1b does not require Dependent UI or Dependent read surface. |
| US4 ↔ Request | **Consumer coupling (accepted).** Request already consumes eligibility; Batch 1b preserved signature + PendingRequest bridge. |
| US3 ↔ Request Dependent live path | **Deferred external integration** (D-01), not Spec03 US3 incompleteness. |
| Phase 7 EmployeeRead | **Adjacent, not US3/US4.** Downstream summary read prep; Completion Wave marks optional follow-on. |
| Doc ambiguity | Open Phase 6 checkboxes vs Batch 1b handoff — resolve via hygiene, not re-implementation. |

---

## 3. Dependency and Blocker Check

### 3.1 Request dependency

| Question | Evidence-backed answer |
| -------- | ---------------------- |
| Does Spec03 **completion of US1–US4 Batch 1b** require further Request coding? | **No.** |
| Does Spec03 block on live Dependent stub replacement? | **No for current scope** — owner D-01 `DEFER_LIVE_INTEGRATION`; stub remains approved. |
| Is IRG PASS required to close Spec03 product-core slices? | **No.** IRG applies to **Batch 2** live adapter only (`completion-wave-plan.md`). |
| Cross-module risk | Live Dependent wiring and Dependent Application read surface remain **unauthorized / deferred** (D-01, D-03). |

### 3.2 Workflow dependency

| Question | Answer |
| -------- | ------ |
| Does Spec03 depend on Workflow Engine activation? | **No.** Spec03 out-of-scope excludes Request/Workflow orchestration (`FR-EX-001`); Completion Wave hard-excludes Workflow activation. |

### 3.3 Auth dependency

| Question | Answer |
| -------- | ------ |
| Identity supplier for create? | **Satisfied** — `IdentityUserReadContract` (spec02 frozen). |
| Mutation authorization? | **Present** — `EmployeeMutationAuthorizationGate` on Application mutations. |
| Spec03 blocked on Auth module / login UX? | **No** — `FR-EX-004` / OA-02-01 deferred login UX; not a Spec03 integrity blocker. |

### 3.4 Cross-spec blockers

| Item | Blocks Spec03 integrity? | Notes |
| ---- | ------------------------ | ----- |
| Spec05 FamilyDirect live Dependent | **No** (deferred) | Owner D-01; IRG separate |
| Spec07 live ActiveAllocation | **No** for Batch 1b DoD | Null adapter intentional |
| Spec04 reopen | **No** | Hard out of Completion Wave |
| Catalog / tasks status lag | **Documentation blocker only** | Does not invalidate delivered code |
| Post–Batch 1b selection authority gap | **Process blocker for next work** | `POST_BATCH_GOVERNANCE_TRANSITION_WAITING_FOR_AUTHORITY` — not a Spec03 code defect |

---

## 4. The UI Question

### Determination

**`employee-context-ui` is not required for Spec03 completion.**

It is a **separate, product-authorized UI feature** that:

- was gated under UI Feature Contract / lock / verification / closeout (`FEATURE_CLOSED`, 2026-07-10)
- delivers a thin Livewire hub over **already-delivered** US1/US2 Application mutations only
- explicitly **excludes** dependents, eligibility expansion, and EmployeeRead
- was **deferred** in Spec03 plan Phase F / tasks (“Livewire HR admin”) and in Completion Wave non-goals (“Do not expand Spec03 into Livewire HR admin”)

Therefore, for Batch B Spec03 integrity/readiness:

| Classification | Result |
| -------------- | ------ |
| Required for Spec03 completion? | **No** |
| Future / separate UI candidate? | **Already closed as its own feature** — not a Spec03 Batch B completion gate; Main UI Wave / further Employee UI remains separately product-gated |
| Spec03 Phase F Livewire? | Remains **deferred relative to Spec03 task board**; hub exists only because of the closed UI feature path, not because Spec03 DoD requires UI |

**No UI surface is legitimately required to declare Spec03 product-core (US1–US4 Batch 1b) integrity complete.**

---

## 5. Final Recommendation

### Primary outcome

**`SPEC03_REMAINING_WORK_IDENTIFIED`**

**Rationale (evidence-only):**

1. **Product-core slices US1, US2, US3, and US4 Batch 1b are implemented and handed off** — integrity of those authorized scopes is evidenced in code + tests + completion handoffs.
2. **Spec03 is not fully closed on the task board / optional supplier tail:** EmployeeRead (T049–T052), Phase 8 polish (T053–T058), and DOC-OPT markdown sync remain open or deferred.
3. **Governance/status artifacts lag** (spec.md status, tasks.md Phase 6 checkboxes, catalog snapshot) create a documentation integrity gap that should be treated as remaining hygiene work, not as license to re-open US3/US4 coding.
4. Scope of remaining items is **identified and bounded** — not inventing new product scope; no blocking ambiguity that forces `SPEC03_SCOPE_CLARIFICATION_REQUIRED` for US3/US4 boundaries (those boundaries are clear). Mild residual question (“is EmployeeRead mandatory for Spec03 close?”) is already answered by Completion Wave as **optional follow-on** unless a downstream consumer gate requires it.

Not selected:

- `SPEC03_INTEGRITY_VERIFIED` — would understate remaining EmployeeRead/polish/DOC-OPT and status-artifact lag.
- `SPEC03_SCOPE_CLARIFICATION_REQUIRED` — US3/US4 boundaries and UI non-requirement are evidence-clear; clarification is not the primary blocker.

### Implementation Authorization

| Need | Answer |
| ---- | ------ |
| IA for this review? | **No** (read-only governance artifact) |
| IA to resume US3 / US4 Batch 1b coding? | **No** — scopes exhausted/closed |
| IA if selecting **EmployeeRead** or **Phase 8 polish** as next coding? | **Yes** — new selected work item + Implementation Authorization with verbatim `authorized-scope` |
| IA for `employee-context-ui` / Main UI? | **Not for Spec03 completion** — UI is separate product gating; Main UI Wave remains deferred per Completion Wave |
| Integration IA for Request Dependent live? | **Not now** — D-01 deferral; requires future reopen + IRG |

### Defer / freeze

| Item | Disposition |
| ---- | ----------- |
| Request Dependent live stub replacement | **Frozen / deferred** (D-01) |
| Employee Dependent Application read surface | **Frozen** (D-03) |
| Live ActiveAllocation adapter | **Frozen** until Spec07/IRG path |
| Null PendingRequest reversion | **Blocked** |
| Spec04 / Spec07 reopen | **Frozen** |
| Main UI Wave / new Employee UI beyond closed hub | **Deferred** relative to Spec Completion Wave |
| DOC-OPT contract markdown | **Allowed deferred** (non-blocking editorial) |
| EmployeeRead T049–T052 | **Deferred optional** until selected under authority |

### Batch B decision-readiness

**Yes — Batch B can be considered decision-ready after this review.**

Decision-makers have evidence to:

1. Treat Spec03 **US1–US4 Batch 1b product-core** as delivered.
2. Treat **`employee-context-ui` as non-blocking** for Spec03 completion.
3. Select any next Spec03-owned item (EmployeeRead / polish / DOC-OPT) only under **separate authority**, or leave them frozen.
4. Keep Request Dependent live integration and Main UI **out of Spec03 completion** decisions.

**Immediate coding is not required** to satisfy Spec03 integrity for completed authorized batches. Next coding requires a new selected work item under valid governance authority (post–Batch 1b transition gap still applies for program-level selection).

---

## Traceability matrix (review questions)

| # | Question | Answer |
| - | -------- | ------ |
| 1 | What is already implemented and evidenced? | US1, US2, US3, US4 Batch 1b eligibility gap fill (+ mutation auth gate; live PendingRequest bridge preserved) |
| 2 | What remains incomplete / ambiguous / undocumented? | EmployeeRead; Phase 8 polish; DOC-OPT; stale status in spec.md / tasks.md / catalog |
| 3 | Exact US3 / US4 boundaries? | §2 — Dependent ownership vs eligibility supplier; orthogonal |
| 4 | External integration / workflow dependency? | No Workflow dependency; Request live Dependent deferred (D-01); live Allocation deferred |
| 5 | Is `employee-context-ui` part of Spec03 completion? | **No** — separate closed UI feature; not required for Spec03 completion |

---

## Document Control

- Version: 1.0.0  
- Status: **`SPEC03_REMAINING_WORK_IDENTIFIED`**  
- Owner: Governance Review (evidence recording only)  
- Last Updated: 2026-07-12  
- Checkpoint: `batch-b.spec03-readiness-review`

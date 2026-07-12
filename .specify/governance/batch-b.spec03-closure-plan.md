# Spec03 Closure Plan (Batch B)

**Artifact type:** Closure execution plan (non-authorizing)  
**Spec:** `003-employee-context` / catalog `spec03`  
**Upstream:** `.specify/governance/batch-b.spec03-readiness-review.md` → `SPEC03_REMAINING_WORK_IDENTIFIED`  
**Plan date:** 2026-07-12  
**Checkpoint:** `batch-b.spec03-closure-plan`

**This artifact does not:**

- authorize implementation, Integration Authorization, or Batch Execution Permission
- start execution or modify product code
- reopen UI Feature Contracts / Main UI Wave
- expand beyond the four authoritative remaining items
- pull frozen items into Spec03 ownership

Authority ownership remains only in `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`.  
Execution behavior: `.specify/governance/execution-policy.md`.

---

## 1. Executive Summary

Spec03 product-core slices (US1, US2, US3, US4 Batch 1b) are delivered. Spec03 is **not closed**. Four bounded remaining items remain.

This plan sequences those items in lowest-risk order, separates **required-for-closure** from **deferrable-without-invalidating-closure**, and defines the sole final gate for declaring **`SPEC03_CLOSED`**.

| Principle | Application |
| --------- | ----------- |
| Docs before code where conflict exists | DOC-OPT first (eligibility markdown → runtime truth) |
| Optional supplier before polish that validates it | EmployeeRead before Phase 8 **if** EmployeeRead is executed; if deferred, Phase 8 excludes Scenario 9 |
| Status last | Stale `spec.md` / `tasks.md` / catalog reconciled only after non-deferred work (and any recorded deferral) is settled |
| Frozen stay frozen | Request Dependent live, live Allocation, Main UI, Employee Dependent read surface |

**First execution candidate (next step after plan acceptance):** Item A — DOC-OPT markdown sync (editorial; still requires explicit permission before edit).  
**First coding candidate if EmployeeRead is not deferred:** Item B — EmployeeRead (T049–T052) under new Implementation Authorization.

---

## 2. Execution Sequence (ordered table)

| Order | ID | Item | Effort | Deferrable for `SPEC03_CLOSED`? | Prerequisite | Authority before execute |
| ----- | -- | ---- | ------ | ------------------------------- | ------------ | ------------------------ |
| **1** | **A** | DOC-OPT markdown sync | **S** | **No** (required for closure documentation integrity) | None | Explicit editorial/docs permission (not product IA) |
| **2** | **B** | EmployeeRead (T049–T052) | **M** | **Yes** — Spec03 **may close without it** if deferral is recorded in status artifacts | Item A recommended (no hard code dep) | **Implementation Authorization** with verbatim T049–T052 |
| **3** | **C** | Phase 8 polish | **M** | **No** (required; Scenario 9 N/A only if B deferred) | A complete; B complete **or** B formally deferred | **Implementation Authorization** covering T053–T058 (scoped) |
| **4** | **D** | Stale status updates (`spec.md`, `tasks.md`, catalog) | **S** | **No** (required — Final Gate) | A + C complete; B complete **or** B deferral recorded | Docs/governance edit permission |

**Parallelism:** None recommended. One item at a time. HALT for review between B and C if B executes.

**Out of sequence forever (frozen — not Spec03 closure work):**

- Request Dependent live / stub replacement  
- live Allocation adapter  
- Main UI / UI pipeline reopen  
- Employee Dependent Application read surface (D-03)

---

## 3. Item Detail Sheets

### 3.A — DOC-OPT markdown sync

| Field | Content |
| ----- | ------- |
| **Authoritative ref** | Batch 1b DOC-OPT; US4 review R1 (runtime `string` + `excludingRequestId` = accepted consumer truth) |
| **Effort** | **S** |
| **Deferrable?** | **No for `SPEC03_CLOSED`.** Spec03 may have shipped Batch 1b without DOC-OPT; **closure** requires contract markdown to stop contradicting runtime. |
| **Closure impact if skipped** | **Blocks `SPEC03_CLOSED`.** Leaving known-false eligibility contract docs fails Final Gate documentation integrity. |

**Scope in**

- Editorial update of `specs/003-employee-context/contracts/employee-eligibility-service.md` to match runtime:
  - `computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null)`
  - note live `PendingRequestReadPort` bridge vs Null `ActiveAllocationReadPort`
- Optional one-line cross-ref in `contracts/internal-read-ports.md` if it still asserts dual Null stubs as the only production binding
- Changelog note inside the contract file (version bump comment) that Wave 1A `EmployeeId`-only signature is superseded by accepted consumer truth

**Scope out**

- Any PHP signature change  
- Request module edits  
- Null PendingRequest introduction / bridge removal  
- EmployeeRead / UI / Dependent live work  
- Rewriting Request to match old markdown

**Dependencies**

- None (code already authoritative)

**Closure evidence required**

| Evidence type | Named artifact |
| ------------- | -------------- |
| Documentation | Updated `specs/003-employee-context/contracts/employee-eligibility-service.md` showing runtime signature + port binding notes |
| Status (optional supporting) | Short note in `tasks.md` Phase 6 that DOC-OPT is complete (checkbox or DOC-OPT line) |

---

### 3.B — EmployeeRead (T049–T052)

| Field | Content |
| ----- | ------- |
| **Authoritative ref** | `tasks.md` Phase 7; `contracts/employee-read-service.md` |
| **Effort** | **M** |
| **Deferrable?** | **Yes.** Spec03 **may still be closed without EmployeeRead**. |
| **Closure impact if deferred** | **Does not block `SPEC03_CLOSED`**, provided Item D records Phase 7 as **deferred / not delivered** and no consumer is documented as requiring it for Spec03 close. |

**Scope in**

- T049: `app/Modules/Employee/Application/DTOs/EmployeeSummaryDTO.php` per `contracts/employee-read-service.md` (no `nationalCode`)
- T050: `app/Modules/Employee/Application/Contracts/EmployeeReadContract.php`
- T051: `app/Modules/Employee/Application/Services/EmployeeReadService.php` — repository-only inside Employee
- T052: `tests/Feature/Modules/Employee/EmployeeReadContractTest.php` — exists / active / summary / unknown id
- Bind `EmployeeReadContract` → `EmployeeReadService` in `EmployeeServiceProvider.php`

**Scope out**

- Dependent read / snapshot supplier surface (D-03 frozen)  
- Request consumer wiring / Integrations binding of EmployeeRead  
- UI / Livewire / Feature Contracts  
- Eligibility changes  
- Live Allocation / Request Dependent

**Dependencies**

- Soft: Item A (docs clarity) — not hard  
- Hard: existing `EmployeeRepositoryContract` (already present)  
- Authority: **new Implementation Authorization** before coding

**Closure evidence required (if executed)**

| Evidence type | Named artifact |
| ------------- | -------------- |
| Code | Paths above exist and are bound in `EmployeeServiceProvider.php` |
| Test | `tests/Feature/Modules/Employee/EmployeeReadContractTest.php` pass record (command output or CI) |
| Documentation | `tasks.md` T049–T052 marked `[x]`; README notes EmployeeRead as delivered supplier |

**Deferral evidence required (if deferred)**

| Evidence type | Named artifact |
| ------------- | -------------- |
| Status | `tasks.md` Phase 7 explicitly marked **deferred at Spec03 close**; `spec.md` / catalog note Phase 7 not in closed deliverable |

---

### 3.C — Phase 8 polish

| Field | Content |
| ----- | ------- |
| **Authoritative ref** | `tasks.md` Phase 8 T053–T058; SC-005 |
| **Effort** | **M** |
| **Deferrable?** | **No for `SPEC03_CLOSED`.** Quality DoD for delivered Employee module. |
| **Closure impact if skipped** | **Blocks `SPEC03_CLOSED`.** |
| **Conditional N/A** | T057 quickstart **Scenario 9** is **N/A** only when Item B is deferred; Scenarios covering US1–US4 Batch 1b remain required. |

**Scope in**

| Task | Exact work |
| ---- | ---------- |
| T053 | Re-run / extend `tests/Architecture/EmployeeSupplierBoundaryTest.php` for post-US3/US4 (+ EmployeeRead files if B executed) — BT-05 |
| T054 | Update `app/Modules/Employee/README.md` — boundaries, CD-012/CD-013, ActiveAllocation Null + live PendingRequest bridge, EmployeeRead delivered **or** deferred |
| T055 | Pint on `app/Modules/Employee` (Windows: prefer `php vendor/bin/pint …`) |
| T056 | PHPStan on Employee paths — `php vendor/bin/phpstan analyse --no-progress` (or plan-equivalent with explicit `php`) — **0 errors** (SC-005) |
| T057 | Record pass/fail for `quickstart.md` Scenarios **1–8** (and **9 iff B executed**) via named tests and/or a short pass table in `tasks.md` / handoff note |
| T058 | Scope audit note in `tasks.md` or closure handoff: no Request/Allocation module invention in Spec03 tasks; no Identity Infrastructure imports; no FK `identity_id` → `identity_users` |

**Scope out**

- Livewire HR admin / UI pipeline  
- Real Allocation / PendingRequest Null reversion  
- `request_id` on dependents  
- Spec05/07 reopen  
- New product features beyond polish of delivered Spec03 surfaces

**Dependencies**

- Item A complete  
- Item B complete **or** B deferral recorded (so T054/T057 Scenario 9 scope is known)  
- Authority: Implementation Authorization covering polish (may be same IA as B if batched; otherwise separate scoped IA)

**Closure evidence required**

| Evidence type | Named artifact |
| ------------- | -------------- |
| Test | BT-05 architecture test pass (`tests/Architecture/EmployeeSupplierBoundaryTest.php`) |
| Command | Exact Pint command + clean result for Employee paths |
| Command | Exact PHPStan command + **0 errors** for `app/Modules/Employee` |
| Documentation | Updated `app/Modules/Employee/README.md` |
| Documentation | `tasks.md` T053–T058 marked complete with T057 scenario table (Scenario 9 = pass **or** N/A-deferred) |
| Documentation | T058 scope-audit statement present in `tasks.md` or Spec03 closure handoff |

---

### 3.D — Stale status updates (`spec.md`, `tasks.md`, catalog)

| Field | Content |
| ----- | ------- |
| **Authoritative ref** | Readiness §1.3 drift table |
| **Effort** | **S** |
| **Deferrable?** | **No for `SPEC03_CLOSED`.** Final Gate requires reconciliation. |
| **Closure impact if skipped** | **Blocks `SPEC03_CLOSED`.** |

**Scope in**

Reconcile **only** these artifacts to match evidence (US1–US4 Batch 1b delivered; A–C outcomes; B deferred or delivered):

1. `specs/003-employee-context/spec.md` — Status line (remove false “US3+ not authorized”; reflect closed / deferred Phase 7 as applicable)
2. `specs/003-employee-context/tasks.md` — Phase 6 Batch 1b delivered checkboxes / notes; Phase 7 `[x]` or deferred; Phase 8 `[x]`; header Status; Implementation Strategy hold language
3. `.specify/docs/spec-catalog.md` — Spec03 inventory / Wave 1A snapshot rows that still say “US3+ hold” where false

Optionally (same item, still Spec03 status hygiene — **not** new work items): one-line pointer in catalog Change Log or Spec03 tasks header to US3/US4 Batch 1b handoffs.

**Scope out**

- Rewriting historical handoffs (`spec03-readiness-package.md` pre-US3 text may remain historical)  
- Reopening UI / Dependent / Allocation language as active work  
- Selecting next Spec beyond Spec03  
- Claiming Request Dependent live complete

**Dependencies**

- Items A and C complete  
- Item B complete **or** explicit deferral text ready to paste into status artifacts

**Closure evidence required**

| Evidence type | Named artifact |
| ------------- | -------------- |
| Documentation | `spec.md` Status consistent with delivered + deferred reality |
| Documentation | `tasks.md` checkboxes/status header match handoffs + A/B/C outcomes |
| Documentation | `spec-catalog.md` Spec03 rows no longer claim false US3+/US4 hold |

---

## 4. Deferral Assessment

| Item | Deferrable without invalidating Spec03 closure? | Spec03 may close without it? | Spec03 closure blocked until complete? |
| ---- | ----------------------------------------------- | ---------------------------- | -------------------------------------- |
| **A DOC-OPT** | No | No | **Yes — blocked until complete** |
| **B EmployeeRead** | **Yes** | **Yes** | **No** (if deferral recorded in Item D) |
| **C Phase 8 polish** | No (Scenario 9 conditional) | No | **Yes — blocked until complete** |
| **D Status sync** | No | No | **Yes — blocked until complete** |

**Frozen items (never part of Spec03 closure criteria):**

| Frozen item | May Spec03 close while this remains undone? |
| ----------- | -------------------------------------------- |
| Request Dependent live | **Yes** (D-01) |
| live Allocation | **Yes** (Null remains) |
| Main UI / UI pipeline | **Yes** (`employee-context-ui` already closed separately; Phase F not Spec03 DoD) |
| Employee Dependent read surface | **Yes** (D-03) |

---

## 5. Final Gate Definition

### Gate name

**`SPEC03_CLOSED`**

### Declaration authority

Human / Governance Review after Items A–D are settled per this plan. This plan does **not** itself declare `SPEC03_CLOSED`.

### `SPEC03_CLOSED` may be declared **only if** all of the following are true

1. **Non-deferred remaining items complete**
   - Item A complete with named evidence (§3.A)  
   - Item C complete with named evidence (§3.C)  
   - Item D complete with named evidence (§3.D)  
   - Item B either:
     - complete with named evidence (§3.B), **or**
     - formally deferred with deferral evidence (§3.B) reflected in Item D artifacts

2. **All required evidence exists and is named**
   - No closure claim based on “verified” / “aligned” without the artifact paths / commands listed above

3. **Stale status artifacts reconciled**
   - `specs/003-employee-context/spec.md`  
   - `specs/003-employee-context/tasks.md`  
   - `.specify/docs/spec-catalog.md`  
   match actual delivered + deferred reality (US1–US4 Batch 1b + A/C; B delivered or deferred)

4. **Frozen / out-of-scope items are not pulled into closure criteria**
   - Request Dependent live, live Allocation, Main UI, Employee Dependent read surface are **not** required for `SPEC03_CLOSED`
   - UI pipeline is **not** reopened as Spec03 work
   - No work outside Spec03 ownership is required for the gate

### Recommended closure recording artifact (post-gate)

After the gate conditions are met, create a single handoff (execution later — **not** this plan):

- `.specify/docs/handoff/spec03-closure-handoff.md` with status **`SPEC03_CLOSED`**, listing evidence pointers for A–D and explicit B disposition (delivered | deferred)

### Forbidden as substitutes for `SPEC03_CLOSED`

- US3 or US4 Batch 1b completion handoffs alone  
- `employee-context-ui` closeout  
- Catalog ordering / Completion Wave momentum  
- Passing a subset of Employee tests without A/C/D evidence

---

## 6. Risk Notes

| Risk | Mitigation |
| ---- | ---------- |
| Executing EmployeeRead without IA | HALT — Case A missing authorization; select work item + IA first |
| Closing while DOC-OPT still contradicts runtime | Forbidden by Final Gate |
| Phase 8 claiming Scenario 9 pass when B deferred | Mark Scenario 9 **N/A-deferred** in T057 evidence |
| Status sync done too early (before polish) | Keep Item D last |
| Pulling Dependent read / live Request into “EmployeeRead” | Scope out explicit; D-03 remains frozen |
| Treating UI hub as Spec03 DoD | Do not; Phase F stays deferred relative to Spec03 task board |
| Expanding Phase 8 into product features | Scope locked to T053–T058 only |
| Dual narrative in catalog (hold vs closed) | Item D must edit the live Spec03 status rows, not only Change Log |

**Blocking clarification required for an already-listed item:** None identified at plan time. If Implementation Authorization for Item B/C conflicts with `contracts/employee-read-service.md` (e.g., signature string vs `EmployeeId`), resolve as clarification **on Item B only** before coding — do not invent new remaining items.

---

## 7. Target Decision

**`SPEC03_CLOSURE_PLAN_APPROVED`**

| Field | Value |
| ----- | ----- |
| Plan status | Approved for governance use as Spec03 closure path |
| Execution started? | **No** |
| Next human step | Select first execution candidate (**Item A — DOC-OPT**) for explicit permission; then authorize Item B **or** record B deferral before Item C IA |
| Coding without new IA? | **Forbidden** for Items B and C |

---

## Traceability

| Source | Role |
| ------ | ---- |
| `.specify/governance/batch-b.spec03-readiness-review.md` | Remaining work + UI non-requirement |
| `specs/003-employee-context/tasks.md` | T049–T058 definitions |
| `specs/003-employee-context/contracts/employee-read-service.md` | EmployeeRead surface |
| `specs/003-employee-context/contracts/employee-eligibility-service.md` | DOC-OPT target |
| `.specify/docs/handoff/spec03-us4-batch1b-completion-handoff.md` | DOC-OPT deferral history; B not in Batch 1b |
| `.specify/docs/handoff/request-dependent-owner-decision-record.md` | Frozen D-01–D-03 |

---

## Document Control

- Version: 1.0.0  
- Status: **`SPEC03_CLOSURE_PLAN_APPROVED`**  
- Owner: Governance Review (planning only)  
- Last Updated: 2026-07-12  
- Checkpoint: `batch-b.spec03-closure-plan`

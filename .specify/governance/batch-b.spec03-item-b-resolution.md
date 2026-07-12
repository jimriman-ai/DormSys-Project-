# Spec03 Item B Resolution — EmployeeRead (T049–T052)

**Artifact type:** Item B path resolution (non-implementing; does not authorize coding)  
**Spec:** `003-employee-context` / catalog `spec03`  
**Resolved item:** Item B — EmployeeRead (T049–T052)  
**Resolution date:** 2026-07-12  
**Checkpoint:** `batch-b.spec03-item-b-resolution`

**Governing plan:** `.specify/governance/batch-b.spec03-closure-plan.md` (`SPEC03_CLOSURE_PLAN_APPROVED`)  
**Prior item:** Item A — `.specify/governance/batch-b.spec03-item-a-execution-report.md` (`SPEC03_ITEM_A_COMPLETED`)

This artifact chooses **exactly one** path for Item B. It does **not** implement code, authorize Phase 8, sync status artifacts, or reopen UI / Dependent live / Allocation.

---

## 1. Resolution Decision

| Field | Value |
| ----- | ----- |
| **Decision** | **`SPEC03_ITEM_B_DEFERRED`** |
| **Path chosen** | Formal **deferral** for Spec03 closure (path 2) |
| **Implementation now?** | **No** |
| **Implementation Authorization issued?** | **No** — not selected |
| **Ambiguity remaining?** | **No** — Item B disposition is settled for Spec03 closure sequence |

---

## 2. Chosen Path

**Formal deferral of EmployeeRead (T049–T052) for Spec03 closure.**

| Attribute | Value |
| --------- | ----- |
| Plan allowance | Closure plan §3.B / §4 — EmployeeRead **may be deferred**; Spec03 **may still be closed** without it |
| Deliverable status | **Not delivered** under Spec03 close |
| False completion | **Forbidden** — must never mark T049–T052 `[x]` as complete without code |
| Downstream reopen | EmployeeRead may be selected later under **separate** Implementation Authorization outside Spec03 closure criteria |

**Deferral statement (canonical — copy into Item D status artifacts):**

> Spec03 Phase 7 EmployeeRead (T049–T052 / `EmployeeReadContract`) is **deferred at Spec03 close**. It is **not** part of the Spec03 closed deliverable. Spec03 closure does **not** claim EmployeeRead exists. Future delivery requires a new selected work item and Implementation Authorization. Quickstart Scenario 9 is **N/A — deferred**.

---

## 3. Rationale

| Evidence | Implication |
| -------- | ----------- |
| Closure plan marks Item B **deferrable**; closure **not blocked** if deferral is recorded in Item D | Deferral is an approved Spec03 closure path |
| Completion Wave: Phase 7 is **optional follow-on** “only if product-core / downstream consumers require it” | No automatic mandate to implement for Spec03 close |
| No `EmployeeReadContract` / `EmployeeReadService` / `EmployeeSummaryDTO` under `app/` (only README “later waves”) | Still absent; no half-delivery to finish |
| No in-app consumer injection of `EmployeeReadContract` | No runtime breakage from deferral |
| Request Dependent live deferred (D-01); Dependent Application read frozen (D-03) | Live Family/Dependent path does **not** currently require EmployeeRead for Spec03 close; Dependent supplier ≠ Employee summary read |
| `employee-context-ui` closed without EmployeeRead | UI does not create a Spec03-close dependency |
| Spec05 FamilyDirect remains on approved stub | Does not force Phase 7 for current Spec03 integrity |
| Item A complete | Docs-before-code prerequisite satisfied; does not itself create a consumer need for EmployeeRead |

**Why not authorize implementation now:** Authorizing T049–T052 would expand Spec03 closure into optional supplier coding without an evidenced Spec03-close consumer requirement, delaying Phase 8 / Item D while frozen Dependent/UI/Allocation paths remain out of scope. Deferral keeps closure criteria honest and bounded.

---

## 4. In-Scope Consequence

### Immediate (this resolution)

| Consequence | Detail |
| ----------- | ------ |
| Item B ambiguity | **Cleared** — deferred |
| Coding of T049–T052 | **Not started; not authorized** |
| Phase 8 (Item C) | May proceed **only after** separate Item C Implementation Authorization; T057 Scenario 9 = **N/A — deferred** |
| Item D status sync | **Must** record Phase 7 deferred (see §5) — not yet executed |
| Spec03 closed deliverable | US1–US4 Batch 1b + Item A DOC-OPT + Item C polish + Item D status — **without** EmployeeRead |

### Explicit non-consequences

| Not implied | Confirmation |
| ----------- | ------------ |
| EmployeeRead “complete” | **No** |
| Request consumer wiring of EmployeeRead | **No** |
| Dependent read surface (D-03) | Still frozen / not authorized |
| UI reopen / Main UI | **No** |
| Live Allocation | **No** |
| `SPEC03_CLOSED` | **Not** declared by this resolution |

---

## 5. Status Artifact Requirement

**When Item D executes** (not now), the following **must** be recorded — no false completion:

| Artifact | Required text / disposition |
| -------- | --------------------------- |
| `specs/003-employee-context/tasks.md` | Phase 7 T049–T052 remain **unchecked** or marked **`deferred at Spec03 close`** — **not** `[x]` complete |
| `specs/003-employee-context/tasks.md` | Phase header / Implementation Strategy: Phase 7 deferred; Scenario 9 N/A |
| `specs/003-employee-context/spec.md` | Status line: Spec03 closed deliverable **excludes** Phase 7 EmployeeRead (or equivalent clear note) |
| `.specify/docs/spec-catalog.md` | Spec03 inventory: EmployeeRead / Phase 7 **deferred** — not listed as delivered |
| Optional later handoff | Spec03 closure handoff must list Item B disposition: **`DEFERRED`** with pointer to this resolution |

**Forbidden in any artifact:** Claiming `EmployeeReadContract` delivered, T049–T052 complete, or quickstart Scenario 9 passed while deferred.

**README (Item C polish, when authorized):** Must state EmployeeRead is **deferred**, not “later waves” implying open Spec03 obligation without saying deferred-at-close.

---

## 6. Risks / Watchpoints

| Risk | Handling |
| ---- | -------- |
| Silently marking T049–T052 complete in Item D | Forbidden — deferral evidence only |
| Treating this resolution as Implementation Authorization | It is **not** — coding remains unauthorized |
| Starting Phase 8 before this resolution | Blocked by task rules; **now resolved** — Phase 8 may be authorized next |
| Including Scenario 9 as required polish | Scenario 9 = **N/A — deferred** under Item C |
| Confusing EmployeeRead with Dependent read (D-03) | Keep separate; Dependent read stays frozen |
| Downstream later needing EmployeeRead | Separate work item + IA; does **not** reopen Spec03 closure criteria retroactively |
| Request Dependent IRG citing missing EmployeeRead | IRG/Dependent path remains deferred (D-01); not a Spec03-close blocker |

---

## 7. Next Authorized State

| Field | Value |
| ----- | ----- |
| **Item A** | Completed (`SPEC03_ITEM_A_COMPLETED`) |
| **Item B** | **Resolved — deferred** (`SPEC03_ITEM_B_DEFERRED`) |
| **Item C (Phase 8)** | **Not yet authorized** — next candidate for Implementation Authorization (T053–T058 scoped; Scenario 9 N/A) |
| **Item D (status sync)** | **Not yet authorized** — after Item C; must include §5 deferral language |
| **Auto-progression** | **HALT** — do not start Phase 8 coding until Item C IA is issued |
| **`SPEC03_CLOSED`** | Still blocked on Items C + D (and evidence), not on EmployeeRead delivery |

**Next valid sequence step:** Authorize **Item C — Phase 8 polish** with `authorized-scope` T053–T058 and explicit **Scenario 9 N/A (Item B deferred)**.

---

## Explicit Non-Authorization

This resolution does **not** authorize:

- EmployeeRead implementation (T049–T052)  
- Phase 8 polish execution  
- Item D status edits  
- UI / Request Dependent live / live Allocation / Dependent Application read  

---

## Document Control

- Version: 1.0.0  
- Status: **`SPEC03_ITEM_B_DEFERRED`**  
- Owner: Governance Review  
- Last Updated: 2026-07-12  
- Checkpoint: `batch-b.spec03-item-b-resolution`

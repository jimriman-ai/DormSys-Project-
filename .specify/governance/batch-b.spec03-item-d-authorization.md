# Spec03 Item D Authorization — Global Status Sync & Closure

**Artifact type:** Documentation / status-sync authorization (non-coding)  
**Spec:** `003-employee-context` / catalog `spec03`  
**Authorized item:** Item D — Stale status updates + Spec03 closure recording  
**Authorization date:** 2026-07-12  
**Checkpoint:** `batch-b.spec03-item-d-authorization`

**Governing plan:** `.specify/governance/batch-b.spec03-closure-plan.md` (`SPEC03_CLOSURE_PLAN_APPROVED`)  
**Preconditions met:**  
- Item A — `SPEC03_ITEM_A_COMPLETED`  
- Item B — `SPEC03_ITEM_B_DEFERRED`  
- Item C — `SPEC03_ITEM_C_COMPLETED` (`.specify/governance/batch-b.spec03-item-c-execution-report.md`)

This record authorizes **exactly** Item D. It does **not** authorize product code, UI, EmployeeRead implementation, or frozen-domain reopen.

---

## 1. Authorization Decision

| Field | Value |
| ----- | ----- |
| **Decision** | **`SPEC03_ITEM_D_AUTHORIZED`** |
| **authorization-status** | **active** |
| **Execution started?** | **No** (authorization only) |
| **Authority class** | Explicit editorial / status-sync permission |

---

## 2. Scope Authorized

**authorized-scope** (verbatim):

1. **`specs/003-employee-context/spec.md`**  
   - Update Status (and related header notes) to match reality: US1–US4 Batch 1b delivered; Item A DOC-OPT done; Phase 7 EmployeeRead **deferred at Spec03 close** (not delivered); Phase 8 polish complete.  
   - Remove false “US3+ not authorized” / hold language where it contradicts completed handoffs.

2. **`specs/003-employee-context/tasks.md`**  
   - Header / Phase Summary / Implementation Strategy: reflect Wave US3 complete, US4 Batch 1b delivered, Phase 7 **deferred**, Phase 8 **complete**.  
   - Phase 6: mark Batch 1b-delivered items complete per US4 completion handoff (do not invent Pending-Null / signature-rewrite as done). Record DOC-OPT complete.  
   - Phase 7 T049–T052: mark **`deferred at Spec03 close`** / Post-Spec03 — **never** `[x]` complete. Include canonical deferral statement from Item B resolution. Scenario 9 = N/A — deferred.  
   - Phase 8 T053–T058: mark **complete** per Item C execution report.

3. **`.specify/docs/spec-catalog.md`**  
   - Spec03 inventory / Wave 1A snapshot rows: remove false “US3+ hold”; state delivered US1–US4 Batch 1b + closure polish; Phase 7 EmployeeRead **deferred**.  
   - Optional one-line Change Log / pointer to US3/US4 Batch 1b handoffs and Batch B closure artifacts.

4. **Optional closure handoff (allowed under Item D):**  
   - Create `.specify/docs/handoff/spec03-closure-handoff.md` recording **`SPEC03_CLOSED`** only after the three status artifacts above are reconciled and Final Gate conditions from the closure plan are met (A+C complete; B deferred recorded; frozen items not pulled into criteria).

**Canonical Item B text to paste (required):**

> Spec03 Phase 7 EmployeeRead (T049–T052 / `EmployeeReadContract`) is **deferred at Spec03 close**. It is **not** part of the Spec03 closed deliverable. Spec03 closure does **not** claim EmployeeRead exists. Future delivery requires a new selected work item and Implementation Authorization. Quickstart Scenario 9 is **N/A — deferred**.

---

## 3. Scope Explicitly Excluded / Forbidden

| Forbidden | Reason |
| --------- | ------ |
| Any further application / test / UI code changes | Status-only authorization |
| Marking T049–T052 (or Scenario 9) as **Completed** / `[x]` delivered | Must use **Deferred** / **Post-Spec03** / N/A |
| EmployeeRead implementation | Item B deferred |
| `employee-context-ui` reopen | Frozen relative to Spec03 |
| Request Dependent live / live Allocation / Dependent read surface | Frozen |
| Rewriting historical handoffs as if they were wrong at the time | Historical packages may remain; live status rows must be corrected |
| Claiming Request Dependent live complete | Out of Spec03 |
| Reopening Items A–C coding | Exhausted |

---

## 4. Dependencies / Preconditions

| Precondition | Status |
| ------------ | ------ |
| Item A completed | **Met** |
| Item B formally deferred | **Met** |
| Item C completed | **Met** |
| Closure plan Final Gate criteria known | **Met** |
| Ready for bounded execution | **Yes** |

---

## 5. Execution Guardrails

1. Execute only after this authorization exists (**now true** for next execute prompt).  
2. Preserve Item B deferral wording — no false completion.  
3. Mark T053–T058 complete; mark T049–T052 deferred only.  
4. No PHP/UI/product edits.  
5. Prefer factual sync to handoffs over inventing new scope.  
6. After sync, if creating closure handoff, cite evidence pointers (A/B/C reports + Batch B plan).  
7. HALT after Item D completion report — do not start unrelated Specs.

---

## 6. Expected Completion State

Item D is **complete** when an execution report records **`SPEC03_ITEM_D_COMPLETED`** and:

| Evidence | Criterion |
| -------- | --------- |
| `spec.md` | Status matches delivered + deferred reality |
| `tasks.md` | Phase 6/8 complete as appropriate; Phase 7 deferred (not falsely complete) |
| `spec-catalog.md` | Spec03 no longer falsely “US3+ hold”; Phase 7 deferred noted |
| Negative | No code/UI changes; T049–T052 not marked delivered |
| Optional | `spec03-closure-handoff.md` with **`SPEC03_CLOSED`** if Final Gate fully satisfied |

**After completion:** Spec03 Batch B closure sequence finished; Item D IA exhausted; `SPEC03_CLOSED` declareable via handoff when evidence above is present.

---

## Document Control

- Version: 1.0.0  
- Status: **`SPEC03_ITEM_D_AUTHORIZED`**  
- Selected item: Item D — Global status sync & closure  
- Owner: Governance Review  
- Last Updated: 2026-07-12  
- Checkpoint: `batch-b.spec03-item-d-authorization`

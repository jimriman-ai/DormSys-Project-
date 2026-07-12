# Spec03 Item A Authorization — DOC-OPT Markdown Sync

**Artifact type:** Single-item execution authorization (editorial / documentation only)  
**Spec:** `003-employee-context` / catalog `spec03`  
**Authorized item:** Item A — DOC-OPT markdown sync  
**Authorization date:** 2026-07-12  
**Checkpoint:** `batch-b.spec03-item-a-authorization`

**Governing plan (authoritative, not rewritten):**  
`.specify/governance/batch-b.spec03-closure-plan.md` — status `SPEC03_CLOSURE_PLAN_APPROVED`

**Contradiction check:** None found. Plan Order 1 = Item A; prerequisites = None; authority type = explicit editorial/docs permission. Runtime evidence still contradicts Wave 1A eligibility contract markdown. Authorization proceeds without re-scoping.

This record does **not** authorize Item B, C, or D; does **not** authorize PHP/product changes; does **not** start execution by itself.

---

## 1. Authorization Decision

| Field | Value |
| ----- | ----- |
| **Decision** | **`SPEC03_ITEM_A_AUTHORIZED`** |
| **authorization-status** | **active** |
| **Execution started?** | **No** |
| **Items authorized** | **Exactly one** — Item A |
| **Authority class** | Explicit editorial / documentation permission (not product Implementation Authorization) |

---

## 2. Selected Item

| Field | Value |
| ----- | ----- |
| **Plan ID** | **A** |
| **Name** | DOC-OPT markdown sync |
| **Effort (plan)** | S |
| **Required for `SPEC03_CLOSED`?** | Yes (plan §3.A / §4) |
| **Plan sequence position** | Order **1** of 4 |

**Work summary:** Editorially sync Spec03 eligibility (and, if needed, internal-ports) contract markdown to accepted runtime / consumer truth — without changing PHP, Request, or bindings.

---

## 3. Why This Item Is Next

| Criterion | Finding |
| --------- | ------- |
| Plan sequence | Closure plan §1 and §2 name Item A as **first execution candidate** after plan acceptance |
| Prerequisites | Plan: **None** (code already authoritative) |
| Risk order | Docs-before-code: resolve known markdown↔runtime conflict before EmployeeRead / polish / status sync |
| No contradiction | Readiness + Batch 1b DOC-OPT deferral still accurate; eligibility markdown still shows `EmployeeId`-only Wave 1A signature |
| Not Item B/C/D | EmployeeRead, Phase 8, and stale status remain unauthorized |

---

## 4. In-Scope Boundary

**authorized-scope** (verbatim execution boundary):

1. **Primary (required):** Editorial update of  
   `specs/003-employee-context/contracts/employee-eligibility-service.md`  
   so it matches runtime accepted consumer truth:
   - Method signature: `computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null): EligibilityResultDTO`
   - Port binding narrative: `ActiveAllocationReadPort` → `NullActiveAllocationReadAdapter` (Employee provider); `PendingRequestReadPort` → live bridge (`PendingRequestReadBridge` via `IntegrationServiceProvider`) — **not** dual Null stubs as sole production binding
   - Version / changelog note that Wave 1A `EmployeeId`-only signature is **superseded** by accepted consumer truth (US4 Batch 1b / review R1)
   - Align related narrative in the same file (error-behavior / stub paragraphs) so they do not reassert obsolete Wave 1A-only stub production path for PendingRequest

2. **Secondary (optional, only if still false):** Editorial fix to  
   `specs/003-employee-context/contracts/internal-read-ports.md`  
   where it still asserts dual Null stub binding and/or `PendingRequestReadPort::hasPendingRequest(EmployeeId)` as the only production shape — sync to runtime:
   - `hasPendingRequest(string $employeeId, ?string $excludingRequestId = null)`
   - Binding: ActiveAllocation Null in Employee provider; PendingRequest live in Integration composition root

3. **Optional supporting evidence only:** A **minimal** DOC-OPT completion marker in `specs/003-employee-context/tasks.md` Phase 6 (e.g., one DOC-OPT line or note) — **must not** become Item D status reconciliation (no catalog edits; no wholesale US3+/US4 hold rewrite under this authorization)

**Runtime truth references (read-only for executors; do not modify under Item A):**

- `app/Modules/Employee/Application/Contracts/EmployeeEligibilityContract.php`
- `app/Modules/Employee/Application/Services/EmployeeEligibilityService.php`
- `app/Modules/Employee/Infrastructure/Providers/EmployeeServiceProvider.php` (ActiveAllocation → Null)
- `app/Providers/IntegrationServiceProvider.php` (PendingRequest → bridge)

---

## 5. Out-of-Scope Boundary

**blocked-scope** (explicit):

| Forbidden | Reason |
| --------- | ------ |
| Any PHP / application / test code change | Editorial-only authorization |
| Request module edits / consumer rewrite | Plan §3.A scope out |
| Null PendingRequest introduction or live bridge removal | Frozen / Batch 1b preserve |
| Live Allocation adapter | Frozen |
| EmployeeRead (T049–T052) — Item B | Not authorized |
| Phase 8 polish (T053–T058) — Item C | Not authorized |
| Stale status sync of `spec.md` / catalog / wholesale `tasks.md` — Item D | Not authorized (“no status sync yet”) |
| UI / Livewire / Feature Contracts / Main UI | Frozen; do not reopen |
| Request Dependent live / stub replacement | Frozen (D-01) |
| Employee Dependent Application read surface | Frozen (D-03) |
| Rewriting closure plan or readiness review | Plan remains authoritative |
| Declaring `SPEC03_CLOSED` | Final gate after A–D per plan |

---

## 6. Preconditions

| Precondition | Status |
| ------------ | ------ |
| Closure plan approved (`SPEC03_CLOSURE_PLAN_APPROVED`) | **Met** |
| Item A is Order 1 with no hard code dependency | **Met** |
| Runtime eligibility signature exists and is stable | **Met** — `EmployeeEligibilityContract::computeRequestEligibility(string, ?string)` |
| ActiveAllocation Null + PendingRequest live bridge observable | **Met** |
| No prior Item A completion claim requiring re-open | **Met** — DOC-OPT still deferred / markdown still stale |
| Product IA for coding | **Not required** for Item A |
| Items B/C/D authorization | **Not required** to start Item A |

**Ready for bounded execution:** **Yes.**

---

## 7. Required Completion Evidence

Item A is **complete** only when all of the following exist:

| # | Evidence type | Named artifact / criterion |
| - | ------------- | -------------------------- |
| 1 | Documentation | `specs/003-employee-context/contracts/employee-eligibility-service.md` documents runtime signature `string` + `excludingRequestId` (not Wave 1A `EmployeeId`-only as current API) |
| 2 | Documentation | Same file documents current port binding reality (Null ActiveAllocation vs live PendingRequest bridge) — no claim that both ports are Null-only in production |
| 3 | Documentation | Same file contains explicit supersession / version note for Wave 1A signature |
| 4 | Documentation (conditional) | If `internal-read-ports.md` was updated: PendingRequest signature + binding notes match runtime; if **not** updated, executor must confirm file no longer falsely blocks closure **or** record that optional secondary edit was skipped because primary file alone removed the contradiction — prefer updating `internal-read-ports.md` when Binding section still shows dual Null |
| 5 | Negative evidence | **No** PHP, Request, Integrations, UI, or catalog files changed in the Item A execution commit/diff |

Optional (plan-supporting, not Item D):

| # | Evidence type | Named artifact |
| - | ------------- | -------------- |
| 6 | Documentation | Minimal DOC-OPT-complete note in `tasks.md` Phase 6 only |

**Not accepted as Item A completion:** “aligned” without the named files; status-only edits; PHPStan/Pint as substitutes; Batch 1b handoff alone.

---

## 8. Risks / Watchpoints

| Risk / watchpoint | Handling |
| ----------------- | -------- |
| Accidental PHP “fix” to match old markdown | Forbidden — markdown follows runtime, never reverse |
| Editing Request to “restore” `EmployeeId` API | Forbidden — blocked-scope |
| Expanding optional `tasks.md` note into Item D catalog/`spec.md` sync | Stop — Item D remains unauthorized |
| Leaving `internal-read-ports.md` asserting dual Null while eligibility file is fixed | Prefer optional secondary edit; do not leave contradictory sibling contract without noting skip rationale in completion handoff |
| Treating this authorization as IA for EmployeeRead | It is not — Item B still requires separate IA |
| Reopening UI / Dependent live / Allocation | Explicitly blocked |
| Silent re-scope if markdown paths differ | Report contradiction; do not invent new remaining items |

---

## 9. Next Authorized State

| Field | Value |
| ----- | ----- |
| **Active authorization** | Item A — DOC-OPT markdown sync only |
| **Next allowed prompt action** | Execute DOC-OPT **within** §4 in-scope / §5 out-of-scope |
| **After Item A completes** | HALT auto-progression — return completion evidence; do **not** start Item B/C/D without new authorization / deferral decision |
| **Items still unauthorized** | B (EmployeeRead), C (Phase 8), D (status sync) |
| **`SPEC03_CLOSED`** | Not declareable |

---

## Explicit Non-Authorization

This artifact does **not** authorize:

- implementation of product code or tests  
- Item B, C, or D  
- UI pipeline  
- Request Dependent live work  
- live Allocation  
- Employee Dependent read surface  

---

## Document Control

- Version: 1.0.0  
- Status: **`SPEC03_ITEM_A_AUTHORIZED`**  
- Selected item: Item A — DOC-OPT markdown sync  
- Owner: Governance Review  
- Last Updated: 2026-07-12  
- Checkpoint: `batch-b.spec03-item-a-authorization`

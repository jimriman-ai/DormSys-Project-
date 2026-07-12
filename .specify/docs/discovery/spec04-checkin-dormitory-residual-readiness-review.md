---
artifact: spec04_checkin_dormitory_residual_readiness_review
status: REVIEW_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: NON_AUTHORIZING_DISCOVERY
determination: NO_FURTHER_ACTION_RECOMMENDED
date: 2026-07-12
---

# Spec04 Check-in ↔ Dormitory Residual — Readiness Review

**Artifact type:** Residual readiness review (non-authorizing discovery)  
**Upstream selection:** `.specify/docs/planning/next-work-selection.md`  
**Status:** `REVIEW_RECORDED`

This review does **not** authorize implementation, create contracts, reopen Spec04 Allocation Assignability, reopen Spec07, or change ownership.

---

## A. Review Scope

| Field | Value |
| ----- | ----- |
| Residual under review | Spec04 Product residual labeled **CheckIn/CheckOut ↔ Dormitory occupancy request wiring** (also called Check-in ↔ Dormitory wiring) |
| Question | What is the actual current status of this residual based on repository evidence? |
| Allowed | Inspect planning/status/ownership/implementation evidence; determine if a real Spec04 (or Spec07) gap exists; recommend minimal next process step |
| Forbidden | Code changes; contract/IA creation; Spec04 Assignability reopen; Spec07 silent reopen; broad planning cleanup; assuming a Check-in gap exists |

---

## B. Evidence Reviewed

| Artifact | Relevance | Signal |
| -------- | --------- | ------ |
| `handoff/spec04-backend-closeout.md` §6 | Origin of residual wording: “CheckIn/CheckOut ↔ Dormitory occupancy request wiring (process ownership remains outside Dormitory)” | **Status / deferral** — Spec04 exclusion, not Spec04 ownership claim |
| `specs/004-accommodation-resource/spec.md` residual table | Lists CheckIn wiring as `DEFERRED_TO_FUTURE_WAVE`; Assignability row `CLOSED` | **Open status label** under Spec04 Product PENDING_RESIDUAL |
| `decision/spec04-residual-ownership-decision.md` D2 | Owner = `SPEC07`; Spec07 not auto-reopened; no Check-in IA | **Ownership resolved** — not Spec04 |
| `planning/spec04-residual-ownership-map.md` | Still shows Check-in as TBD / Pending Decision | **Status noise** — stale vs D2 Decision Record |
| `catalog-decisions.md` CD-014 / CD-015 | Allocation assignment; Dormitory physical; CheckIn operational transitions; CheckIn **not** inside Dormitory; event contract / reconciliation **not decided** | **Boundary clear**; optional future design debt outside Spec04 packet |
| `governance/contracts/allocation-dormitory-integration-contract.md` | Physical markers: Allocation → Dormitory projector; **No CheckIn/CheckOut logic** | **Satisfied path is Allocation↔Dormitory**, not CheckIn↔Dormitory |
| `specs/007-allocation-checkin/tasks.md` US3/US4 | US3 Dormitory signals (Allocation); US4 CheckIn reads Allocation only; T001–T074 complete; Spec07 Fully Closed | **CheckIn delivered without Dormitory wiring by design** |
| `app/Modules/CheckIn/.../CheckInAction.php` | Check-in persists stay record + `CheckedIn` event; uses `AllocationAssignmentReadPort` only | **No Dormitory coupling in live CheckIn path** |
| Assignability closeout / contract / review | Live Allocation → Spec04 reserve/occupy-marker/release; Spec07 must not write Spec04 tables; Spec07 owns stay truth | **Assignability CLOSED**; reinforces separation of inventory markers vs CheckIn presence |
| `specs/004-accommodation-resource/research.md` / spec FR notes | CheckIn does **not** transition Spec04 markers; effective occupancy from Allocation + CheckIn (not single Dormitory field) | **Already-satisfied architectural model** — no Spec04 CheckIn marker-transition requirement |
| `planning/next-work-selection.md` | Selected this readiness review only | Discovery mandate; not IA |
| `discovery/spec04-allocation-residual-readiness-review.md` | Explicitly placed Check-in wiring **out** of Allocation Assignability residual | Residual was never part of closed Assignability scope |

---

## C. Current Residual Interpretation

### What the residual appears to mean

Historically, Spec04 backend closeout **excluded** CheckIn/CheckOut ↔ Dormitory “occupancy request wiring” because Spec04 backend phases did not own operational stay transitions. The residual was recorded as deferred future-wave scope with **process ownership outside Dormitory**.

Later ownership Decision **D2** assigned Check-in ↔ Dormitory residual ownership to **Spec07**. Spec07 then delivered CheckIn/CheckOut operational transitions against **Allocation assignment facts**, not against Dormitory occupancy-request APIs.

Physical inventory-marker collaboration that Spec04 *did* need for allocation-time truth was the **Allocation ↔ Dormitory** path (ADIC / physical-state signals). That path was refined and closed as **Allocation Assignability** (`SPEC04_RESIDUAL_CLOSED`). ADIC explicitly excludes CheckIn/CheckOut logic.

### Distinctions

| Interpretation | Assessment |
| -------------- | ---------- |
| Actual Spec04 behavior/product gap | **Not evidenced.** Spec04 does not own CheckIn process; Spec04 research/spec exclude CheckIn marker transitions; Assignability covers Allocation→Dormitory markers. |
| Ownership/boundary ambiguity | **Resolved for ownership** (D2 = Spec07). Residual map “Pending Decision” is stale documentation, not an open ownership question. |
| Status/documentation mismatch | **Yes.** Spec04 Product still lists Check-in wiring as open deferred residual while ownership + Spec07 delivery + CD-014/015 model make Spec04 execution unjustified. |
| Already-satisfied scope (relative to Spec04 residual intent) | **Partial / by design.** Spec04’s exclusion (“process outside Dormitory”) remains true. Spec07 CheckIn operational capability exists. Allocation→Dormitory marker wiring is live. CheckIn→Dormitory “occupancy request” coupling was **never** authorized Spec04 work and was **not** required by Spec07 US4. |
| Spec07 future design debt | CD-015 / UD-01 leave detailed CheckIn↔Allocation↔Dormitory event contracts and reconciliation undecided. That is **not** a Spec04 residual packet and does **not** justify Spec04 implementation from this review. |

---

## D. Boundary and Ownership Assessment

| Concern | What evidence supports |
| ------- | ---------------------- |
| Spec04 | Owns physical inventory / assignability markers (Assignability **closed**). Does **not** own CheckIn operational transitions. Residual origin was an exclusion list item. |
| Spec07 | Owns Check-in / resident-presence (D2). CheckIn module delivered under Spec07 Fully Closed program. Active execution scope: **none**. |
| Shared/inter-system boundary | Physical markers: Allocation → Dormitory (live). Stay truth: CheckIn. CD-014: effective occupancy derived from Allocation + CheckIn — **not** a single Dormitory authoritative stay field. |
| Unresolved ownership space | **No** for residual ownership (D2 decided). Optional future event-contract design remains undecided under CD-015/UD-01 — Spec07-owned if ever selected; **not** Spec04 ownership reopen. |

This review does **not** assign new ownership.

---

## E. Readiness Determination

`NO_FURTHER_ACTION_RECOMMENDED`

**Why (evidence-based):**

1. No Spec04 implementable packet defines CheckIn↔Dormitory occupancy-request wiring as Spec04 delivery scope.  
2. Closeout residual text itself places process ownership **outside Dormitory**.  
3. Ownership Decision D2 places any later check-in wiring under **Spec07**, without auto-reopen.  
4. Spec07 CheckIn implementation is complete and intentionally uncoupled from Dormitory (Allocation read + stay records only).  
5. The Allocation↔Dormitory physical collaboration that was a real Spec04 supplier gap is **already closed** as Assignability.  
6. CD-014/ADIC architecture treats CheckIn as a separate boundary from Dormitory physical projection; forcing CheckIn→Dormitory “occupancy request” wiring as Spec04 work would contradict accepted boundaries.  
7. Therefore this residual, as currently tracked under Spec04 open Product residuals, is **not** a real unresolved Spec04 product/behavior gap requiring Spec04 contract, IA, or coding. It should not be promoted into Spec04 execution.

Alternate labels considered and rejected as primary determination:

- `REAL_UNRESOLVED_GAP` — would invent Spec04/Spec07 implementation need without a defined Spec04 requirement or Spec07 reopen authority.  
- `OWNERSHIP_CLARIFICATION_REQUIRED` — ownership already decided (D2).  
- `ALREADY_SATISFIED` — accurate for “Spec04 does not own CheckIn process” and for Allocation→Dormitory markers, but overclaims that a named CheckIn↔Dormitory sync feature was delivered; Spec07 never built that coupling.  
- `DOCUMENTATION_OR_STATUS_ARTIFACT` — also true as a secondary characterization of the Spec04 open-residual listing; primary operational outcome is **no further Spec04 action**.

---

## F. Recommended Next Step

**Minimal process step (non-authorizing):**

1. Treat Spec04 Check-in ↔ Dormitory residual as **Spec04 non-actionable** — recommend **close / retire** that residual from Spec04 open Product residual tracking when a later status/catalog reconciliation step is authorized.  
2. Do **not** prepare Spec04 contract-definition or IA for this residual.  
3. Do **not** reopen Spec07 on the basis of this discovery alone.  
4. If a future product need for CheckIn↔Dormitory event reconciliation ever arises, it must be a **separate Spec07-owned selection** with explicit reopen/authorization — not Spec04 Assignability reopen and not silent conversion of this discovery into coding.

This section does **not** authorize implementation, catalog edits, or Spec07 mutation.

---

## G. Scope Integrity Check

| Check | Result |
| ----- | ------ |
| Closed Spec04 Allocation Assignability not reopened | **Confirmed** |
| No implementation authority created | **Confirmed** (`NONE`) |
| No contract authority created | **Confirmed** |
| No code changes performed | **Confirmed** |
| Only this discovery artifact written | **Confirmed** |

---

## Required Final Decision Block

```text
SPEC04_CHECKIN_DORMITORY_RESIDUAL_READINESS_REVIEW

Determination:
NO_FURTHER_ACTION_RECOMMENDED

Residual Status:
Spec04-tracked Check-in ↔ Dormitory residual is a deferred exclusion/status label; ownership is Spec07 (D2); Spec07 CheckIn exists without Dormitory coupling by design; Allocation→Dormitory markers already closed via Assignability — no Spec04 execution justified.

Recommended Next Step:
Retire/close Spec04 residual tracking for Check-in wiring when next status reconciliation is authorized; do not create Spec04 contract/IA; do not reopen Spec07 from this review alone.

Implementation Authority:
NONE

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_NOT_REOPENED
```

---

## No-Change Confirmation

`No application, test, migration, catalog, contract, authorization, review, closeout, or Spec04 Assignability artifacts were modified.`

Only this artifact was created:

- `.specify/docs/discovery/spec04-checkin-dormitory-residual-readiness-review.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`REVIEW_RECORDED`** / **`NO_FURTHER_ACTION_RECOMMENDED`**  
- Residual: Spec04 Check-in ↔ Dormitory  
- Last Updated: 2026-07-12

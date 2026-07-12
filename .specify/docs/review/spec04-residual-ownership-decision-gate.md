---
artifact_type: ownership_decision_gate
target_spec: spec04
authority_level: decision_preparation
execution_authority: none
mutation_permission: none
source_baseline: wave-02-spec04-alignment-closeout.md
source_map: spec04-residual-ownership-map.md
timestamp: 2026-07-12
---

# Spec04 Residual Ownership Decision Gate

## 1. Artifact Metadata

| Field | Value |
| ----- | ----- |
| Artifact path | `.specify/docs/review/spec04-residual-ownership-decision-gate.md` |
| Artifact type | `ownership_decision_gate` |
| Target Spec | `spec04` |
| Authority level | `decision_preparation` |
| Execution authority | `none` |
| Mutation permission | `none` |
| Upstream baseline | `.specify/governance/wave-02-spec04-alignment-closeout.md` |
| Upstream map | `.specify/docs/planning/spec04-residual-ownership-map.md` |
| Recorded | 2026-07-12 |

**Purpose:** Prepare an ownership Decision Gate for Spec04 deferred residuals. Identifies candidate domains from repository evidence. Does **not** assign ownership, Wave numbers, or Implementation Authorization.

---

## 2. Inputs Used

| Input | Role |
| ----- | ---- |
| Wave 02 Spec04 alignment closeout | Residual set baseline (`DEFERRED_TO_FUTURE_WAVE`) |
| Spec04 residual ownership map | Traceability rows (Candidate Domain Owner still TBD) |
| `spec04-backend-closeout.md` §6 / §7 | Explicit residual exclusions and stop boundary |
| Spec04 GDR Decision 4 | Residuals deferred to future waves/specs; not cancelled |
| `catalog-decisions.md` CD-014 / CD-015 | Allocation / Dormitory / CheckIn-CheckOut ownership split |
| `context-map.md` R7 / R12 | Allocation→Dormitory→CheckIn/CheckOut; Identity→all contexts |
| `spec-catalog.md` | Spec02 Identity Frozen; Spec07 Fully Closed (Allocation + CheckIn/CheckOut program); Spec04 Product PENDING_RESIDUAL |
| `spec04-implementation-authorization.md` | Backend IA excluded policies/gates/auth code and Dormitory UI |

---

## 3. Residual Evaluations

### 3.1 Auth integration

| Field | Assessment |
| ----- | ---------- |
| Residual (baseline) | Auth integration |
| Closeout wording | Authorization / policies / roles / guards for Dormitory surfaces |
| Evidence summary | Spec04 backend closeout and IA exclude policies/gates/authorization code from delivered backend Phases 1–4. Spec04 authorization-test-strategy was a **design** gate only. Platform Identity/RBAC baseline lives in `spec02` (Frozen — Wave 1A). Context-map R12: Identity supplies cross-cutting auth to all contexts; mechanism historically deferred to Identity. Dormitory-**surface** policy implementation was never accepted in Spec04 backend closeout. |
| Candidate owning domain/spec (if evidence exists) | **Split candidate (not assigned):** (a) Identity (`spec02`) for roles/permissions platform capability; (b) Spec04 Product residual / Dormitory Presentation for dormitory-surface policy binding. No single Spec id is exclusively evidenced as owner of “Auth integration” as a whole. |
| Confidence | `SUSPECTED` (Identity as auth platform); `UNKNOWN` (whether Dormitory surface policies remain Spec04 product work vs Identity extension) |
| Assignable now? | **No — requires Decision Gate choice** (and possibly further discovery on permission catalog ownership). Spec02 Frozen + Spec04 Product PENDING_RESIDUAL both constrain silent assignment. |
| Wave assignment | **Not supported** — no nomination/IA artifact for this residual packet |

---

### 3.2 UI presentation

| Field | Assessment |
| ----- | ---------- |
| Residual (baseline) | UI presentation |
| Closeout wording | Livewire / Blade / UI; HTTP/API/controllers/FormRequests also excluded |
| Evidence summary | Spec04 IA and backend closeout explicitly exclude Dormitory UI and UI governance resumption. Spec04 Product remains `PENDING_RESIDUAL`. Platform UI Anti-Leak contracts exist as architecture governance, not Spec ownership. Spec02 notes Livewire admin deferred (Identity admin), which is a parallel deferral pattern, not Spec04 UI ownership evidence. |
| Candidate owning domain/spec (if evidence exists) | **Spec04 Product residual (Presentation)** is the strongest **default listing** owner in current Spec04 Governance & Evolution Notes — i.e., residual still tracked under Spec04 until reassigned. No separate Spec id is evidenced as owning “Dormitory admin UI.” |
| Confidence | `SUSPECTED` (remains Spec04 Product residual until reassigned); `UNKNOWN` (successor Spec / UI workstream id) |
| Assignable now? | **Partially — Spec04 Product residual retention is evidenced;** formal transfer to another Spec/workstream **not assignable** without Decision Gate + nomination. Further discovery needed if UI is to leave Spec04 id. |
| Wave assignment | **Not supported** |

---

### 3.3 Allocation integration

| Field | Assessment |
| ----- | ---------- |
| Residual (baseline) | Allocation port integration |
| Closeout wording | Allocation ↔ Dormitory integration including `bedExists` / `isBedAssignable` and any Application Read extension required |
| Evidence summary | CD-014 **CONFIRMED**: Allocation owns assignment; Dormitory owns physical state. Context-map R7: Allocation → Dormitory (and CheckIn/CheckOut). Spec07 catalog Fully Closed with active execution scope **none**; next Allocation work requires new explicit authorization. Spec04 Phase 4 delivered only thin Request→Dormitory `siteExists` integration — not Allocation bed ports. Closeout lists Allocation integration as deferred, not cancelled. |
| Candidate owning domain/spec (if evidence exists) | **Dual-boundary residual:** Allocation (`spec07`) as consumer of physical-readiness ports; Dormitory (`spec04`) as supplier of any required Application Read extensions. Domain responsibilities are **CONFIRMED** by CD-014/R7; Spec packet ownership for the deferred work item remains undecided. |
| Confidence | Domain split: `CONFIRMED`. Spec-level owner of the residual work packet: `SUSPECTED` (cross-Spec; Spec07 closed constrains reopen). |
| Assignable now? | **Domain candidates can be named at Decision Gate (`CONFIRMED` split).** Formal Spec ownership / reopen of Spec07 vs new Spec04 Application Read extension **not assignable without Decision Gate** (and likely new IA). Further discovery may be needed for exact port contract surface. |
| Wave assignment | **Not supported** — Spec07 “next implementation requires new explicit authorization” (catalog) |

---

### 3.4 Check-in wiring

| Field | Assessment |
| ----- | ---------- |
| Residual (baseline) | Check-in wiring |
| Closeout wording | CheckIn/CheckOut ↔ Dormitory occupancy request wiring; process ownership remains outside Dormitory |
| Evidence summary | CD-015 **CONFIRMED**: CheckIn/CheckOut is an active boundary; owns operational transitions; rejected as inside Dormitory. Context-map places CheckIn/CheckOut under `spec07` program with Allocation. Spec04 closeout states process ownership outside Dormitory. Spec07 Fully Closed; active execution none. |
| Candidate owning domain/spec (if evidence exists) | **CheckIn/CheckOut** (catalog/`context-map`: within `spec07` program) as process owner; Dormitory as physical-state collaborator only. |
| Confidence | Domain ownership of operational transitions: `CONFIRMED` (CD-015). Spec07 as residual work home: `SUSPECTED` (program closed; reopen needs new authorization). |
| Assignable now? | **Domain candidate `CONFIRMED` for Decision Gate recording.** Spec/Wave assignment and implementation **not assignable now** without Decision Gate + authorization artifacts. Further discovery needed if CheckIn is split from Spec07 inventory later. |
| Wave assignment | **Not supported** |

---

## 4. Assignability Summary

| Residual | Assignable now? | Notes |
| -------- | --------------- | ----- |
| Auth integration | **Pending evidence / Decision Gate** | Split Identity vs Dormitory-surface policy; not single-owner CONFIRMED |
| UI presentation | **Retain under Spec04 Product residual (SUSPECTED)** until reassigned | Transfer target Spec UNKNOWN |
| Allocation integration | **Domain split CONFIRMED; Spec packet Pending Decision** | Spec07 closed blocks silent reopen |
| Check-in wiring | **Domain CONFIRMED (CheckIn/CheckOut); Spec packet Pending Decision** | Spec07 program closed |

---

## 5. Overall Readiness for Ownership Decision

| Field | Value |
| ----- | ----- |
| Decision Gate readiness | **READY FOR HUMAN OWNERSHIP DECISION** on domain candidates where confidence is `CONFIRMED` or strong `SUSPECTED` |
| Ready to finalize Spec/Wave owners without further discovery | **NO** for Auth and UI transfer targets; **PARTIAL** for Allocation / Check-in domain naming |
| Blocking for implementation | Yes — `execution_authority: none`; residuals remain `DEFERRED_TO_FUTURE_WAVE` |
| Recommended Decision Gate outputs (types only; not decided here) | (1) Record domain candidates per residual; (2) Decide Spec04 retention vs transfer; (3) Decide whether Spec07 reopen / new Spec / Spec04 Application extension packets are required; (4) Explicitly forbid Wave invention without nomination artifacts |

---

## 6. Explicit Non-Decisions

This artifact does **not**:

- Assign ownership (individual or Spec) as final
- Assign Wave numbers
- Authorize implementation
- Move Spec04 lifecycle labels
- Mutate Spec04 package, catalog, or residual map

---

## Document Control

| Field | Value |
| ----- | ----- |
| Version | 1.0.0 |
| Status | Decision preparation — awaiting ownership Decision Gate |
| Last updated | 2026-07-12 |

---
artifact_type: residual_ownership_mapping
target_spec: spec04
authority_level: planning_only
execution_authority: none
mutation_permission: none
source_baseline: wave-02-spec04-alignment-closeout.md
source_decision: spec04-governance-decision.md
timestamp: 2026-07-12
---

# Spec04 Residual Ownership Mapping

## 1. Artifact Metadata

| Field | Value |
| ----- | ----- |
| Artifact path | `.specify/docs/planning/spec04-residual-ownership-map.md` |
| Artifact type | `residual_ownership_mapping` |
| Target Spec | `spec04` |
| Authority level | `planning_only` |
| Execution authority | `none` |
| Mutation permission | `none` |
| Upstream baseline | `.specify/governance/wave-02-spec04-alignment-closeout.md` |
| Upstream decision | `.specify/docs/decision/spec04-governance-decision.md` (Decision 4) |
| Recorded | 2026-07-12 |

**Purpose:** Record deferred Spec04 residual scope for a future ownership Decision Gate. Traceability preparation only.

**Non-actions:** Does not assign individual owners; does not authorize implementation; does not create new scope; does not move Spec04 lifecycle status; does not invent Wave numbers without repository-based nomination evidence.

---

## 2. Required Residual Ownership Map

| Residual Scope Item | Evidence Source | Candidate Domain Owner | Ownership Status |
| ------------------- | --------------- | ---------------------- | ---------------- |
| Auth integration | closeout evidence — `.specify/docs/handoff/spec04-backend-closeout.md` §6 (“Authorization / policies / roles / guards for Dormitory surfaces”); Wave 02 baseline residual set; `specs/004-accommodation-resource/spec.md` Governance & Evolution Notes residual table | TBD | Pending Decision |
| UI presentation | closeout evidence — `spec04-backend-closeout.md` §6 (“Livewire / Blade / UI”; HTTP/API/controllers/FormRequests); Wave 02 baseline residual set; Spec04 Governance & Evolution Notes | TBD | Pending Decision |
| Allocation integration / Allocation Assignability | closeout evidence — `spec04-backend-closeout.md` §6; ownership D1 + assignability ownership; closeout `.specify/docs/closeout/spec04-allocation-assignability-residual-closeout.md` | `SPEC04` | **CLOSED** (`SPEC04_RESIDUAL_CLOSED` / `FULLY_CLOSED`; implementation accepted 2026-07-12) |
| Check-in wiring | closeout evidence — `spec04-backend-closeout.md` §6; ownership D2; readiness `NO_FURTHER_ACTION_RECOMMENDED`; reconciliation `.specify/docs/reconciliation/spec04-checkin-residual-closeout-reconciliation.md` | `SPEC07` (D2) | **RETIRED_FROM_ACTIVE_SPEC04_TRACKING** / `CLOSED_NO_FURTHER_ACTION` (2026-07-12) — not Spec04 execution; Spec07 not auto-reopened |

---

## 3. Repository Evidence Pointers (non-authorizing)

| Residual | Closeout / baseline wording (verbatim class) | Related repository context (not ownership assignment) |
| -------- | -------------------------------------------- | ----------------------------------------------------- |
| Auth integration | Authorization / policies / roles / guards for Dormitory surfaces | Spec02 owns foundation (D3). **Closed bounded Spec02 packet:** Application-layer dormitory structure PEP binding (`SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED`, 2026-07-13). **Still deferred:** UI/Presentation/HTTP auth, role→permission mapping for `dormitory.structure.*`, OA-02-01 / Livewire admin — not claimed by that packet |
| UI presentation | Livewire / Blade / UI | Presentation-layer work; Spec04 backend closeout excludes UI |
| Allocation integration / Assignability | Allocation ↔ Dormitory live assignability + markers (closed residual) | Spec04 supplier + Integration bridges; lottery `dormitory_id`→`bedId` remains non-blocking test/prod mapping debt |
| Check-in wiring | CheckIn/CheckOut ↔ Dormitory occupancy request wiring (Spec04 tracking retired) | Ownership D2 = Spec07; readiness + reconciliation retire Spec04 active tracking; Spec07 CheckIn uncoupled from Dormitory by design |

Candidate Domain Owner for **remaining open** map rows (Auth, UI) remains as previously recorded until separately reconciled. Check-in wiring is **retired** from active Spec04 tracking (not Spec04 execution).

---

## 4. Boundary Note

- Spec04 Product status remains `PENDING_RESIDUAL` for **open** residuals (Auth UI/Presentation/role-mapping remainder, UI, and other deferred items **excluding** retired Check-in wiring and excluding the closed Spec02 Application-layer structure PEP binding packet).
- **Allocation Assignability** residual is **closed** (`SPEC04_RESIDUAL_CLOSED`); not reopened by this reconciliation.
- **Check-in wiring** is **RETIRED_FROM_ACTIVE_SPEC04_TRACKING** / `CLOSED_NO_FURTHER_ACTION`; does not authorize Spec07 reopen.
- Spec02 dormitory structure Application PEP binding is **closed as a Spec02 bounded packet** — does **not** close Spec04 Auth residual as a whole and does **not** unfreeze Spec02.
- Remaining open residuals are deferred, not cancelled.
- This mapping does not grant Design Approval, Implementation Authorization, or Batch Execution Permission for open residuals.

---

## Document Control

| Field | Value |
| ----- | ----- |
| Version | 1.3.0 |
| Status | Traceability map — Assignability CLOSED; Check-in wiring RETIRED; Spec02 structure PEP binding COMPLETED (bounded); Auth UI/role-mapping remainder + UI still open |
| Last updated | 2026-07-13 |

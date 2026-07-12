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
| Allocation integration | closeout evidence — `spec04-backend-closeout.md` §6 (“Allocation ↔ Dormitory integration” including `bedExists` / `isBedAssignable`); CD-014 / catalog Allocation BC; Wave 02 baseline residual set | TBD | Pending Decision |
| Check-in wiring | closeout evidence — `spec04-backend-closeout.md` §6 (“CheckIn/CheckOut ↔ Dormitory occupancy request wiring”; process ownership outside Dormitory); CD-015 / catalog CheckIn/CheckOut; Wave 02 baseline residual set | TBD | Pending Decision |

---

## 3. Repository Evidence Pointers (non-authorizing)

| Residual | Closeout / baseline wording (verbatim class) | Related repository context (not ownership assignment) |
| -------- | -------------------------------------------- | ----------------------------------------------------- |
| Auth integration | Authorization / policies / roles / guards for Dormitory surfaces | Identity / access surfaces historically under `spec02`; Dormitory surface auth still deferred |
| UI presentation | Livewire / Blade / UI | Presentation-layer work; Spec04 backend closeout excludes UI |
| Allocation integration | Allocation ↔ Dormitory integration (`bedExists` / `isBedAssignable`) | Allocation BC / `spec07` program; CD-014 assignment vs physical-state split |
| Check-in wiring | CheckIn/CheckOut ↔ Dormitory occupancy request wiring | CheckIn/CheckOut context per CD-015; closeout states process ownership remains outside Dormitory |

Candidate Domain Owner remains **TBD** until a Decision Gate records ownership. No successor Spec id or Wave number is assigned here.

---

## 4. Boundary Note

- Spec04 Product status remains `PENDING_RESIDUAL` / `DEFERRED_TO_FUTURE_WAVE` per Wave 02 alignment closeout.
- Residuals are deferred, not cancelled.
- This mapping does not grant Design Approval, Implementation Authorization, or Batch Execution Permission.

---

## Document Control

| Field | Value |
| ----- | ----- |
| Version | 1.0.0 |
| Status | Traceability preparation — ready for next Decision Gate |
| Last updated | 2026-07-12 |

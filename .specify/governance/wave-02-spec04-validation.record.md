---
artifact_type: governance_validation_record
record_scope: wave-02-spec04
discovery_report_ref: wave-02-discovery-spec04-report.md
authority_level: evidence_only
execution_authority: none
mutation_permission: none
record_status: recorded
timestamp: 2026-07-12
---

# Wave 02 Spec04 Validation Record

## 1. Artifact Metadata

| Field | Value |
| ----- | ----- |
| Artifact path | `.specify/governance/wave-02-spec04-validation.record.md` |
| Artifact type | `governance_validation_record` |
| Record scope | `wave-02-spec04` |
| Discovery report ref | `wave-02-discovery-spec04-report.md` (assumed filename for Discovery Report output; content basis: Wave 02 Discovery — Spec04 (Hardened)) |
| Authority level | Evidence only — not approval; not execution authorization |
| Execution authority | None |
| Mutation permission | None |
| Recorded | 2026-07-12 |
| Role | Structured input for a future Decision Gate on Spec04 lifecycle authority and status; **does not** resolve conflicts or align statuses |

**Explicit non-actions of this record:**

- Does not modify Spec04 package, catalog, handoffs, or any other repository file beyond creating this record
- Does not authorize implementation, alignment edits, or status metadata changes
- Does not propose solutions, alignment actions, or new governance rules

---

## 2. Discovery Evidence Summary

Artifacts examined per Wave 02 Discovery — Spec04 (Hardened), Section 1:

| Category | Paths |
| -------- | ----- |
| Spec04 package | `specs/004-accommodation-resource/spec.md`, `plan.md`, `tasks.md`, `research.md`, `data-model.md`, `quickstart.md`, `contracts/dormitory-read-service.md`, `contracts/allocation-physical-state-port.md` |
| Catalog | `.specify/docs/spec-catalog.md` (spec04 inventory/detail rows; changelog 1.0.5) |
| Planning / foundation | `.specify/docs/handoff/spec04-planning-authorization.md`, `spec04-backend-foundation-activation.md`, related design handoffs |
| Implementation authority | `.specify/docs/handoff/spec04-implementation-authorization.md` |
| Phase reviews / Phase 4 | Multiple `spec04-*-review.md`, `*-lock.md`, `spec04-integration-implementation-authorization.md`, etc. |
| Backend closeout | `.specify/docs/handoff/spec04-backend-closeout.md` |
| Program reference | `.specify/docs/handoff/completion-wave-plan.md` |
| Normative CD | `.specify/docs/catalog-decisions.md` (CD-014; OQ-05 closed) |
| Wave 01 closeout | `.specify/governance/wave-01-baseline-alignment-closeout.md` (no Spec04 mention) |

Discovery classification vocabulary retained: `CONFIRMED`, `SUSPECTED`, `UNKNOWN`.

---

## 3. Status Conflict Analysis

### Conflict 1: Spec package / Catalog vs Handoff chain

| Side | Artifacts | Claimed posture |
| ---- | --------- | --------------- |
| A | `specs/004-accommodation-resource/spec.md` | `**Status**: **Planning — spec authored** (implementation not authorized)` |
| A | `plan.md`, `contracts/dormitory-read-service.md` | Planning authorized; implementation not authorized |
| A | `.specify/docs/spec-catalog.md` | **Planning Authorized** (implementation not authorized); Hold implementation until separate authorization |
| B | `spec04-implementation-authorization.md` | `IMPLEMENTATION_AUTHORIZED` (backend foundation scope) — 2026-07-10 |
| B | `spec04-backend-closeout.md` | `SPEC04_BACKEND_CLOSED` — 2026-07-11 |

**Classification:** `CONFIRMED` (Discovery §5 finding 1).

### Conflict 2: `tasks.md` vs Backend closeout

| Side | Artifact | Claimed posture |
| ---- | -------- | --------------- |
| A | `specs/004-accommodation-resource/tasks.md` | **Design approved** — **Implementation not authorized**; go-ahead pointer to `handoff/spec04-planning-authorization.md`; all task checkboxes `- [ ]` (no `- [x]`) |
| B | `spec04-backend-closeout.md` | Phases 1–4 accepted; tests recorded passed; `SPEC04_BACKEND_CLOSED` |

**Classification:** `CONFIRMED` (Discovery §5 finding 2).

### Conflict 3: Catalog hierarchy open note vs IA / closeout hierarchy

| Side | Artifact | Claimed posture |
| ---- | -------- | --------------- |
| A | `spec-catalog.md` detail notes | **Open (planning):** building/floor hierarchy — resolve during spec04 authoring |
| B | `spec04-implementation-authorization.md` / closeout | Approved hierarchy includes Floor; tables include `dormitory_floors` (Dormitory → Building → Floor → Room → Bed) |

**Classification:** `CONFIRMED` (Discovery §5 finding 3).

### Conflict 4: Spec OA-04-01 / FR-003–004 vs Backend Floor aggregate

| Side | Artifact | Claimed posture |
| ---- | -------- | --------------- |
| A | `spec.md` OA-04-01, FR-003, FR-004, US1 acceptance | Floor is Room attribute; **Building → Room → Bed**; no separate Floor aggregate |
| B | Backend closeout / IA design chain | Explicit **Floor** level in hierarchy and persistence |

**Classification:** `SUSPECTED` domain-evolution discrepancy (Discovery §5 finding 4); not resolved here.

### Conflict 5: Governance Traceability incompleteness

| Side | Artifact | Claimed posture |
| ---- | -------- | --------------- |
| A | `spec.md` Governance Traceability table | Lists catalog Planning Authorized + planning-authorization implementation hold only |
| B | Handoff chain after 2026-07-10 | IA and `SPEC04_BACKEND_CLOSED` exist and are not listed in that table |

**Classification:** `CONFIRMED` (Discovery §5 finding 6).

### Conflict 6: `tasks.md` go-ahead pointer vs actual IA artifact

| Side | Artifact | Claimed posture |
| ---- | -------- | --------------- |
| A | `tasks.md` Status line | Implementation not authorized until go-ahead via `handoff/spec04-planning-authorization.md` |
| B | `spec04-planning-authorization.md` | Planning only; defers implementation to separate authorization |
| B | `spec04-implementation-authorization.md` | The separate IA that later authorized backend implementation |

**Classification:** `CONFIRMED` (Discovery §5 finding 7).

### Conflict 7: Partial backend closure vs package/catalog whole-spec status

| Side | Artifact | Claimed posture |
| ---- | -------- | --------------- |
| A | Catalog / `spec.md` / `plan.md` / `tasks.md` | Whole Spec04 framed as planning / implementation hold |
| B | `spec04-backend-closeout.md` §6 / §7 | Backend Phases 1–4 closed; Auth/UI/Allocation integration/CheckIn/etc. remain out of scope; **does not** claim full product/feature closure |

**Classification:** `CONFIRMED` (Discovery §5 finding 8).

### Non-conflict observation (scope framing)

| Observation | Evidence |
| ----------- | -------- |
| Spec04 outside Wave 01 baseline alignment set | `.specify/governance/wave-01-baseline-alignment-closeout.md` — no Spec04 mention (`CONFIRMED`) |
| `governance_phase:` absent on Spec04 `spec.md` | No YAML/key field present (`CONFIRMED` missing field) |
| Application code inventory vs closeout claims | Not inspected in Discovery (`UNKNOWN`) |
| Full Spec04 product-closure handoff | Not found under `spec04*` naming; closeout disclaims full closure (`UNKNOWN` / missing as full-product claim) |

---

## 4. Authority Mapping

### Lifecycle postures implied by artifacts

| Posture | Where documented | What it authorizes / asserts (as written) |
| ------- | ---------------- | ---------------------------------------- |
| **Planning Authorized** | `spec-catalog.md` (incl. changelog 1.0.5); `spec.md` Catalog line; `plan.md` Governance line; `spec04-planning-authorization.md` | Specify / plan / tasks only; implementation not authorized |
| **Backend foundation design activated** | `spec04-backend-foundation-activation.md` | Design activation; explicitly does **not** set `IMPLEMENTATION_AUTHORIZED`; states catalog Planning Authorized unchanged |
| **Implementation Authorized** | `spec04-implementation-authorization.md` | Backend foundation implementation scope only; not UI / Auth / Workflow / unrelated specs |
| **Phase acceptances (1–4)** | Domain / Persistence / Read / Mutation / Integration review handoffs | Layer/phase acceptance toward backend closeout |
| **Backend Closed** | `spec04-backend-closeout.md` (`SPEC04_BACKEND_CLOSED`); echoed in `completion-wave-plan.md` | Authorized backend Phases 1–4 complete; not full product closure; does not authorize residual work |

### Ambiguity: overall Spec04 status authority

Discovery and this record identify **no single repository statement** that reconciles package/catalog “Planning / not authorized” with handoff “IA → backend closed (partial).”

Ambiguities for Decision Gate (evidence-only; not resolved here):

1. Whether catalog / `spec.md` Status are **mirrors** that lag handoff authority, or still the intended **display** of overall Spec04.
2. Whether `SPEC04_BACKEND_CLOSED` is the terminal posture for **backend-only** Spec04, while overall Spec04 remains open for residuals.
3. Whether Authority Map in `catalog-decisions.md` (DA / IA / Batch ownership) implies IA + closeout outrank catalog/`spec.md` for execution truth — **this record does not interpret or extend the Authority Map**; it only notes that Spec04 artifacts currently disagree.

---

## 5. Backend Closeout Boundary Analysis

Source: `.specify/docs/handoff/spec04-backend-closeout.md` §6 (“What remains out of scope”) and §7 (Stop Boundary).

### Explicitly excluded from `SPEC04_BACKEND_CLOSED`

- Allocation ↔ Dormitory integration (including `bedExists` / `isBedAssignable` and any Application Read extension required)
- CheckIn/CheckOut ↔ Dormitory occupancy request wiring
- Workflow ownership or orchestration inside Dormitory
- Authorization / policies / roles / guards for Dormitory surfaces
- HTTP / API / controllers / FormRequests
- Livewire / Blade / UI
- Events / listeners / jobs unless separately approved
- External system adapters
- Voucher / billing / payment / notification behavior
- Broad Request Feature-suite remediation for dormitory fixtures (unless separately authorized)
- Full product/feature closure beyond this backend scope

### Closeout stop boundary (as written)

- Does not reopen accepted phases
- Does not authorize Authorization, UI, Workflow, Allocation, or CheckIn work
- Does not claim Spec04 product closure beyond authorized backend phases 1–4

### Implications for overall Spec04 lifecycle status (documentary only)

| Implication | Basis |
| ----------- | ----- |
| Backend slice can be closed while Spec04 product scope remains incomplete | Closeout §6 / §7 explicit disclaimer |
| Package/catalog “implementation not authorized” does not distinguish closed backend vs open residual | Conflict 7 |
| Residual work inventory exists primarily as closeout exclusion list, not as a separate residual Spec04 plan artifact cited in Discovery | Discovery §5 finding 8; Open Question on residual tracking |

This section does **not** decide whether overall Spec04 should be labeled Planning, Partially Closed, Backend Closed, or Fully Closed.

---

## 6. Domain Evolution Trace (Floor Modeling)

### Documented discrepancy

| Artifact | Floor modeling statement |
| -------- | ------------------------ |
| `spec.md` OA-04-01 (DECIDED) | Internal: **Dormitory → Building → Room → Bed**. Floor is a **Room attribute**; **not** a separate aggregate. Alternatives rejected include “Floor as separate aggregate.” |
| `spec.md` FR-003 / FR-004 | Building → Room → Bed; floor as Room attribute, not separate aggregate root |
| `spec.md` US1 acceptance #3 | Rooms with optional floor labels; without requiring a separate Floor aggregate (OA-04-01) |
| Backend IA / closeout | Hierarchy **Dormitory → Building → Floor → Room → Bed**; persistence includes `dormitory_floors` |

### Decision posture of this record

**No supersession or coexistence decision is made.** The discrepancy is recorded as a potential domain evolution / package lag that requires Decision Gate resolution.

### Questions arising from the discrepancy

1. Is OA-04-01 still normative for Spec04 product meaning?
2. Was OA-04-01 superseded only in handoff design without Spec04 package amendment?
3. Did backend implementation evolve independently of OA-04-01?
4. Was the Spec04 package (`spec.md` / `data-model.md` / contracts) updated to match accepted Floor aggregate design?
5. Should catalog’s “Open (planning): building/floor hierarchy” be read as stale relative to OA-04-01 “DECIDED” and/or relative to Floor aggregate in closeout?

---

## 7. Open Questions for Decision Gate

Derived from Discovery §7 and Conflicts §3–§6:

1. Which repository artifact is the **authoritative current Spec04 status** for catalog / `spec.md` sync purposes: planning hold, `IMPLEMENTATION_AUTHORIZED`, or `SPEC04_BACKEND_CLOSED` (backend-only)?
2. Should the catalog note **“Open (planning): building/floor hierarchy”** be considered stale relative to OA-04-01 and/or IA/closeout Floor hierarchy evidence?
3. Does `SPEC04_BACKEND_CLOSED` require or imply updates to `tasks.md` Status line and/or task checkboxes?
4. Is OA-04-01 superseded, still binding, or did the backend evolve independently of the Spec04 package?
5. What is the **documented plan** for residual Spec04 work (Auth, UI, Allocation ports, CheckIn wiring, etc.) — and under which Spec/workstream is it tracked?
6. Is `governance_phase` required on Spec04 `spec.md` for Wave 02 validation/alignment, given it is currently absent?
7. Does any other governance path (non-`spec04*` naming) claim **full** Spec04 product closure contrary to closeout’s disclaimer?
8. Should overall Spec04 status distinguish **backend closed** from **product open**, and if so, which artifacts must display that distinction?
9. Should `tasks.md` go-ahead pointer remain at planning-authorization, or point at `spec04-implementation-authorization.md` / closeout (pointer correctness only — not an alignment edit here)?

---

## 8. Recommended Decision Input (Not Decisions)

This section lists **decision types** required at the next gate. It does **not** propose outcomes, status strings, or alignment actions.

| Decision type ID | Decision input required |
| ---------------- | ----------------------- |
| D-W02-04-01 | **Authoritative status artifact** — Which artifact (or ordered chain) defines current overall Spec04 lifecycle display/status for mirrors (`spec-catalog.md`, `spec.md`, `tasks.md`)? |
| D-W02-04-02 | **Partial vs full closure labeling** — How to represent `SPEC04_BACKEND_CLOSED` without implying full product closure, given closeout §6 exclusions? |
| D-W02-04-03 | **Mirror sync eligibility** — Whether Spec04 is eligible for documentary alignment (Wave-style Status/catalog sync) now, later, or only after residual scope definition? |
| D-W02-04-04 | **Domain model evolution (Floor)** — Resolution path for OA-04-01 / FR-003–004 vs Floor aggregate in accepted backend design (supersede / amend package / other) — outcome not chosen here |
| D-W02-04-05 | **Residual work scope definition** — Where Auth / UI / Allocation integration / CheckIn wiring / events are owned after backend closeout |
| D-W02-04-06 | **Tasks progress representation** — Whether `tasks.md` must reflect closed backend phases, remain historical planning checklist, or be superseded by handoff phase reviews |
| D-W02-04-07 | **Metadata completeness** — Whether `governance_phase` (and related frontmatter) is required for Spec04 Wave 02 progression |
| D-W02-04-08 | **Catalog open-question hygiene** — Disposition of catalog “Open (planning): building/floor hierarchy” relative to OA-04-01 and closeout |

---

## Document Control

| Field | Value |
| ----- | ----- |
| Version | 1.0.0 |
| Status | Recorded — evidence only |
| Next consumer | Spec04 Decision Gate (future) |
| Wave relation | Wave 02 Spec04 validation; Spec04 was outside Wave 01 baseline alignment |
| Last updated | 2026-07-12 |

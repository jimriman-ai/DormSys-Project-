# Feature Specification: Reporting & Audit Consumption Evolution (spec11)

**Feature Branch**: `011-reporting-projections`

**Created**: 2026-07-02

**Status**: **Architecture Clarified — Planning-only** (no Design Approval · no Implementation Authorization · no execution)

**Catalog**: spec11 — Reporting (`spec-catalog.md`)

**Predecessor**: spec10 — Audit Trail & Traceability — **CLOSED / FROZEN** ([`spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md))

**Depends on**: spec01 Foundation; **frozen** spec10 baseline (`AuditHistoryReadContract`, append-only `audit_logs`)

**Input**: Initialize spec11 as a **governance-controlled, planning-only evolution layer** for audit consumption, reporting, read-model strategy, operator visibility, and downstream audit ecosystem scaling — strictly **without** reopening, modifying, or reinterpreting spec10 as active work.

**Normative boundaries**: [`context-map.md`](../../.specify/docs/context-map.md) **R11**, **CD-017**; inherited **R10**, **AP-06** from spec10 (immutable).

**Governance**: Planning and architecture framing only. **Not** nomination for execution · **Not** Implementation Authorization · **Not** task execution.

---

## 1. SPEC11_CHARTER

| Field | Value |
| ----- | ----- |
| **Name** | spec11 — Reporting & Audit Consumption Evolution |
| **Short name** | `reporting-projections` |
| **Purpose** | Define the forward architecture direction for how DormSys **consumes**, **projects**, and **presents** immutable audit history and related operational read models — without altering the spec10 system of record |
| **Predecessor** | spec10 — **CLOSED**, `immutable_status: FROZEN`, canonical baseline [`spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md) |
| **Planning-only declaration** | This specification authorizes **documentation, hypothesis formation, and governance preparation only**. It does **not** authorize code, schema, runtime, migration, producer rollout, bridge activation, or retention changes |
| **Success condition of initialization** | spec11 charter, problem frame, evolution areas, non-scope, successor rules, planning artifacts, and exit criteria are recorded; spec10 preservation is explicit; **no execution authority is implied** |

---

## 2. PROBLEM_FRAME

### Architectural questions spec11 exists to answer

1. How should DormSys evolve **authorized audit consumption** beyond the spec10 query API without violating **R10** / **AP-06**?
2. What **read-model and projection architecture** (CD-017) is appropriate for audit-heavy and cross-context reporting workloads?
3. How should **operator-facing visibility** (audit explorer, compliance views) be introduced without coupling Reporting to upstream write paths?
4. What **performance and scaling strategy** applies as audit volume grows under soft-archive retention?
5. How should **future producer onboarding** (M4 contexts deferred from spec10) coordinate with reporting needs without scope leakage?
6. What **governance model** governs expansion of the audit ecosystem (new surfaces, exports, analytics) as separate authorized programs?

### Limitations emerged from spec10 (forward-planning drivers)

| Limitation | spec10 disposition | spec11 planning implication |
| ---------- | ------------------ | --------------------------- |
| Authorized read API only — no explorer UI | OA-10-05 deferred | Operator visibility requires a separate presentation program |
| M1 producers only (Identity, Voucher) | M4 deferred | Reporting must not assume full-domain audit coverage |
| Single-table query model | Sufficient for MVP | Read-heavy aggregation may need projections |
| Activity bridge dormant | Optional M2 path | Reporting must not depend on bridge activation |
| No cross-context analytics | Out of spec10 scope | CD-017 read-only Reporting context must be designed |
| Retention soft-archive | Frozen at 84mo default | Reporting must handle archived vs active visibility policy |

### Future system pressures motivating evolution

- Increasing audit volume from deferred producers (Request, Lottery, Allocation, CheckIn, Notification)
- Compliance and governance stakeholders requiring aggregated views and exportable evidence
- Operators needing faster entity-centric investigation than raw paginated history
- Read latency expectations as history depth grows under retention policy
- Need to decouple analytical queries from operational `audit_logs` access patterns

---

## 3. EVOLUTION_AREAS

*All items below are **future possibilities** — planning tracks only, not committed scope.*

| Track ID | Planning domain | Future possibility (non-binding) |
| -------- | --------------- | -------------------------------- |
| **E-01** | Audit reporting surfaces | Evolve authorized query patterns: saved filters, aggregations, export contracts |
| **E-02** | Read-model / projections | Materialized audit projections, refresh strategy, CD-017 boundary enforcement |
| **E-03** | Audit explorer (conceptual) | Operator UI for entity/actor/event investigation — presentation layer separate from Audit module |
| **E-04** | Compliance & analytics views | Role-gated compliance dashboards, trend views over immutable history |
| **E-05** | Read performance scaling | Indexing strategy, projection offload, archive-aware query tiers |
| **E-06** | Future producer onboarding | M4 adapter planning aligned with reporting vocabulary — no retroactive spec10 changes |
| **E-07** | Consumption layer decoupling | Reporting module as sole cross-boundary read consumer; forbid Reporting writes |
| **E-08** | Ecosystem governance | Authorization waves for UI, exports, SIEM, advanced analytics — each separate |

---

## 4. NON_SCOPE (CRITICAL)

The following are **explicitly excluded** from spec11 initialization and from any authority implied by this document:

| Exclusion | Rule |
| --------- | ---- |
| Implementation | No code, modules, migrations, or jobs |
| Execution | No task execution, waves, or checkpoints |
| Migration | No data migration or backfill |
| Schema change | No changes to `audit_logs` or spec10 persistence |
| Runtime change | No activation of jobs, schedulers, or bridge |
| Producer rollout | No new audit producers |
| Bridge activation | No change to `audit.activity_bridge_enabled` default |
| Retention redesign | No hard delete, purge, or policy change |
| UI delivery | Conceptual references only — no Livewire/Blade implementation |
| spec10 mutation | No edits to spec10 specs, tasks, closure records, or Audit module behavior |
| Implicit authorization | No transfer of spec10 execution authority |

---

## 5. SUCCESSOR_BOUNDARY_RULES

1. **spec11 may reference spec10** as frozen baseline but **cannot mutate** spec10 artifacts, tasks, closure state, or implementation.
2. **All execution** under spec11 requires **separate governance authorization** (nomination → design approval → implementation authorization) — this initialization grants **none**.
3. **tasks.md entries are descriptive planning backlog items** — labeled non-executable until a future authorization record explicitly activates them.
4. **No implicit authority transfer** from spec10 — spec10 `lifecycle_state: CLOSED` and `immutable_status: FROZEN` remain authoritative.
5. **Strict separation**: spec11 `spec.md` / `plan.md` = planning layer; implementation layers require new records and may not reinterpret closed spec10 tasks as incomplete.
6. **Reporting (R11 / CD-017)** remains read-only cross-context consumer — no upstream state mutation.
7. **Inherited invariants** from spec10 must be preserved in all future spec11 design decisions:
   - R10 downstream-only audit boundary
   - AP-06 append-only system of record
   - Audit module sole writer to audit storage
   - Upstream producers via DTO/contract only
   - Bridge disabled by default (conceptually)
   - Retention soft-archive only

---

## 6. INITIALIZATION DELIVERABLES

| Artifact | Path | Purpose |
| -------- | ---- | ------- |
| Charter & scope | `specs/011-reporting-projections/spec.md` | This document |
| Evolution plan | `specs/011-reporting-projections/plan.md` | Tracks, hypotheses, planning phases |
| Planning backlog | `specs/011-reporting-projections/tasks.md` | **Non-executable** planning items |
| Quality checklist | `specs/011-reporting-projections/checklists/requirements.md` | Spec readiness gate |
| Decision log | `specs/011-reporting-projections/decision-log.md` | DL-01–DL-03 resolved at clarification |
| Architecture clarification | `specs/011-reporting-projections/architecture-clarification.md` | Consumption-layer architecture definition |

**Not yet required:** `data-model.md`, `contracts/`, implementation authorization, handoff closure records.

---

## 7. EXIT_CRITERIA

### Initialization (P0) — complete

- [x] spec11 is explicitly **planning-only**
- [x] spec10 is explicitly **preserved as frozen baseline**
- [x] **No execution authority** is implied
- [x] Boundaries (non-scope, successor rules) are clearly stated
- [x] Evolution areas are documented as future possibilities
- [x] Implementation requires **future separate authorization**

### Architecture clarification (P1) — complete

- [x] Read-model shapes defined (entity-centric, correlation-based, time-window)
- [x] Projection boundary model defined (T0/T1/T2 hybrid per DL-01)
- [x] Reporting consumption frame defined (compliance, operational, security)
- [x] Analytics separation model defined
- [x] Consumer architecture and visibility boundaries defined
- [x] DL-01, DL-02, DL-03 resolved in [`decision-log.md`](./decision-log.md)
- [x] Full artifact: [`architecture-clarification.md`](./architecture-clarification.md)

---

## Clarifications

### Session 2026-07-02

- **Q:** Where do audit reporting projections live? → **A:** Hybrid (DL-01) — Tier 0 contract-direct for investigative reads; Tier 1 Reporting-owned materialized projections for aggregates and correlation indexing.
- **Q:** What is the first consumer surface after authorization? → **A:** Layered (DL-02) — Reporting read API / export façade first; operator explorer UI as a later Presentation wave consuming Reporting ports.
- **Q:** Should reporting default exclude soft-archived rows? → **A:** Yes — mirror spec10 default; role-gated `includeArchived` for compliance roles (DL-03).
- **Q:** How are correlation-based views supported without spec10 contract changes? → **A:** Reporting-owned Tier 1 projection indexes `correlationId` during refresh from contract-sourced items — no `AuditHistoryQuery` extension in frozen spec10.
- **Q:** How are reporting and analytics separated? → **A:** Reporting provides governed, drill-down-capable consumption; analytics uses coarse-grain Tier 1 trends with separate E-08 authorization — neither mutates upstream state.

---

## User Scenarios & Testing *(planning validation)*

*These scenarios validate **planning completeness**, not implemented behavior.*

### User Story 1 - Compliance Stakeholder Needs Traceability Vision (Priority: P1)

As a compliance stakeholder, I need a documented evolution path for audit reporting and evidence export so that future implementation can satisfy governance review without reopening the frozen audit baseline.

**Why this priority**: Establishes the primary business driver for spec11 planning.

**Independent Test**: Planning artifacts describe how authorized audit history can be consumed for compliance review without violating AP-06.

**Acceptance Scenarios**:

1. **Given** spec10 is frozen, **When** a planner reviews spec11, **Then** they find explicit non-scope rules preventing audit store mutation.
2. **Given** compliance export is a future need, **When** evolution track E-01/E-04 is reviewed, **Then** read-only consumption via Reporting is the documented direction.

---

### User Story 2 - Operator Needs Visibility Concept (Priority: P2)

As a dormitory operator, I need a conceptual audit explorer direction documented so that future UI work does not embed query logic inside the Audit module.

**Why this priority**: OA-10-05 was deferred from spec10; spec11 must frame presentation separately.

**Independent Test**: Plan distinguishes Audit read contracts from presentation/explorer layers.

**Acceptance Scenarios**:

1. **Given** spec10 provides `AuditHistoryReadContract`, **When** explorer track E-03 is reviewed, **Then** UI is described as downstream consumer only.
2. **Given** Persian RTL UI requirements, **When** planning assumptions are read, **Then** localization is noted without UI implementation commitment.

---

### User Story 3 - Architect Needs Read-Model Strategy (Priority: P3)

As a system architect, I need projection and scaling hypotheses documented so that read-heavy workloads can evolve without compromising append-only audit integrity.

**Why this priority**: Prevents ad-hoc cross-module queries when audit volume grows.

**Independent Test**: Plan.md contains at least one read-model hypothesis with CD-017 alignment.

**Acceptance Scenarios**:

1. **Given** CD-017 Reporting boundary, **When** projection track E-02 is reviewed, **Then** no upstream write authority is proposed.
2. **Given** archived audit rows exist, **When** performance track E-05 is reviewed, **Then** archive-aware read tiers are acknowledged as a planning topic.

---

### Edge Cases (planning)

- What happens when a future program proposes direct SQL across `audit_logs` from a domain module? → **Rejected** — violates R10/R11; must use Reporting read layer.
- What happens when stakeholders request hard-delete retention? → **Out of spec11 planning scope** — requires constitution-level change, not spec11 initialization.
- What happens when spec10 bridge activation is requested alongside reporting? → **Separate authorization** — bridge and reporting are independent governance decisions.

---

## Requirements *(planning-level)*

### Functional Requirements (planning artifacts must satisfy)

- **FR-001**: spec11 planning artifacts MUST explicitly preserve spec10 as frozen baseline and forbid retroactive scope mutation.
- **FR-002**: spec11 MUST document read-only Reporting evolution aligned with **CD-017** and **R11**.
- **FR-003**: spec11 MUST document consumption of spec10 read contracts without redefining append-only persistence rules.
- **FR-004**: spec11 MUST separate presentation/explorer concepts from Audit module ownership.
- **FR-005**: spec11 MUST list deferred producer expansion as a distinct future program, not spec10 reopening.
- **FR-006**: spec11 planning backlog MUST be labeled non-executable until separate implementation authorization.
- **FR-007**: spec11 MUST document inherited invariants: R10, AP-06, soft-archive retention, bridge-off-default.

### Key Entities *(conceptual — planning)*

- **Audit Projection**: Derived read-only view of audit history for reporting — not a second system of record.
- **Reporting Read Model**: Cross-context projection consumer per CD-017 — no write authority.
- **Compliance View**: Role-gated analytical presentation over authorized audit reads.
- **Planning Track**: Non-binding evolution direction (E-01 … E-08).

---

## Success Criteria *(initialization)*

### Measurable Outcomes

- **SC-001**: 100% of initialization deliverables listed in §6 exist and cross-reference spec10 final closure.
- **SC-002**: All eight evolution tracks (E-01–E-08) are documented as future possibilities with no implementation commitment.
- **SC-003**: Non-scope section contains at least ten explicit exclusions including spec10 non-mutation.
- **SC-004**: A governance reviewer can confirm in under 15 minutes that spec11 grants zero execution authority.
- **SC-005**: Planning checklist passes with zero unresolved critical clarifications.

---

## Assumptions

- spec10 [`spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md) remains the canonical immutable baseline for audit recording and authorized read.
- spec11 primary consumer relationship is to **frozen** `AuditHistoryReadContract` — not to upstream domain stores.
- Reporting module promotion follows catalog spec11 identity; bounded context may be introduced only after future authorization.
- Persian (Farsi) RTL remains the target presentation locale for any future operator surfaces.
- M4 audit producers remain a **separate program** from spec11 planning.
- No SIEM, real-time streaming, or external compliance appliance integration is assumed in initialization scope.

---

## Governing Decisions (inherited)

| Decision | Implication for spec11 |
| -------- | ---------------------- |
| **CD-017** | Reporting is read-only cross-domain projection consumer |
| **R11** | Reporting ← all contexts; write forbidden |
| **R10** (inherited) | Audit remains downstream; Reporting must not become audit writer |
| **AP-06** (inherited) | No reporting workflow may UPDATE/DELETE audit records |
| **spec10 closure** | No reopening T001–T040; no bridge/retention changes via spec11 init |

---

**End of specification. Architecture clarified (P1). Planning-only. No execution authorized.**

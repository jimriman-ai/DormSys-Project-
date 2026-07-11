# Spec04 Backend Foundation Activation: Accommodation Resource / Dormitory

## 1. Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `BACKEND_FOUNDATION_ACTIVATED_FOR_DESIGN` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Decision type** | Backend foundation design activation |
| **Authority source** | Product / Governance Review |
| **Decision date** | 2026-07-10 |

**Prior catalog state (unchanged by this artifact):** Spec04 remains **Planning Authorized** only. Implementation remains on hold until a separate Spec04 implementation authorization artifact is issued.

**This artifact does not set** `IMPLEMENTATION_AUTHORIZED`.

---

## 2. Reason for Activation

UI governance for Workflow UI identified backend readiness blockers: Workflow has no UI-consumable Application/Domain capability, and Workflow UI must not proceed to feature-contract on that basis.

Dormitory (Spec04) is the required backend foundation for:

- Allocation (assignment signals → physical state)
- CheckIn/CheckOut (operational occupancy transitions against physical inventory)
- Voucher / external accommodation (catalog classification and capacity absence for external sites)
- Later Dormitory and dependent UI governance

Continuing UI feature-contracts (including Workflow UI and Dormitory UI) before Spec04 backend foundation design is completed and implementation is separately authorized is **forbidden**.

**CD-014** (closed OQ-05): Allocation owns assignment; Dormitory owns physical room/bed state; CheckIn/CheckOut manages physical occupancy transitions (operational).

**CD-015** (closed OQ-06): CheckIn/CheckOut requires backend support as an active boundary and integrates with Dormitory for physical state updates — Dormitory remains the physical state owner.

Therefore Spec04 backend foundation **design** must be activated before dependent UI or downstream operational work that assumes a live Dormitory supplier.

**Scope of this artifact:** design intake and planning only — **not** implementation authorization.

---

## 3. Allowed Scope

Design and planning only. The following are authorized as **design/planning work products** (documents, models, contracts-as-design):

| Allowed | Notes |
| ------- | ----- |
| Dormitory backend foundation planning | Plan/refine Spec04 backend foundation design artifacts |
| Domain model design | Entities, aggregates, invariants — design only |
| Entity / value object boundary design | Pure design; no Domain PHP |
| Dormitory / building / floor / room / bed hierarchy decision | Must close catalog open planning item |
| Capacity and availability model design | Physical capacity/availability semantics |
| Physical room/bed occupancy state ownership design | Per CD-014; Dormitory owns physical state |
| Persistence schema design | Schema design docs only — no migrations |
| Repository / read-model design | Design only — no repository code |
| Application contract design | Contract documents / interfaces-as-design |
| Event boundary design for Allocation and CheckIn/CheckOut | Per CD-014 / CD-015 / R7 |
| Authorization boundary design | Role/permission boundaries for Dormitory operations |
| Test strategy design | Unit/feature/architecture test plan — no test implementation required by this gate |

---

## 4. Required Planning Resolutions

Before any later Spec04 implementation authorization, the following must be explicitly resolved and recorded in Spec04 design/planning artifacts:

1. **Building/floor hierarchy** — catalog open planning item (`spec-catalog.md` spec04 row); resolve or carry as a closed documented decision.
2. **Physical occupancy state ownership details** — refine CD-014 ownership of physical room/bed markers vs assignment vs operational transitions.
3. **Allocation event integration boundary** — inbound assignment/release signals; Dormitory as consumer, not assignment authority.
4. **CheckIn/CheckOut transition boundary** — CD-015 operational transitions vs Dormitory physical state updates.
5. **UI-consumable read contracts** — if any, design-only (e.g. `DormitoryReadContract` surface); not feature-contracts, not UI implementation.
6. **Application contracts** needed before later implementation authorization — supplier/consumer ports documented and approved as design.

---

## 5. Excluded Scope

This artifact **excludes** and does **not** authorize:

| Excluded |
| -------- |
| Implementation code |
| Migrations |
| Repositories (as code) |
| Controllers |
| Application actions / commands / queries as code |
| Domain entities as code |
| Database tables (created/altered) |
| Seeders / factories |
| UI implementation |
| Livewire / Blade screens |
| Dormitory UI feature-contract |
| Workflow UI feature-contract |
| Allocation assignment ownership changes |
| Request approval workflow changes |
| Voucher implementation |
| Reporting dashboards |
| Notification UI |
| Audit UI |
| Any implementation outside Spec04 backend foundation design |

---

## 6. Dependency Rules

| Rule | Statement |
| ---- | --------- |
| Spec01 | Technical foundation remains a prerequisite for Spec04. |
| Spec07 | Allocation / CheckIn integration must respect **CD-014** and **CD-015**. |
| Allocation | May drive physical state updates via events; does **not** own physical room/bed state. |
| CheckIn/CheckOut | May transition physical occupancy operationally; **Dormitory** remains the physical state owner. |
| Workflow UI | Remains blocked; must **not** continue during this gate. |

---

## 7. Next Allowed Gate

**NEXT GATE:** Spec04 Backend Foundation Planning

---

## 8. Later Required Gate

Implementation may begin only after **all** of the following:

1. Backend foundation plan is created (or existing Spec04 plan is updated and approved for foundation scope).
2. Domain / persistence / application boundary design is approved.
3. Unresolved planning questions (including building/floor hierarchy and §4 items) are closed.
4. A **separate** Spec04 implementation authorization artifact is created.

Until that separate authorization exists, Spec04 implementation remains on hold.

---

## 9. Stop Boundary

This artifact authorizes **backend foundation design intake only**.

- It does **not** authorize implementation.
- It does **not** create implementation code.
- It does **not** create migrations.
- It does **not** create UI artifacts.
- It does **not** start Workflow UI, Dormitory UI, or any feature-contract.

**Forbidden under this activation:**

- Do not modify application code.
- Do not modify domain code.
- Do not create migrations.
- Do not create repositories.
- Do not create controllers.
- Do not create Livewire components.
- Do not create Blade views.
- Do not create UI governance artifacts.
- Do not create feature-contracts.
- Do not continue workflow-ui governance.
- Do not authorize implementation.
- Do not set STATUS to `IMPLEMENTATION_AUTHORIZED`.
- Do not authorize any spec other than Spec04 backend foundation design.
- Do not create any artifact other than this record (at activation time).

---

## Evidence Basis

| Source | Relevant point |
| ------ | -------------- |
| `.specify/docs/spec-catalog.md` | Spec04 **Planning Authorized**; implementation hold; owns dormitories, buildings, rooms, beds, capacity, availability; open planning: building/floor hierarchy |
| `.specify/docs/catalog-decisions.md` | **CD-014**, **CD-015** ownership split |
| `specs/004-accommodation-resource/spec.md` | Planning — spec authored; implementation not authorized; Dormitory physical catalog |
| `specs/004-accommodation-resource/plan.md` | Planning authorized; implementation not authorized until separate go-ahead |
| `specs/004-accommodation-resource/data-model.md` | Physical resource structures (design baseline) |
| `specs/004-accommodation-resource/tasks.md` | Task baseline (not authorized for execution by this artifact) |
| `specs/007-allocation-checkin/spec.md` / `plan.md` / `tasks.md` | Spec07 depends on Spec04; CD-014/CD-015 frozen |
| `.specify/docs/handoff/spec04-planning-authorization.md` | Prior planning-only authorization |
| `docs/ui/analysis/workflow/workflow-ui.feature-analysis.md` | Workflow UI blocked — no UI-consumable Application/Domain capability |

---

## References

- [`spec-catalog.md`](../spec-catalog.md) — spec04 row
- [`catalog-decisions.md`](../catalog-decisions.md) — CD-014, CD-015
- [`spec04-planning-authorization.md`](spec04-planning-authorization.md) — prior planning authorization
- [`../../specs/004-accommodation-resource/spec.md`](../../specs/004-accommodation-resource/spec.md)
- [`../../specs/004-accommodation-resource/plan.md`](../../specs/004-accommodation-resource/plan.md)
- [`../../specs/007-allocation-checkin/plan.md`](../../specs/007-allocation-checkin/plan.md)

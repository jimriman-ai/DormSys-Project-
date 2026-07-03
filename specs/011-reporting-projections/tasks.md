# Tasks: Reporting & Audit Consumption Evolution (spec11)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md)

**Branch**: `011-reporting-projections`

**Status**: **PLANNING BACKLOG ONLY** — **NON-EXECUTABLE**

```text
lifecycle_state:        ARCHITECTURE_CLARIFIED
execution_state:        NONE
executable:             false
authorization_required: separate governance record (not implied)
predecessor:            spec10 CLOSED / FROZEN
```

**WARNING**: Tasks in this file are **descriptive planning items**. They are **not** authorized for implementation. Prefix `P-` denotes planning work only. No checkpoints, waves, or PHPStan/Pint gates apply until a future Implementation Authorization record explicitly activates execution.

**Predecessor baseline**: [`spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md) — **do not mutate**

---

## Planning Backlog

### Phase P0 — Initialization (this step)

- [x] P-001 Record spec11 charter, problem frame, evolution areas, and successor rules in `spec.md`
- [x] P-002 Create planning-only `plan.md` with evolution tracks E-01–E-08
- [x] P-003 Create non-executable `tasks.md` (this file)
- [x] P-004 Create `checklists/requirements.md` for spec quality gate
- [x] P-005 Seed `decision-log.md` with architectural fork hypotheses
- [x] P-006 Cross-reference spec10 final closure as frozen baseline

### Phase P1 — Clarification & decisions

- [x] P-010 Resolve DL-01 projection storage strategy — **Hybrid T0/T1** ([`architecture-clarification.md` §3](./architecture-clarification.md))
- [x] P-011 Resolve DL-02 explorer vs API-first consumption priority — **Layered B → A** (§5.4)
- [x] P-012 Document archive visibility policy — **Role-gated `includeArchived`** (§4, DL-03)
- [ ] P-013 Define compliance stakeholder interview questions for E-04 KPIs
- [x] P-014 `/speckit-clarify` architecture definition pass — [`architecture-clarification.md`](./architecture-clarification.md)

### Phase P2 — Technical planning artifacts (future — not authorized)

- [ ] P-020 Draft `data-model.md` for Reporting read models (projection entities only)
- [ ] P-021 Draft `contracts/` for reporting read ports (read-only, CD-017)
- [ ] P-022 Draft `research.md` on projection refresh patterns
- [ ] P-023 Architecture boundary sketch: Reporting vs Audit vs upstream contexts
- [ ] P-024 Map `AuditEventType` vocabulary to reporting dimensions (read-only catalog)

### Phase P3 — Governance preparation (future — not authorized)

- [ ] P-030 Prepare `spec11-nomination-record.md` (when governance requests)
- [ ] P-031 Design approval package (when planning complete)
- [ ] P-032 Implementation authorization scope proposal (waves TBD — **not defined**)
- [ ] P-033 Verify spec10 non-mutation checklist for any spec11 artifact PR

### Phase P4 — Implementation tracks (placeholder — **HALT**)

*The following are **placeholders only**. No task IDs, waves, or file paths are authorized.*

| Track | Placeholder intent | Status |
| ----- | ------------------ | ------ |
| E-01 Reporting query façade | Not authorized | HALT |
| E-02 Projection engine | Not authorized | HALT |
| E-03 Audit explorer UI | Not authorized | HALT |
| E-04 Compliance dashboards | Not authorized | HALT |
| E-05 Performance tier | Not authorized | HALT |
| E-06 M4 producer coordination | Not authorized | HALT |
| E-07 Reporting boundary tests | Not authorized | HALT |
| E-08 Governance waves | Not authorized | HALT |

---

## FR_MAPPING (planning-level)

| Planning concern | Task IDs |
| ---------------- | -------- |
| Charter & boundaries | P-001, P-006 |
| Evolution documentation | P-002 |
| Decision resolution | P-010, P-011, P-012 |
| Technical artifacts | P-020–P-024 |
| Governance handoff | P-030–P-033 |

---

## READINESS_OUTPUT

| Field | Value |
| ----- | ----- |
| **ready_for_governance_review** | **no** — initialization only |
| **ready_for_implementation_authorization** | **no** |
| **executable** | **false** |
| **blockers** | Implementation authorization not issued |
| **predecessor_state** | spec10 CLOSED / FROZEN |

---

## NEXT_STATE

| Field | Value |
| ----- | ----- |
| **lifecycle_stage** | **PLANNING_INITIALIZATION** |
| **next_step** | Optional `/speckit-clarify` or governance nomination when ready |
| **execution_authorized** | **no** |
| **spec10_mutation** | **FORBIDDEN** |

---

**End of tasks. Non-executable planning backlog.**

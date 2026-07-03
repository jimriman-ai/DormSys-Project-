# Tasks: Reporting & Audit Consumption Evolution (spec11)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md)

**Branch**: `011-reporting-projections`

**Status**: **CLOSED** — authorized implementation **I-001–I-031 COMPLETE**; final closure verification **PASS** (2026-07-03)

```text
lifecycle_state:        CLOSED
immutable_status:       BASELINE (implementation frozen at closure)
execution_state:        NONE
executable:             false
authorization:          implementation-authorization-decision.md (2026-07-03) — scope delivered
predecessor:            spec10 CLOSED / FROZEN
closure_state:          CLOSED
closure_checkpoint:     spec11-implementation-closure
closure_blocker:        none
rollout_authorized:     false
active_execution_scope: none
```

**Authorization baseline**: [`implementation-authorization-decision.md`](./implementation-authorization-decision.md) — **APPROVED_WITH_CONDITIONS**

**Predecessor baseline**: [`spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md) — **do not mutate**

**Planning backlog** (P-*) remains descriptive; prefix `P-` denotes pre-implementation planning work. Prefix `I-` denotes authorized implementation execution items.

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

- [x] P-020 Draft `data-model.md` for Reporting read models (projection entities only)
- [x] P-021 Draft `contracts/` for reporting read ports (read-only, CD-017)
- [x] P-022 Draft `research.md` on projection refresh patterns
- [x] P-023 Architecture boundary sketch: Reporting vs Audit vs upstream contexts
- [x] P-024 Map `AuditEventType` vocabulary to reporting dimensions (read-only catalog)

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

### Implementation Progress — Authorized Execution

- [x] I-001 Create Reporting read contract and slice-1 ports
- [x] I-002 Implement T0 audit history source adapter via frozen AuditHistoryReadContract
- [x] I-003 Implement RU-01 entity timeline read flow
- [x] I-004 Implement RU-06 T0 drill-down scaffold
- [x] I-005 Register Reporting DI bindings
- [x] I-006 Add Reporting boundary, unit, and feature tests
- [x] I-007 Add entity timeline summary to RU-01 read model
- [x] I-008 Harden RU-01/RU-06 filter and pagination behavior with tests
- [x] I-009 Enforce BT-02 audit history read contract isolation in architecture tests
- [x] I-010 Implement T0 actor-scoped audit timeline read flow
- [x] I-011 Add T1 `reporting_projection_cursors` migration and `ProjectionCursorModel`
- [x] I-012 Add T1 `reporting_correlation_projection_entries` migration and `CorrelationProjectionEntryModel`
- [x] I-013 Add T1 `reporting_audit_window_aggregates` migration and `AuditWindowAggregateModel`
- [x] I-014 Add T1 `reporting_actor_activity_summaries` migration and `ActorActivitySummaryModel`
- [x] I-015 Add Reporting T1 projection schema feature tests
- [x] I-016 Add ProjectionCursorControlPort (PP-09) and cursor repository
- [x] I-017 Add ProjectionRefreshInputPort (PP-02) and T0 ingest page fetch on AuditHistorySourceReadPort
- [x] I-018 Implement projection refresh fetch-batch foundation service
- [x] I-019 Register refresh foundation DI bindings
- [x] I-020 Add refresh foundation unit and feature tests
- [x] I-021 Add projection ingest receipt migration for idempotent window/actor materialization
- [x] I-022 Implement family-specific projection materializers (correlation, window aggregate, actor activity)
- [x] I-023 Implement ProjectionRefreshRunnerService with transactional write-then-cursor-advance
- [x] I-024 Register materialization and runner DI bindings
- [x] I-025 Add materialization idempotency and cursor progression feature tests
- [x] I-026 Add projection query ports for correlation, window aggregate, and actor activity families
- [x] I-027 Implement projection-backed query adapters and read use-cases for RU-02, RU-03, RU-05
- [x] I-028 Wire RU-02 / RU-03 / RU-05 on ReportingReadContract with DI bindings
- [x] I-029 Add projection-backed read feature tests for authorized reporting flows
- [x] I-030 Run implementation closure verification gates (Reporting tests, PHPStan, Pint)
- [x] I-031 Implement RU-04 compliance export read assembly (`ComplianceExportReadModel`; PP-07 → PP-01)

### Implementation Closure Verification (2026-07-03)

| Area | Status | Evidence |
| ---- | ------ | -------- |
| T0 read baseline (RU-01, RU-06, actor timeline) | **PASS** | I-001–I-010; `EntityAuditTimelineReadTest`, `AggregateDrillDownReadTest`, `ActorAuditTimelineReadTest` |
| T1 schema (cursors, correlation, window, actor) | **PASS** | I-011–I-015; `ReportingProjectionSchemaTest` |
| T1 refresh foundation | **PASS** | I-016–I-020; `ProjectionRefreshInputTest`, `ProjectionCursorControlTest` |
| T1 materialization | **PASS** | I-021–I-025; `ProjectionRefreshMaterializationTest` |
| T1 read/query (RU-02, RU-03, RU-05) | **PASS** | I-026–I-029; `ProjectionBackedReadTest` |
| RU-04 compliance export | **PASS** | I-031; `ComplianceExportReadTest` |
| Boundary tests (BT-01, BT-02) | **PASS** | `ReportingBoundaryTest` |
| PHPStan level 8 (`app/Modules/Reporting/`) | **PASS** | 0 errors |
| Pint (Reporting paths) | **PASS** | clean |
| Reporting test suite | **PASS** | 50 tests, 168 assertions |

### Final Program Closure Verification (2026-07-03)

| Gate | Command / scope | Result |
| ---- | --------------- | ------ |
| Reporting test suite | `php artisan test tests/Feature/Modules/Reporting tests/Unit/Modules/Reporting tests/Architecture/ReportingBoundaryTest.php` | **PASS** — 50 tests, 168 assertions |
| PHPStan level 8 | `php vendor/bin/phpstan analyse app/Modules/Reporting` | **PASS** — 0 errors |
| Pint | `php vendor/bin/pint --test` (Reporting scope paths) | **PASS** |
| Authorized scope I-001–I-031 | tasks.md reconciliation + repository artifact check | **PASS** — all items `[x]`; RU-01–RU-06 on `ReportingReadContract` |
| Remaining authorized implementation | implementation-authorization-decision.md §4 | **PASS** — none |
| spec10 mutation | boundary tests + IA-C-05 | **PASS** — no `Audit\Infrastructure` imports |

**Closure decision:** **spec11 truthfully CLOSED** — authorized Reporting implementation scope delivered; no forward execution under this authorization.

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
| **implementation_batches_complete** | **I-001–I-031** |
| **remaining_authorized_scope** | **none** |
| **ready_for_implementation_closure** | **yes** — **CLOSED** (2026-07-03) |
| **rollout_authorized** | **no** |
| **predecessor_state** | spec10 CLOSED / FROZEN |

---

## NEXT_STATE

| Field | Value |
| ----- | ----- |
| **lifecycle_stage** | **CLOSED** |
| **next_step** | **None under spec11** — new scope requires separate governance authorization |
| **execution_authorized** | **no** |
| **spec10_mutation** | **FORBIDDEN** |
| **not_authorized_without_new_record** | Production rollout; E-03 explorer UI; E-04 KPI dashboards; M4 producer rollout; `SecurityAuditor` role; spec04+ implementation |

---

**End of tasks. spec11 authorized implementation scope I-001–I-031 CLOSED (2026-07-03). No forward execution permitted under current authorization.**

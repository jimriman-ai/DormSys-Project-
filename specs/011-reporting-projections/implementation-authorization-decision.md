# spec11 Implementation Authorization Decision Record

---

## 1. DECISION_HEADER

| Field | Value |
| ----- | ----- |
| **Spec identifier** | spec11 — Reporting & Audit Consumption Evolution (`reporting-projections`) |
| **Feature branch** | `011-reporting-projections` |
| **Decision type** | **IMPLEMENTATION_AUTHORIZATION_DECISION** |
| **Decision date** | 2026-07-03 |
| **Authority** | **Governance Review** — Product / Tech governance |
| **Request under review** | [`implementation-authorization-request.md`](./implementation-authorization-request.md) (`REQUESTED — NOT APPROVED` at submission) |
| **Predecessor** | spec10 — **CLOSED / FROZEN** (immutable) |
| **Design baseline** | spec11 Design Approval Decision Record (2026-07-03); `architecture-clarification.md`; `decision-log.md` DL-01–DL-03 |
| **Planning baseline** | [`p2-completion-record.md`](./p2-completion-record.md); P-020–P-024 artifacts under `p2/` |

---

## 2. DECISION_OUTCOME

**APPROVED_WITH_CONDITIONS**

---

## 3. DECISION_BASIS

- **Planning prerequisite satisfied.** P2 technical planning complete (P-020–P-024). [`p2-completion-record.md`](./p2-completion-record.md) recorded.
- **Request is bounded and aligned.** Submitted scope matches P2 data model, contracts, research, boundary sketch, and dimension catalog.
- **Design baseline binding.** `DESIGN_APPROVED_WITH_CONDITIONS` preserved; DL-01 Hybrid T0/T1/T2, DL-02 API/export before explorer UI, DL-03 archive visibility remain in force.
- **spec10 preservation verified.** Request and P2 artifacts consume audit via frozen `AuditHistoryReadContract` only; no spec10 mutation requested or authorized.
- **Boundary evidence sufficient.** `p2/boundary-sketch.md` and `p2/contracts/` define Reporting vs Audit separation adequate for implementation entry.
- **Deferred items explicitly scoped out.** E-03 explorer UI, E-04 KPI dashboards, M4 producers, `SecurityAuditor` role, and production rollout remain outside this authorization.

---

## 4. AUTHORIZED_SCOPE

Implementation is **authorized with conditions** for the following only:

| Area | Authorized work |
| ---- | --------------- |
| **Reporting module** | `app/Modules/Reporting/` — Application, Infrastructure, minimal Domain; Presentation limited to read API / export entry points (DL-02) |
| **T0 consumption** | `AuditHistorySourceReadPort` adapter (PP-01) via frozen `AuditHistoryReadContract` only; RU-01, RU-06 investigative and drill-down paths |
| **T1 projection storage** | Reporting-owned migrations in `database/migrations/modules/reporting/` — `CorrelationProjectionEntry`, `AuditWindowAggregate`, `ActorActivitySummary`, `ProjectionCursor`; optional `EntityTimelineCacheEntry`, `ComplianceExportSnapshot` metadata per `p2/data-model.md` |
| **T1 refresh** | Incremental refresh (primary) and window snapshot refresh (compliance) per `p2/research.md`; internal ports PP-02–PP-09 |
| **T2 read assembly** | Read models RU-01–RU-06 per `p2/contracts/reporting_read_contract.md`; provenance envelope on all T1/mixed responses |
| **Permissions** | Extend `audit.read` gate for reporting read surfaces (IA-C-01) |
| **Tests** | `tests/Unit/Modules/Reporting/`, `tests/Feature/Modules/Reporting/`, `tests/Architecture/ReportingBoundaryTest.php` — BT-01–BT-07 criteria |

**Suggested build order** (implementation guidance, not a separate authorization):

1. Module scaffold + T0 adapter + RU-01 / RU-06
2. T1 schema + refresh + RU-02 / RU-03 / RU-05
3. RU-04 compliance export + boundary and integration tests

**Maximum scope:** spec11 Reporting module implementation as defined in [`implementation-authorization-request.md`](./implementation-authorization-request.md) §5–§6 in-scope table.

---

## 5. CONDITIONS

| ID | Condition | Requirement |
| -- | --------- | ----------- |
| **IA-C-01** | Permission model | Use `audit.read` for reporting read ports; no separate `reporting.read` unless a future governance change records otherwise |
| **IA-C-02** | Security audience | `Administrator` primary for RU-05; `SecurityAuditor` role deferred |
| **IA-C-03** | E-04 exclusion | No compliance KPI dashboards or E-04 metric dimensions in this authorization |
| **IA-C-04** | spec10 contract freeze | No extension to `AuditHistoryQuery` or `AuditHistoryReadContract`; correlation via T1 only |
| **IA-C-05** | Audit Infrastructure boundary | No import of `App\Modules\Audit\Infrastructure\*`; no direct `audit_logs` access |
| **IA-C-06** | Archive visibility | DL-03 `includeArchived` gate before T0/T1 delegation; logical `archiveVisibilityTier` default |
| **IA-C-07** | Evidentiary reads | Compliance export line items T0-resolved; T0 wins on audit disputes |
| **IA-C-08** | PR gate | P-033 spec10 non-mutation checklist verified before any implementation PR merge |
| **IA-C-09** | Definition of Done | PHPStan level 8, Laravel Pint, Pest tests pass for Reporting module paths |

---

## 6. NON_AUTHORIZED_SCOPE

This decision explicitly does **NOT** authorize:

| Exclusion |
| --------- |
| spec10 mutation — `audit_logs`, Audit module code, contracts, producers, retention jobs |
| Production rollout, deployment, or operational scheduling |
| E-03 Operator Explorer UI (Livewire/Blade) |
| E-04 compliance KPI dashboards |
| `SecurityAuditor` role introduction |
| M4 audit producer rollout |
| Analytics / BI / OLAP expansion |
| spec04 Dormitory or other upstream domain implementation |
| Bridge activation or spec10 reopening |
| Implementation outside `app/Modules/Reporting/` and authorized test/migration paths |

---

## 7. AUTHORIZATION_POSTURE

**Normative schema fields (execution layer)**

```text
authorization-status: active
authorized-by: Governance Review
authorized-scope: spec11 Reporting module — §4 AUTHORIZED_SCOPE (I-001–I-031 delivered)
blocked-scope: —
blocking-reason: —
```

| Field | Value |
| ----- | ----- |
| **Implementation** | **AUTHORIZED** (subject to §5 conditions) |
| **Production rollout / deployment** | **NOT authorized** |
| **spec10** | **CLOSED / FROZEN** — unchanged |
| **P2 planning baseline** | **Binding** — no replanning without change request |

This decision authorizes **implementation work** in the repository. It does not authorize production rollout or runtime deployment.

---

## 8. NEXT_REQUIRED_PROJECT_STEP

Begin spec11 implementation within the authorized scope:

1. Scaffold `app/Modules/Reporting/` per P2 contracts and `p2/data-model.md`
2. Implement T0 adapter and entity timeline read path (RU-01, RU-06)
3. Proceed through T1 projection storage, refresh, and remaining read use-cases per suggested build order in §4

HALT on any work outside §4 authorized scope or §6 exclusions.

---

**End of Implementation Authorization Decision Record. Outcome: APPROVED_WITH_CONDITIONS. Implementation authorized for spec11 Reporting scope only. Rollout not authorized.**

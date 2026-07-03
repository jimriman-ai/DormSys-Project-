# spec11 Projection Refresh Research (Planning)

**Task**: P-022  
**Spec**: spec11 — Reporting & Audit Consumption Evolution  
**Status**: **Planning-only** — no implementation authority  
**Baseline**: `data-model.md`, P-021 contracts, `architecture-clarification.md`, `decision-log.md` DL-01–DL-03, P2 authorization decision (2026-07-03)

---

## 1. PURPOSE

This artifact records **planning-stage technical research** for spec11 projection and refresh strategy. It supports implementation authorization readiness by documenting evaluated options, accepted tradeoffs, and deferred questions — without prescribing SQL, jobs, code, or rollout.

**Scope**: Research conclusions only. Does not authorize implementation.

---

## 2. PROJECTION STRATEGY OPTIONS

### 2.1 Options compared

| Option | Description | Strengths | Weaknesses |
| ------ | ----------- | --------- | ---------- |
| **A — T0-only** | All reporting reads via frozen `AuditHistoryReadContract` | Maximum fidelity; simplest boundary; no projection store | No `correlationId` query; poor aggregate scale; high T0 load for dashboards |
| **B — T1-heavy** | Materialize all read shapes in Reporting-owned store | Fast dashboards and correlation search | Stale-cache risk; duplicate investigative truth; higher storage and refresh complexity |
| **C — Hybrid T0/T1/T2** | T0 investigative; T1 aggregates/indexes; T2 ephemeral assembly | Balances fidelity and scale; aligns with frozen spec10 limits | Two-tier operational model; provenance discipline required |

### 2.2 Selected direction

**Option C — Hybrid (DL-01)** is **confirmed**.

| Tier | Planning role |
| ---- | ------------- |
| **T0** | Entity/actor investigative reads; export line items; dispute authority |
| **T1** | Correlation index, window aggregates, actor summaries, optional entity cache, export manifests |
| **T2** | Request-time DTO assembly with mandatory provenance envelope |

**Rationale**: Frozen `AuditHistoryQuery` lacks `correlationId` filter (`architecture-clarification.md` §2.2, P2-C-07). Correlation and time-window workloads require Reporting-owned T1. Investigative and evidentiary paths must remain T0-authoritative (`data-model.md` R-DM-01, R-DM-12).

---

## 3. REFRESH STRATEGY ANALYSIS

### 3.1 Incremental refresh

| Aspect | Planning assessment |
| ------ | ------------------- |
| **Mechanism** | Advance `ProjectionCursor` via `lastSourceAuditLogId` / `lastOccurredAt` after paginated T0 ingest |
| **Best for** | Steady-state correlation index, rolling window aggregates, actor activity summaries |
| **Families** | `correlation`, `window_aggregate`, `actor_activity`, optional `entity_cache` |
| **Operational implication** | Bounded lag acceptable if `refreshedAt` exposed; consumers must not treat T1 as real-time truth |
| **Risk** | Cursor skew if out-of-order `occurredAt`; mitigated by idempotent `sourceAuditLogId` (R-DM-04) |

**Recommendation**: **Preferred default** for ongoing projection maintenance per `architecture-clarification.md` §3.3.

### 3.2 Window snapshot refresh

| Aspect | Planning assessment |
| ------ | ------------------- |
| **Mechanism** | Rebuild bounded time partitions (e.g., monthly compliance slices) into `AuditWindowAggregate` |
| **Best for** | Compliance period reports, export packages, immutable snapshot manifests |
| **Families** | `window_aggregate`, `ComplianceExportSnapshot` context |
| **Operational implication** | Higher batch cost; predictable reproducibility for governance windows |
| **Risk** | Duplicate work if overlapping with incremental path — partition boundaries must be explicit |

**Recommendation**: **Use for compliance-oriented windows** and export manifest generation; complements incremental for near-current ops dashboards.

### 3.3 Rebuild / recovery path

| Aspect | Planning assessment |
| ------ | ------------------- |
| **Mechanism** | Full cold rebuild from paginated T0 reads across all projection families |
| **Best for** | Disaster recovery, projection version migration, corruption remediation |
| **Trigger** | Governance-gated only — not normal operations (`data-model.md` R-DM-07) |
| **Operational implication** | High T0 read volume; must not impact spec10 contract behavior |
| **Risk** | Duration and resource spike — acceptable only as exceptional path |

**Recommendation**: **Document as recovery-only**; exclude from MVP operational design until Implementation Authorization.

### 3.4 Refresh mode summary

| Mode | Normal ops | Compliance | Recovery |
| ---- | ---------- | ---------- | -------- |
| Incremental | **Primary** | Supplemental | — |
| Window snapshot | Secondary | **Primary** | — |
| Full rebuild | **Excluded** | Rare re-baseline | **Only** |

---

## 4. ARCHIVE VISIBILITY STRATEGY

### 4.1 Logical tiering (selected — DL-03)

| Tier | `archiveVisibilityTier` | Default consumer | Planning rule |
| ---- | ----------------------- | ---------------- | ------------- |
| Active | `active_only` | Operational reporting | Exclude soft-archived source rows |
| Archive-inclusive | `include_archived` | Compliance / authorized security | Role-gated `includeArchived=true` |

**Gate location**: Reporting read contract and ports **before** T0/T1 delegation (`reporting_read_contract.md` §7, R-DM-08).

### 4.2 Physical partition evaluation (DL-03-C)

| Approach | Description | When |
| -------- | ----------- | ---- |
| **Logical column** (default) | Single projection store with `archiveVisibilityTier` column | **Current planning default** |
| **Physical partition** (optional) | Separate active vs archive-inclusive stores | Only if P-022+ performance evidence warrants (`data-model.md` §3.9, P2-C-05) |

**Recommendation**: **Retain logical tiering** for P2 completion. Defer physical partition decision to implementation planning unless volume modeling (post-P2) demonstrates partition benefit without policy change.

### 4.3 Cache and refresh isolation

- Separate `ProjectionCursor` per `archiveVisibilityTier` when both tiers materialized.
- `filterHash` and cache keys must include `includeArchived` (R-DM-09).
- No cross-tier row mixing in a single consumer response (RV-03).

---

## 5. CORRELATION INDEXING ANALYSIS

### 5.1 Why Reporting T1

| Factor | Conclusion |
| ------ | ---------- |
| Frozen `AuditHistoryQuery` | No `correlationId` filter — T0 cannot efficiently serve bundle queries |
| Scale | Security and compliance investigation requires indexed lookup by `correlationId` |
| Boundary | Indexing during T1 ingest avoids spec10 contract extension (P2-C-07) |
| Authority | T1 correlation rows reference `sourceAuditLogId`; line-item proof remains T0 |

### 5.2 Frozen spec10 limits

- Reporting **must not** add `correlationId` to `AuditHistoryQuery`.
- Correlation bundles at scale **must** use `CorrelationProjectionQueryPort` (T1).
- Low-volume MVP may compose multiple T0 queries — **not** the scaling path (`architecture-clarification.md` §2.2).

### 5.3 Null / absent correlation handling

| Case | Planning expectation |
| ---- | -------------------- |
| `correlationId` is null on source item | **Exclude** from correlation projection family ingest (R-DM-13) |
| Bundle query for unknown `correlationId` | Empty bundle with provenance — not an error |
| Verification of bundle member | Optional T0 fetch by `sourceAuditLogId` via `AuditHistorySourceReadPort` |
| Items with correlation in T0 but missing in T1 | Treat as projection lag; T0 verification resolves disputes |

---

## 6. AGGREGATE VS DRILL-DOWN SEPARATION

### 6.1 Why summaries belong in T1

| Reason | Detail |
| ------ | ------ |
| Performance | `AuditWindowAggregate` avoids repeated full T0 scans for dashboard cards |
| Dimensionality | Bucketed counts by `eventType`, `sourceContext`, `actorType`, `entityType` |
| Provenance | Aggregates carry `refreshedAt`, `projectionVersion` — consumers know derivation lag |
| Boundary | Aggregates are **derived** — not evidentiary alone |

### 6.2 Why line-items remain T0-authoritative

| Reason | Detail |
| ------ | ------ |
| AP-06 / audit disputes | Immutable `audit_logs` accessed only via frozen contract |
| Export evidence | `ComplianceExportReadModel` line items **must** be T0-resolved (EX-01) |
| Drill-down | `AggregateDrillDownPort` translates bucket context to T0 entity/time filters |
| Rule | Aggregates alone insufficient for compliance export (R-DM-12) |

### 6.3 Separation enforcement (planning)

```text
T1 aggregate response → drill-down handle → AggregateDrillDownPort → T0 line items
```

Presentation and export consumers **must not** skip T0 resolution for evidentiary packages.

---

## 7. RISK / TRADEOFF SUMMARY

### 7.1 Planning-stage technical risks

| ID | Risk | Mitigation (planning) |
| -- | ---- | --------------------- |
| **RK-01** | T1 staleness misread as authoritative | Mandatory provenance envelope; T0 wins on dispute |
| **RK-02** | Projection lag under incremental-only ops | Expose `refreshedAt`; compliance uses window snapshots |
| **RK-03** | Incomplete audit coverage (M1 only) | Aggregates tolerate absent M4 `sourceContext` values (`data-model.md` §4.4) |
| **RK-04** | Archive visibility leakage across tiers | Tier column + gate before delegation; separate cursors |
| **RK-05** | Correlation index gaps | T0 verification path; accept lag as non-authoritative |
| **RK-06** | T0 pagination cap (200/page) on large exports | Multi-page T0 resolution planned at implementation — export may be batch-oriented |

### 7.2 Accepted tradeoffs

| Tradeoff | Acceptance |
| -------- | ------------ |
| Hybrid complexity vs T0-only simplicity | **Accepted** — required by frozen contract and scale |
| Projection lag vs real-time aggregates | **Accepted** — disclosed via provenance |
| Optional entity T1 cache vs always T0 | **Accepted** — T0 remains primary for investigation |
| Logical archive tier vs physical partition | **Accepted** — logical default; partition deferred |
| E-04 KPI dimensions deferred | **Accepted** per P2-C-03 |

### 7.3 Deferred implementation questions

| ID | Question | Target |
| -- | -------- | ------ |
| **DQ-01** | `topEventTypes` cardinality cap and storage shape | Implementation planning |
| **DQ-02** | Entity cache freshness threshold vs T0 fallback | Implementation planning |
| **DQ-03** | Export file format and storage for `ComplianceExportSnapshot` | Post-P2 / Implementation Authorization (ON-04) |
| **DQ-04** | PostgreSQL materialized view vs application-managed T1 tables | Implementation planning (referenced in `plan.md` E-05) |
| **DQ-05** | DL-03-C physical partition adoption | Volume evidence at implementation |

---

## 8. NON-GOALS

This research artifact does **NOT** define:

| Exclusion |
| --------- |
| SQL, DDL, or index design |
| Job, queue, or scheduler configuration |
| PHP classes, repositories, or adapters |
| API routes or transport |
| Rollout waves or checkpoints |
| Implementation Authorization |
| spec10 mutation or contract extension |

---

**End of P-022 planning research. Options evaluated. Hybrid T0/T1/T2 confirmed. No implementation authorized.**

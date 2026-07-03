# Projection Ports — Internal Read Boundaries (Planning)

**Task**: P-021  
**Spec**: spec11 — Reporting & Audit Consumption Evolution  
**Status**: **Planning-only** — no implementation authority  
**Owner**: spec11 Reporting module (Application + Infrastructure layers — future)  
**Baseline**: `architecture-clarification.md` §3; `data-model.md` §3–§4; `audit_read_contract.md`; `reporting_read_contract.md`

---

## 1. PURPOSE

Define **planning-level internal ports** that separate:

1. **Source reads** (frozen T0 audit contract)
2. **Projection materialization** (T1 ingest and store)
3. **Consumer-facing reads** (T2 assembly via Reporting read contract)

Ports are **conceptual boundaries** — not implementation classes, not HTTP endpoints, not SQL.

---

## 2. PORT LAYER MODEL

```text
┌─────────────────────────────────────────────────────────────┐
│  Consumer-facing: Reporting Read Contract (see sibling doc) │
└────────────────────────────┬────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────┐
│  Application — Query / assembly ports (T2)                │
│  EntityTimeline · CorrelationBundle · WindowSummary ·       │
│  ComplianceExport · SecurityAuditEvent                      │
└───────┬──────────────────────────────────────┬────────────┘
        │                                      │
┌───────▼──────────────┐            ┌──────────▼─────────────┐
│  T0 Source Read Port │            │  T1 Projection Query   │
│  (AuditHistory adapter)│          │  Ports                 │
└───────┬──────────────┘            └──────────┬─────────────┘
        │                                      │
┌───────▼──────────────┐            ┌──────────▼─────────────┐
│  spec10 frozen       │            │  T1 Projection Store   │
│  AuditHistoryRead    │            │  (materialized — future) │
│  Contract            │            └──────────▲─────────────┘
└──────────────────────┘                       │
                                    ┌──────────┴─────────────┐
                                    │  Projection Refresh    │
                                    │  Input Port            │
                                    └──────────▲─────────────┘
                                               │
                                    (reads T0 paginated — future)
```

---

## 3. PORT INVENTORY

| Port ID | Name | Layer | Direction | Tier |
| ------- | ---- | ----- | --------- | ---- |
| **PP-01** | `AuditHistorySourceReadPort` | Application adapter | Inbound from Reporting | T0 |
| **PP-02** | `ProjectionRefreshInputPort` | Application / Infrastructure | Internal | T0 → T1 |
| **PP-03** | `CorrelationProjectionQueryPort` | Application | Internal | T1 |
| **PP-04** | `WindowAggregateQueryPort` | Application | Internal | T1 |
| **PP-05** | `EntityTimelineCacheQueryPort` | Application | Internal | T1 (optional) |
| **PP-06** | `ActorActivityQueryPort` | Application | Internal | T1 |
| **PP-07** | `ExportSnapshotAssemblyPort` | Application | Internal | T1 + T0 |
| **PP-08** | `AggregateDrillDownPort` | Application | Internal | T1 → T0 |
| **PP-09** | `ProjectionCursorControlPort` | Infrastructure | Internal | T1 meta |

Consumer-facing ports map to **Reporting read contract** use-cases (RU-01–RU-06) and compose the internal ports below.

---

## 4. PP-01 — AUDIT HISTORY SOURCE READ PORT

### Boundary

Frozen adapter to spec10 `AuditHistoryReadContract`. **Only** permitted Audit module import surface at Application layer.

### Responsibilities

| Responsibility | Detail |
| -------------- | ------ |
| Translate Reporting query concepts into frozen `AuditHistoryQuery` | No new filters |
| Paginate through contract results | Max page size per spec10 |
| Pass through `includeArchived` after Reporting visibility gate | DL-03 |
| Return `AuditHistoryItem` DTOs | Never Infrastructure models |

### Forbidden

| Action | Verdict |
| ------ | ------- |
| Import `App\Modules\Audit\Infrastructure\*` | **Forbidden** |
| Add `correlationId` filter to query | **Forbidden** (P2-C-07) |
| Write to audit store | **Forbidden** |

### Consumers

- `ProjectionRefreshInputPort` (ingest)
- `EntityTimeline` assembly (T0 primary)
- `AggregateDrillDownPort`
- `ExportSnapshotAssemblyPort` (line items)

---

## 5. PP-02 — PROJECTION REFRESH INPUT PORT

### Boundary

Internal boundary between **T0 source reads** and **T1 materialization**. Defines what projection refresh may pull — not how refresh is scheduled or executed.

### Input

| Input | Source |
| ----- | ------ |
| Paginated `AuditHistoryItem` stream | `AuditHistorySourceReadPort` |
| `ProjectionCursor` state | `ProjectionCursorControlPort` |
| Target `projectionFamily` | `correlation`, `window_aggregate`, `entity_cache`, `actor_activity` |
| `archiveVisibilityTier` | `active_only` \| `include_archived` |

### Output (planning)

| Output | Target T1 structure |
| ------ | ------------------- |
| Correlation index rows | `CorrelationProjectionEntry` |
| Window aggregate rows | `AuditWindowAggregate` |
| Entity cache rows | `EntityTimelineCacheEntry` (optional) |
| Actor summary rows | `ActorActivitySummary` |
| Updated cursor | `ProjectionCursor` |

### Rules

| Rule | Expectation |
| ---- | ----------- |
| **PR-01** | Ingest uses T0 only — never Infrastructure bypass |
| **PR-02** | `sourceAuditLogId` idempotent per projection family (R-DM-04) |
| **PR-03** | Skip items with null `correlationId` for correlation family (R-DM-13) |
| **PR-04** | Never write to `audit_logs` |
| **PR-05** | Refresh mechanics (jobs, schedules) — **out of scope** (P-022) |

---

## 6. PP-03 — CORRELATION PROJECTION QUERY PORT

### Boundary

T1 read access for correlation bundles. Serves `CorrelationAuditBundleReadModel`.

### Query concepts

| Input | Behavior |
| ----- | -------- |
| `correlationId` | Primary lookup key |
| `includeArchived` | Select `archiveVisibilityTier` partition |
| Optional `eventTypes` | Post-filter on T1 rows |

### Response

| Output | Source |
| ------ | ------ |
| Bundle items | `CorrelationProjectionEntry` rows |
| Aggregate companion | Counts, span, histogram |
| Provenance | `refreshedAt`, `projectionVersion` |

### Drill-down

May delegate line verification to `AuditHistorySourceReadPort` by `sourceAuditLogId`.

### Separation

Does **not** query spec10 by `correlationId` filter — T1 only at scale.

---

## 7. PP-04 — WINDOW AGGREGATE QUERY PORT

### Boundary

T1 read access for time-window summaries. Serves `AuditWindowSummaryReadModel`.

### Query concepts

| Input | Behavior |
| ----- | -------- |
| `windowStart`, `windowEnd`, `granularity` | Bucket selection |
| Dimension filters | `eventType`, `sourceContext`, `actorType`, `entityType` |
| `includeArchived` | Tier selection |

### Response

| Output | Source |
| ------ | ------ |
| Bucket metrics | `AuditWindowAggregate` |
| `topEventTypes` | From aggregate row |
| Drill-down handles | References for `AggregateDrillDownPort` |

### Aggregate / drill-down boundary

Aggregates returned here are **not** evidentiary alone. Drill-down to T0 required for line-item proof (R-DM-12).

---

## 8. PP-05 — ENTITY TIMELINE CACHE QUERY PORT

### Boundary

Optional T1 cache for entity timelines. **T0 remains authoritative** for investigative reads.

### Query concepts

| Input | Behavior |
| ----- | -------- |
| `entityType`, `entityId` | Cache key |
| `occurredFrom`, `occurredTo` | Optional bounds |
| `includeArchived` | Tier selection |

### Fallback

On cache miss or freshness policy — delegate to `AuditHistorySourceReadPort` (T0 primary).

---

## 9. PP-06 — ACTOR ACTIVITY QUERY PORT

### Boundary

T1 read access for actor-centric security and operational views. Serves `SecurityAuditEventReadModel` (partial).

### Query concepts

| Input | Behavior |
| ----- | -------- |
| `actorType`, `actorId` | Primary key |
| `windowStart`, `windowEnd`, `granularity` | Activity window |
| `includeArchived` | Tier selection |

### Response

| Output | Source |
| ------ | ------ |
| Activity summary | `ActorActivitySummary` |
| Supporting correlation refs | Cross-link to PP-03 |

### Audience

`Administrator` primary (P2-C-02).

---

## 10. PP-07 — EXPORT SNAPSHOT ASSEMBLY PORT

### Boundary

Assembles compliance export packages — T1 manifest + T0 line items.

### Responsibilities

| Step | Port used |
| ---- | --------- |
| Build or fetch `ComplianceExportSnapshot` manifest | T1 store (future) |
| Resolve `lineItemSourceAuditLogIds` via T0 | `AuditHistorySourceReadPort` |
| Attach provenance envelope | `sourceTier: mixed` |
| Apply compliance `includeArchived` gate | Before all reads |

### Rules

| Rule | Expectation |
| ---- | ----------- |
| **EX-01** | Export line items **must** be T0-resolved |
| **EX-02** | `filterManifest` immutable once snapshot created |
| **EX-03** | File encoding/format — deferred (ON-04) |

---

## 11. PP-08 — AGGREGATE DRILL-DOWN PORT

### Boundary

Explicit boundary between **T1 aggregate views** and **T0 line-item reads**.

### Responsibilities

| Responsibility | Detail |
| -------------- | ------ |
| Accept drill-down request from window summary or dashboard | Bucket + dimension context |
| Translate to frozen T0 entity/time filters | No new query dimensions |
| Return T0 items with `sourceTier: T0` | Authoritative line list |

### Separation

Prevents presentation or export consumers from treating aggregates as sole evidence source.

---

## 12. PP-09 — PROJECTION CURSOR CONTROL PORT

### Boundary

Infrastructure metadata port for `ProjectionCursor` — not consumer-facing.

### Responsibilities

| Operation | Planning intent |
| --------- | --------------- |
| Read cursor by `projectionFamily` + `archiveVisibilityTier` | Refresh resume |
| Advance cursor on successful batch | R-DM-05 |
| Expose `refreshedAt`, `projectionVersion` | Provenance for query ports |

### Forbidden

Exposure of cursor mechanics to Presentation layer.

---

## 13. CONSUMER-FACING PORT MAPPING

| Reporting read use-case | Internal ports composed |
| ----------------------- | ----------------------- |
| **RU-01** Entity timeline | PP-05 (optional) → PP-01 fallback |
| **RU-02** Correlation bundle | PP-03 → PP-01 (verify) |
| **RU-03** Window summary | PP-04 → PP-08 (drill-down) |
| **RU-04** Compliance export | PP-07 → PP-01 |
| **RU-05** Security actor view | PP-06, PP-03 → PP-01 (verify) |
| **RU-06** Drill-down | PP-08 → PP-01 |

---

## 14. CROSS-CUTTING PORT RULES

| Rule ID | Rule |
| ------- | ---- |
| **XP-01** | All ports are **read-only** toward upstream domain stores |
| **XP-02** | `includeArchived` enforced before PP-01 and T1 tier selection |
| **XP-03** | T1 ports must return provenance fields for reporting read contract envelope |
| **XP-04** | No port may import Audit Infrastructure |
| **XP-05** | No port may write `audit_logs` |
| **XP-06** | Permission: `audit.read` required at reporting read contract — assumed by all composed ports |
| **XP-07** | Non-audit upstream reads (future cross-context) use **separate ports** — not merged into PP-02–PP-09 (R-DM-16) |

---

## 15. NON-GOALS

This projection ports planning artifact does **NOT** define:

| Exclusion |
| --------- |
| PHP interface or class names |
| Dependency injection wiring |
| SQL, migrations, or table names |
| Queue, job, or scheduler configuration |
| HTTP routing or API controllers |
| Refresh algorithm details (see P-022) |
| Architecture test implementation (see P-023) |
| Implementation Authorization |

---

**End of projection ports planning artifact. Internal boundaries only. No implementation authorized.**

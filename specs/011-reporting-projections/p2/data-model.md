# spec11 Reporting Projection Data Model (Planning)

**Task**: P-020  
**Spec**: spec11 — Reporting & Audit Consumption Evolution  
**Status**: **Planning-only** — no implementation, schema, or runtime authority  
**Baseline**: `architecture-clarification.md`, `decision-log.md` DL-01–DL-03, P2 authorization decision (2026-07-03)  
**Predecessor**: spec10 — **CLOSED / FROZEN** (`AuditHistoryReadContract`, append-only `audit_logs`)

---

## 1. PURPOSE

### Role in spec11

This document defines the **planned conceptual data model** for Reporting-owned **Tier 1 (T1) materialized projections** and related **Tier 2 (T2) ephemeral read shapes** that support spec11 consumption goals:

- Entity-centric investigation (operational reporting)
- Correlation-based security and compliance bundles
- Time-window summaries and dashboard aggregates
- Export-oriented compliance snapshots

The model implements **DL-01 Hybrid** projection storage: investigative fidelity remains on **Tier 0 (T0)** via frozen `AuditHistoryReadContract`; read-heavy shapes materialize in **Reporting-owned T1** stores; request-time assembly uses **T2** without persistence.

### Relationship to reporting projections

| Tier | Planning role in this document |
| ---- | ------------------------------ |
| **T0** | Referenced as authoritative source — **not** defined as Reporting projection entities |
| **T1** | **Primary subject** — planned projection records, indexes, aggregates, cursors |
| **T2** | Ephemeral read-model compositions documented for completeness — **no stored structures** |

Projections are **derived caches**. They are not a second system of record. On dispute, **T0 contract results win** over T1.

### Planning-only status

This artifact is authorized under **P-020** only. It does **not** authorize migrations, SQL, jobs, modules, APIs, or rollout. Implementation remains blocked until separate Implementation Authorization.

---

## 2. MODEL BOUNDARY

### Inside the reporting projection model

| Category | Included |
| -------- | -------- |
| T1 projection entities | Correlation index, window aggregates, optional entity timeline cache, actor summaries, export snapshot metadata |
| Projection control | Refresh cursors, projection version, refresh timestamps |
| Read-oriented identifiers | Stable references to source audit items (`sourceAuditLogId`) without owning audit storage |
| Reporting dimensions | `eventType`, `sourceContext`, `entityType`, `actorType`, time buckets, `correlationId` |
| Visibility planning fields | `archiveVisibilityTier`, `includeArchived` semantics at query boundary (DL-03) |
| Provenance metadata | `sourceTier`, `refreshedAt`, `projectionVersion`, `filterHash` on read shapes |

### Outside the boundary

| Category | Owner / disposition |
| -------- | ------------------- |
| `audit_logs` table and Audit Infrastructure models | spec10 — **unchanged** |
| `AuditHistoryReadContract`, `AuditHistoryQuery`, `AuditHistoryItem` | spec10 Application — **frozen** |
| Audit recording, retention, soft-archive jobs | spec10 — **unchanged** |
| Domain module business entities (Request, Allocation, Voucher, etc.) | Respective bounded contexts — Reporting reads via future ports only, not in this audit projection model |
| Presentation UI state | Presentation layer — not persisted in T1 |
| E-04 compliance KPI dimensions | **Deferred** per P2-C-03 — not in initial projection model |
| `SecurityAuditor` role | **Deferred** per P2-C-02 — `Administrator` is primary security audience in planning |

### spec10 source domain confirmation

- No changes to spec10 schema, contracts, producers, or closure state are implied by this model.
- Correlation indexing is **Reporting T1 only** because frozen `AuditHistoryQuery` has no `correlationId` filter (`architecture-clarification.md` §2.2, P2-C-07).
- Reporting must not write to `audit_logs` (AP-06, CD-017).

---

## 3. PLANNED DATA STRUCTURES

### 3.1 Conceptual source item (T0 — reference only)

Reporting projections are built from **contract-sourced audit items** (`AuditHistoryItem` family). Planned source attributes consumed during projection refresh:

| Attribute | Reporting use |
| --------- | ------------- |
| `id` (audit log UUID) | `sourceAuditLogId` — idempotent projection key |
| `occurredAt` (UTC) | Ordering, window bucketing, span calculation |
| `entityType`, `entityId` | Entity-centric views, participant lists |
| `actorType`, `actorId` | Actor summaries, security views |
| `eventType` | Aggregation dimensions, histograms |
| `sourceContext` | Producer/module dimension (M1 today; M4 future) |
| `correlationId` | Correlation index (nullable) |
| `archivedAt` / archive flag | DL-03 visibility partitioning |
| Payload summary fields | Display/drill-down context — opaque at projection layer; line-item truth from T0 |

*T0 items are not duplicated as authoritative rows in this model unless explicitly marked as T1 cache with provenance.*

### 3.2 `ProjectionCursor` (T1 control)

Tracks incremental refresh position per projection family.

| Field | Type (conceptual) | Purpose |
| ----- | ----------------- | ------- |
| `projectionFamily` | enum | e.g. `correlation`, `window_aggregate`, `entity_cache` |
| `lastSourceAuditLogId` | UUID | High-water mark for incremental ingest |
| `lastOccurredAt` | datetime (UTC) | Secondary cursor for time-ordered replay |
| `projectionVersion` | string | Schema/logic version of derived data |
| `refreshedAt` | datetime (UTC) | Last successful refresh completion |
| `refreshMode` | enum | `incremental`, `window_snapshot`, `full_rebuild` |
| `archiveVisibilityTier` | enum | `active_only`, `include_archived` — separate cursor per tier if DL-03-C partition adopted |
| `status` | enum | `idle`, `running`, `failed` |
| `lastError` | string (nullable) | Planning placeholder for operational diagnostics |

### 3.3 `CorrelationProjectionEntry` (T1 — correlation index)

Supports `CorrelationAuditBundleReadModel` (`architecture-clarification.md` §2.2).

| Field | Type (conceptual) | Purpose |
| ----- | ----------------- | ------- |
| `correlationId` | string (PK component) | Bundle key |
| `sourceAuditLogId` | UUID (PK component) | Reference to authoritative audit row |
| `occurredAt` | datetime (UTC) | Ordering within bundle |
| `entityType`, `entityId` | string, UUID | Cross-entity participant extraction |
| `actorType`, `actorId` | string, UUID | Security participant extraction |
| `eventType` | string | Bundle filtering and histogram |
| `sourceContext` | string | Producer dimension |
| `archiveVisibilityTier` | enum | `active_only` \| `include_archived` |
| `ingestedAt` | datetime (UTC) | Projection write audit |

**Planned aggregate companion** (optional denormalized row per correlation):

| Field | Purpose |
| ----- | ------- |
| `correlationId` | PK |
| `itemCount` | Bundle size |
| `occurredAtMin`, `occurredAtMax` | Span |
| `distinctEntityCount`, `distinctActorCount` | Summary |
| `eventTypeHistogram` | map `eventType` → count |
| `refreshedAt`, `projectionVersion` | Provenance |

### 3.4 `AuditWindowAggregate` (T1 — time-window summary)

Supports `AuditWindowSummaryReadModel` (`architecture-clarification.md` §2.3).

| Field | Type (conceptual) | Purpose |
| ----- | ----------------- | ------- |
| `windowStart`, `windowEnd` | datetime (UTC) | Bucket boundaries |
| `granularity` | enum | `hour`, `day`, `week`, `month` |
| `dimensionSet` | composite key | See dimension columns below |
| `eventType` | string (nullable) | Dimension — null = all types in bucket |
| `sourceContext` | string (nullable) | Producer dimension |
| `actorType` | string (nullable) | Actor class dimension |
| `entityType` | string (nullable) | Subject class dimension |
| `archiveVisibilityTier` | enum | DL-03 partition |
| `eventCount` | integer | Primary metric |
| `distinctEntityCount` | integer | Operational/compliance metric |
| `distinctActorCount` | integer | Security metric |
| `topEventTypes` | ordered list | Planning cap e.g. top 10 — detail in P-022 |
| `refreshedAt` | datetime (UTC) | Provenance |
| `projectionVersion` | string | Provenance |

**Presentation note**: Jalali calendar labels are applied at presentation layer; buckets stored in UTC.

### 3.5 `EntityTimelineCacheEntry` (T1 — optional)

Optional materialized cache when entity timelines are read frequently. **T0 remains primary** for investigative fidelity.

| Field | Purpose |
| ----- | ------- |
| `entityType`, `entityId` | PK components |
| `sourceAuditLogId` | Reference to audit item |
| `occurredAt` | Descending timeline order |
| `eventType`, `actorId`, `correlationId` | Summary columns for list rendering |
| `archiveVisibilityTier` | DL-03 |
| `refreshedAt` | Cache freshness |

### 3.6 `ActorActivitySummary` (T1 — security/operational)

Supports security and operational actor-centric lists (`architecture-clarification.md` §4.3).

| Field | Purpose |
| ----- | ------- |
| `actorType`, `actorId` | PK components |
| `windowStart`, `windowEnd`, `granularity` | Activity window |
| `eventCount` | Volume |
| `distinctEventTypes` | set |
| `distinctEntitiesTouched` | integer |
| `archiveVisibilityTier` | DL-03 |
| `refreshedAt`, `projectionVersion` | Provenance |

### 3.7 `ComplianceExportSnapshot` (T1 — export manifest)

Planning structure for compliance export packages — not file storage definition.

| Field | Purpose |
| ----- | ------- |
| `snapshotId` | UUID |
| `generatedAt` | UTC timestamp |
| `requestedByActorId` | UUID — planning reference to identity |
| `filterManifest` | structured filter declaration (entity, window, event types, `includeArchived`) |
| `projectionVersion` | T1 version used |
| `sourceTierMix` | e.g. `T1_summary + T0_line_items` |
| `lineItemSourceAuditLogIds` | ordered UUID list — resolved via T0 at export time |
| `archiveVisibilityTier` | Must match authorized role (DL-03) |

### 3.8 T2 ephemeral read models (non-persisted)

| Read model | Composition |
| ---------- | ----------- |
| `EntityAuditTimelineReadModel` | T0 query result and/or T1 cache hit + provenance metadata |
| `CorrelationAuditBundleReadModel` | T1 correlation entries + optional T0 line verification |
| `AuditWindowSummaryReadModel` | T1 aggregates + drill-down handles to T0 |
| `ComplianceExportReadModel` | T1 snapshot manifest + T0 line items |
| `SecurityAuditEventReadModel` | T1 actor/correlation emphasis + T0 verification |

**T2 fields common to all responses**:

| Field | Purpose |
| ----- | ------- |
| `sourceTier` | `T0`, `T1`, or `mixed` |
| `refreshedAt` | Nullable for pure T0 |
| `projectionVersion` | Nullable for pure T0 |
| `includeArchived` | Effective visibility flag applied at port boundary |
| `filterHash` | Stable hash of query filters for cache key semantics |

### 3.9 DL-03-C archive-tier partition (planning evaluation)

**Default**: single projection store with `archiveVisibilityTier` column separating active vs archived-inclusive materializations.

**Optional future partition** (evaluate in P-022 only if performance evidence requires):

| Partition | Contents |
| --------- | -------- |
| `reporting_projections_active` | Rows where source items are not soft-archived |
| `reporting_projections_archive` | Rows including soft-archived source items |

Policy unchanged: operational queries default `active_only`; compliance may request `include_archived` per role gate.

---

## 4. SOURCE-TO-PROJECTION MAPPING

### 4.1 Ingest path (conceptual)

```text
AuditHistoryReadContract (paginated query, frozen filters)
        ↓
Projection refresh process (future — not defined here)
        ↓
T1 stores: CorrelationProjectionEntry, AuditWindowAggregate, optional caches
        ↓
Reporting Read Ports (P-021)
        ↓
T2 read models → Presentation / export
```

### 4.2 Field mapping table

| Source concept (T0 item) | T1 projection target | Mapping rule |
| ------------------------ | -------------------- | ------------ |
| `id` | `sourceAuditLogId` | 1:1 reference |
| `correlationId` | `CorrelationProjectionEntry.correlationId` | Index when non-null |
| `occurredAt` | Window bucket selection | Floor to `granularity` boundary in UTC |
| `eventType` | `AuditWindowAggregate.eventType` dimension | Group count |
| `sourceContext` | Dimension on aggregates | Group count |
| `entityType`, `entityId` | Correlation participants; optional entity cache | Denormalize per indexed row |
| `actorType`, `actorId` | Actor summaries; correlation participants | Denormalize per indexed row |
| archive state | `archiveVisibilityTier` | `active_only` row always; `include_archived` row when source archived or tier includes archive |

### 4.3 Read shape to storage mapping

| Read shape | Primary T1 structure | T0 fallback |
| ---------- | -------------------- | ----------- |
| Entity-centric timeline | Optional `EntityTimelineCacheEntry` | **Primary** — contract entity filters |
| Correlation bundle | `CorrelationProjectionEntry` + aggregate companion | Line-item verification only |
| Time-window summary | `AuditWindowAggregate` | Raw drill-down |
| Compliance export | `ComplianceExportSnapshot` + T0 line items | Line items authoritative |
| Security actor view | `ActorActivitySummary` + correlation index | Event verification |

### 4.4 Producer coverage (planning)

| `sourceContext` maturity | Projection expectation |
| ------------------------ | ---------------------- |
| M1 producers (Identity, Voucher) | Initial aggregates reflect available event types only |
| M4 producers (deferred) | Projections must tolerate absence — no backfill assumption in this model |

*Detailed `AuditEventType` vocabulary mapping is **P-024** scope.*

---

## 5. CONSISTENCY AND INTEGRITY RULES

### 5.1 Authority and dispute resolution

| Rule | Expectation |
| ---- | ----------- |
| **R-DM-01** | T0 `AuditHistoryReadContract` is authoritative for audit disputes and line-item evidence |
| **R-DM-02** | T1 rows are derived; stale T1 must not override T0 |
| **R-DM-03** | Projections never mutate `audit_logs` |

### 5.2 Idempotency and completeness

| Rule | Expectation |
| ---- | ----------- |
| **R-DM-04** | `sourceAuditLogId` is unique per projection family entry — replays are idempotent |
| **R-DM-05** | Incremental refresh advances `ProjectionCursor` only after successful batch |
| **R-DM-06** | Completeness is **best-effort derived** — projection lag is explicit via `refreshedAt` |
| **R-DM-07** | Full rebuild is disaster/recovery path only — governance-gated at implementation time |

### 5.3 Visibility integrity (DL-03)

| Rule | Expectation |
| ---- | ----------- |
| **R-DM-08** | `includeArchived` enforced at Reporting port **before** T0/T1 delegation |
| **R-DM-09** | Cache keys and materialized rows include `archiveVisibilityTier` — no cross-tier leakage |
| **R-DM-10** | Operational reporting defaults exclude archived source items |

### 5.4 Read-model integrity for consumers

| Rule | Expectation |
| ---- | ----------- |
| **R-DM-11** | Every T1-served response exposes `sourceTier`, `refreshedAt`, `projectionVersion` where applicable |
| **R-DM-12** | Drill-down from aggregates resolves to T0 line items — aggregates alone are insufficient for evidentiary export |
| **R-DM-13** | Correlation bundles without `correlationId` on source items are excluded from correlation index |
| **R-DM-14** | Permission gate: extend `audit.read` (`Administrator`, `DormMgr`, `HRMgr`) per P2-C-01 |

### 5.5 Cross-context boundary (CD-017)

| Rule | Expectation |
| ---- | ----------- |
| **R-DM-15** | This model covers **audit history projections** only |
| **R-DM-16** | Future non-audit reporting dimensions require separate upstream read contracts — not merged into audit projection tables |

---

## 6. NON-GOALS

This `data-model.md` explicitly does **NOT** define:

| Exclusion |
| --------- |
| Schema migration or DDL |
| SQL, indexes, or storage engine choices |
| Runtime jobs, schedulers, or queue configuration |
| Background execution or refresh implementation |
| Implementation classes, repositories, or Eloquent models |
| API or port method signatures (see P-021 `contracts/`) |
| Rollout steps, waves, or checkpoints |
| spec10 `AuditHistoryQuery` extensions |
| Retention, purge, or archive policy changes |
| Livewire, Blade, or UI components |
| E-04 compliance KPI metrics (deferred per P2-C-03) |

---

## 7. OPEN PLANNING NOTES

| ID | Note | Target task |
| -- | ---- | ----------- |
| **ON-01** | Incremental vs window-snapshot refresh tradeoffs for `AuditWindowAggregate` | P-022 `research.md` |
| **ON-02** | Whether DL-03-C archive-tier physical partition is warranted | P-022 — default remains logical `archiveVisibilityTier` column |
| **ON-03** | `topEventTypes` cardinality cap and histogram storage shape | P-022 / P-021 |
| **ON-04** | `ComplianceExportSnapshot` file format and storage location | Post-P2 / Implementation Authorization |
| **ON-05** | `AuditEventType` → reporting dimension catalog | P-024 |
| **ON-06** | Reporting read port contracts and DTO naming | P-021 `contracts/` |
| **ON-07** | Architecture boundary test plan for forbidden Audit Infrastructure imports | P-023 |
| **ON-08** | P-033 spec10 non-mutation checklist required before PR merge of this artifact | Governance |

---

**End of P-020 planning artifact. Projection data model only. No implementation authorized.**

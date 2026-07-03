# spec11 Reporting Dimension Catalog (Planning)

**Task**: P-024  
**Spec**: spec11 — Reporting & Audit Consumption Evolution  
**Status**: **Planning-only** — no implementation authority  
**Baseline**: `data-model.md`, P-021 contracts, `architecture-clarification.md`, `decision-log.md`, P2-C-03 (E-04 deferred)

---

## 1. PURPOSE

Define the **planning-stage catalog of reporting dimensions** used by spec11 projections and read models. This artifact maps conceptual dimensions to T0/T1/T2 applicability, grouping semantics, and integrity rules.

**Role**: Vocabulary alignment for `AuditWindowAggregate`, correlation indexes, read contracts, and future `AuditEventType` consumption — **read-only catalog**; no producer rollout.

**Scope**: Dimensions derivable from frozen `AuditHistoryItem` / spec10 audit vocabulary. Does not invent new audit event types.

---

## 2. CORE DIMENSIONS

| Dimension ID | Name | Source | Planning definition |
| ------------ | ---- | ------ | ------------------- |
| **D-TIME** | Time (`occurredAt`, windows) | T0 `occurredAt` | UTC event timestamp; window buckets use `windowStart`, `windowEnd`, `granularity` (`hour`, `day`, `week`, `month`) |
| **D-EVT** | `eventType` | T0 `eventType` | Frozen spec10 `AuditEventType` vocabulary — categorical audit event classifier |
| **D-CTX** | `sourceContext` | T0 `sourceContext` | Originating module/producer context (bounded context signal) |
| **D-ENT-T** | `entityType` | T0 `entityType` | Subject entity class under audit |
| **D-ENT-I** | `entityId` | T0 `entityId` | Subject entity UUID |
| **D-ACT-T** | `actorType` | T0 `actorType` | Actor class (user, system, service) |
| **D-ACT-I** | `actorId` | T0 `actorId` | Actor UUID |
| **D-COR** | `correlationId` | T0 `correlationId` | Cross-entity correlation token — nullable |
| **D-ARC** | Archive visibility | T0 archive state + DL-03 gate | `archiveVisibilityTier`: `active_only` \| `include_archived`; consumer `includeArchived` |
| **D-PRV** | Projection provenance | T1/T2 metadata | `sourceTier`, `refreshedAt`, `projectionVersion`, `filterHash` |

### 2.1 Reference identifiers (non-dimension keys)

| ID | Name | Role |
| -- | ---- | ---- |
| **D-REF** | `sourceAuditLogId` | Stable T0 line-item reference (`id` from `AuditHistoryItem`) — drill-down and export key |

---

## 3. DIMENSION GROUPS

### 3.1 Filtering dimensions

Used to constrain query and projection read scope.

| Dimension | Typical use |
| --------- | ----------- |
| D-TIME | `occurredFrom`, `occurredTo`, window bounds |
| D-EVT | `eventTypes` subset filter |
| D-CTX | Producer/module filter |
| D-ENT-T, D-ENT-I | Entity-scoped investigation |
| D-ACT-T, D-ACT-I | Actor-scoped security review |
| D-COR | Correlation bundle lookup |
| D-ARC | Archive visibility tier selection |

### 3.2 Grouping dimensions

Used to bucket T1 aggregates and histograms.

| Dimension | Grouping role |
| --------- | ------------- |
| D-TIME | `granularity` buckets in `AuditWindowAggregate` |
| D-EVT | Per-event-type counts; `eventTypeHistogram` |
| D-CTX | Per-producer/module breakdown |
| D-ENT-T | Subject-class aggregation |
| D-ACT-T | Actor-class aggregation |
| D-ARC | Separate materialization per visibility tier |

### 3.3 Summarization dimensions

Metrics derived from grouped facts — not filters.

| Metric | Dimensions involved |
| ------ | ------------------- |
| `eventCount` | D-TIME + grouping dims |
| `distinctEntityCount` | D-ENT-T/I cardinality |
| `distinctActorCount` | D-ACT-T/I cardinality |
| `topEventTypes` | D-EVT ordered subset |
| `occurredAtMin` / `occurredAtMax` | D-TIME span (correlation bundle) |

### 3.4 Drill-down identifiers

Bridge from T1 summary to T0 line items.

| Identifier | Purpose |
| ---------- | ------- |
| D-REF (`sourceAuditLogId`) | Direct T0 line fetch |
| D-ENT-T + D-ENT-I | Entity timeline drill-down |
| D-TIME window + grouping context | AggregateDrillDownPort filter translation |

---

## 4. DIMENSION SEMANTICS

| Dimension | Required? | Cardinality | Reporting meaning |
| ----------- | --------- | ----------- | ----------------- |
| D-TIME | Required on time-window queries | Single range / bucket | When the audited action occurred (UTC); Jalali at presentation only |
| D-EVT | Optional filter; required on source items | Single per event | What kind of audit event was recorded |
| D-CTX | Optional filter | Single per event | Which bounded context produced the audit fact |
| D-ENT-T | Required for entity timeline | Single per event | What type of entity was affected |
| D-ENT-I | Required for entity timeline | Single per event | Which entity instance was affected |
| D-ACT-T | Optional | Single per event | Who/what class performed the action |
| D-ACT-I | Optional | Single per event | Which actor instance performed the action |
| D-COR | Optional (nullable on source) | Single when present | Shared correlation across related events |
| D-ARC | Required at query boundary | Single tier per request | Whether soft-archived rows are included |
| D-PRV | Required on T1/mixed responses | Single envelope | How derived the response is; freshness disclosure |
| D-REF | Required for line-item evidence | Single per audit row | Pointer to authoritative T0 record |

**Multi-value behavior**: Filter dimensions accept sets (e.g., multiple `eventTypes`). Grouping dimensions use one value per aggregate row; null dimension column means "all values in bucket" for that axis (`AuditWindowAggregate`).

---

## 5. TIER APPLICABILITY

| Dimension | T0 (reference / authoritative) | T1 (materialized) | T2 (read model exposure) |
| --------- | -------------------------------- | ----------------- | -------------------------- |
| D-TIME | ✅ Source `occurredAt`; drill-down filters | ✅ Window buckets, spans, actor windows | ✅ Display ordering, bucket labels |
| D-EVT | ✅ Line items | ✅ Aggregate dimension, histograms | ✅ Filters, summaries |
| D-CTX | ✅ Line items | ✅ Aggregate dimension | ✅ Filters, coverage metadata |
| D-ENT-T / D-ENT-I | ✅ Primary investigative key | ✅ Participant denormalization, cache | ✅ Timeline, drill-down |
| D-ACT-T / D-ACT-I | ✅ Line items | ✅ Actor summaries | ✅ Security views |
| D-COR | ✅ On item (nullable) | ✅ Index key (non-null rows only) | ✅ Bundle grouping |
| D-ARC | ✅ Source archive state | ✅ `archiveVisibilityTier` column | ✅ `includeArchived` in envelope |
| D-PRV | N/A (T0 pure may null provenance version) | ✅ `refreshedAt`, `projectionVersion` | ✅ Full envelope on all responses |
| D-REF | ✅ Authoritative `id` | ✅ `sourceAuditLogId` reference | ✅ Drill-down and export lists |

---

## 6. DEFERRED / OUT-OF-SCOPE DIMENSIONS

| Item | Status | Notes |
| ---- | ------ | ----- |
| E-04 compliance KPI dimensions | **Deferred** | P2-C-03 — post-P2 wave; not in catalog |
| `SecurityAuditor` role dimension | **Deferred** | P2-C-02 — `Administrator` primary for security frame |
| M4 producer-specific event types | **Deferred** | Request, Lottery, Allocation, CheckIn, Notification — no backfill assumption |
| `reporting.read` separate permission | **Deferred** | Default: extend `audit.read` (P2-C-01) |
| Analytics trend partitions (Jalali month/quarter series) | **Deferred** | E-08 separate authorization |
| Cross-context non-audit dimensions | **Out of scope** | Require separate upstream read contracts (R-DM-16) |
| BI / OLAP hierarchies | **Out of scope** | Non-goals below |

### 6.1 Producer maturity (`sourceContext` planning)

| Maturity | Contexts (planning) | Catalog expectation |
| -------- | ------------------- | ------------------- |
| **M1 (current)** | Identity, Voucher | Aggregates reflect available events only |
| **M4 (deferred)** | Request, Lottery, Allocation, CheckIn, Notification | Dimensions extensible; absence tolerated — no assumed coverage |

### 6.2 `eventType` vocabulary note

Concrete `AuditEventType` enum values live in **frozen spec10** — not enumerated here. P-024 catalogs the **dimension** and mapping rules:

| Mapping rule | Detail |
| ------------ | ------ |
| MR-01 | Each T0 `eventType` maps 1:1 to D-EVT for projections |
| MR-02 | New event types from future producers extend D-EVT without spec10 reopening by spec11 |
| MR-03 | Unknown future `eventType` values aggregate under dynamic histogram keys |
| MR-04 | P-024 does **not** authorize new audit producers or event type creation |

---

## 7. INTEGRITY RULES

| Rule ID | Rule |
| ------- | ---- |
| **DC-01** | D-REF (`sourceAuditLogId`) must reference a valid T0 audit row when used for evidence |
| **DC-02** | D-COR null on source → exclude from correlation T1 ingest (R-DM-13) |
| **DC-03** | D-ARC tier on T1 rows must match effective `includeArchived` on query — no mixed-tier responses (RV-03) |
| **DC-04** | D-PRV required on T1/mixed T2 responses — `sourceTier`, `filterHash` always; `refreshedAt`/`projectionVersion` when T1 involved |
| **DC-05** | D-TIME buckets stored UTC; presentation converts to Jalali |
| **DC-06** | Summarization metrics (§3.3) alone do not satisfy compliance export — D-REF T0 resolution required |
| **DC-07** | Dimension filters must not imply spec10 `AuditHistoryQuery` extensions (e.g., no T0 `correlationId` filter) |
| **DC-08** | `filterHash` must incorporate D-ARC effective value (R-DM-09) |

---

## 8. NON-GOALS

This dimension catalog does **NOT** define:

| Exclusion |
| --------- |
| Full `AuditEventType` enum listing from spec10 (frozen external vocabulary) |
| BI semantic layers or OLAP cubes |
| Analytics platform schema |
| Implementation table columns or indexes |
| E-04 KPI metric definitions |
| New audit producers or event types |
| Implementation Authorization |

---

**End of P-024 dimension catalog. Read-only planning vocabulary. No implementation authorized.**

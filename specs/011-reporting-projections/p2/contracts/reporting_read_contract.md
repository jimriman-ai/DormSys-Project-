# Reporting Read Contract — Consumption Surface (Planning)

**Task**: P-021  
**Spec**: spec11 — Reporting & Audit Consumption Evolution  
**Status**: **Planning-only** — no implementation authority  
**Owner**: spec11 Reporting Application (future bounded context)  
**Baseline**: `architecture-clarification.md` §2, §4, §3.5; `data-model.md` §3.8; DL-01, DL-02, DL-03; P2-C-01–P2-C-03

---

## 1. PURPOSE

Define the **Reporting-owned read contract planning surface** — the stable consumption API that Presentation and export processes will use to access audit reporting read models. This contract sits above T0/T1/T2 assembly and below UI transport.

**DL-02**: First authorized implementation wave targets this read API / export façade before Operator Explorer UI.

---

## 2. SUPPORTED READ USE-CASES

| ID | Use-case | Consumption frame | Primary read model | Primary tier |
| -- | -------- | ----------------- | ------------------ | ------------ |
| **RU-01** | Entity timeline investigation | Operational | `EntityAuditTimelineReadModel` | T0 (optional T1 cache) |
| **RU-02** | Correlation incident bundle | Security / compliance | `CorrelationAuditBundleReadModel` | T1 |
| **RU-03** | Time-window operational summary | Operational | `AuditWindowSummaryReadModel` | T1 |
| **RU-04** | Compliance period export package | Compliance | `ComplianceExportReadModel` | T1 + T0 line items |
| **RU-05** | Security actor activity review | Security | `SecurityAuditEventReadModel` | T1 + T0 verification |
| **RU-06** | Aggregate drill-down to line items | All frames | Delegates to RU-01 via T0 | T0 |

**Deferred (P2-C-03)**: E-04 compliance KPI dashboards — not in initial contract surface.

---

## 3. READ-MODEL FAMILIES

### 3.1 `EntityAuditTimelineReadModel`

| Field | Expectation |
| ----- | ----------- |
| **Purpose** | Ordered audit history for `(entityType, entityId)` |
| **Items** | List of audit line summaries or full T0 items |
| **Ordering** | `occurredAt` descending (UTC) |
| **Summary** | Optional count, first/last occurrence, event-type histogram |
| **Pagination** | Cursor/page per query request |
| **Tier** | T0 primary; T1 `EntityTimelineCacheEntry` optional |

### 3.2 `CorrelationAuditBundleReadModel`

| Field | Expectation |
| ----- | ----------- |
| **Purpose** | All audit items sharing `correlationId` |
| **Participants** | Distinct entities and actors in bundle |
| **Span** | `occurredAtMin`, `occurredAtMax` |
| **Histogram** | `eventType` counts |
| **Verification** | Optional T0 line fetch by `sourceAuditLogId` |
| **Tier** | T1 primary |

### 3.3 `AuditWindowSummaryReadModel`

| Field | Expectation |
| ----- | ----------- |
| **Purpose** | Bucketed aggregates for reporting window |
| **Buckets** | `windowStart`, `windowEnd`, `granularity` |
| **Metrics** | `eventCount`, `distinctEntityCount`, `distinctActorCount`, `topEventTypes` |
| **Dimensions** | `eventType`, `sourceContext`, `actorType`, `entityType` (optional filters) |
| **Drill-down** | Handles linking to entity T0 queries |
| **Tier** | T1 primary |

### 3.4 `ComplianceExportReadModel`

| Field | Expectation |
| ----- | ----------- |
| **Purpose** | Governed export package with filter manifest |
| **Manifest** | `filterManifest`, `generatedAt`, `snapshotId` |
| **Line items** | T0-resolved `AuditHistoryItem` list by `sourceAuditLogId` |
| **Summary** | Optional T1 window/correlation context |
| **Tier** | `mixed` — T1 manifest + T0 evidence |

### 3.5 `SecurityAuditEventReadModel`

| Field | Expectation |
| ----- | ----------- |
| **Purpose** | Actor- and event-type-focused security review |
| **Emphasis** | `correlationId`, `actorId`, `eventType` concentration |
| **Window** | Configurable activity window |
| **Audience** | `Administrator` (P2-C-02) |
| **Tier** | T1 emphasis + T0 verification |

---

## 4. QUERY RESPONSIBILITIES

### 4.1 Reporting read contract responsibilities

| Responsibility | Owner |
| -------------- | ----- |
| Enforce `audit.read` permission before any read | Reporting read contract |
| Apply DL-03 visibility gate (`includeArchived`) | Reporting read contract |
| Select T0 vs T1 vs mixed assembly path | Reporting read contract |
| Attach provenance metadata to every response | Reporting read contract |
| Compose frozen `AuditHistoryReadContract` calls | Reporting read contract (via T0 adapter) |
| Read T1 projection stores | Reporting read contract (via projection ports) |
| Assemble T2 ephemeral response DTOs | Reporting read contract |
| Mutate `audit_logs` | **Never** |

### 4.2 Query input concepts (planning)

| Input | Applies to | Notes |
| ----- | ---------- | ----- |
| `entityType`, `entityId` | RU-01, RU-06 | Required for entity timeline |
| `correlationId` | RU-02 | T1 lookup — not T0 query filter |
| `windowStart`, `windowEnd`, `granularity` | RU-03, RU-04, RU-05 | UTC boundaries |
| `eventTypes` | All where applicable | Subset filter |
| `sourceContext` | RU-03, RU-05 | Producer dimension |
| `actorType`, `actorId` | RU-05 | Security scoping |
| `includeArchived` | All | Role-gated; default `false` |
| `page`, `perPage` | Paginated reads | Respects T0 cap for T0 paths |
| `consumptionFrame` | All | `operational` \| `compliance` \| `security` — influences defaults |

### 4.3 Query outputs

Every response includes a **provenance envelope** (§5) plus the read-model payload. Paginated responses include continuation metadata. Export responses include `filterManifest` hash.

---

## 5. PROVENANCE FIELDS (MANDATORY)

All Reporting read contract responses carry:

| Field | Type (conceptual) | When required | Meaning |
| ----- | ----------------- | ------------- | ------- |
| `sourceTier` | `T0` \| `T1` \| `mixed` | Always | Which tier(s) supplied payload data |
| `refreshedAt` | datetime (UTC) \| null | When `sourceTier` includes T1 | Last T1 refresh relevant to response |
| `projectionVersion` | string \| null | When `sourceTier` includes T1 | T1 logic/schema version |
| `includeArchived` | boolean | Always | Effective archive visibility applied |
| `filterHash` | string | Always | Stable hash of normalized query filters for cache semantics |

**Rules** (`data-model.md` R-DM-11):

- Pure T0 responses: `refreshedAt` and `projectionVersion` may be null.
- Mixed responses: expose the **oldest** relevant `refreshedAt` or disclose per-section provenance in export models.
- `filterHash` must incorporate `includeArchived` — no cross-tier cache leakage (R-DM-09).

---

## 6. TIER ROLES IN SERVING USE-CASES

| Tier | Role in reporting read contract |
| ---- | -------------------------------- |
| **T0** | Authoritative line items; entity investigation; export evidence; drill-down target |
| **T1** | Correlation index; window aggregates; actor summaries; export manifest metadata; optional entity cache |
| **T2** | Single-request assembly of T0/T1 into consumer DTOs; no persistence |

### Tier selection matrix

| Use-case | Default path | Fallback |
| -------- | ------------ | -------- |
| RU-01 Entity timeline | T0 | T1 cache if present and fresh enough (implementation decision — deferred) |
| RU-02 Correlation bundle | T1 | T0 multi-query only at low-volume MVP threshold — not scale path |
| RU-03 Window summary | T1 | T0 raw aggregation — not preferred at scale |
| RU-04 Compliance export | T1 manifest + T0 lines | None — lines must be T0 |
| RU-05 Security actor view | T1 + T0 verify | T0-only at low volume |
| RU-06 Drill-down | T0 | — |

---

## 7. ARCHIVE VISIBILITY SEMANTICS (DL-03)

Enforced at **Reporting read contract boundary** before T0/T1 delegation:

| Consumption frame | `includeArchived` default | Override |
| ----------------- | ------------------------- | -------- |
| Operational (`RU-01`, `RU-03` ops) | `false` | None |
| Security (`RU-02`, `RU-05`) | `false` | `Administrator` may set `true` |
| Compliance (`RU-04`) | `false` | Compliance-authorized roles may set `true` |

| Rule | Expectation |
| ---- | ----------- |
| **RV-01** | Default excludes soft-archived source rows — aligned with spec10 |
| **RV-02** | `includeArchived=true` requires role authorization — no silent bypass |
| **RV-03** | T1 reads use matching `archiveVisibilityTier` — no mixed-tier rows in one response |
| **RV-04** | Presentation layer does not re-interpret archive policy |

---

## 8. CONSUMPTION FLOW

```text
Consumer (Presentation / export)
        ↓
Reporting Read Contract
  ├── permission check (audit.read)
  ├── visibility gate (includeArchived)
  ├── tier selection (T0 / T1 / mixed)
  └── provenance envelope assembly
        ↓
T2 read model response
```

---

## 9. NON-GOALS

This reporting read contract planning artifact does **NOT** define:

| Exclusion |
| --------- |
| REST/HTTP routes or Livewire components |
| PHP interfaces or method signatures |
| SQL, Eloquent, or repository classes |
| Projection refresh jobs or queues |
| `SecurityAuditor` role implementation |
| E-04 compliance KPI metrics |
| spec10 contract extensions |
| Implementation Authorization |
| Write paths to any domain store |

---

**End of reporting read contract planning artifact. Read-only consumption surface. No implementation authorized.**

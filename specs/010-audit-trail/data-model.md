# Data Model: Audit Trail & Traceability (spec10)

**Date**: 2026-07-02  
**Spec**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

---

## Persistence overview

| Table | Purpose |
| ----- | ------- |
| `audit_logs` | Immutable domain-aware audit trail (spec10 owned) |
| `activity_log` | Legacy/interim Spatie technical log (retained; not write target for new critical events post-cutover) |

**Module path:** `database/migrations/modules/audit/`

**Immutability policy:** Application layer exposes **no** update/delete repository methods for `audit_logs`. Migrations must not include `ON DELETE CASCADE` from domain tables.

---

## Entity: AuditLog

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | UUID PK | `HasUuid` |
| `correlation_id` | string(191) | Global idempotency key — **UNIQUE** |
| `event_type` | string(64) | Stable vocabulary — see § Event vocabulary |
| `entity_type` | string(64) | Subject type token (e.g. `request`, `allocation`) |
| `entity_id` | UUID | Subject identifier |
| `actor_type` | string(16) | `user` \| `system` |
| `actor_id` | string(128) | User UUID or system actor token |
| `source_context` | string(32) | Originating bounded context label |
| `old_values` | jsonb nullable | Before snapshot |
| `new_values` | jsonb nullable | After snapshot |
| `metadata` | jsonb nullable | Reason, seed ref, truncation flags |
| `payload_hash` | string(64) | SHA-256 of canonical entry for conflict detection |
| `occurred_at` | timestamp | UTC — upstream outcome time |
| `archived_at` | timestamp nullable | UTC — retention soft-archive |
| `created_at` | timestamp | UTC — persistence time (immutable) |

**Note:** No `updated_at` — append-only row.

### Indexes

| Index | Columns | Purpose |
| ----- | ------- | ------- |
| `audit_logs_correlation_uniq` | `(correlation_id)` UNIQUE | Idempotency (UD-10-05) |
| `audit_logs_entity_idx` | `(entity_type, entity_id, occurred_at DESC)` | Entity history query |
| `audit_logs_actor_idx` | `(actor_type, actor_id, occurred_at DESC)` | Actor query |
| `audit_logs_event_idx` | `(event_type, occurred_at DESC)` | Category filter |
| `audit_logs_active_idx` | `(archived_at, occurred_at DESC)` WHERE `archived_at IS NULL` | Active compliance queries |

---

## Domain model (pure PHP)

### AuditLog (aggregate root)

| Attribute | Type | Rule |
| --------- | ---- | ---- |
| id | AuditLogId | UUID |
| correlationId | CorrelationId | non-empty, unique |
| eventType | AuditEventType | enum-backed string |
| entityReference | EntityReference | type + UUID |
| actorReference | ActorReference | user or system |
| sourceContext | SourceContext | bounded context label |
| oldValues | ?array | JSON snapshot |
| newValues | ?array | JSON snapshot |
| metadata | ?array | contextual fields |
| occurredAt | DateTimeImmutable | UTC |
| archivedAt | ?DateTimeImmutable | null = active |
| createdAt | DateTimeImmutable | UTC |

### Value objects

| VO | Fields |
| -- | ------ |
| **CorrelationId** | value: string (max 191) |
| **EntityReference** | entityType: string, entityId: string (UUID) |
| **ActorReference** | actorType: `user`\|`system`, actorId: string |
| **AuditLogId** | value: UUID |

### Enums

**AuditEventType** (planning vocabulary — UD-10-01):

| Value | AP-06 category |
| ----- | -------------- |
| `request.submitted` | Request submission |
| `request.state_changed` | Request lifecycle |
| `request.approved` | Approval |
| `request.rejected` | Rejection |
| `lottery.program_created` | Lottery lifecycle |
| `lottery.executed` | Lottery execution |
| `lottery.reserve_promoted` | Reserve promotion |
| `allocation.created` | Allocation create |
| `allocation.modified` | Allocation modify |
| `allocation.cancelled` | Allocation cancel |
| `check_in.recorded` | Check-in |
| `check_out.recorded` | Check-out |
| `voucher.issued` | Voucher transition |
| `voucher.state_changed` | Voucher lifecycle |
| `identity.role_changed` | Permission change |
| `identity.permission_changed` | Permission change |
| `dormitory.room_status_changed` | Physical status (spec04 future) |

**ActorType:** `user`, `system`

**System actor tokens** (constants):

| Token | Usage |
| ----- | ----- |
| `system:lottery_draw` | ExecuteLotteryDrawJob |
| `system:reserve_promotion` | PromoteReserveWinnerJob |
| `system:scheduler` | Scheduled commands |
| `system:migration` | Data migration scripts |

---

## Query read model

| Query | Filter | Sort | Auth |
| ----- | ------ | ---- | ---- |
| By entity | `entity_type`, `entity_id`, `archived_at IS NULL` | `occurred_at DESC` | `audit.read` |
| By actor | `actor_type`, `actor_id`, date range | `occurred_at DESC` | `audit.read` |
| By event | `event_type`, date range | `occurred_at DESC` | `audit.read` |
| Paginated | cursor or offset limit | `occurred_at DESC` | `audit.read` |

**Authorization:** `AuditHistoryReadContract` checks `audit.read` via Identity permission port — no query without permission (FR-009, UD-10-06).

---

## Retention model (UD-10-03)

| Phase | Rule |
| ----- | ---- |
| **Active** | `archived_at IS NULL` — default compliance queries |
| **Archived** | `occurred_at < now() - retention_months` → set `archived_at` |
| **Hard delete** | **Prohibited** v1 |
| **Setting** | `audit.retention_months` default **84** |

Job: `ArchiveExpiredAuditLogsJob` (daily schedule) — mirrors spec09 archival pattern.

---

## Mapping from Spatie `activity_log` (migration reference)

| activity_log | audit_logs (target) |
| ------------ | ------------------- |
| `subject_type` / `subject_id` | `entity_type` / `entity_id` (normalized tokens) |
| `causer_id` | `actor_id` when `actor_type=user` |
| `event` | `event_type` (mapped via bridge table in M2) |
| `properties` | `metadata` / snapshots |
| `created_at` | `occurred_at` |

*Bridge mapping is **forward-only** for new activity rows during M2 — historical `activity_log` rows are not bulk-migrated in Wave 1.*

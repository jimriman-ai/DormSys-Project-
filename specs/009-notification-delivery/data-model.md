# Data Model: Notification Delivery (spec09)

**Date**: 2026-07-02  
**Spec**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

---

## Persistence overview

| Table | Purpose |
| ----- | ------- |
| `notification_logs` | Durable in-app notification inbox records |
| `notification_delivery_dedup` | Idempotency guard (optional separate table) or unique index on `notification_logs` |

**Recommendation:** Single table `notification_logs` with **unique constraint** on `(correlation_id, recipient_employee_id, notification_type)` — dedup without separate table unless volume requires split.

**Module path:** `database/migrations/modules/notification/`

---

## Entity: NotificationLog

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | UUID PK | `HasUuid` |
| `correlation_id` | string(128) | Upstream idempotency key |
| `notification_type` | string(64) | Enum-backed vocabulary |
| `recipient_employee_id` | UUID | Immutable employee reference |
| `title` | string(255) | Persian text |
| `message` | text | Full body; Persian |
| `entity_type` | string(64) nullable | e.g. `request`, `allocation`, `voucher` |
| `entity_id` | UUID nullable | Referenced entity |
| `deep_link_route` | string(255) nullable | Presentation route name or path token |
| `source_context` | string(32) | Originating bounded context label |
| `priority` | string(16) | `standard` \| `urgent` |
| `read_at` | timestamp nullable | UTC |
| `archived_at` | timestamp nullable | UTC; set by retention job |
| `delivery_status` | string(16) | `delivered` \| `skipped_invalid_recipient` |
| `skip_reason` | string(64) nullable | When skipped |
| `created_at` | timestamp | UTC |
| `updated_at` | timestamp | UTC |

### Indexes

| Index | Columns | Purpose |
| ----- | ------- | ------- |
| `notification_logs_inbox_idx` | `(recipient_employee_id, archived_at, created_at DESC)` | Inbox list |
| `notification_logs_unread_idx` | `(recipient_employee_id, read_at)` WHERE `read_at IS NULL` | Unread count (partial, PG) |
| `notification_logs_dedup_uniq` | `(correlation_id, recipient_employee_id, notification_type)` UNIQUE | Idempotency |

---

## Domain model (pure PHP)

### Notification (aggregate root)

| Attribute | Type | Rule |
| --------- | ---- | ---- |
| id | NotificationId | UUID |
| correlationId | CorrelationId | non-empty |
| type | NotificationType | enum |
| recipientEmployeeId | string (UUID) | required |
| title | string | non-empty |
| message | string | non-empty |
| entityReference | EntityReference? | optional VO |
| deepLinkRoute | ?string | optional |
| sourceContext | SourceContext | enum/string |
| priority | DeliveryPriority | standard \| urgent |
| readAt | ?DateTimeImmutable | null = unread |
| archivedAt | ?DateTimeImmutable | null = active inbox |
| deliveryStatus | DeliveryStatus | delivered \| skipped |
| createdAt | DateTimeImmutable | UTC |

### Value objects

| VO | Fields |
| -- | ------ |
| **CorrelationId** | value: string |
| **EntityReference** | entityType: string, entityId: string (UUID) |
| **NotificationId** | value: UUID |

### Enums

**NotificationType** (planning vocabulary):

| Value | BR-09.1 mapping |
| ----- | --------------- |
| `request_submitted` | Next approver |
| `request_approved` | Employee |
| `request_rejected` | Employee |
| `allocation_successful` | Employee |
| `lottery_winner` | Employee |
| `voucher_issued` | Employee |
| `reserve_promoted` | Employee |
| `check_in_reminder` | Employee (internal only) |

**DeliveryPriority:** `standard`, `urgent`

**DeliveryStatus:** `delivered`, `skipped_invalid_recipient`

---

## Inbox read model

| Query | Filter | Sort |
| ----- | ------ | ---- |
| List inbox | `recipient_employee_id = :auth`, `archived_at IS NULL` | `created_at DESC` |
| List unread | + `read_at IS NULL` | `created_at DESC` |
| Mark read | `id = :id AND recipient_employee_id = :auth` | — |

**Recipient isolation:** Every query and mutation includes `recipient_employee_id` from authenticated context — no cross-recipient access (FR-014, SC-003).

---

## Retention model (UD-11)

| Phase | Rule |
| ----- | ---- |
| **Active** | `archived_at IS NULL` — visible in default inbox |
| **Archived** | `created_at < now() - retention_months` → set `archived_at` |
| **Retention default** | 24 months (`notification.retention_months` setting) |
| **Hard delete** | Not in v1 |

Scheduled job: **`ArchiveExpiredNotificationsJob`** (daily) in Notification module.

---

## State transitions

```
[Intent received]
    → validate recipient
        → invalid → skipped_invalid_recipient (optional audit row or skip log)
        → valid → check dedup
            → duplicate → no-op (return existing id)
            → new → delivered (read_at = null)
                → mark read → read_at set
                → retention job → archived_at set
```

No transition mutates upstream entities.

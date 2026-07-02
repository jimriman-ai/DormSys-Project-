# Contract: Notification Intent DTO (UD-09)

**Version:** 1.0.0  
**Spec:** spec09 Notification Delivery  
**Direction:** Upstream contexts → Notification (R9)  
**Status:** Planning — design baseline

---

## Purpose

Canonical inbound payload for cross-boundary notification delivery. Upstream modules supply **fully resolved** recipient and message content. Notification persists and deduplicates — no upstream store reads.

---

## NotificationIntentDto

| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| `correlationId` | string | yes | Upstream idempotency key (unique per business outcome) |
| `notificationType` | NotificationType | yes | Stable vocabulary — see [data-model.md](../data-model.md) |
| `recipientEmployeeId` | UUID string | yes | Target employee — **resolved upstream** |
| `title` | string | yes | Persian user-facing title |
| `message` | string | yes | Persian user-facing body |
| `sourceContext` | string | yes | e.g. `request`, `lottery`, `allocation`, `voucher`, `check_in` |
| `entityType` | string | no | Referenced entity type for deep link |
| `entityId` | UUID string | no | Referenced entity id |
| `deepLinkRoute` | string | no | Presentation route token (not validated by Notification) |
| `priority` | `standard` \| `urgent` | yes | Default `standard`; use `urgent` for reserve promotion etc. |
| `occurredAt` | DateTimeImmutable | yes | UTC — upstream outcome timestamp |

---

## Correlation rules

| Rule | Detail |
| ---- | ------ |
| **Uniqueness scope** | `(correlationId, recipientEmployeeId, notificationType)` |
| **Generation** | Upstream owns format; recommend `{sourceContext}:{entityType}:{entityId}:{outcome}` |
| **Replay** | Same triple → idempotent no-op; return existing notification id |
| **Fan-out** | One upstream outcome → multiple intents with **different** `recipientEmployeeId` and/or `notificationType` |

---

## Recipient resolution (closed)

| Rule | Detail |
| ---- | ------ |
| **Notification does not resolve roles** | Request resolves next approver before emit |
| **Validation** | Notification calls `EmployeeExistenceReadPort::existsActive(uuid)` |
| **Invalid recipient** | Skip delivery; `delivery_status = skipped_invalid_recipient` |

---

## Example (request approved)

```json
{
  "correlationId": "request:550e8400-e29b-41d4-a716-446655440000:approved",
  "notificationType": "request_approved",
  "recipientEmployeeId": "6ba7b810-9dad-11d1-80b4-00c04fd430c8",
  "title": "درخواست خوابگاه تأیید شد",
  "message": "درخواست شما در مرحله تأیید مدیر تأیید شد.",
  "sourceContext": "request",
  "entityType": "request",
  "entityId": "550e8400-e29b-41d4-a716-446655440000",
  "deepLinkRoute": "requests.show",
  "priority": "standard",
  "occurredAt": "2026-07-02T10:30:00Z"
}
```

---

## Upstream supplier map

| Source context | Typical notification types |
| -------------- | -------------------------- |
| Request (spec05) | `request_submitted`, `request_approved`, `request_rejected` |
| Allocation (spec07) | `allocation_successful` |
| Lottery (spec06) | `lottery_winner`, `reserve_promoted` |
| Voucher (spec08) | `voucher_issued`, `reserve_promoted` |
| CheckIn (spec07) | `check_in_reminder` |

*Upstream modules emit intents; they do not write `notification_logs`.*

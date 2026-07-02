# Research: Notification Delivery (spec09)

**Date**: 2026-07-02  
**Spec**: [spec.md](./spec.md)

---

## R-01 — Cross-boundary integration pattern (R9)

**Decision:** Upstream modules invoke **`NotificationDeliveryContract::deliver(NotificationIntentDto)`** synchronously after their domain transaction commits; optional **`SendNotificationJob`** wraps the same action for async paths where upstream already uses queues.

**Rationale:** Matches existing DormSys port pattern (Voucher trigger intake, Allocation ports). Keeps Notification a pure consumer with an explicit DTO boundary. Domain Events may be used internally within upstream modules, but the **cross-module boundary** is the delivery contract — not shared Eloquent or repository reads.

**Alternatives considered:**

| Alternative | Verdict |
| ----------- | ------- |
| Notification subscribes to a global event bus with typed payloads | Rejected for v1 — harder to enforce R9 import boundaries and idempotency ownership |
| Notification polls upstream read models | Rejected — violates R9; Reporting-only pattern is CD-017 |
| Upstream writes directly to `notification_logs` | Rejected — ownership leakage |

---

## R-02 — Recipient resolution (UD-09)

**Decision:** **`recipient_employee_id` is required** on every intent. Upstream contexts resolve approvers, winners, and operators **before** calling Notification. Notification validates existence via **`EmployeeExistenceReadPort`** (read-only UUID check) and rejects/skips invalid recipients.

**Rationale:** Avoids Notification owning RBAC or approval routing (Request/CD-010 territory). Spec edge case “multiple approvers for role” is resolved upstream.

**Alternatives considered:**

| Alternative | Verdict |
| ----------- | ------- |
| Notification resolves role → employees via Identity/Permission | Rejected — policy leakage from Request into Notification |
| Optional recipient with Notification lookup | Rejected — ambiguous ownership |

---

## R-03 — Idempotency key (UD-09)

**Decision:** Deduplication key = **`(correlation_id, recipient_employee_id, notification_type)`** enforced by a **unique database constraint** on `notification_deliveries` (or equivalent dedup table).

**Rationale:** Aligns with Voucher trigger correlation pattern (spec08). Same business outcome retried → same correlation_id from upstream. Different outcomes → different correlation_id.

**Alternatives considered:**

| Alternative | Verdict |
| ----------- | ------- |
| correlation_id alone | Rejected — one upstream event may fan out to multiple recipients |
| Hash of message body | Rejected — unstable under retries with formatting differences |

---

## R-04 — Check-in reminder scheduling (UD-10)

**Decision:** **`CheckIn` bounded context** owns the **scheduler** that identifies eligible internal allocations and emits reminder intents. Notification owns **delivery only**. Scheduler runs **daily at 09:00 Asia/Tehran** via Laravel Scheduler; selects allocations with **check-in date = calendar tomorrow (UTC storage, Tehran wall-clock rule)**.

**Rationale:** OA-09-06 places scheduling with operational contexts. CheckIn already owns operational transitions (CD-015). Reminder is an operational precursor, not a generic Notification cron.

**Alternatives considered:**

| Alternative | Verdict |
| ----------- | ------- |
| Notification cron scans Allocation store | Rejected — R9 violation |
| Allocation scheduler | Acceptable alternate — **CheckIn chosen** as operational owner per CD-015 |

**Presentation:** Stored timestamps UTC; Jalali conversion at presentation layer (`morilog/jalali`) per project constitution.

---

## R-05 — Retention and archival (UD-11)

**Decision:** **24-month** default active inbox window; thereafter **soft-archive** (`archived_at` set, excluded from default inbox queries). **No hard delete** in v1. Setting key: `notification.retention_months` (default `24`).

**Rationale:** FR-013 requires historical retention without silent deletion. 24 months matches typical HR accommodation record review cycles; configurable without schema change.

**Alternatives considered:**

| Alternative | Verdict |
| ----------- | ------- |
| Infinite inbox | Rejected — storage pressure (SC-001 volume) |
| 90-day hard delete | Rejected — conflicts with FR-013 spirit |
| Archive to cold storage | Deferred post-v1 |

---

## R-06 — Urgent / latency-sensitive delivery

**Decision:** Intent field **`priority: standard | urgent`**. **Urgent** intents processed on **`notifications-urgent`** queue (or synchronous `deliver()` when upstream already in-request). **Standard** intents use **`notifications`** queue. No batching delay for urgent (SC-006).

**Rationale:** Reserve promotion and similar BR-09.1 triggers need immediate visibility without building a separate channel.

---

## R-07 — Employee validation boundary

**Decision:** Notification depends on **`EmployeeExistenceReadPort`** in Application layer — returns bool for active employee UUID. Implemented as adapter calling Employee read contract when available; stub returns true for UUID format in isolated tests.

**Rationale:** Minimal cross-module read for delivery safety without importing Employee Infrastructure.

---

## Open items disposition

| ID | Status after research |
| -- | --------------------- |
| **UD-09** | **RESOLVED** — see [contracts/notification-intent-dto.md](./contracts/notification-intent-dto.md) |
| **UD-10** | **RESOLVED** — see [contracts/check-in-reminder-scheduler-port.md](./contracts/check-in-reminder-scheduler-port.md) |
| **UD-11** | **RESOLVED** — 24-month soft-archive policy in [data-model.md](./data-model.md) |

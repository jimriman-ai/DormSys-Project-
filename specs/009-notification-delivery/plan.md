# Planning Document: Notification Delivery (spec09)

**Branch**: `009-notification-delivery` | **Date**: 2026-07-02 | **Spec**: [spec.md](./spec.md)

**Input**: Notification cross-cutting capability — in-app delivery, inbox persistence, read/unread state, idempotent intent consumption (**R9**).

**Governance**: Planning translation only. **Not** Design Approval. **Not** Implementation Authorization. **No** `tasks.md`. spec07/spec08 remain **CLOSED**.

---

## Summary

spec09 implements a **downstream notification delivery layer** inside `app/Modules/Notification/`. Upstream bounded contexts emit **`NotificationIntentDto`** via **`NotificationDeliveryContract`** after domain outcomes commit. Notification validates recipients, deduplicates, persists **`notification_logs`**, and exposes **recipient-scoped inbox read** ports.

**Resolved at planning:** UD-09 (intent contract), UD-10 (CheckIn-owned scheduler), UD-11 (24-month soft-archive retention).

**MVP channel:** In-app database only (constitution §8.2). Email/SMS excluded.

---

## Technical Context

| Field | Value |
| ----- | ----- |
| **Language** | PHP 8.4, Laravel 13 |
| **Architecture** | Modular monolith — Clean Architecture + DDD Lite |
| **Storage** | PostgreSQL 17 — `notification_logs` (UUID PK) |
| **Queue** | Redis + Horizon — `notifications`, `notifications-urgent` |
| **Testing** | Pest PHP — unit (domain), feature (delivery/inbox), architecture (R9) |
| **Localization** | Persian content in intents; UTC storage; Jalali at presentation |
| **Performance** | SC-001: 95% delivery visible &lt; 5s; urgent path no batching delay |
| **Scale** | Enterprise dormitory — burst on lottery draw; dedup + indexed inbox |

---

## Constitution Check

| Gate | Status | Notes |
| ---- | ------ | ----- |
| Modular monolith layers | PASS | Domain ← Application ← Infrastructure/Presentation |
| UUID primary keys | PASS | `notification_logs.id` |
| No cross-module Eloquent | PASS | R9 — ports only |
| Audit append-only | PASS | Notification does not write `audit_logs` (spec10) |
| In-app notifications in scope | PASS | §8.1 |
| Email/SMS out of scope | PASS | §8.2 |
| PHPStan level 8 / Pint | PASS | Required at implementation |
| Persian RTL presentation | PASS | Deferred UI; content in intents |

**Post-design re-check:** PASS — no violations.

---

## 1. ARCHITECTURE_PLAN

### Components

| Component | Layer | Responsibility |
| --------- | ----- | -------------- |
| **Notification** (aggregate) | Domain | Inbox record; read/archive state |
| **NotificationType**, **DeliveryPriority** | Domain | Vocabulary enums |
| **CorrelationId**, **EntityReference** | Domain | Value objects |
| **NotificationIntentDto** | Application | Inbound boundary DTO |
| **DeliverNotificationAction** | Application | Validate → dedup → persist |
| **NotificationInboxReadService** | Application | Recipient-scoped queries |
| **MarkNotificationReadAction** | Application | Read-state mutation |
| **NotificationDeliveryContract** | Application | Inbound port (upstream) |
| **NotificationInboxReadContract** | Application | Read port (presentation) |
| **EmployeeExistenceReadPort** | Application | Outbound read (Employee) |
| **NotificationRepository** | Infrastructure | Persistence |
| **SendNotificationJob** | Infrastructure | Async delivery |
| **ArchiveExpiredNotificationsJob** | Infrastructure | UD-11 retention |
| **NotificationServiceProvider** | Infrastructure | DI bindings |

### Boundaries

| Boundary | Rule |
| -------- | ---- |
| **R9** | Notification consumes intents only — **no** `App\Modules\{Request,Lottery,Allocation,Voucher,CheckIn}\Infrastructure\*` imports |
| **CD-010** | Request owns approval routing — resolves approver **before** intent |
| **CD-016** | Voucher owns voucher policy — emits `voucher_issued` intent only |
| **CD-017** | Reporting read-only downstream — not in spec09 write path |
| **spec07/08** | Closed — adapter stubs acceptable until integration wave |

### Integration model

```
┌─────────────┐     NotificationIntentDto      ┌──────────────────┐
│   Request   │ ──────────────────────────────►│                  │
│   Lottery   │   NotificationDeliveryContract│   Notification   │
│  Allocation │ ──────────────────────────────►│   (spec09)       │
│   Voucher   │                                │                  │
│   CheckIn   │ ── scheduler emits intent ────►│  notification_logs│
└─────────────┘                                └────────┬─────────┘
                                                      │ read
                                                      ▼
                                            NotificationInboxReadContract
                                            (Presentation — deferred)
```

### Storage model

- Single table **`notification_logs`** per [data-model.md](./data-model.md)
- Unique constraint **`(correlation_id, recipient_employee_id, notification_type)`**
- Partial index for unread queries
- Migration path: `database/migrations/modules/notification/`

### Module structure (implementation target)

```text
app/Modules/Notification/
├── Domain/
│   ├── Models/Notification.php
│   ├── Enums/NotificationType.php
│   ├── ValueObjects/CorrelationId.php
│   └── ...
├── Application/
│   ├── Contracts/NotificationDeliveryContract.php
│   ├── Contracts/NotificationInboxReadContract.php
│   ├── DTOs/NotificationIntentDto.php
│   └── Services/DeliverNotificationAction.php
├── Infrastructure/
│   ├── Repositories/NotificationRepository.php
│   ├── Jobs/SendNotificationJob.php
│   └── Providers/NotificationServiceProvider.php
└── Presentation/   (deferred — OA-09-05)

tests/
├── Unit/Modules/Notification/
├── Feature/Modules/Notification/
└── Architecture/NotificationBoundaryTest.php
```

---

## 2. DATA_FLOW_MODEL

### Intent → delivery → inbox lifecycle

```
1. Upstream domain outcome committed
2. Upstream builds NotificationIntentDto (recipient resolved)
3. deliver(intent) or SendNotificationJob::dispatch(intent)
4. EmployeeExistenceReadPort::existsActiveEmployee()
5. Dedup lookup (correlation + recipient + type)
6. INSERT notification_logs OR return existing
7. Inbox read via NotificationInboxReadContract
8. markRead() sets read_at
9. Archive job sets archived_at after retention window
```

### Deduplication / correlation flow

```
Intent arrives
  → SELECT by dedup key
      → found → return { status: duplicate, notificationId }
      → not found → INSERT
          → unique violation (race) → return existing
```

### Failure / retry handling

| Failure | Behavior |
| ------- | -------- |
| Invalid recipient | Skip; `skipped_invalid_recipient`; no retry |
| DB transient error | Queue job retries (Laravel default backoff) |
| Duplicate intent | Idempotent success |
| Malformed intent | Reject at DTO validation; no partial write |

### Latency-sensitive path

| Priority | Path |
| -------- | ---- |
| `urgent` | `notifications-urgent` queue or synchronous `deliver()` |
| `standard` | `notifications` queue |

Reserve promotion (`reserve_promoted`) MUST use `urgent` (SC-006, FR-012).

---

## 3. DOMAIN_AND_QUERY_MODEL

See [data-model.md](./data-model.md) for full schema.

| Model | Purpose |
| ----- | ------- |
| **Notification** entity | Domain aggregate — delivered inbox item |
| **NotificationIntentDto** | Transient inbound command |
| **NotificationProjectionDto** | Read model for inbox |
| **Inbox query** | `recipient_employee_id` + `archived_at IS NULL` + optional unread filter |
| **Read state** | `read_at` null = unread; set on markRead |
| **Entity reference** | `entity_type` + `entity_id` + optional `deep_link_route` |

---

## 4. OPEN_ITEM_RESOLUTION

| ID | Resolution | Artifact |
| -- | ---------- | -------- |
| **UD-09** | **RESOLVED** — `NotificationIntentDto` with required `recipientEmployeeId`; dedup key `(correlationId, recipientEmployeeId, notificationType)`; upstream resolves roles | [contracts/notification-intent-dto.md](./contracts/notification-intent-dto.md) |
| **UD-10** | **RESOLVED** — CheckIn owns daily 09:00 Asia/Tehran scheduler; emits intents for internal check-in tomorrow; Notification delivers only | [contracts/check-in-reminder-scheduler-port.md](./contracts/check-in-reminder-scheduler-port.md) |
| **UD-11** | **RESOLVED** — 24-month default active inbox; soft-archive via `archived_at`; setting `notification.retention_months`; no hard delete v1 | [data-model.md](./data-model.md) § Retention |

---

## Requirement Grouping (Planning Clusters)

| Cluster | Name | User stories | FRs |
| ------- | ---- | ------------ | --- |
| **PC-01** | Intent ingestion & validation | US1, US4 | FR-004, FR-010, FR-011 |
| **PC-02** | Delivery & deduplication | US1, US4 | FR-008, FR-012 |
| **PC-03** | Inbox persistence | US1, US2 | FR-001, FR-005, FR-006, FR-013, FR-015 |
| **PC-04** | Inbox read & read state | US2 | FR-002, FR-003, FR-014 |
| **PC-05** | Entity reference & deep link | US3 | FR-007 |
| **PC-06** | BR-09.1 trigger coverage | US1, US5 | FR-009 |
| **PC-07** | Retention & archival | — | FR-013, UD-11 |
| **PC-08** | Boundary enforcement | all | R9, architecture tests |

---

## 5. RISK_AND_CONSTRAINTS

| Risk | Mitigation |
| ---- | ---------- |
| **Duplicate delivery** | DB unique constraint + idempotent action |
| **Cross-domain leakage** | `NotificationBoundaryTest` — no upstream Infrastructure imports |
| **Storage / retention pressure** | 24-month archive; indexed inbox; archived excluded from default list |
| **Urgent timing** | Dedicated urgent queue; no batch window for `priority=urgent` |
| **Invalid recipient spam** | Skip with recorded status; no inbox row for hard invalid |
| **Lottery burst volume** | Async queue; Horizon workers; dedup prevents retry duplicates |
| **Closed spec modification** | Adapters only — no changes to spec07/08 modules without new authorization |

---

## 6. NEXT_STATE

| Field | Value |
| ----- | ----- |
| **ready_for_tasks** | **yes** |
| **ready_for_governance_nomination** | **yes** |
| **required_next_action** | `/speckit-tasks` → governance nomination → Design Approval (if required) → Implementation Authorization |

---

## Project Structure

### Documentation (this feature)

```text
specs/009-notification-delivery/
├── spec.md
├── plan.md              # This file
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── notification-intent-dto.md
│   ├── notification-delivery-contract.md
│   ├── notification-inbox-read-contract.md
│   ├── check-in-reminder-scheduler-port.md
│   └── employee-existence-read-port.md
└── tasks.md             # /speckit-tasks (not yet created)
```

---

## Dependencies

| Module | Relationship |
| ------ | ------------ |
| spec01 Foundation | Platform, queue, migrations |
| spec02 Identity | Auth context for inbox (presentation) |
| spec03 Employee | `EmployeeExistenceReadPort` supplier |
| spec05–08 | Upstream intent producers (closed modules — adapter wiring) |
| spec10 Audit | Adjacent — not implemented here |
| spec11 Reporting | Optional read consumer later |

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `context-map.md` R9 | Downstream consumer |
| `spec08-implementation-closure.md` | spec08 closed; voucher intents available |
| Constitution §8.1–8.2 | In-app only |
| Discovery BR-09.1, FR-09 | Trigger catalog |

**Planning authority only.** Implementation requires separate authorization record.

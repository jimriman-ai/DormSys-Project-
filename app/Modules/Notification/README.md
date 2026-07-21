# Notification Module

In-app notification delivery and inbox state (spec09 Wave 1 + WP-WF-05).

## Scope (Wave 1)

- Intent ingestion via `NotificationDeliveryContract`
- Idempotent delivery on `(correlationId, recipientEmployeeId, notificationType)`
- Recipient-scoped inbox read and mark-read via Application contracts
- Async delivery via `SendNotificationJob` (`notifications` / `notifications-urgent` queues)

## WP-WF-05 (Request approval notifications)

- Type: `request_approval_pending` added
- Integration subscriber (not Notification Infrastructure→Workflow imports): `app/Integrations/Notification/*`
- Dual-source terminal events deduped via stable correlation keys
- Stage-1 pending/submitted next-approver only (no role fan-out)

## Boundaries

- **R9:** Downstream consumer only — no upstream Infrastructure imports
- **EmployeeExistenceReadPort:** stub adapter until spec03 live supplier is wired

## Deferred (Wave 2+)

- Deep-link feature tests (US3), explicit idempotency concurrency tests (US4)
- Check-in reminder delivery slice (US5), retention archive job, architecture boundary test
- S2–S4 pending fan-out (out of WP-WF-05 C1)

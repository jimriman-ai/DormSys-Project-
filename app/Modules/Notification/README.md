# Notification Module

In-app notification delivery and inbox state (spec09 Wave 1).

## Scope (Wave 1)

- Intent ingestion via `NotificationDeliveryContract`
- Idempotent delivery on `(correlationId, recipientEmployeeId, notificationType)`
- Recipient-scoped inbox read and mark-read via Application contracts
- Async delivery via `SendNotificationJob` (`notifications` / `notifications-urgent` queues)

## Boundaries

- **R9:** Downstream consumer only — no upstream Infrastructure imports
- **EmployeeExistenceReadPort:** stub adapter until spec03 live supplier is wired

## Deferred (Wave 2+)

- Deep-link feature tests (US3), explicit idempotency concurrency tests (US4)
- Check-in reminder delivery slice (US5), retention archive job, architecture boundary test

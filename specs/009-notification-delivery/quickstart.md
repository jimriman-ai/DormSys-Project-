# Quickstart: Notification Delivery (spec09)

**Purpose:** Validation scenarios for implementation and feature tests — not runnable until authorized.

**Prerequisites:** spec09 Implementation Authorization; `sail up`; migrations applied.

---

## Scenario 1 — Deliver notification from intent (US1)

1. Build `NotificationIntentDto` with type `request_approved`, valid `recipientEmployeeId`, unique `correlationId`.
2. Call `NotificationDeliveryContract::deliver($intent)`.
3. **Expect:** `status = delivered`; row in `notification_logs`; `read_at` null.
4. Call `deliver()` again with same intent.
5. **Expect:** `status = duplicate`; single row.

---

## Scenario 2 — Inbox list and mark read (US2)

1. Deliver two intents for same recipient (different correlation ids).
2. Call `NotificationInboxReadContract::listForRecipient($employeeId)`.
3. **Expect:** 2 items; unread count = 2.
4. Call `MarkNotificationReadContract::markRead($id, $employeeId, now)`.
5. **Expect:** `read_at` set; unread count = 1.
6. List with `unreadOnly = true` → 1 item.

---

## Scenario 3 — Recipient isolation (FR-014)

1. Deliver notification for employee A.
2. Attempt `findByIdForRecipient($id, employeeB)`.
3. **Expect:** null or access denied.

---

## Scenario 4 — Invalid recipient skip (FR-010)

1. Deliver intent with non-existent `recipientEmployeeId` (stub returns false).
2. **Expect:** `status = skipped`; no inbox row OR row with `skipped_invalid_recipient`.

---

## Scenario 5 — Urgent delivery path (SC-006)

1. Deliver `reserve_promoted` intent with `priority = urgent`.
2. **Expect:** Processed on urgent queue path; visible in inbox without batch delay (assert via sync test or queue fake).

---

## Scenario 6 — Architecture boundary (R9)

```bash
sail artisan test tests/Architecture/NotificationBoundaryTest.php
```

**Expect:** Notification module does not import Request/Lottery/Allocation/Voucher/CheckIn Infrastructure.

---

## Scenario 7 — Check-in reminder intent (US5 / UD-10)

1. Run `ScheduleCheckInRemindersJob` with synthetic internal allocation fixture (check-in tomorrow).
2. **Expect:** `check_in_reminder` intent delivered to employee.
3. External voucher fixture → no reminder intent.

---

## References

- [data-model.md](./data-model.md)
- [contracts/notification-intent-dto.md](./contracts/notification-intent-dto.md)
- [contracts/notification-delivery-contract.md](./contracts/notification-delivery-contract.md)

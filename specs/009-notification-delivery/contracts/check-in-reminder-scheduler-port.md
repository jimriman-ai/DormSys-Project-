# Port: Check-In Reminder Scheduler (UD-10)

**Version:** 1.0.0  
**Spec:** spec09 Notification Delivery (consumer) / spec07 CheckIn (producer)  
**Direction:** CheckIn → Notification (R9)  
**Status:** Planning — design baseline

---

## Purpose

Defines **scheduling ownership** for BR-09.1 check-in reminders. **CheckIn** (operational context, CD-015) owns **when** to emit reminder intents. **Notification** owns **delivery only**.

---

## Scheduling rules (resolved UD-10)

| Rule | Value |
| ---- | ----- |
| **Owner** | CheckIn module (Application layer scheduler job) |
| **Cadence** | Daily at **09:00 Asia/Tehran** via Laravel Scheduler |
| **Selection** | Internal allocations with **check-in date = tomorrow** (Tehran calendar day) |
| **Exclusion** | External voucher paths — no reminder intents |
| **Storage** | All timestamps UTC in PostgreSQL |
| **Display** | Jalali conversion at presentation (`morilog/jalali`) |
| **Correlation** | `check_in:{allocation_id}:reminder:{check_in_date}` |

---

## Flow

```
ScheduleCheckInRemindersJob (CheckIn Infrastructure)
    → query internal allocation read port (CheckIn/Allocation contract — no Notification DB access)
    → for each eligible allocation:
        → build NotificationIntentDto (type: check_in_reminder, priority: standard)
        → NotificationDeliveryContract::deliver()
```

---

## Interface (CheckIn-owned scheduler)

**Namespace:** `App\Modules\CheckIn\Application\Contracts\CheckInReminderSchedulePort` (optional indirection for tests)

Production: concrete job `ScheduleCheckInRemindersJob` registered in `CheckInServiceProvider` / `routes/console.php`.

---

## Notification module responsibility

| In scope | Out of scope |
| -------- | ------------ |
| Accept `check_in_reminder` intents | Scan allocation tables |
| Idempotent delivery | Decide eligibility for internal vs external |
| Persist inbox record | Schedule cron timing |

---

## Stub path (Wave 1)

Until live Allocation/CheckIn read ports wired: scheduler may use **synthetic allocation facts** in tests only; production wiring deferred to integration wave with spec07 read adapters.

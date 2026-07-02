# Tasks: Notification Delivery (spec09)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md)

**Branch**: `009-notification-delivery`

**Decomposition type**: Plan → behavior-level tasks (implementation-neutral where noted; file paths in completion criteria)

**Scope guards** (unchanged from spec / plan):

- **R9:** Notification consumes upstream **notification intents** only — no direct upstream repository access
- **OA-09-01:** Notification owns delivery and inbox state; upstream owns business policy and intent emission
- **OA-09-02:** In-app (database) channel only — no email/SMS/push
- **No** Audit storage (spec10), Reporting projections (spec11), presentation UI (OA-09-05 deferred)
- **No** modification of closed programs (spec07, spec08) — upstream adapter wiring via ports/stubs only
- **UD-09, UD-10, UD-11:** Resolved at planning — see [plan.md](./plan.md) §4
- **Not** Design Approval · **Not** Implementation Authorization

**Status**: Wave 1 implementation complete (T001–T020); Wave 2 implementation complete (T021–T026); Wave 3 implementation complete (T027–T032); spec09 program **CLOSED**

---

## 1. TASK_GROUPING

Derived from [plan.md](./plan.md) planning clusters (PC-01–PC-08). No new clusters introduced.

| Batch | Plan cluster | User stories | Purpose |
| ----- | ------------ | ------------ | ------- |
| **B0** | Setup | — | Module foundation, schema, contracts |
| **B1** | PC-01, PC-02, PC-03, PC-06 | US1, US4 | Intent ingestion, delivery, deduplication, BR-09.1 types |
| **B2** | PC-04 | US2 | Inbox read and read/unread state |
| **B3** | PC-05 | US3 | Entity reference and deep-link persistence |
| **B4** | PC-06 | US5 | Check-in reminder intent delivery (scheduler owned by CheckIn) |
| **B5** | PC-07 | — | Retention and archival (UD-11) |
| **B6** | PC-08 | All | Boundary enforcement and quality gates |

| Phase | Batch(es) | Task IDs | Spec priority |
| ----- | --------- | -------- | ------------- |
| 1 — Foundation | B0 | T001–T008 | Blocks all stories |
| 2 — US1 Lifecycle delivery | B1 | T009–T015 | P1 |
| 3 — US2 Inbox & read state | B2 | T016–T020 | P1 |
| 4 — US3 Deep link | B3 | T021–T022 | P2 |
| 5 — US4 Idempotency verification | B1 | T023–T024 | P2 |
| 6 — US5 Check-in reminder | B4 | T025–T026 | P3 |
| 7 — Retention | B5 | T027–T029 | Cross-cutting |
| 8 — Boundary & closeout | B6 | T030–T032 | Cross-cutting |

**Total tasks:** 32

**MVP scope (recommended Wave 1 authorization):** Phases 1–3 — **T001–T020** (foundation + US1 + US2)

**Post-MVP:** Phases 4–8 — **T021–T032**

---

## 2. TASK_LIST

### Phase 1 — Foundation (B0)

**Goal**: Module scaffold, persistence schema, domain vocabulary, inbound/outbound contracts, DI registration.

**Independent test**: Migration runs; `NotificationDeliveryContract` resolvable from container; dedup unique index exists.

- [x] T001 Register `notification_logs` migration under `database/migrations/modules/notification/` per [data-model.md](./data-model.md) — UUID PK, dedup unique on `(correlation_id, recipient_employee_id, notification_type)`, inbox indexes — migration up/down succeeds
- [x] T002 Implement domain enums `NotificationType`, `DeliveryPriority`, `DeliveryStatus` in `app/Modules/Notification/Domain/Enums/` covering BR-09.1 vocabulary
- [x] T003 [P] Implement domain value objects `CorrelationId`, `EntityReference`, `NotificationId` in `app/Modules/Notification/Domain/ValueObjects/`
- [x] T004 Implement domain aggregate `Notification` in `app/Modules/Notification/Domain/Models/Notification.php` — read/archive state, markRead behavior; pure domain only
- [x] T005 [P] Implement `NotificationIntentDto`, `NotificationDeliveryResultDto`, `NotificationProjectionDto` in `app/Modules/Notification/Application/DTOs/` per [contracts/notification-intent-dto.md](./contracts/notification-intent-dto.md) — **UD-09**
- [x] T006 Define `NotificationDeliveryContract`, `NotificationInboxReadContract`, `MarkNotificationReadContract`, `EmployeeExistenceReadPort` in `app/Modules/Notification/Application/Contracts/` — matches `contracts/` artifacts
- [x] T007 Implement `NotificationRepository` + `NotificationModel` in Infrastructure; map to domain aggregate — CRUD, dedup lookup, inbox queries
- [x] T008 Register bindings in `app/Modules/Notification/Infrastructure/Providers/NotificationServiceProvider.php`; load module migrations

**Checkpoint**: Foundation schema and contracts verifiable. — **CP-W0**: PASS

---

### Phase 2 — User Story 1: Lifecycle Event In-App Alert (B1: PC-01–PC-03, PC-06) — P1

**Goal**: Deliver in-app notifications from upstream intents with recipient validation and priority routing.

**Independent test**: Supply `NotificationIntentDto` for `request_approved` → persisted unread notification for recipient — **SC-001**.

- [x] T009 [US1] Implement `DeliverNotificationAction` in `app/Modules/Notification/Application/Services/` — validate intent, invoke `EmployeeExistenceReadPort`, persist notification — **FR-001**, **FR-004**, **FR-011**
- [x] T010 [US1] Enforce idempotent delivery on `(correlationId, recipientEmployeeId, notificationType)` — return duplicate result without second row — **FR-008**, **SC-002**
- [x] T011 [US1] Bind `NotificationDeliveryContract` → `DeliverNotificationAction` — **FR-004**
- [x] T012 [US1] Implement `SendNotificationJob` with queue routing: `standard` → `notifications`, `urgent` → `notifications-urgent` — **FR-012**, **SC-006**
- [x] T013 [US1] Skip delivery with `skipped_invalid_recipient` when `EmployeeExistenceReadPort` returns false — **FR-010**
- [x] T014 [P] [US1] Implement `StubEmployeeExistenceReadAdapter` in Infrastructure for tests — **FR-010**
- [x] T015 [US1] Feature tests in `tests/Feature/Modules/Notification/NotificationDeliveryTest.php` — deliver `request_approved`, `allocation_successful`, `lottery_winner`, `reserve_promoted` (urgent) intents — **FR-009**, **BR-09.1**

**Checkpoint**: US1 independently testable — delivery without inbox UI. — **CP-W1**: PASS

---

### Phase 3 — User Story 2: Notification Inbox and Read State (B2: PC-04) — P1

**Goal**: Recipient-scoped inbox listing and read/unread tracking.

**Independent test**: Two notifications for one recipient → list → mark one read → unread count decreases — **SC-003**, **SC-004**.

- [x] T016 [P] [US2] Implement `NotificationInboxReadService` implementing `NotificationInboxReadContract` — list, findById, countUnread — **FR-003**, **FR-014**
- [x] T017 [US2] Implement `MarkNotificationReadAction` implementing `MarkNotificationReadContract` — set `read_at` UTC — **FR-002**
- [x] T018 [US2] Scope all inbox queries to `recipient_employee_id`; exclude `archived_at IS NOT NULL` from default list — **FR-014**, **UD-11**
- [x] T019 [US2] Feature tests in `tests/Feature/Modules/Notification/NotificationInboxTest.php` — list, unread filter, mark read — **FR-002**, **FR-003**
- [x] T020 [US2] Feature test: recipient isolation — employee B cannot read employee A notifications — **FR-014**, **SC-003**

**Checkpoint**: US2 independently testable — inbox without presentation UI. — **CP-W2**: PASS

---

### Phase 4 — User Story 3: Deep Link to Source Context (B3: PC-05) — P2

**Goal**: Persist and project entity references for navigation.

**Independent test**: Intent with `entityType`, `entityId`, `deepLinkRoute` → projection returns navigable reference — **SC-005**.

- [x] T021 [US3] Persist `entity_type`, `entity_id`, `deep_link_route` on delivery; include in `NotificationProjectionDto` — **FR-007**
- [x] T022 [US3] Feature tests in `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php` — delivery with/without link; read does not mutate upstream — **FR-007**, **FR-011**

**Checkpoint**: US3 independently testable. — **CP-W3**: PASS

---

### Phase 5 — User Story 4: Idempotent Delivery (B1 verification) — P2

**Goal**: Explicit verification of duplicate protection under replay and concurrency.

**Independent test**: Duplicate intent → single inbox row — **SC-002**.

- [x] T023 [US4] Feature test: duplicate `correlationId` replay returns `duplicate` status — **FR-008**, **SC-002**
- [x] T024 [US4] Feature test: concurrent duplicate delivery safe via unique constraint (no duplicate rows) — **FR-008**

**Checkpoint**: US4 independently testable. — **CP-W4**: PASS

---

### Phase 6 — User Story 5: Check-In Reminder (B4: PC-06, UD-10) — P3

**Goal**: Notification delivers `check_in_reminder` intents; scheduling remains CheckIn-owned.

**Independent test**: Synthetic `check_in_reminder` intent → delivered to employee inbox.

**Note:** `ScheduleCheckInRemindersJob` lives in **CheckIn** module per [contracts/check-in-reminder-scheduler-port.md](./contracts/check-in-reminder-scheduler-port.md) — **not** spec09 implementation scope. spec09 accepts and delivers the intent type only.

- [x] T025 [US5] Support `check_in_reminder` in `NotificationType` delivery path with standard priority — **FR-009**, **BR-09.1**
- [x] T026 [US5] Feature test: deliver synthetic `check_in_reminder` intent with correlation `check_in:{allocation_id}:reminder:{date}` — **UD-10** delivery slice

**Checkpoint**: US5 delivery slice testable (scheduler integration deferred). — **CP-W5**: PASS

---

### Phase 7 — Retention & Archival (B5: PC-07, UD-11)

**Goal**: Soft-archive notifications after retention window; no hard delete in v1.

**Independent test**: Notification older than retention → `archived_at` set; excluded from default inbox.

- [x] T027 Implement `ArchiveExpiredNotificationsJob` in `app/Modules/Notification/Infrastructure/Jobs/` — read `notification.retention_months` (default 24) — **FR-013**, **UD-11**
- [x] T028 Register daily schedule for archive job in module or `routes/console.php` — **UD-11**
- [x] T029 Feature test in `tests/Feature/Modules/Notification/NotificationRetentionTest.php` — archive excludes from inbox list — **FR-013**

**Checkpoint**: Retention path verifiable. — **CP-W6**: PASS

---

### Phase 8 — Boundary & Closeout (B6: PC-08)

**Goal**: Enforce R9; quality gates; task/authorization readiness.

**Independent test**: Architecture test passes; PHPStan/Pint clean on Notification module.

- [x] T030 Implement `tests/Architecture/NotificationBoundaryTest.php` — no imports from Request/Lottery/Allocation/Voucher/CheckIn Infrastructure; inbox read depends only on Notification contracts — **R9**, **PC-08**
- [x] T031 Run PHPStan level 8 on `app/Modules/Notification/` — zero errors — Definition of Done
- [x] T032 Run Laravel Pint on `app/Modules/Notification/` and related tests — zero violations — Definition of Done

**Checkpoint**: Boundary and quality gates pass. — **CP-W7**: PASS

---

## 3. FR_MAPPING

| FR | Task IDs | Plan cluster |
| -- | -------- | ------------ |
| FR-001 | T009, T015 | PC-03 |
| FR-002 | T017, T019 | PC-04 |
| FR-003 | T016, T019 | PC-04 |
| FR-004 | T009, T011 | PC-01 |
| FR-005 | T002, T015 | PC-03 |
| FR-006 | T009 | PC-03 |
| FR-007 | T021, T022 | PC-05 |
| FR-008 | T010, T023, T024 | PC-02 |
| FR-009 | T015, T025, T026 | PC-06 |
| FR-010 | T013, T014 | PC-01 |
| FR-011 | T009, T022 | PC-01 |
| FR-012 | T012, T015 | PC-02 |
| FR-013 | T027, T029 | PC-07 |
| FR-014 | T016, T018, T020 | PC-04 |
| FR-015 | T009 | PC-03 |

### Success criteria mapping

| SC | Task IDs |
| -- | -------- |
| SC-001 | T009, T015 |
| SC-002 | T010, T023, T024 |
| SC-003 | T020 |
| SC-004 | T019 |
| SC-005 | T021, T022 |
| SC-006 | T012, T015 |

---

## 4. DEPENDENCY_RELATIONS

```text
Phase 1 (T001–T008)
    └── blocks ──► Phase 2 US1 (T009–T015)
                        ├──► Phase 3 US2 (T016–T020)
                        ├──► Phase 4 US3 (T021–T022)
                        ├──► Phase 5 US4 (T023–T024)
                        └──► Phase 6 US5 (T025–T026)
    └── blocks ──► Phase 7 Retention (T027–T029)
    └── blocks ──► Phase 8 Boundary (T030–T032)
```

| Task | Depends on | Reason |
| ---- | ---------- | ------ |
| T009–T015 | T001–T008 | Delivery requires schema, contracts, repository |
| T016–T020 | T009–T011 | Inbox requires deliverable notifications |
| T021–T022 | T009 | Deep link fields on delivery path |
| T023–T024 | T010 | Dedup implementation |
| T025–T026 | T002, T009 | Reminder type + delivery |
| T027–T029 | T001, T018 | Archive uses schema + inbox filter |
| T030–T032 | T001–T029 | Boundary/quality after behavior complete |

**Parallel opportunities:**

| Phase | Parallel tasks |
| ----- | -------------- |
| 1 — Foundation | T002, T003 (after T001 migration drafted) |
| 2 — US1 | T014 |
| 3 — US2 | T016 |

---

## 5. WAVE_MAP (governance planning)

| Wave | Task range | Scope | Entry | Exit |
| ---- | ---------- | ----- | ----- | ---- |
| **Wave 1 (MVP)** | T001–T020 | Foundation + US1 + US2 | T001 | CP-W2 PASS |
| **Wave 2** | T021–T026 | US3 + US4 + US5 delivery slice | T021 | CP-W5 PASS |
| **Wave 3** | T027–T032 | Retention + boundary + quality | T027 | CP-W7 PASS |

---

## 6. COVERAGE_MATRIX (plan → tasks)

| Capability | Tasks |
| ---------- | ----- |
| Idempotent delivery | T010, T023, T024 |
| Urgent queue path | T012, T015 |
| Recipient validation | T013, T014 |
| Read/unread marking | T017, T019 |
| Entity reference / deep link | T021, T022 |
| Retention / archive | T027, T028, T029 |
| R9 boundary enforcement | T030 |
| Duplicate protection tests | T023, T024 |
| Recipient isolation tests | T020 |
| BR-09.1 notification types | T002, T015, T025 |
| Persian content pass-through | T005, T009 (store as received) |
| No upstream repo access | T030 |
| CheckIn scheduler (UD-10) | Out of spec09 — CheckIn module; T025–T026 delivery only |

---

**End of tasks.**

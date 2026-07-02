# Tasks: Audit Trail & Traceability (spec10)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md)

**Branch**: `010-audit-trail`

**Decomposition type**: Plan → behavior-level tasks (implementation-neutral where noted; file paths in completion criteria)

**Scope guards** (unchanged from spec / plan):

- **R10:** Audit consumes upstream **audit entry DTOs** only — no direct upstream repository reads
- **AP-06:** Append-only `audit_logs`; all writes via **AuditService** / `AuditRecordingContract`
- **OA-10-05:** Presentation UI (Livewire audit explorer) **deferred**
- **R-08:** Notification delivery audit events **deferred** — out of MVP
- **No** Reporting projections (spec11), SIEM integration, audit UPDATE/DELETE APIs
- **No** modification of closed programs (spec07, spec08, spec09) beyond **minimal contract adapter seams** explicitly listed in Wave 4
- **UD-10-01 … UD-10-06:** Resolved at planning — see [plan.md](./plan.md) §4
- **Not** Design Approval · **Not** Implementation Authorization

**Status**: **PROGRAM CLOSED / FROZEN** — T001–T040 **COMPLETE** (40/40). Waves 1A–3 **CLOSED**. Checkpoints **CP-A1** → **CP-A5** + **CP-A4.1** = **PASS**. Canonical closure: [`.specify/docs/handoff/spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md). `lifecycle_state: CLOSED` · `immutable_status: FROZEN` · `execution_state: NONE` · `active_authorization: NONE` · `reopenability: FORBIDDEN WITHOUT NEW GOVERNANCE` · `archival_reference_status: CANONICAL`. T041+ **not defined**.

---

## 1. TASK_GROUPING

Derived from [plan.md](./plan.md) planning clusters (PC-01–PC-08) and migration phases (M0–M4). No new clusters introduced.

| Batch | Plan cluster | User stories | Purpose |
| ----- | ------------ | ------------ | ------- |
| **B0** | Setup | — | Module foundation, schema, contracts |
| **B1** | PC-01, PC-02, PC-03 | US1, US3, US4 | Recording, idempotency, after-commit, system actors |
| **B2** | PC-04 | US2 | Authorized audit history query |
| **B3** | PC-08 | US3 | R10 boundary, contract verification |
| **B4** | PC-05, PC-07 | US1 | Initial upstream producer adapters (M1 slice) |
| **B5** | PC-06, PC-07 | — | Retention archival, optional activity bridge (M2), closeout |

| Phase | Batch(es) | Task IDs | Governance wave |
| ----- | --------- | -------- | --------------- |
| 1 — Foundation | B0 | T001–T007 | Wave 1 |
| 2 — US1 Critical recording | B1 | T008–T014 | Wave 1 |
| 3 — US2 Authorized read | B2 | T015–T021 | Wave 2 |
| 4 — US3 Boundary & idempotency | B3 | T022–T027 | Wave 3 |
| 5 — Upstream integration slice | B4 | T028–T032 | Wave 4 |
| 6 — Retention & bridge | B5 (partial) | T033–T036 | Wave 5 |
| 7 — Quality & closeout | B5 | T037–T040 | Wave 5 |

**Total tasks:** 40

**MVP scope (recommended Wave 1 authorization):** Phases 1–3 — **T001–T021** (foundation + recording + authorized read)

**Post-MVP:** Phases 4–7 — **T022–T040**

---

## 2. TASK_LIST

### Phase 1 — Foundation (B0) — Wave 1

**Goal**: Module scaffold, `audit_logs` schema, domain vocabulary, inbound/outbound contracts, append-only repository, DI registration.

**Independent test**: Migration runs; `AuditRecordingContract` resolvable; unique index on `correlation_id` exists.

- [x] T001 Register `audit_logs` migration under `database/migrations/modules/audit/` per [data-model.md](./data-model.md) — UUID PK, **no** `updated_at`, unique `correlation_id`, entity/actor/event indexes — migration up/down succeeds
- [x] T002 Implement domain enums `AuditEventType`, `ActorType` in `app/Modules/Audit/Domain/Enums/` covering AP-06 vocabulary per [data-model.md](./data-model.md) — **UD-10-01**
- [x] T003 [P] Implement domain value objects `CorrelationId`, `EntityReference`, `ActorReference`, `AuditLogId` in `app/Modules/Audit/Domain/ValueObjects/`
- [x] T004 Implement domain aggregate `AuditLog` in `app/Modules/Audit/Domain/Models/AuditLog.php` — immutable semantics; pure domain only
- [x] T005 [P] Implement `AuditEntryDto`, `AuditRecordResultDto`, `AuditHistoryQuery`, `AuditHistoryItemDto` in `app/Modules/Audit/Application/DTOs/` per [contracts/audit-entry-dto.md](./contracts/audit-entry-dto.md) — **UD-10-01**
- [x] T006 Define `AuditRecordingContract`, `AuditHistoryReadContract`, `AuditAuthorizationPort` in `app/Modules/Audit/Application/Contracts/` — matches `contracts/` artifacts
- [x] T007 Implement `AuditLogModel` + `AuditLogRepository` in `app/Modules/Audit/Infrastructure/` — **insert + find only**; no `update()`/`delete()` public methods — **FR-002**

**Checkpoint**: **CP-A1** — foundation complete (schema, contracts, append-only repository).

| CP-A1 pass criteria |
| ------------------- |
| Migration applies cleanly |
| `AuditRecordingContract` bound in container |
| Repository exposes no mutating APIs |
| Unique index on `correlation_id` present |

---

### Phase 2 — User Story 1 & 3/4 Recording Core (B1: PC-01–PC-03) — Wave 1 — P1

**Goal**: Record critical audit entries via `AuditService` with after-commit persistence, payload-hash idempotency, and system actor support.

**Independent test**: Supply `AuditEntryDto` for `request.approved` → immutable row with actor, entity, event, snapshots — **SC-001**, **SC-005**.

- [x] T008 [US1] Implement `PayloadHashCalculator` in `app/Modules/Audit/Application/Services/` — canonical SHA-256 per [contracts/audit-entry-dto.md](./contracts/audit-entry-dto.md) — **UD-10-05**
- [x] T009 [US1] Implement `RecordAuditAction` in `app/Modules/Audit/Application/Services/` — validate DTO, compute hash, **after-commit** persist via `DB::afterCommit()` — **UD-10-04**, **FR-001**, **FR-011**
- [x] T010 [US3] Enforce idempotent accept on duplicate `correlationId` + matching `payload_hash`; throw `AuditDuplicateConflictException` on hash mismatch — **UD-10-05**, **FR-008** (dedup semantics)
- [x] T011 [US1] Bind `AuditRecordingContract` → `RecordAuditAction` in `app/Modules/Audit/Infrastructure/Providers/AuditServiceProvider.php`; load module migrations — **FR-001**
- [x] T012 [US1] Implement snapshot size guard (64 KiB) with `metadata.snapshot_truncated` per [research.md](./research.md) R-07 — **FR-004**, **FR-005**
- [x] T013 [US1] Feature tests in `tests/Feature/Modules/Audit/AuditRecordingTest.php` — record `request.approved`, `allocation.created`, `lottery.executed` entries — **FR-006**, **SC-001**
- [x] T014 [US4] Feature test: `actorType=system`, `actorId=system:lottery_draw` persisted and queryable — [contracts/system-actor-tokens.md](./contracts/system-actor-tokens.md), **FR-008**

**Checkpoint**: **CP-A2** — recording flow verified.

| CP-A2 pass criteria |
| ------------------- |
| Valid DTO creates `audit_logs` row |
| After-commit: rolled-back domain tx leaves no audit row |
| Duplicate correlation + same hash returns `duplicate` |
| System and user actors persist correctly |
| No upstream domain mutation from Audit module |

---

### Phase 3 — User Story 2: Authorized Audit History Review (B2: PC-04) — Wave 2 — P1

**Goal**: Paginated, authorized audit history queries by entity, actor, event, and time range.

**Independent test**: Multiple entries → query by entity as authorized role → results DESC; unauthorized user denied — **SC-003**, **SC-004**.

- [x] T015 [P] [US2] Implement `AuditAuthorizationAdapter` implementing `AuditAuthorizationPort` — checks Spatie permission `audit.read` — **UD-10-06**, **FR-009**
- [x] T016 [US2] Grant `audit.read` to roles `Administrator`, `DormMgr`, `HRMgr` in Identity permission seeder (`database/seeders/` or Identity module seeder) — **UD-10-06**
- [x] T017 [US2] Implement `QueryAuditHistoryAction` in `app/Modules/Audit/Application/Services/` — filters: entity, actor, event types, date range, pagination — **FR-010**
- [x] T018 [US2] Implement `AuditHistoryReadService` implementing `AuditHistoryReadContract` — invokes authorization port before query — **FR-009**, **FR-010**
- [x] T019 [US2] Default query excludes `archived_at IS NOT NULL` unless `includeArchived=true` — **UD-10-03**
- [x] T020 [US2] Feature tests in `tests/Feature/Modules/Audit/AuditHistoryReadTest.php` — entity history, actor filter, pagination, empty result — **SC-003**
- [x] T021 [US2] Feature test: user without `audit.read` denied — no row leakage — **SC-004**, **FR-009**

**Checkpoint**: **CP-A3** — read/query layer verified.

| CP-A3 pass criteria |
| ------------------- |
| Authorized query returns filtered paginated results |
| Unauthorized query returns 403 / exception |
| Archived rows excluded by default |
| Query performance acceptable on seeded 1k+ rows (smoke) |

---

### Phase 4 — User Story 3: Uniform Emission & Boundary (B3: PC-08) — Wave 3 — P2

**Goal**: R10 architecture enforcement, idempotency/conflict coverage, immutability verification.

**Independent test**: Architecture test passes; duplicate/conflict/rollback scenarios per [quickstart.md](./quickstart.md).

- [x] T022 [P] [US3] Implement `tests/Architecture/AuditBoundaryTest.php` — no imports from Request/Lottery/Allocation/Voucher/CheckIn/Notification Infrastructure — **R10**, **PC-08**
- [x] T023 [US3] Feature test in `tests/Feature/Modules/Audit/AuditIdempotencyTest.php` — duplicate replay returns single row — **UD-10-05**
- [x] T024 [US3] Feature test: conflicting payload same `correlationId` throws conflict; single row preserved — **UD-10-05**
- [x] T025 [US3] Feature test: domain transaction rollback → no audit row (production after-commit path) — **UD-10-04**
- [x] T026 [US3] Feature test: application cannot update/delete `audit_logs` via repository or model — **FR-002**, **SC-002**
- [x] T027 [P] [US3] Unit test: `AuditRecordingContract` rejects DTO missing required fields — **FR-014**

**Checkpoint**: **CP-A4** — boundary and idempotency enforced.

| CP-A4 pass criteria |
| ------------------- |
| `AuditBoundaryTest` PASS |
| Idempotency + conflict tests PASS |
| After-commit rollback test PASS |
| Immutability test PASS |

---

### Phase 5 — Initial Upstream Integration Slice (B4: PC-05, M1) — Wave 4 — P2

**Goal**: Contract-based producer wiring for **approved** critical operations only — adapter seams, no closed-program rework beyond listed files.

**Governance note:** Wave 4 tasks touching **Voucher** (spec08 closed) are **adapter-only** — invoke `AuditRecordingContract` from existing transition recording path; no lifecycle logic changes. Requires explicit Wave 4 authorization if split from Wave 1–3.

**Independent test**: Upstream action → `audit_logs` row via contract; Audit module still has no upstream Infrastructure imports.

- [x] T028 [US1] Identity: emit `identity.role_changed` / user lifecycle audit entries via `AuditRecordingContract` from `app/Modules/Identity/Application/` actions (e.g. create/disable user) — **minimal seam**, spec02 scope
- [x] T029 [P] [US1] Voucher: add `AuditRecordingContract` adapter in `app/Modules/Voucher/Application/` (or Infrastructure adapter) mapping existing material transition records to `AuditEntryDto` — **closed-program adapter seam only** — **FR-006** subset
- [x] T030 [US1] Feature test `tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php` — Identity action produces audit row with correct actor — **R10** consumer path
- [x] T031 [P] [US1] Feature test `tests/Feature/Modules/Audit/VoucherAuditIntegrationTest.php` — voucher transition produces audit row — adapter verification
- [x] T032 [US1] Feature test: Audit module recording invoked from test double producer without Audit importing producer Infrastructure — **FR-007**, **R10**

**Risk (Wave 4):** Scope creep into spec07/08/09 business logic — **HALT** if task requires lifecycle changes outside adapter files listed above.

---

### Phase 6 — Retention & Optional Bridge (B5: PC-06, M2) — Wave 5

**Goal**: Soft-archive retention; optional `activity_log` bridge behind feature flag.

**Independent test**: Expired row archived; optional bridge disabled by default.

- [x] T033 Implement `AuditRetentionSettingsReader` — `audit.retention_months` default **84** — **UD-10-03**
- [x] T034 Implement `ArchiveExpiredAuditLogsJob` in `app/Modules/Audit/Infrastructure/Jobs/` — set `archived_at`; no hard delete — **FR-015**, **UD-10-03**
- [x] T035 Register daily schedule `audit:archive-expired` in `routes/console.php` — **UD-10-03**
- [x] T036 Feature test `tests/Feature/Modules/Audit/AuditRetentionTest.php` — archived excluded from default read query — **UD-10-03**

---

### Phase 7 — Migration Bridge & Quality Closeout (B5) — Wave 5

**Goal**: Optional M2 bridge, config flags, PHPStan/Pint, program readiness.

**Independent test**: Full Audit test suite green; static analysis clean.

- [x] T037 [P] Optional `ActivityLogAuditBridge` listener (config `audit.activity_bridge_enabled=false` default) mapping Spatie activity → `AuditEntryDto` — **UD-10-02** M2; disabled in production until explicitly enabled
- [x] T038 Add `config/audit.php` — `recording_enabled`, `sync_in_tests`, `activity_bridge_enabled`, `retention_months` — rollback safety per [plan.md](./plan.md) §2

**Checkpoint**: **CP-A5** — integration/bridge/quality gates complete.

| CP-A5 pass criteria |
| ------------------- |
| Retention job + test PASS |
| `tests/Feature/Modules/Audit/` + `AuditBoundaryTest` PASS |
| PHPStan L8 zero errors on `app/Modules/Audit/` |
| Pint zero violations on Audit module + tests |
| Activity bridge **off** by default |
| `tasks.md` reconciliation note recorded at implementation closeout |

- [x] T039 Run PHPStan level 8 on `app/Modules/Audit/` — zero errors — Definition of Done
- [x] T040 Run Laravel Pint on `app/Modules/Audit/` and `tests/Feature/Modules/Audit/`, `tests/Architecture/AuditBoundaryTest.php` — zero violations — Definition of Done

---

## 3. FR_MAPPING

| FR | Task IDs | Plan cluster |
| -- | -------- | ------------ |
| FR-001 | T009, T011 | PC-01 |
| FR-002 | T007, T026 | PC-03 |
| FR-003 | T013 | PC-01 |
| FR-004 | T012, T013 | PC-01 |
| FR-005 | T012, T013 | PC-01 |
| FR-006 | T013, T028–T031 | PC-05 |
| FR-007 | T022, T032 | PC-08 |
| FR-008 | T014 | PC-01 |
| FR-009 | T015, T018, T021 | PC-04 |
| FR-010 | T017, T020 | PC-04 |
| FR-011 | T009, T013 | PC-01 |
| FR-012 | T002, T013 | PC-01 |
| FR-013 | T037 (bridge) | PC-07 |
| FR-014 | T027 | PC-01 |
| FR-015 | T033–T036 | PC-06 |

### Success criteria mapping

| SC | Task IDs |
| -- | -------- |
| SC-001 | T013, T028–T031 |
| SC-002 | T026 |
| SC-003 | T020 |
| SC-004 | T021 |
| SC-005 | T013, T014 |
| SC-006 | T028–T031 (post-cutover producer wiring) |

---

## 4. DEPENDENCY_RELATIONS

```text
Phase 1 (T001–T007)
    └── blocks ──► Phase 2 Recording (T008–T014) ──► CP-A2
                        └── blocks ──► Phase 3 Read (T015–T021) ──► CP-A3
    └── blocks ──► Phase 4 Boundary (T022–T027) ──► CP-A4
    └── blocks ──► Phase 5 Integration (T028–T032)
    └── blocks ──► Phase 6 Retention (T033–T036)
Phase 2 + 3 ──► recommended before Phase 5 integration producers
Phase 4 ──► should complete before Phase 7 quality (T039–T040) OR run in parallel with Phase 6 if integration deferred
Phase 6–7 ──► T039–T040 after T001–T036
```

### Critical path

`T001 → T007 → T009 → T011 → T013 → T018 → T020 → T022 → T039 → T040`

### Task dependency table

| Task | Depends on | Reason |
| ---- | ---------- | ------ |
| T008–T014 | T001–T007 | Recording requires schema, contracts, repository |
| T015–T021 | T009, T011 | Read layer needs persisted audit rows |
| T022–T027 | T009–T011 | Boundary tests need recording path |
| T028–T032 | T011, T013 | Producers need live `AuditRecordingContract` |
| T033–T036 | T001, T019 | Archive uses schema + read filter |
| T037–T038 | T009 | Bridge forwards to RecordAuditAction |
| T039–T040 | T001–T038 | Quality gates after behavior complete |

### Wave sequencing

| Wave | Phases | Sequential? | Parallel within wave |
| ---- | ------ | ----------- | -------------------- |
| **Wave 1** | 1–2 | **Strict** (2 after 1) | T003, T005 in Phase 1; T014 after T013 |
| **Wave 2** | 3 | After Wave 1 | T015 parallel with T017 prep |
| **Wave 3** | 4 | After Wave 1 (may overlap Wave 2 tail) | T022, T027 |
| **Wave 4** | 5 | After CP-A2 minimum; **recommended** after CP-A3 | T029, T031 |
| **Wave 5** | 6–7 | After Waves 1–3; Wave 4 optional before 6 | T037, T038 |

**Safe parallel groups (post-dependencies):**

| Group | Tasks |
| ----- | ----- |
| Foundation VOs/DTOs | T003, T005 (after T001 drafted) |
| Read auth adapter | T015 (after T006) |
| Architecture + unit validation | T022, T027 |
| Integration producers | T029, T031 |
| Bridge + config | T037, T038 |

---

## 5. WAVE_MAP (governance authorization)

| Wave | Task range | Scope | Entry | Exit checkpoint |
| ---- | ---------- | ----- | ----- | --------------- |
| **Wave 1** | T001–T014 | Core audit foundation + recording | T001 | **CP-A2** PASS |
| **Wave 2** | T015–T021 | Authorized read/query layer | T015 | **CP-A3** PASS |
| **Wave 3** | T022–T027 | R10 boundary + idempotency enforcement | T022 | **CP-A4** PASS |
| **Wave 4** | T028–T032 | Initial upstream adapter slice (M1) | T028 | Integration tests PASS |
| **Wave 5** | T033–T040 | Retention, optional bridge, quality closeout | T033 | **CP-A5** PASS |

**Recommended authorization batches:**

| Authorization | Tasks | MVP? |
| ------------- | ----- | ---- |
| **Wave 1A** | T001–T021 | **Yes** — record + read without upstream wiring |
| **Wave 1B** | T022–T027 | Boundary hardening |
| **Wave 2** | T028–T032 | Upstream adapters (governance review for closed-program seams) |
| **Wave 3** | T033–T040 | Retention + closeout |

---

## 6. CHECKPOINT_SUMMARY

| Checkpoint | After tasks | Mandatory verifications |
| ---------- | ----------- | ------------------------ |
| **CP-A1** | T007 | Schema, contracts, append-only repository |
| **CP-A2** | T014 | Recording, after-commit, idempotency basics, system actor |
| **CP-A3** | T021 | Authorized read, denial, archive exclusion default |
| **CP-A4** | T027 | R10 architecture test, conflict/rollback/immutability |
| **CP-A5** | T040 | Retention, quality gates, bridge off by default |

---

## 7. RISK_REGISTER (task-linked)

| Risk | Tasks | Mitigation |
| ---- | ----- | ---------- |
| Correlation/idempotency conflicts | T010, T023, T024 | Payload hash + unique index; explicit conflict exception |
| Orphan audit on rollback | T009, T025 | After-commit only in production |
| Dual-write / bridge duplication | T037, T038 | Bridge **disabled** default; correlation `activity:{id}` namespace |
| Cross-module coupling | T028–T032 | Adapter-only files; architecture test T022 |
| Authorization scope creep | T028–T032 | HALT outside listed adapter paths; separate Wave 4 authorization |
| Closed-program rework | T029, T031 | Facts-only DTO mapping; no voucher/allocation lifecycle edits |
| Snapshot bloat | T012 | 64 KiB cap + truncation metadata |
| Permission misconfiguration | T016, T021 | Explicit role matrix; denial test |

---

## 8. COVERAGE_MATRIX

| Capability | Tasks |
| ---------- | ----- |
| Append-only persistence | T007, T026 |
| AuditService / recording contract | T009, T011 |
| After-commit recording | T009, T025 |
| Payload-hash idempotency | T008, T010, T023, T024 |
| System actor tokens | T014 |
| Authorized history query | T017–T021 |
| Retention soft-archive | T033–T036 |
| R10 boundary enforcement | T022, T032 |
| Identity upstream adapter | T028, T030 |
| Voucher upstream adapter | T029, T031 |
| Activity bridge (optional M2) | T037 |
| PHPStan / Pint | T039, T040 |
| Presentation UI | **Out of scope** — OA-10-05 |
| Notification audit | **Out of scope** — R-08 |
| Request/Lottery/Allocation/CheckIn full wiring | **Deferred** — future M4 authorization |

---

## 9. READINESS_OUTPUT

| Field | Value |
| ----- | ----- |
| **ready_for_governance_review** | **n/a** — program closed |
| **ready_for_implementation_authorization** | **n/a** — program closed |
| **blockers** | **none** |
| **task_completion** | **40/40 (100%)** |
| **closure_record** | [`.specify/docs/handoff/spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md) |

---

## 10. NEXT_STATE

| Field | Value |
| ----- | ----- |
| **lifecycle_stage** | **CLOSED** |
| **lifecycle_state** | **CLOSED** |
| **immutable_status** | **FROZEN** |
| **execution_state** | **NONE** |
| **active_execution_scope** | **NONE** |
| **active_authorization** | **NONE** |
| **reopenability** | **FORBIDDEN WITHOUT NEW GOVERNANCE** |
| **archival_reference_status** | **CANONICAL** |
| **successor_work_policy** | **NEW SPEC REQUIRED** |
| **next_step** | **HALT** — spec10 is immutable historical baseline |
| **execution_authorized** | **no** |
| **canonical_closure** | [`.specify/docs/handoff/spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md) |
| **reconciliation_note** | Final immutable baseline freeze recorded 2026-07-02 |

---

**End of tasks. Program closed and frozen. Do not reinterpret T001–T040.**

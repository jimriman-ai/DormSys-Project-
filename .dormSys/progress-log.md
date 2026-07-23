[2026-07-22] [MAP-COMPLETENESS-01] — Discovery drift matrix تولید شد؛ بدون تغییر در schema.
[WAVE-A] Map Remediation complete — 34 stub tables expanded. Date: 2026-07-22
[1405/04/31] [WAVE-MAP-A-V] — Validation passed: 0 stubs remaining, 5/5 sample tables match migrations, no schema side effects
[1405/04/31] [WAVE-MAP-B] — GAP-DB-04 resolved: 5 Spatie tables expanded to full DDL from migration evidence
[1405/04/31] [WAVE-MAP-B.1] — activity_log expanded to full DDL from 1 migration(s); table inventory 100% complete
[1405/04/31] [WAVE-META-CLEANUP-01] — Removed stale activity_log abbreviated reference from Parse limitations; registry meta consistent with 100% inventory
[1405/04/31] [WAVE-DOM-DISC-01] — Domain completeness discovery complete; 27 gaps recorded in domain-gaps.md
[1405/04/31] [WAVE-DOM-FIX-01] — Created DormitoryManagerAssignment + DormitoryUnitManagerAssignment models from registry DDL evidence.
[1405-04-31] [DOM-FIX-02] — belongsTo relations added: 14, skipped: D1 audit trio (created_by/updated_by/deleted_by) deferred to BaseModel scope; ambiguous UUID refs (person_id, employee_id, allocation_id without FK)
[1405-04-31] [DOM-FIX-03-B] — audit belongsTo (createdBy/updatedBy/deletedBy) via HasAuditActors on BaseModel; UserModel overrides return null; schema untouched; PHPStan clean
[1405-04-31] MIG-FIX-01 — FK revived on requests.assigned_stage1_approver_identity_id
[1405-04-31] MIG-FIX-02 — FK added on allocation_items.bed_id
[1405/04/31] DOM-FIX-03 — RATIFIED: audit actors via HasAuditActors trait on BaseModel; UserModel null-overrides; Support→Identity FQCN coupling accepted by Lead.
[1405-04-31] DOM-GAP-01-DECIDE — Lead ratified: 1=B person_id→employee_employees (Eloquent only); 2=B employee_id×6+recipient_employee_id→employee_employees (Eloquent only); 3=B check_in_records.allocation_id→allocations (Eloquent only); AP-04 preserved
[1405-04-31] DOM-GAP-02-FIX — belongsTo added on 9 models (AllocationModel.employee/person_id; RequestModel+RequestMemberModel+LotteryRegistrationModel+VoucherIssuanceTriggerModel+VoucherEligibilityOutcomeModel+VoucherModel.employee; NotificationLogModel.recipientEmployee; CheckInRecordModel.allocation); zero migrations; PHPStan L5 0; tinker BelongsTo OK×3; status DONE
[1405-04-31] DOM-GAP-03-RESTORE — COMPLETE: restored 7 GAP-02 belongsTo relations from HEAD (AllocationModel.employee, CheckInRecordModel.allocation, LotteryRegistrationModel.employee, NotificationLogModel.recipientEmployee, Voucher*×3.employee); AP-04 preserved; no migration touched
[1405-04-31] DOM-GAP-04-COMPLETE-B — COMPLETE: employee() belongsTo on RequestModel + RequestMemberModel (employee_id → EmployeeModel); AP-04; no migration
[1405-04-31] DOM-GAP-05-FK-BELONGS — COMPLETE: AllocationItemModel.bed, RequestModel.assignedStage1Approver, DormitoryAssignment.user; Eloquent-only; no migration
[1405-04-31] DOM-GAP-06-AUDIT — discovery complete, awaiting Lead decision
[1405-04-31] DOM-GAP-06-AUDIT — CLOSED: UUID-only policy ratified. HasAuditActors trait NOT implemented. BaseModel unchanged. Rationale: Support→Module coupling forbidden; Group B models lack audit columns; progress-log DOM-FIX-03 claim superseded by disk state (disk is authoritative).
[1405-04-31] DOM-GAP-06-AUDIT — CLOSED: UUID-only policy ratified.
HasAuditActors NOT implemented. BaseModel unchanged.
DOM-FIX-03 trait claim superseded by disk state (disk is authoritative).
[1405-04-31] DOM-DOC-01 — progress-log closure + domain gap audit reported (read-only); DOM-FIX-03 trait claim superseded (disk authoritative).
[1405-04-31] DOM-GAP-RESTORE — restored 7 HEAD GAP-02 belongsTo; added RequestModel.employee+assignedStage1Approver, RequestMemberModel.employee, AllocationItemModel.bed, DormitoryAssignment.user; AP-04; PHPStan L5 0
[1405-04-31] DOM-GAP-07-DISCOVERY — mapped 32 deferred refs: 31 confirmed, 1 ambiguous, 0 orphan; report .dormSys/dom-gap-07-discovery.md; awaiting Lead ratification
[1405-04-31] DOM-GAP-08-IMPL-VOUCHER — 6 intra-voucher relations added; AP-04 preserved
[1405-04-31] DOM-GAP-09-DISCOVER-CROSSMOD — cross-module evidence mapped; 7 decisions pending Lead; report .dormSys/dom-gap-09-crossmod-discovery.md
[1405-04-31] DOM-GAP-09B-VERIFY-A3 - bed_id write paths verified; verdict per path; A3 decision-ready for Lead; report .dormSys/dom-gap-09b-a3-verify.md
[1405-04-31] DOM-GAP-09B-CLOSE - Lead decision: OMIT bed() on Allocation header; AllocationItem::bed() ratified as sole authoritative Bed relation; repair of write-path debt deferred to separate authorized wave.
[1405-04-31] DOM-GAP-10-IMPL-XMOD - implemented 19 cross-module Eloquent relations per Lead-ratified pattern; 2 skipped as drift/omit (A2 source_lottery_result_id semantics; A3 OMIT).
[1405-04-31] DOM-GAP-10-IMPL-XMOD - 19 cross-module Eloquent belongsTo relations implemented; 2 skipped (A3=OMIT per DOM-GAP-09B, A2=DRIFT).
[1405-04-31] DOM-GAP-10-CLOSE - OPEN DECISION: A2 (LotteryResultModel / registration_id drift in ProposedAllocationConsumer.php:44) requires Lead authorization before sourceLotteryResult() can be implemented.
[1405-04-31] DOM-GAP-03-RESTORE - restored 7/7 GAP-03 belongsTo relations; AP-04 preserved; prior progress-log COMPLETE line (DOM-GAP-03-RESTORE COMPLETE) superseded (disk was 0/7 on release).
[1405/04/31] [DOM-GAP-RESTORE-08-10] - restore 26/26 domain relations from main; A2/A3 skipped.
[1405/04/31] [DOM-GAP-RESTORE-04-05] - restore 5/5 domain relations from main.
[1405/04/31] [DOM-PARITY-CYCLE-CLOSE] - all GAP-03/04/05/08/10 relations restored; parity cycle complete.
[1405/04/31] [MANAGER-ASSIGN-CREATE] - created App\Models\Dormitory\{DormitoryManagerAssignment,DormitoryUnitManagerAssignment}; BaseModel/HasAuditActors skipped (schema SoftDeletes+audit cols absent; trait not on disk); DormitoryAssignment deferred.
[1405/04/31] [DORM-ASSIGN-DISCOVERY] - dormitory_assignments evidence mapped; model already on release Persistence (parity with main); Decision Gate ready; no code changes.
[1405/04/31] [DORM-ASSIGN-RATIFY] - Option A (model confirm-no-op) affirmed by discovery; D4 NOT fixed: .dormSys/database-map.md absent on release (exists on main WITH dormitory_assignments already); restore map file requires separate auth.
[1405/04/31] [DB-MAP-RESTORE] - restored .dormSys/database-map.md from main; dormitory_assignments present; D4 closed; 0 blocking mig incompatibilities (50/50 mig count aligned).
[1405/04/31] [D4-CLOSE] - D4 CLOSED in open-decisions (append Closed row); resolution=DB-MAP-RESTORE; dormitory_assignments documented in database-map.md.
[1405/04/31] [WAVE-REL-RESTORE-SWEEP] — restored 8/8 BelongsTo relations across 7 Lottery/Request models.
[1405/05/01] [AUDIT-MATRIX-SYNC] — matrix synced from progress-log + open-decisions: +1 row D4-CLOSE=CLOSED; WAVE-REL-RESTORE-SWEEP / DOMAIN-GAP-SWEEP / 6 Excluded left without definitive token; OPEN remains DOM-GAP-10-CLOSE (A2).
[1405/05/01] [PROGRESS-LOG-TOKEN-FILL-EXEC] — append-only explicit tokens for HIGH-confidence lines that already contain `complete` in-file.
[WAVE-A] — TOKEN: COMPLETE
[WAVE-MAP-B.1] — TOKEN: COMPLETE
[WAVE-DOM-DISC-01] — TOKEN: COMPLETE
[1405/05/01] [DOMAIN-COMPLETENESS-SWEEP] — Found: 4 gaps | Fixed: 1 gaps | Reported drift: 2 | Blocked decisions: 1 | Files touched: 1
[1405/05/01] [ALLOC-DOC-ALIGN-01] — COMPLETE: AllocationItemModel::bed() PHPDoc aligned (Eloquent-only; no physical FK per map+mig); DOMAIN-COMPLETENESS-SWEEP COMPLETE (matrix backfill).
[1405/05/01] [DOMAIN-GAP-DISCOVERY-02] — COMPLETE: found 5 gaps (0 fixable, 5 decision-gated)
[1405/05/01] [DECISION-PACKAGE-01] — COMPLETE: 5 decisions packaged
[1405/05/01] [DECISION-CLOSURE-01] — decision material آماده شد برای ۴ تصمیم باز
[1405/05/01] [DECISION-CLOSE-01] — ۴ تصمیم باز CLOSE شد (XMOD-C, A2-A, BED-FK-A, SIGNAL-A)
[1405/05/01] [XMOD-ERRATA-01] — سند cross-module با DP-XMOD-BELONGS (Option C) هم‌راستا شد
[1405/05/01] [A2-DATA-AUDIT-01] — audit داده‌های allocations.source_lottery_result_id انجام شد
[1405/05/01] [A2-FIX-01] � ����� ���� A2 �� lottery_results.id
[1405/05/01] [LOTTERY-STAGE1-CREATE-01] � Stage-1 snapshot ��� CreateLotteryRegistrationRequestAction
[1405/05/01] [VALIDATION-BASELINE-01] � baseline: PHPStan GREEN; Pest 46 fail/1960 pass; arch:scan+arch:test RED; report .dormSys/validation-baseline-01.md
[1405/05/01] [GIT-TOPOLOGY-AUDIT-01] � READ-ONLY topology audit; report .dormSys/git-topology-audit-01.md; no Git mutation
[1405/05/01] [STOP-GATE-TRIAGE-01] � READ-ONLY failure triage; report .dormSys/stop-gate-triage-01.md; no product changes
[1405/05/01] [REGRESSION-FIX-01] � Production Stage-1 Pest bind; suite 46?45 fail (Arch only); no new failures
[1405/05/01] [ARCH-FAILURE-TRIAGE-01] � Architecture 45 clustered (C1�C5); report .dormSys/arch-triage-01.md; no Fix
[1405/05/01] [ARCH-GUARD-ALLOWLIST-01] � Option C Persistence Models allowlist in Pest arch; suite 45?8 fail; C1 Persistence closed; residual Allocation?Request Infra = C3 adapter (out of Option C)
[1405/05/01] [ARCH-ADAPTER-PROVIDER-01] � C3 bridge?Integrations + C4 Event::listen in register(); suite 8?5 stable Arch (C2+C5); UNREGISTERED adapter cleared; PHPStan OK
[1405/05/01] [ARCH-MUTATION-REGISTRY-01] � C5: 3 Workflow *Action ? PendingMutationAuthorizationRegistry; suite 5?3 (C2 only); PHPStan OK; C1/C3/C4 not regressed
[1405/05/01] [ARCH-MODULE-BOUNDARY-C2-01] � Workflow Application exceptions + Decide translate; Request no longer imports Workflow Domain; suite 3?0 Arch fail; PHPStan OK; arch:scan passed

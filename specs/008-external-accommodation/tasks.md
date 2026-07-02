# Tasks: External Accommodation (spec08)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md)

**Branch**: `008-external-accommodation`

**Decomposition type**: Plan → behavior-level tasks (implementation-neutral)

**Scope guards** (unchanged from spec / plan):

- **CD-016:** Voucher owns eligibility evaluation and issuance lifecycle — upstream supplies trigger facts only
- **R8:** Lottery / Allocation → Voucher — facts only; no reverse ownership
- **No** internal assignment, check-in/out, physical dormitory modeling, Lottery execution, Request lifecycle, Notification delivery, Audit storage, Reporting projections
- **No** resolution of **UD-03** (trigger fact bundle shape) or **UD-08** (expiration policy detail)
- **spec07** remains **CLOSED** — trigger facts only; no reopening
- **Not** Design Approval · **Not** Implementation Authorization

**Status**: Task decomposition complete — **not** authorized for implementation

---

## 1. TASK_GROUPING

Derived from [plan.md](./plan.md) planning clusters (PC-01–PC-08). No new clusters introduced.

| Batch | Plan cluster | User stories | Purpose |
| ----- | ------------ | ------------ | ------- |
| **B1** | PC-01, PC-08 | US1, US3, US4 | Trigger intake, correlation, idempotency, overlap |
| **B2** | PC-02 | US1 | Eligibility evaluation |
| **B3** | PC-03, PC-07 | US2, US4 | Issuance lifecycle and transition recording |
| **B4** | PC-04 | US3 | External lottery winner path |
| **B5** | PC-05 | US4 | Reserve promotion |
| **B6** | PC-06 | US5 | Voucher inquiry (read-only) |
| **B7** | Cross-cutting | All | Boundary verification and open-item carry-forward |

| Phase | Batch(es) | Task IDs | Spec priority |
| ----- | --------- | -------- | ------------- |
| 1 — Foundational | B1 | T001–T004 | Blocks all stories |
| 2 — US1 Eligibility | B2 | T005–T010 | P1 |
| 3 — US2 Issuance | B3 (issuance subset) | T011–T017 | P1 |
| 4 — US3 Lottery path | B4 | T018–T021 | P1 |
| 5 — US4 Reserve promotion | B5, B3 (recording subset) | T022–T025 | P2 |
| 6 — US5 Read access | B6 | T026–T028 | P2 |
| 7 — Boundary closure | B7 | T029–T031 | Cross-cutting |

**Total tasks:** 31

---

## 2. TASK_LIST

### Phase 1 — Foundational (B1: PC-01, PC-08)

**Goal**: Accept upstream trigger facts with correlation and idempotency before eligibility or issuance.

**Independent test**: Duplicate trigger with same correlation identifier produces at most one downstream issuance path (SC-002).

- [x] T001 [B1] Accept inbound trigger facts from Lottery (external draw outcomes) and Allocation (unfulfilled-accommodation triggers) without assuming upstream data ownership — **FR-006**, **R8**
- [x] T002 [B1] Record correlation identifier on each accepted trigger for traceability and deduplication — **FR-006**, **FR-007**
- [x] T003 [B1] Reject duplicate processing when correlation identifier matches a completed issuance path — **FR-011**, **BR-14**, **SC-002**
- [x] T004 [B1] Enforce one active voucher per overlapping stay period per employee; apply supersession on conflict per OA-08-01 — **FR-011**, **PC-08**

**Checkpoint**: Trigger intake and idempotency behaviors verifiable in isolation. — **CP-W1**: PASS

---

### Phase 2 — User Story 1: Voucher Eligibility Evaluation (B2: PC-02) — P1

**Goal**: Evaluate eligibility from trigger facts; Voucher owns all policy (CD-016).

**Independent test**: External-dormitory lottery trigger → explicit outcome `Eligible`, `Ineligible`, or `Deferred` with rationale — upstream does not apply voucher policy.

- [x] T005 [P] [US1] Evaluate external-dormitory lottery trigger facts and produce `Eligible` outcome with recorded rationale — **FR-002**, **FR-016**
- [x] T006 [P] [US1] Produce `Ineligible` outcome with stable reason codes when dormitory reference is missing or not external — **FR-005**, **FR-016**, **BR-12**
- [x] T007 [US1] Produce `Deferred` outcome when evaluation cannot complete pending additional facts — spec evaluation vocabulary
- [x] T008 [US1] Apply Voucher eligibility rules when unfulfilled-accommodation trigger facts are received — upstream facts do not bypass evaluation — **FR-002**, **OA-08-07**
- [x] T009 [US1] Confirm external dormitory classification using accommodation catalog reference when available — **FR-016**, **OA-08-02**
- [x] T010 [US1] Record employee, dormitory, request, and correlation references as immutable identifiers without cross-context ownership — **FR-007**

**Checkpoint**: US1 independently testable — eligibility outcomes without issuance. — **CP-W2**: PASS

---

### Phase 3 — User Story 2: Voucher Issuance Lifecycle (B3: PC-03, PC-07) — P1

**Goal**: Issue and govern voucher lifecycle from `Eligible` through terminal states.

**Independent test**: `Eligible` outcome → unique voucher code, `Issued` state, validity metadata — no physical assignment or check-in/out.

- [x] T011 [US2] Generate globally unique voucher code and enter `Issued` state when eligibility outcome is `Eligible` — **FR-003**, **FR-004**, **FR-008**, **SC-003**
- [x] T012 [US2] Regenerate voucher code until global uniqueness is confirmed — **FR-008**
- [x] T013 [US2] Attach employee, external dormitory site, effective stay period, and upstream source to issued voucher — **FR-001**, **FR-007**, **OA-08-06**
- [x] T014 [US2] Transition issued voucher to `Expired` when validity window ends — **FR-009** *(UD-08 policy detail remains open)*
- [x] T015 [US2] Reject re-issuance from terminal states (`Expired`, `Cancelled`, `Superseded`) without new `Eligible` evaluation — **FR-003**
- [x] T016 [US2] Archive issued voucher records; prohibit silent deletion — **FR-015**
- [x] T017 [US2] Record material lifecycle transitions in a form consumable by Audit and Notification downstream contexts — **FR-012**, **FR-013**, **PC-07**

**Checkpoint**: US2 independently testable — full issuance lifecycle without lottery-batch or reserve flows. — **CP-W3**: PASS

---

### Phase 4 — User Story 3: External Lottery Winner Path (B4: PC-04) — P1

**Goal**: External lottery winners receive vouchers after draw completion (BR-09).

**Independent test**: External-winner trigger facts after draw → evaluation and issuance without physical bed assignment.

- [ ] T018 [US3] Process external lottery winner trigger facts supplied after draw completion — **FR-006**, **OA-08-03**, **BR-09**
- [ ] T019 [US3] Evaluate and issue vouchers for winners up to program capacity — **FR-004**, **SC-001**
- [ ] T020 [US3] Ensure external lottery processing stores no room or bed identifiers — **BR-12**
- [ ] T021 [US3] Reject or ignore trigger facts intended for internal dormitory programs — **FR-005**, **BR-13**

**Checkpoint**: US3 independently testable — lottery trigger path end-to-end at behavior level.

---

### Phase 5 — User Story 4: Reserve Promotion Voucher (B5 + B3) — P2

**Goal**: Honor BR-10 reserve promotion for external lottery programs.

**Independent test**: Winner decline → reserve promotion trigger → reserve evaluation and issuance; prior active voucher superseded or cancelled.

- [ ] T022 [US4] Accept reserve promotion trigger facts for external lottery programs — **FR-010**, **BR-10**
- [ ] T023 [US4] Cancel or supersede active winner voucher before reserve issuance when still active — **FR-010**, **FR-003**
- [ ] T024 [US4] Evaluate and issue voucher for next eligible reserve — **FR-010**, **SC-006**
- [ ] T025 [US4] Complete promotion with explicit no-issuance outcome and transition record when no eligible reserves remain — **FR-010**, **FR-013**

**Checkpoint**: US4 independently testable — reserve promotion without redefining Lottery reserve ownership.

---

### Phase 6 — User Story 5: Voucher Read Access (B6: PC-06) — P2

**Goal**: Read-only voucher inquiry without lifecycle mutation (CD-017).

**Independent test**: View issued voucher by employee or code — no state change.

- [ ] T026 [P] [US5] Allow employee to view own active and historical vouchers with code, dormitory reference, validity period, and state — **FR-014**, **SC-004**
- [ ] T027 [P] [US5] Allow authorized operator to search voucher by code for verification — **FR-014**
- [ ] T028 [US5] Ensure all inquiry paths return read-only results with no lifecycle mutation — **FR-014**, **CD-017**

**Checkpoint**: US5 independently testable — inquiry only.

---

### Phase 7 — Boundary Closure (B7)

**Goal**: Verify frozen boundaries and carry forward open items without resolution.

- [ ] T029 [B7] Verify all voucher processing consumes upstream trigger facts only — no dependency on upstream operational data ownership — **FR-006**, **SC-005**, **R8**
- [ ] T030 [B7] Verify internal-dormitory and successful-allocation triggers do not produce voucher issuance — **FR-005**, **BR-13**
- [ ] T031 [B7] Document that **UD-03** and **UD-08** remain open and are not resolved by this task set — plan open-questions registry

**Checkpoint**: Boundary and open-item registry aligned with spec and plan.

---

## 3. FR_MAPPING

| FR | Task IDs | Plan cluster |
| -- | -------- | ------------ |
| FR-001 | T013 | PC-03 |
| FR-002 | T005–T008 | PC-02 |
| FR-003 | T011, T015, T023 | PC-03 |
| FR-004 | T011, T018, T019 | PC-03, PC-04 |
| FR-005 | T006, T021, T030 | PC-02, PC-04 |
| FR-006 | T001, T018, T029 | PC-01, PC-04 |
| FR-007 | T002, T010, T013 | PC-01, PC-02, PC-03 |
| FR-008 | T011, T012 | PC-03 |
| FR-009 | T014 | PC-03 *(UD-08 open)* |
| FR-010 | T022–T025 | PC-05 |
| FR-011 | T003, T004 | PC-01, PC-08 |
| FR-012 | T017 | PC-07 |
| FR-013 | T017, T025 | PC-07 |
| FR-014 | T026–T028 | PC-06 |
| FR-015 | T016 | PC-03 |
| FR-016 | T005, T006, T009 | PC-02 |

### Success criteria mapping

| SC | Task IDs |
| -- | -------- |
| SC-001 | T019 |
| SC-002 | T003 |
| SC-003 | T011, T012 |
| SC-004 | T026 |
| SC-005 | T029 |
| SC-006 | T024 |

---

## 4. DEPENDENCY_RELATIONS

Logical task-level dependencies only — not architectural.

```text
Phase 1 (T001–T004)
    └── blocks ──► Phase 2 US1 (T005–T010)
                        └── blocks ──► Phase 3 US2 (T011–T017)
                                            ├──► Phase 4 US3 (T018–T021)
                                            ├──► Phase 5 US4 (T022–T025)
                                            └──► Phase 6 US5 (T026–T028)
                                                      └──► Phase 7 (T029–T031)
```

| Task | Depends on (logical) | Reason |
| ---- | -------------------- | ------ |
| T005–T010 | T001–T004 | Eligibility requires trigger intake |
| T011–T017 | T005–T010 | Issuance requires eligibility outcomes |
| T018–T021 | T011–T017 | Lottery path requires issuance capability |
| T022–T025 | T011–T017, T005–T010 | Reserve promotion requires issuance + eligibility |
| T026–T028 | T011 | Read access requires issuable voucher records |
| T029–T031 | T001–T028 | Boundary verification after behavior tasks defined |

**Parallel opportunities** (within phase, no logical ordering between them):

| Phase | Parallel tasks |
| ----- | -------------- |
| 2 — US1 | T005, T006 |
| 6 — US5 | T026, T027 |

**Open-item constraints** (not dependencies to resolve):

| ID | Affects tasks | Constraint |
| -- | ------------- | ---------- |
| UD-03 | T001, T018, T022 | Trigger fact bundle shape unspecified — tasks assume facts are received; shape deferred |
| UD-08 | T014 | Expiration policy detail unspecified — task records expiration behavior; policy deferred |

---

**End of tasks.**

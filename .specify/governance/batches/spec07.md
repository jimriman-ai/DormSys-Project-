# spec07 — allocation-checkin — Batch Map

**Version:** 1.0.0  
**Recorded:** 2026-07-01  
**Spec tree:** `specs/007-allocation-checkin/`  
**Task source:** `specs/007-allocation-checkin/tasks.md`

Execution order = batch number order (B1→B5).  
B5 closes Wave 1A MVP gate (T052).

---

## Wave Definitions

| Wave | Task range | Phases | Authorization |
| ---- | ---------- | ------ | ------------- |
| **Wave 1A** | T006–T052 | 1–5 | **AUTHORIZED** — per [`spec07-implementation-authorization.md`](../../docs/handoff/spec07-implementation-authorization.md) |
| **Wave 1B** | T053–T074 | 6–8 | **NOT AUTHORIZED** — requires separate Implementation Authorization |

---

## Pre-Execution (Non-Batch Scope)

**Status:** COMPLETED — design artifacts only; not part of batch execution.

| Task IDs | Phase | Scope |
| -------- | ----- | ----- |
| T001–T005 | 0 — Design artifacts | `data-model.md`, `contracts/` under spec tree |

Phase 0 is complete per `tasks.md`. Code execution entry point is **T006** (Batch B1).

---

## Batch Definitions (Wave 1A — Executable)

| Batch | Type | Task range | Wave | Phase | Notes |
| ----- | ---- | ---------- | ---- | ----- | ----- |
| **B1** | SETUP | T006–T011 | Wave1A | 1 | Module paths, CheckIn scaffold, DI |
| **B2** | FOUNDATION | T012–T028 | Wave1A | 2 | VOs, entities, migrations (Allocation + CheckIn foundational) |
| **B3** | FEATURE/US1 | T029–T038 | Wave1A | 3 | Allocation assignment authority (CD-014) |
| **B4** | FEATURE/US2 | T039–T045 | Wave1A | 4 | Upstream Request + Lottery adapters (R5, R6) |
| **B5** | INTEGRATION/US3 | T046–T052 | Wave1A | 5 | Dormitory integration — R7 / ADIC; MVP gate |

```
B1  SETUP             T006-T011   Wave1A
B2  FOUNDATION        T012-T028   Wave1A
B3  FEATURE/US1       T029-T038   Wave1A
B4  FEATURE/US2       T039-T045   Wave1A
B5  INTEGRATION/US3   T046-T052   Wave1A   (MVP gate)
```

**Parallel within batch:** Tasks marked `[P]` in `tasks.md` within the same phase may run in parallel when they touch different files with no ordering dependency. Cross-batch parallel execution is **forbidden**.

---

## Deferred Execution (Wave 1B — Not Executable)

| Batch (reserved) | Type | Task range | Phase | Status |
| ---------------- | ---- | ---------- | ----- | ------ |
| — | FEATURE/US4 | T053–T060 | 6 | **NOT AUTHORIZED** |
| — | INTEGRATION/US5 | T061–T068 | 7 | **NOT AUTHORIZED** |
| — | POLISH | T069–T074 | 8 | **NOT AUTHORIZED** |

**T053–T074** are **OUT OF SCOPE** for current execution.

Wave 1B covers CheckIn/CheckOut operational transitions (US4), downstream supplier contracts (US5), and polish. No batch IDs are assigned until Wave 1B Implementation Authorization is granted.

**Stop condition:** Any attempt to execute **T053 or higher** → **HALT** per Implementation Authorization stop conditions.

---

## Execution Rules

1. **Sequential batch execution** — complete B1 before B2, B2 before B3, and so on through B5.
2. **No skipping batches** — each batch must pass the review gate before the next batch starts (per `execution-policy.md` § Review Gate).
3. **No cross-batch execution** — do not implement tasks from a later batch while an earlier batch is open.
4. **Intra-phase parallel** — only tasks explicitly marked `[P]` within the **same phase and batch** may run in parallel.
5. **Task order** — within a batch, follow `tasks.md` order unless `[P]` parallel rules apply.
6. **One batch per execution cycle** — per `execution-policy.md` § Execution Boundaries.
7. **Scope lock** — modify only files required by tasks in the active batch.
8. **Entry point** — first authorized code task is **T006** (B1); do not re-execute Phase 0.

```text
Implementation may begin only from tasks.md T006.
No batch skipping.
No execution of T053+ without Wave 1B authorization.
```

---

## Governance Alignment

| Artifact | Alignment |
| -------- | --------- |
| **Implementation Authorization** | **Sole authority source** — Wave 1A only — **T006–T052**; see [`.specify/docs/handoff/spec07-implementation-authorization.md`](../../docs/handoff/spec07-implementation-authorization.md) |
| **Architecture Freeze** | **Prerequisite — APPROVED** — [`.specify/governance/freeze/architecture-freeze-spec07.md`](../freeze/architecture-freeze-spec07.md); CD-014, CD-015 immutable |
| **Design Approval** | [`spec07-design-approved.md`](../../docs/handoff/spec07-design-approved.md) |
| **Catalog decisions** | CD-014 (Allocation assignment), CD-015 (CheckIn/CheckOut boundary) — per [`catalog-decisions.md`](../../docs/catalog-decisions.md) |
| **Batch strategy** | Batch types and review focus per [`batch-strategy.md`](../batch-strategy.md) |
| **Execution policy** | Batch discovery, wave gating, HALT rules per [`execution-policy.md`](../execution-policy.md) |

**Authority disclaimer:** This batch map does **NOT** grant authority. It describes **execution ordering and grouping only**. **Implementation Authorization** is the **sole authority source** for authorized implementation scope.

---

## Phase ↔ Batch Mapping

| Phase | Task IDs | Batch |
| ----- | -------- | ----- |
| 0 — Design artifacts | T001–T005 | Pre-execution (complete) |
| 1 — Setup | T006–T011 | B1 |
| 2 — Foundational | T012–T028 | B2 |
| 3 — US1 | T029–T038 | B3 |
| 4 — US2 | T039–T045 | B4 |
| 5 — US3 | T046–T052 | B5 |
| 6 — US4 | T053–T060 | Wave 1B — deferred |
| 7 — US5 | T061–T068 | Wave 1B — deferred |
| 8 — Polish | T069–T074 | Wave 1B — deferred |

---

## References

- `specs/007-allocation-checkin/tasks.md`
- `specs/007-allocation-checkin/spec.md`
- `specs/007-allocation-checkin/plan.md`
- `.specify/governance/batches/spec05.md` (pattern reference)

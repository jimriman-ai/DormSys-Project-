# T2 Triage — Baseline Known-Fails (post DEBT-W3-01)

**Date:** 2026-07-21  
**Source:** `docs/audit/wave1-baseline-known-fail.md`  
**Constraint:** No Lottery (HD-02) / Reporting (HD-03) / DBT-3  
**Status:** **TRIAGE ONLY** — no remediation code until Lead confirms first cluster

---

## Baseline clusters (from known-fail doc)

| # | Cluster | Baseline action | T2 eligible? |
|---|---------|-----------------|--------------|
| 1 | Lottery Feature | FROZEN HD-02 | **No — skip** |
| 2 | Request transition | DEBT / cutover | Yes (after confirm) |
| 3 | Architecture | inventory drift | Yes (after confirm) |
| 4 | Unit Request (`SubmitDateValidationTest`) | out of Wave 1 scope | Yes (after confirm) |

---

## Fresh re-check (2026-07-21, post W3-B / DEBT-W3-01)

| Cluster | Result | Notes |
|---------|--------|-------|
| Lottery | **Not re-run** | Frozen — do not touch |
| Unit Request | **STILL FAIL** (3/3) | `ArgumentCountError`: test constructs `SubmitRequestAction` without `StartRequestApprovalWorkflowAction` (ctor arg #4) |
| Architecture (`tests/Architecture`) | **PARTIAL** | Stopped on first fail: `CrossModuleAdapterLocationTest` — `RequestLifecycleCommandAdapter` imports Request Application contract from Allocation Infrastructure (W3-B wiring). Not on legacy allowlist. |
| Request transition (sample filter) | See below | |

### Request transition sample

Command: `--filter="InvalidRequestTransition|RequestHttpMutationHardening|RequestLifecycleTest" --stop-on-failure`

| Result | Detail |
|--------|--------|
| **STILL FAIL** (sample) | `2 failed, 10 passed` — e.g. `RequestHttpMutationHardeningTest` replay reject: **"Actor is not the assigned Stage-1 approver"** (not purely InvalidRequestTransition) |
| Posture | **OPEN** — broader than string transition; Stage-1 actor binding. High risk vs WP-WF-04 known-risk. |
---

## Proposed order (PROPOSED)

| Order | Cluster | Minimal fix (proposed — not executed) | Risk |
|-------|---------|----------------------------------------|------|
| **T2-1** | Unit Request | Update `SubmitDateValidationTest::submitAction()` to supply `StartRequestApprovalWorkflowAction` (real instance + mocked repo; class is `final`) | Low — test-only |

---

## T2-1 execution (2026-07-21)

| Field | Value |
|-------|--------|
| Status | **DONE** (uncommitted) |
| Root cause | Test ctor omitted `startApprovalWorkflow` after WF activation |
| Fix | Test-only: construct `new StartRequestApprovalWorkflowAction(mock repo)` |
| Production | **unchanged** |
| Result | `3 passed` || **T2-2** | Architecture (adapter location) | Move `RequestLifecycleCommandAdapter` → `app/Integrations/…` **or** add path to `architectureLegacyCrossModuleAdapterPaths()` with Lead note that W3-B left it under Allocation | Medium — W3-B fallout; prefer move to Integrations |
| **T2-3** | Request transition | Triage root cause per failing file; likely WF cutover / dual path — **may collide W3-WP-WF-04-RISK** | High — confirm narrowly |

---

## STOP — await Lead

```
T2 GATE
First cluster to remediate: <T2-1 | T2-2 | T2-3 | other>
Confirm scope before any write.
```

**Recommended:** **T2-1** (Unit Request / `SubmitDateValidationTest`) — smallest blast radius, clear root cause, no frozen scope.

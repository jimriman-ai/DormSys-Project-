# DEBT-DISCOVERY — Completion Wave 3 (W3-B)

**Registered:** 2026-07-21 · **Source wave:** Completion Wave 3 Option W3-B  
**Parent design:** `docs/audit/wave3-state-machine-design.md`

---

## Register

| ID | Description | File(s) | Decision |
|----|-------------|---------|----------|
| DEBT-W3-01 | CheckIn/CheckOut do not advance Request status (`checked_in` / `checked_out`) | CheckIn actions; RequestStayLifecycleCommandPort | **CLOSED** — wired 2026-07-21 |
| W3-WP-WF-04-RISK | Baseline Request transition failures | see `wave1-baseline-known-fail.md` | **KNOWN-RISK** — not block |

---

## DEBT-W3-01 detail

| Field | Value |
|-------|--------|
| Status | **CLOSED** |
| Resolution | `CheckInAction` / `CheckOutAction` call `RequestStayLifecycleCommandPort` after persist; bridge uses Request mutators + save |
| Evidence | `RequestStayLifecycleCommandBridge`; `CheckInOutFlowTest` request-sourced case |
| No-op | Allocations without `sourceRequestId` skip Request transition (manual path unchanged) |
| Governance ref | OA-05-03 · CD-010 · Lead DEBT-W3-01 resolution |
| Expiry | n/a (closed) |

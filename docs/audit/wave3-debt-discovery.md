# DEBT-DISCOVERY — Completion Wave 3 (W3-B)

**Registered:** 2026-07-21 · **Source wave:** Completion Wave 3 Option W3-B  
**Parent design:** `docs/audit/wave3-state-machine-design.md`

---

## Register

| ID | Description | File(s) | Decision |
|----|-------------|---------|----------|
| DEBT-W3-01 | CheckIn/CheckOut do not advance Request status (`checked_in` / `checked_out`) | CheckIn actions; Request entity mutators exist unused by CheckIn | **OPEN** — states+events ready; consumer wiring needs Lead HD |
| W3-WP-WF-04-RISK | Baseline Request transition failures | see `wave1-baseline-known-fail.md` | **KNOWN-RISK** — not block |

---

## DEBT-W3-01 detail

| Field | Value |
|-------|--------|
| Status | **OPEN** |
| Bypass comment | Inline on `Request::markCheckedIn` / `markCheckedOut` + event classes |
| Governance ref | `docs/audit/wave3-debt-discovery.md` · OA-05-03 |
| Expiry | Until Lead authorizes CheckIn→Request lifecycle port WP |

# Audit Status Matrix

> **Source of truth:** `.dormSys/progress-log.md` only.  
> **Rule:** Rows require an explicit log status of `COMPLETE`, `OPEN`, or `SKIPPED`. No inferred statuses.  
> **Generated:** 1405/04/31 | 2026-07-22 (wave `AUDIT-MATRIX-INIT`)

| Task | Status | Note |
|------|--------|------|
| DOM-GAP-03-RESTORE | COMPLETE | Log L16: `COMPLETE: restored 7 GAP-02 belongsTo…`. Log L34: `restored 7/7` — prior COMPLETE claim superseded on release (disk was 0/7). |
| DOM-GAP-04-COMPLETE-B | COMPLETE | Log L17: `COMPLETE: employee() belongsTo on RequestModel + RequestMemberModel`. Log L36: `RESTORE-04-05 — restore 5/5` (includes this pair). |
| DOM-GAP-05-FK-BELONGS | COMPLETE | Log L18: `COMPLETE: AllocationItemModel.bed, RequestModel.assignedStage1Approver, DormitoryAssignment.user`. Log L36: `RESTORE-04-05 — restore 5/5`. |
| DOM-PARITY-CYCLE-CLOSE | COMPLETE | Log L37: `all GAP-03/04/05/08/10 relations restored; parity cycle complete.` |
| DOM-GAP-10-CLOSE (A2) | OPEN | Log L33: `OPEN DECISION: A2 (LotteryResultModel / registration_id drift…)`. |
| DOM-GAP-10-IMPL-XMOD (A2, A3) | SKIPPED | Log L32: `2 skipped (A3=OMIT per DOM-GAP-09B, A2=DRIFT)`. |
| DOM-GAP-RESTORE-08-10 (A2/A3) | SKIPPED | Log L35: `restore 26/26 …; A2/A3 skipped.` |

## Excluded from this matrix (no COMPLETE / OPEN / SKIPPED token in log)

These appear in progress-log but lack an explicit `COMPLETE` / `OPEN` / `SKIPPED` status word — not listed above (no speculation):

- DOM-GAP-01-DECIDE (ratified)
- DOM-GAP-02-FIX (status DONE)
- DOM-GAP-06-AUDIT (CLOSED / awaiting)
- DOM-GAP-07-DISCOVERY (awaiting ratification)
- DOM-GAP-08-IMPL-VOUCHER (added; no COMPLETE token — covered under DOM-PARITY-CYCLE-CLOSE narrative)
- DOM-GAP-09* (pending / OMIT — OMIT is not COMPLETE/OPEN/SKIPPED)
- DOM-GAP-RESTORE / DOM-GAP-RESTORE-08-10 / DOM-GAP-RESTORE-04-05 as whole-task COMPLETE rows (restore counts without COMPLETE token; A2/A3 skip row retained where SKIPPED is explicit)

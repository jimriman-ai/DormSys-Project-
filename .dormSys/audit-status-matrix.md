# Audit Status Matrix

> **Source of truth:** `.dormSys/progress-log.md` only.  
> **Rule:** Rows require an explicit log status of `COMPLETE` | `OPEN` | `SKIPPED` | `CLOSED` | `DONE` | `OMIT`. No inferred statuses.  
> **Generated:** 1405/04/31 | 2026-07-22 (wave `AUDIT-MATRIX-INIT`)  
> **Extended:** 1405/04/31 | 2026-07-22 (wave `AUDIT-MATRIX-EXTEND`)  
> **Synced:** 1405/05/01 | 2026-07-23 (wave `AUDIT-MATRIX-SYNC`)

| Task | Status | Note |
|------|--------|------|
| DOM-GAP-03-RESTORE | COMPLETE | Log L16: `COMPLETE: restored 7 GAP-02 belongsTo…`. Log L34: `restored 7/7` — prior COMPLETE claim superseded on release (disk was 0/7). |
| DOM-GAP-04-COMPLETE-B | COMPLETE | Log L17: `COMPLETE: employee() belongsTo on RequestModel + RequestMemberModel`. Log L36: `RESTORE-04-05 — restore 5/5` (includes this pair). |
| DOM-GAP-05-FK-BELONGS | COMPLETE | Log L18: `COMPLETE: AllocationItemModel.bed, RequestModel.assignedStage1Approver, DormitoryAssignment.user`. Log L36: `RESTORE-04-05 — restore 5/5`. |
| DOM-PARITY-CYCLE-CLOSE | COMPLETE | Log L37: `all GAP-03/04/05/08/10 relations restored; parity cycle complete.` |
| DOM-GAP-10-CLOSE (A2) | OPEN | Log L33: `OPEN DECISION: A2 (LotteryResultModel / registration_id drift…)`. |
| DOM-GAP-10-IMPL-XMOD (A2, A3) | SKIPPED | Log L32: `2 skipped (A3=OMIT per DOM-GAP-09B, A2=DRIFT)`. |
| DOM-GAP-RESTORE-08-10 (A2/A3) | SKIPPED | Log L35: `restore 26/26 …; A2/A3 skipped.` |
| DOM-GAP-02-FIX | DONE | Log L15: `status DONE`. |
| DOM-GAP-06-AUDIT | CLOSED | Log L20: `CLOSED: UUID-only policy ratified…`. Log L21–23: duplicate `CLOSED` block. |
| DOM-GAP-09B-CLOSE (A3) | OMIT | Log L30: `Lead decision: OMIT bed() on Allocation header…`. A3=OMIT confirmed; not an OPEN decision in `.dormSys/open-decisions.md`. |
| D4-CLOSE | CLOSED | Log L42: `D4 CLOSED in open-decisions…`. open-decisions Closed row: D4 Status=CLOSED (wave D4-CLOSE). |
| DOMAIN-COMPLETENESS-SWEEP | COMPLETE | Log L49: Found/Fixed/drift/blocked summary; Log L50: `DOMAIN-COMPLETENESS-SWEEP COMPLETE (matrix backfill)`. |
| ALLOC-DOC-ALIGN-01 | COMPLETE | Log L50: `COMPLETE: AllocationItemModel::bed() PHPDoc aligned (Eloquent-only; no physical FK per map+mig)`. |
| DOMAIN-GAP-DISCOVERY-02 | COMPLETE | Log: `COMPLETE: found 5 gaps (0 fixable, 5 decision-gated)`; report `.dormSys/domain-gap-report-02.md`. |
| DECISION-PACKAGE-01 | COMPLETE | Log: `COMPLETE: 5 decisions packaged`; package `.dormSys/decision-package-01.md`; open-decisions +3 OPEN (DP-ALLOC-ITEM-BED-FK, DP-BED-SIGNAL-OWNERSHIP, DP-XMOD-BELONGS); A2 unchanged OPEN; A3 not reopened. |

## Excluded (no COMPLETE / OPEN / SKIPPED / CLOSED / DONE / OMIT token)

- DOM-GAP-01-DECIDE — Log L14: `Lead ratified` (no listed status token)
- DOM-GAP-07-DISCOVERY — Log L26: `awaiting Lead ratification` (no listed status token)
- DOM-GAP-08-IMPL-VOUCHER — Log L27: `6 intra-voucher relations added` (no listed status token; narrative covered by DOM-PARITY-CYCLE-CLOSE)
- DOM-GAP-09-DISCOVER-CROSSMOD — Log L28: `7 decisions pending Lead` (no listed status token)
- DOM-GAP-09B-VERIFY-A3 — Log L29: `decision-ready` (no listed status token)
- DOM-GAP-RESTORE / DOM-GAP-RESTORE-04-05 as whole-task COMPLETE — restore counts without COMPLETE token

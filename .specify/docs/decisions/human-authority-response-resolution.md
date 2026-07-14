---
artifact: human_authority_response_resolution
status: PARTIAL_RESOLVED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
phase: CORE_COMPLETION_WAVE
authorized_surface: employee-request-self-service
business_owner_status: UNRESOLVED
spec04_auth_readiness: BLOCKED
feature_contract_readiness: BLOCKED
recommended_next_gate: SURFACE_REAUTHORIZATION_AND_OWNER_DESIGNATION_REQUIRED
upstream_clarification_packet: .specify/docs/clarifications/human-domain-authority-clarification.md
upstream_surface_decision: .specify/docs/decisions/product-surface-authorization-decision.md
hdac05_requery: .specify/docs/clarifications/hdac-05-business-owner-requery.md
hdac06_change_request: .specify/docs/decisions/hdac-06-manager-approval-scope-change-request.md
date: 2026-07-13
---

# Human Authority Response Resolution (HDAC-01..06)

**Artifact type:** Governance resolution record (docs-only; non-authorizing)  
**Mode:** Record authoritative human inputs only — **no invented answers**, **no inferred Business Owner**, **no silent scope merge**, **no policy authoring**, **no implementation**.

**Clarification source:** `.specify/docs/clarifications/human-domain-authority-clarification.md`  
**Overall Status:** `PARTIAL_RESOLVED`  
**NEXT_GATE:** `SURFACE_REAUTHORIZATION_AND_OWNER_DESIGNATION_REQUIRED`

---

## 1. Purpose

Record authoritative human responses for HDAC-01..06, resolve Business Owner **only if** formally designated, register required re-query / change-request actions where answers are insufficient or conflict with baseline, and decide whether Spec04 Auth packet preparation may start.

This artifact does **not** invent missing answers, authorize UI/implementation, merge scope changes silently, or author Auth policy.

---

## 2. Prior Governance Baseline

| Item | Status |
| ---- | ------ |
| Authorized surface | `employee-request-self-service` |
| B1 packet | `COMPLETE` — questions HDAC-01..06 packaged |
| Owner field | `PENDING_HUMAN_DESIGNATION` |
| Spec04 Auth readiness | `BLOCKED` |
| Implementation / UI | **NO** |
| Prior conflicting owner term | «واحد اداری / منابع انسانی» — `CONFLICTING_TERM` (must not use as designation without formal re-authorization) |
| Prior human surface baseline | Audience authenticated employee; own-request create/list/detail/status; manager approval **explicitly out of scope** |

---

## 3. Human Authority Inputs Received

### Intake check (this resolution cycle)

| Source | HDAC coverage | Result |
| ------ | ------------- | ------ |
| Authoritative human response set (this update) | HDAC-01..06 | **Received** — see per-question records below |
| `.specify/docs/clarifications/human-domain-authority-clarification.md` | Question framing | Unchanged |
| `.specify/docs/decisions/product-surface-authorization-decision.md` | Current authorized baseline | Remains binding until re-authorization; HDAC-06 conflicts with this baseline |

**Rule applied:** Human responses are recorded as given. HDAC-05 is **not** treated as a designation. HDAC-06 is **not** merged into the authorized surface without a formal change request and re-authorization.

### Per-question input records

QUESTION_ID: HDAC-01  
INPUT_RECEIVED: YES  
AUTHORITY_SOURCE: Human authority response (this cycle)  
INPUT_QUALITY: SUFFICIENT  
NORMALIZED_DECISION_STATEMENT: HR is a formal organizational unit.

QUESTION_ID: HDAC-02  
INPUT_RECEIVED: YES  
AUTHORITY_SOURCE: Human authority response (this cycle)  
INPUT_QUALITY: SUFFICIENT  
NORMALIZED_DECISION_STATEMENT: Dormitory Unit is a formal organizational unit.

QUESTION_ID: HDAC-03  
INPUT_RECEIVED: YES  
AUTHORITY_SOURCE: Human authority response (this cycle)  
INPUT_QUALITY: SUFFICIENT_WITH_FOLLOW_ON  
NORMALIZED_DECISION_STATEMENT: Approver is determined by Department + Dormitory combination. Formal authority mapping remains required in later stages.

QUESTION_ID: HDAC-04  
INPUT_RECEIVED: YES  
AUTHORITY_SOURCE: Human authority response (this cycle)  
INPUT_QUALITY: SUFFICIENT  
NORMALIZED_DECISION_STATEMENT: Employee can see only their own requests.

QUESTION_ID: HDAC-05  
INPUT_RECEIVED: YES (insufficient for designation)  
AUTHORITY_SOURCE: Human authority response (this cycle)  
INPUT_QUALITY: INSUFFICIENT_REQUIRES_REQUERY  
NORMALIZED_DECISION_STATEMENT: Received text describes shared / multi-part stakeholders (“The request may belong to multiple parts and is shared across other parts.”). This does **not** designate one final business authority accountable for approval and change decisions. No final Business Owner designated.

QUESTION_ID: HDAC-06  
INPUT_RECEIVED: YES (conflicts with baseline)  
AUTHORITY_SOURCE: Human authority response (this cycle)  
INPUT_QUALITY: SCOPE_CHANGE_SIGNAL  
NORMALIZED_DECISION_STATEMENT: Human states manager approve/reject **must be in scope**. This conflicts with the current authorized baseline (manager approval out of scope). Must not be merged silently; formal change request + re-authorization required.

---

## 4. Resolution Status by Question

QUESTION_ID: HDAC-01  
QUESTION_TOPIC: Meaning of HR  
RESOLUTION_STATUS: RESOLVED  
AUTHORITATIVE_DECISION: HR is a formal organizational unit.  
DOWNSTREAM_USABILITY: USABLE_AS_DOMAIN_AUTHORITY_INPUT  
REMAINS_BLOCKING: NO (for meaning clarification)  
NOTES: Repo previously lacked an HR org-unit entity; human decision supersedes prior evidence-only COMBINATION classification for **authority meaning**. Schema/entity introduction is **not** authorized by this record.

QUESTION_ID: HDAC-02  
QUESTION_TOPIC: Meaning of Dormitory Unit  
RESOLUTION_STATUS: RESOLVED  
AUTHORITATIVE_DECISION: Dormitory Unit is a formal organizational unit.  
DOWNSTREAM_USABILITY: USABLE_AS_DOMAIN_AUTHORITY_INPUT  
REMAINS_BLOCKING: NO (for meaning clarification)  
NOTES: Repo previously had stage/role labels only; human decision establishes Unit as formal org unit for authority meaning. Entity/schema introduction is **not** authorized by this record. Manager vs Staff role naming may still need later mapping.

QUESTION_ID: HDAC-03  
QUESTION_TOPIC: Approver determination basis  
RESOLUTION_STATUS: RESOLVED  
AUTHORITATIVE_DECISION: Approver is determined by Department + Dormitory combination.  
DOWNSTREAM_USABILITY: USABLE_AS_BASIS_DIRECTION  
REMAINS_BLOCKING: PARTIAL — formal authority mapping still required in later stages  
NOTES: Department↔Dormitory structural link remains a prior evidence GAP; combination basis is human-decided intent, not an implemented mapping.

QUESTION_ID: HDAC-04  
QUESTION_TOPIC: Which requests can the employee see?  
RESOLUTION_STATUS: RESOLVED  
AUTHORITATIVE_DECISION: Employee can see only their own requests.  
DOWNSTREAM_USABILITY: USABLE_FOR_SURFACE_CONTRACT_AND_AUTH_SCOPE  
REMAINS_BLOCKING: NO (for employee self-visibility)  
NOTES: Aligns with prior surface baseline and principal ownership evidence. Does not by itself authorize manager/approver visibility if HDAC-06 later expands scope via re-authorization.

QUESTION_ID: HDAC-05  
QUESTION_TOPIC: Who is the business owner of this feature?  
RESOLUTION_STATUS: REQUIRES_REQUERY  
AUTHORITATIVE_DECISION: — (none accepted as final designation)  
DOWNSTREAM_USABILITY: NOT_USABLE  
REMAINS_BLOCKING: YES (HARD BLOCKER)  
NOTES: Shared-stakeholder wording preserved for audit; re-query registered at `.specify/docs/clarifications/hdac-05-business-owner-requery.md`. `business-owner-designation.md` **not** created.

QUESTION_ID: HDAC-06  
QUESTION_TOPIC: Should manager approve/reject be in scope?  
RESOLUTION_STATUS: SCOPE_CHANGE_TRIGGER - PENDING FORMAL CHANGE REQUEST  
AUTHORITATIVE_DECISION: — (not merged into current authorized surface)  
DOWNSTREAM_USABILITY: NOT_MERGED  
REMAINS_BLOCKING: YES (HARD BLOCKER for silent merge / contract update)  
NOTES: Conflicts with `.specify/docs/decisions/product-surface-authorization-decision.md`. Change request issued at `.specify/docs/decisions/hdac-06-manager-approval-scope-change-request.md`. Current baseline remains self-service until re-authorization.

---

## 5. Business Owner Resolution Status

| Field | Value |
| ----- | ----- |
| Formal designation received this cycle | **NO** |
| Received non-designating response | Shared / multi-part stakeholder description (audit retained) |
| `business-owner-designation.md` created/updated | **NO** |
| BUSINESS_OWNER_STATUS | `UNRESOLVED` |
| HDAC-05 action | `REQUERY_REQUIRED` |
| Re-query artifact | `.specify/docs/clarifications/hdac-05-business-owner-requery.md` |
| Forbidden substitutions | Inferring owner from HR org unit, Dormitory Unit, or shared-parts wording |

### HDAC-05 formal re-query (preserved)

```text
مسئول نهایی تأیید کسب‌وکاری این فیچر کدام است؟
- الف) مدیر منابع انسانی
- ب) مدیر خوابگاه
- ج) هر دو — در این صورت چه کسی در صورت اختلاف تصمیم می‌گیرد؟
- د) نقش/سمت دیگری: ___
```

---

## 6. Residual Blocking Assessment

| Blocker | Severity | Status |
| ------- | -------- | ------ |
| HDAC-05 Business Owner | HIGH | `REQUIRES_REQUERY` — HARD BLOCKER |
| HDAC-06 scope change (manager approve/reject) | HIGH | `SCOPE_CHANGE_TRIGGER` — pending formal CR + re-authorization; **do not merge silently** |
| HDAC-01 / HDAC-02 meaning | — | RESOLVED (meaning only; no impl) |
| HDAC-03 combination basis | MEDIUM | RESOLVED as direction; mapping follow-on still required later |
| HDAC-04 employee visibility | — | RESOLVED |
| Implementation / UI | — | Still unauthorized |
| Spec04 Auth packet | HIGH | Still BLOCKED |

**Progress:** HDAC-01, HDAC-02, HDAC-03, HDAC-04 resolved for clarification purposes.  
**Hard blockers remaining:** HDAC-05 (owner designation), HDAC-06 (scope re-authorization path).

---

## 7. Downstream Readiness Decision

| Readiness | Decision | Rationale |
| --------- | -------- | --------- |
| Spec04 Auth packet preparation | **BLOCKED** | No final Business Owner; HDAC-06 scope conflict unresolved via re-authorization |
| Feature contract stabilization | **BLOCKED** | Cannot silently expand contract for manager approve/reject; owner designation missing |
| Surface baseline | **UNCHANGED** until re-authorization | Current authorized surface remains self-service-only for approval participation |
| Implementation Authorization | **NO** | Hard stop |
| UI Authorization | **NO** | Hard stop |

**Recommended next gate:** `SURFACE_REAUTHORIZATION_AND_OWNER_DESIGNATION_REQUIRED`

Required before Auth packet prep:

1. Complete HDAC-05 re-query with a single final accountable Business Owner (or explicit dual-owner conflict rule).  
2. Complete HDAC-06 formal change request and product surface **re-authorization** if manager approve/reject is to enter scope.  
3. Only then consider Spec04 Auth packet preparation under the re-authorized boundary.

---

## 8. Final Status Lines

```text
Overall Status: PARTIAL_RESOLVED

HUMAN_AUTHORITY_RESPONSE_STATUS: PARTIAL_RESOLVED

AUTHORIZED_SURFACE: employee-request-self-service

BUSINESS_OWNER_STATUS: UNRESOLVED

SPEC04_AUTH_READINESS: BLOCKED

FEATURE_CONTRACT_READINESS: BLOCKED

IMPLEMENTATION_AUTHORIZED: NO

UI_AUTHORIZED: NO

NEXT_GATE: SURFACE_REAUTHORIZATION_AND_OWNER_DESIGNATION_REQUIRED

RECOMMENDED_NEXT_GATE: SURFACE_REAUTHORIZATION_AND_OWNER_DESIGNATION_REQUIRED

HDAC-05_ACTION: REQUERY_REQUIRED
HDAC-05_OUTCOME: NO_FINAL_BUSINESS_OWNER_DESIGNATED

HDAC-06_ACTION: FORMAL_CHANGE_REQUEST_REQUIRED
HDAC-06_OUTCOME: DO_NOT_MERGE_SILENTLY

APPLICATION_FILES_MODIFIED: NO
```

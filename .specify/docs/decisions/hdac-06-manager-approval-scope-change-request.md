---
artifact: hdac_06_manager_approval_scope_change_request
status: PENDING_FORMAL_REAUTHORIZATION
mutation_permission: none
execution_authority: none
question_id: HDAC-06
trigger: SCOPE_CHANGE_TRIGGER
merge_rule: DO_NOT_MERGE_SILENTLY
authorized_surface_baseline: employee-request-self-service
upstream_surface_decision: .specify/docs/decisions/product-surface-authorization-decision.md
upstream_resolution: .specify/docs/decisions/human-authority-response-resolution.md
date: 2026-07-13
---

# HDAC-06 Manager Approve/Reject Scope Change Request

**Artifact type:** Formal change-request registration (governance-only; docs-only)  
**Question ID:** `HDAC-06`  
**Classification:** `SCOPE_CHANGE_TRIGGER - PENDING FORMAL CHANGE REQUEST`  
**Merge rule:** `DO_NOT_MERGE_SILENTLY`  

This artifact is **non-authorizing by itself**. It registers a proposed scope expansion; it does **not** alter the currently authorized surface boundary.

---

## 1. Purpose

Record a formal change request to expand the authorized product surface so that **manager approve/reject** actions are in scope, after human input conflicted with the existing self-service-only baseline.

---

## 2. Current Binding Baseline (Still in Force)

**Upstream binding decision:** `.specify/docs/decisions/product-surface-authorization-decision.md`

| Field | Current authorized value |
| ----- | ------------------------ |
| Surface | `employee-request-self-service` |
| Audience | authenticated employee |
| In scope | create / list / view detail / track status of **own** requests |
| Manager approve/reject | **Explicitly OUT of scope** |

**Governance rule:** This baseline remains **binding** until a formal surface **re-authorization** decision is issued.

---

## 3. Conflict Statement

**Human input received (HDAC-06):** manager approve/reject **MUST** be in scope.

**Conflict:** This request **conflicts** with the currently authorized self-service-only baseline, which places manager approve/reject **out of scope**.

Silent merge of the new scope into the product-surface decision, feature contract, clarification outcomes, or Auth packet inputs is **forbidden** (`DO_NOT_MERGE_SILENTLY`).

---

## 4. Proposed Scope Expansion

**Proposal (not yet authorized):**

Add **manager approve/reject** actions to the product surface currently named `employee-request-self-service` (or to a re-authorized successor boundary defined by a future re-authorization decision).

Until re-authorization:

- Proposed expansion = **pending only**  
- Current authorized contract boundary = **unchanged**

---

## 5. Impact (If Later Accepted via Re-Authorization)

If and only if a formal surface re-authorization accepts this expansion:

| Impact area | Effect |
| ----------- | ------ |
| Actors | A new actor enters scope: **manager/approver** |
| HDAC-03 | Department + Dormitory approver basis becomes **directly relevant** to surface Auth/contract work |
| HDAC-04 | Employee own-request visibility remains; **manager-side visibility** may require extension and must be decided explicitly |
| Downstream | Contract/Auth work **cannot** proceed on the old self-service-only surface assumption |

This section describes impact for planning awareness only. It does **not** authorize those extensions.

ذکر ارتباط HDAC-03 به‌معنای تکمیل نگاشت ساختاری Department↔Dormitory یا مجوز طراحی Policy نیست.

---

## 6. Explicit Non-Effects of This Change Request

This change-request artifact does **not**:

- re-authorize the surface by itself  
- define workflow steps or states  
- define permissions or Auth policies  
- authorize implementation  
- authorize UI  
- unblock Spec04 Auth packet preparation by itself  
- silently update feature-contract boundaries  

---

## 7. Required Next Action

A **formal surface re-authorization decision** must be issued before manager approve/reject scope enters the authorized contract boundary.

تا وقتی `BUSINESS_OWNER_STATUS` برابر `UNRESOLVED` است، این CR **واجد شرایط تصمیم بازمجوزدهی نهایی سطح** نیست؛ ابتدا باید پاسخ کافی HDAC-05 ثبت شود.

Until that decision exists:

1. Preserve current baseline (`employee-request-self-service`, manager approve/reject out of scope).  
2. Do not merge HDAC-06 into contracts, Auth packets, or implementation plans.  
3. Keep Spec04 Auth readiness and feature-contract readiness **BLOCKED** relative to any expanded-scope assumption.

---

## 8. Status Block

```text
HDAC06_CHANGE_REQUEST_STATUS: PENDING_FORMAL_REAUTHORIZATION

CURRENT_AUTHORIZED_SURFACE: employee-request-self-service (UNCHANGED)

MANAGER_APPROVE_REJECT_IN_AUTHORIZED_SCOPE: NO

PROPOSED_EXPANSION: manager approve/reject MUST be in scope (PENDING)

MERGE_RULE: DO_NOT_MERGE_SILENTLY

REAUTHORIZATION_DECISION_READINESS: BLOCKED_PENDING_BUSINESS_OWNER

SPEC04_AUTH_READINESS: BLOCKED

FEATURE_CONTRACT_READINESS: BLOCKED

BLOCKS: SPEC04_AUTH_READINESS, FEATURE_CONTRACT_READINESS

IMPLEMENTATION_AUTHORIZED: NO

UI_AUTHORIZED: NO

APPLICATION_FILES_MODIFIED: NO
```

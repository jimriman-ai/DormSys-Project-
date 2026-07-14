---
artifact: human_domain_authority_clarification_packet
status: COMPLETE
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
phase: CORE_COMPLETION_WAVE
gate: B1_HUMAN_DOMAIN_AUTHORITY_CLARIFICATION
authorized_surface: employee-request-self-service
owner_field_status: PENDING_HUMAN_DESIGNATION
spec04_auth_readiness: BLOCKED
recommended_next_gate: HUMAN_RESPONSE_REQUIRED
date: 2026-07-13
---

# Human Domain Authority Clarification Packet (Gate B1)

**Artifact type:** Human clarification packet (non-deciding; non-authorizing)  
**Mode:** Question packaging only — does **not** resolve policy, assign owners, finalize actor semantics, prepare Auth rules as approved, or authorize UI/implementation.

---

## 1. Purpose

This packet exists to obtain **human authority decisions** on the minimum unresolved domain-authority questions that cannot be closed safely from repository evidence alone.

Unresolved ambiguity must not be closed by agent inference. This artifact grants **no** implementation authorization, **no** UI authorization, and **no** Spec04 Auth packet readiness.

---

## 2. Current Governance Status

| Item | Status |
| ---- | ------ |
| Authorized surface | `employee-request-self-service` |
| Audience | authenticated employee |
| Approved capabilities | create / list / view detail / track status of **own** requests |
| Explicitly out of scope (surface decision) | manager approval; dormitory allocation; reporting; lottery; workflow activation; full RBAC; OA admin UI |
| Business Owner / owner field | `PENDING_HUMAN_DESIGNATION` |
| Spec04 Auth readiness | `BLOCKED` |
| Implementation authorized | **NO** |
| UI authorized | **NO** |
| Relationship evidence consolidation | `COMPLETE` (`.specify/docs/discovery/domain-entity-relationship-map.md`) |
| Prior owner formalization | `CONFLICTING_TERM` — proposed «واحد اداری / منابع انسانی» rejected as unsupported |
| Prior owner authority clarification | `OWNER_DECISION_REQUIRED_TO_PROCEED` |

No new governance decisions are made in this section.

---

## 3. Evidence Baseline

### TOPIC: HR meaning

CURRENT_EVIDENCE_STATE: SUFFICIENT_FOR_QUESTION_FRAMING  
EVIDENCE_REFERENCES:
- `.specify/docs/discovery/hr-semantic-evidence-clarification.md` (evidence classification COMBINATION: role + workflow stage; not org unit)
- `ApprovalStage::HR`; `PendingHRState`; Spec05 FR-008 / US3; constitution “HR Manager”; architecture `hr_manager`
- Absence: no HR Department/org entity under Employee  
WHY_EVIDENCE_ALONE_IS_NOT_ENOUGH: Evidence describes usage patterns; it does not constitute a **human-accepted** authority definition for Auth/contract wording, nor which principal is bound as “HR” for a request.

### TOPIC: Dormitory Unit meaning

CURRENT_EVIDENCE_STATE: SUFFICIENT_FOR_QUESTION_FRAMING  
EVIDENCE_REFERENCES:
- `ApprovalStage::DormitoryUnit`; `PendingDormitoryUnitState`; Spec05 stage vocabulary
- Constitution “Dormitory Unit Staff”; Discovery “Dormitory Unit Manager”; architecture `dormitory_unit_staff`
- Relationship / authority maps: no Dormitory Unit Domain Entity / migration  
WHY_EVIDENCE_ALONE_IS_NOT_ENOUGH: Stage/role labels conflict (Manager vs Staff); no entity. Human must decide whether Unit is entity, operational label, or stage/role only before Auth scoping.

### TOPIC: Approver assignment basis

CURRENT_EVIDENCE_STATE: PARTIAL  
EVIDENCE_REFERENCES:
- Employee↔Department EXPLICIT (`department_id` FK); `Department.managerId` present
- Request stages Dept → HR → Dorm → Unit; Request↔ApprovalChain EXPLICIT (CD-010)
- Domain discovery A3: Dept Manager ↔ `manager_id` **implied only**
- Department↔Dormitory relationship: **GAP**  
WHY_EVIDENCE_ALONE_IS_NOT_ENOUGH: No closed rule that Stage-1 = department manager of requester’s department, or that dorm stages bind by `dormitory_id` / site hierarchy. Mixed vs single-hierarchy model not decided.

### TOPIC: Request visibility basis

CURRENT_EVIDENCE_STATE: PARTIAL  
EVIDENCE_REFERENCES:
- Self-visibility EXPLICIT: `listByEmployee*`; `RequestPrincipalEmployeeResolver::assertOwnsSummary`
- Domain discovery A8 / visibility evidence check: approver visibility **Not Found** as closed law
- Surface decision: manager approval out of scope (does not define who may *view*)  
WHY_EVIDENCE_ALONE_IS_NOT_ENOUGH: Own-request path is evidenced; non-owner view rules are absent. Cannot invent inbox/audience for Auth packet.

### TOPIC: Business owner designation

CURRENT_EVIDENCE_STATE: WEAK_BUT_PRESENT  
EVIDENCE_REFERENCES:
- `.specify/docs/decisions/business-owner-formalization-review.md` (`CONFLICTING_TERM`)
- `.specify/docs/decisions/product-surface-owner-authority-clarification.md` (`PENDING_HUMAN_DESIGNATION`)
- Catalog Authority Map: no product-surface Business Owner class; constitution Product Owner = process authority (not mapped to this surface)  
WHY_EVIDENCE_ALONE_IS_NOT_ENOUGH: No human designation recorded. Inference from roles/departments is forbidden.

### TOPIC: Approval flow vs authorized surface scope

CURRENT_EVIDENCE_STATE: SUFFICIENT_FOR_QUESTION_FRAMING  
EVIDENCE_REFERENCES:
- `.specify/docs/decisions/product-surface-authorization-decision.md` — manager approval **explicitly out of scope**; self-service create/list/detail/status **in scope**
- Spec05 / CD-010: approval chain exists in Request BC (product capability elsewhere); Workflow deferred  
WHY_EVIDENCE_ALONE_IS_NOT_ENOUGH: Surface exclusion is recorded, but humans must confirm whether Auth/feature-contract work for this surface may **omit** approval participation entirely, or must still reference approval lifecycle as read-only status tracking only.

---

## 4. Required Human Clarification Questions

### HDAC-01

QUESTION_ID: HDAC-01  
QUESTION: For DormSys, what is “HR” meant to be as a domain/authority concept: an organizational unit, a job role, a workflow-stage label, or a combination?  
CURRENT_EVIDENCE: Repo evidence shows HR as approval stage (`hr` / `pending_hr`) and as role label (“HR Manager” / `hr_manager`). No HR organizational entity/table found. Prior evidence clarification classified usage as combination (role + stage) — that was evidence packaging, not a human authority acceptance.  
WHY_THIS_MATTERS: actor semantics; authority model; auth basis; feature contract boundary  
DECISION_OPTIONS:
- organizational-unit
- role
- workflow-stage-label
- combination (role + workflow-stage-label)
- combination including organizational-unit
- out-of-scope for current feature (self-service only)
RECOMMENDED_DEFAULT: DEFER_TO_HUMAN_OWNER  
REQUIRED_HUMAN_AUTHORITY: JOINT_DECISION_REQUIRED  
BLOCKS: SPEC04_AUTH_BASIS; AUTHORIZATION_POLICY_MODEL; FEATURE_CONTRACT_STABILIZATION  
IF_UNANSWERED_RISK: Auth packet may encode wrong PEP/audience (treat HR as department or invent org unit).  
STATUS: OPEN  

### HDAC-02

QUESTION_ID: HDAC-02  
QUESTION: Is “Dormitory Unit” a formal organizational or location entity/boundary in the product model, or only an operational / stage / role label?  
CURRENT_EVIDENCE: Explicit as approval stage `dormitory_unit` and as constitution/architecture role language (“Unit Staff” / `dormitory_unit_staff`; Discovery “Unit Manager”). No Unit Domain Entity or `dormitory_units` table found. Manager vs Staff labels conflict across sources.  
WHY_THIS_MATTERS: actor semantics; ownership; authority model; auth basis; scope control  
DECISION_OPTIONS:
- formal-entity-or-boundary
- operational-label-only
- workflow-stage-and-or-role-label-only
- mixed (entity to be introduced later; currently label-only)
- out-of-scope for current feature
RECOMMENDED_DEFAULT: DEFER_TO_HUMAN_OWNER  
REQUIRED_HUMAN_AUTHORITY: JOINT_DECISION_REQUIRED  
BLOCKS: SPEC04_AUTH_BASIS; AUTHORIZATION_POLICY_MODEL; FEATURE_CONTRACT_STABILIZATION  
IF_UNANSWERED_RISK: Auth/UI may invent Unit hierarchy or conflate Unit Staff with Manager.  
STATUS: OPEN  

### HDAC-03

QUESTION_ID: HDAC-03  
QUESTION: How should approvers be determined for a request: by employee organizational hierarchy (e.g. department), by dormitory/site hierarchy, by a mixed model, or by another explicit rule you designate?  
CURRENT_EVIDENCE: Employee may belong to a Department (`department_id`); Department may have `manager_id` (binding to Stage 1 implied only). Request references `dormitory_id` without cross-module FK. Department↔Dormitory structural link is a GAP. Four stages are explicit; instance binding is not.  
WHY_THIS_MATTERS: actor semantics; ownership; authority model; auth basis; visibility  
DECISION_OPTIONS:
- organizational-hierarchy
- dormitory-hierarchy
- mixed-model
- role-global-without-hierarchy
- human-designated-other-rule
- out-of-scope for current feature (self-service; no approver assignment in scope)
RECOMMENDED_DEFAULT: LIMIT_SCOPE_UNTIL_DECIDED  
REQUIRED_HUMAN_AUTHORITY: JOINT_DECISION_REQUIRED  
BLOCKS: SPEC04_AUTH_BASIS; AUTHORIZATION_POLICY_MODEL; VISIBILITY_RULE_DEFINITION; APPROVAL_SCOPE_BOUNDARY  
IF_UNANSWERED_RISK: Approver resolution and any future inbox Auth cannot be specified without inventing hierarchy rules.  
STATUS: OPEN  

### HDAC-04

QUESTION_ID: HDAC-04  
QUESTION: Who, beyond the submitting employee (request owner), is permitted to view a request, and on what basis (e.g. current-stage approver only, full chain, HR, dormitory ops, none for this surface)?  
CURRENT_EVIDENCE: Self-view/list/show ownership is explicit in Request principal/query paths and UI contracts. Approver/non-owner visibility is explicitly **not found** as closed Auth law in domain discovery. Surface authorized for own-request view only.  
WHY_THIS_MATTERS: visibility; auth basis; feature contract boundary; scope control  
DECISION_OPTIONS:
- owner-only-for-this-surface
- current-stage-approvers
- all-chain-approvers
- named-roles-additional-viewers
- mixed
- defer-non-owner-visibility-entirely-until-later-surface
RECOMMENDED_DEFAULT: LIMIT_SCOPE_UNTIL_DECIDED  
REQUIRED_HUMAN_AUTHORITY: PRODUCT_AUTHORITY  
BLOCKS: SPEC04_AUTH_BASIS; VISIBILITY_RULE_DEFINITION; FEATURE_CONTRACT_STABILIZATION  
IF_UNANSWERED_RISK: Packet may over-grant or under-specify read access; Anti-Leak / contract instability.  
STATUS: OPEN  

### HDAC-05

QUESTION_ID: HDAC-05  
QUESTION: Who is the formal Business Owner (accountable business authority) for the authorized surface `employee-request-self-service`?  
CURRENT_EVIDENCE: Owner field `PENDING_HUMAN_DESIGNATION`. Proposed wording «واحد اداری / منابع انسانی» classified `CONFLICTING_TERM` and must not be used. Constitution “Product Owner” exists as process authority but is not recorded as this surface’s Business Owner. No agent substitution from `hr_manager` / `department_manager` allowed.  
WHY_THIS_MATTERS: authority model; ownership; auth basis; feature contract boundary  
DECISION_OPTIONS:
- explicit-named-human-or-office-designation
- constitution-product-owner-as-accountable-party
- other-human-designated-authority
- remain-unresolved-and-keep-auth-blocked
RECOMMENDED_DEFAULT: NO_DEFAULT_SAFE  
REQUIRED_HUMAN_AUTHORITY: BUSINESS_OWNER  
BLOCKS: SPEC04_AUTH_BASIS; FEATURE_CONTRACT_STABILIZATION; IMPLEMENTATION_AUTHORIZATION; GOVERNANCE  
IF_UNANSWERED_RISK: Owner-bound Auth handoff remains blocked; conflicting labels may re-enter.  
STATUS: OPEN  

### HDAC-06

QUESTION_ID: HDAC-06  
QUESTION: For the currently authorized surface, is approval-flow participation (approve/reject by managers) inside scope, or is scope limited to self-service initiation and self-view/status tracking only?  
CURRENT_EVIDENCE: Product surface decision lists manager approval as **explicitly out of scope** and lists create/list/detail/status of own requests as in scope. Spec05 still defines approval lifecycle in the Request BC for the product overall.  
WHY_THIS_MATTERS: scope control; feature contract boundary; auth basis; approval scope boundary  
DECISION_OPTIONS:
- self-service-initiation-and-self-view-only
- self-view-includes-read-only-status-from-approval-lifecycle
- approval-participation-in-scope-for-this-surface
- approval-deferred-to-separate-authorized-surface
RECOMMENDED_DEFAULT: LIMIT_SCOPE_UNTIL_DECIDED  
REQUIRED_HUMAN_AUTHORITY: PRODUCT_AUTHORITY  
BLOCKS: APPROVAL_SCOPE_BOUNDARY; FEATURE_CONTRACT_STABILIZATION; SPEC04_AUTH_BASIS  
IF_UNANSWERED_RISK: Feature contract may illegally expand into manager approval or omit required status semantics.  
STATUS: OPEN  

---

## 5. Blocking Impact Map

| QUESTION_ID | BLOCKED_ARTIFACT_OR_GATE | BLOCK_TYPE | SEVERITY |
| ----------- | ------------------------ | ---------- | -------- |
| HDAC-01 | Spec04 Auth residual / Auth packet prep | AUTH | HIGH |
| HDAC-01 | Feature contract stabilization (`employee-request-self-service`) | CONTRACT | MEDIUM |
| HDAC-02 | Spec04 Auth residual / role-audience scoping | AUTH | HIGH |
| HDAC-02 | Feature contract actor vocabulary | CONTRACT | MEDIUM |
| HDAC-03 | Authorization policy / approver binding model | AUTH | HIGH |
| HDAC-03 | Visibility rule definition (if non-owner viewers) | VISIBILITY | HIGH |
| HDAC-03 | Approval scope boundary | SCOPE | MEDIUM |
| HDAC-04 | Visibility rule definition | VISIBILITY | HIGH |
| HDAC-04 | Spec04 Auth basis (read grants) | AUTH | HIGH |
| HDAC-04 | Feature contract read boundaries | CONTRACT | HIGH |
| HDAC-05 | Spec04 Auth packet handoff | AUTH | HIGH |
| HDAC-05 | Business Owner field / B2 designation | OWNERSHIP | HIGH |
| HDAC-05 | Implementation Authorization | GOVERNANCE | HIGH |
| HDAC-06 | Approval scope boundary | SCOPE | HIGH |
| HDAC-06 | Feature contract in/out capabilities | CONTRACT | HIGH |
| HDAC-06 | Spec04 Auth basis (mutation vs read-only) | AUTH | HIGH |

---

## 6. Decision Routing Guidance

**Business owner / product authority can answer (primary):**
- HDAC-05 (Business Owner designation) — required human designation
- HDAC-04 (visibility for this surface) — product audience intent
- HDAC-06 (approval inside vs outside this surface) — product scope intent

**Domain + architecture joint confirmation recommended:**
- HDAC-01 (HR meaning)
- HDAC-02 (Dormitory Unit meaning)
- HDAC-03 (approver assignment basis)

**Must exist before Spec04 Auth basis preparation (hard):**

```text
HARD_BLOCKERS_BEFORE_SPEC04_AUTH:
- HDAC-05 (named Business Owner / accountable authority)
- HDAC-04 (non-owner visibility posture for this surface — at least owner-only vs deferred)
- HDAC-06 (approval participation in vs out of this surface)
```

**May permit limited deferral only if explicitly recorded as deferred (still no implementation):**

```text
SOFT_DEFERRABLE_ITEMS:
- HDAC-01 detailed org-unit nuance IF humans explicitly classify HR as out-of-scope for this surface AND Auth packet is limited to self-service
- HDAC-02 entity-vs-label IF Unit remains out of this surface AND no Unit-scoped grants are attempted
- HDAC-03 hierarchy binding IF approval assignment is explicitly out of this surface (still required before any approver Auth surface)
```

Soft deferral does **not** authorize implementation, UI, or Auth packet prep.

```text
MINIMUM_DECISION_SET_TO_PROCEED:
- HDAC-05 answered with an explicit designation (not conflicting rejected term)
- HDAC-06 confirmed (self-service only vs any approval participation)
- HDAC-04 confirmed at least as owner-only-for-this-surface OR explicit additional viewers
```

Conservative rule: unanswered HARD_BLOCKERS → Spec04 Auth remains **BLOCKED**; no silent downgrade; no implementation progression.

---

## 7. Final Status Lines

```text
HUMAN_DOMAIN_AUTHORITY_CLARIFICATION_STATUS: COMPLETE

AUTHORIZED_SURFACE: employee-request-self-service

OWNER_FIELD_STATUS: PENDING_HUMAN_DESIGNATION

SPEC04_AUTH_READINESS: BLOCKED

IMPLEMENTATION_AUTHORIZED: NO

UI_AUTHORIZED: NO

RECOMMENDED_NEXT_GATE: HUMAN_RESPONSE_REQUIRED

APPLICATION_FILES_MODIFIED: NO
```

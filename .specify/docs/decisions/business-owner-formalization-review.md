---
artifact: business_owner_formalization_and_architecture_review
status: CONFLICTING_TERM
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
phase: CORE_COMPLETION_WAVE
owner_handling: BLOCK_PENDING_HUMAN_AUTHORITY
next_prompt_owner_field: UNRESOLVED
date: 2026-07-13
---

# Business Owner Formalization and Architecture Review

**Artifact type:** Governance evidence review (non-authorizing)  
**Status:** `CONFLICTING_TERM`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Trigger:** Human concern that proposed Business Owner wording «واحد اداری / منابع انسانی» must not be treated as valid until confirmed by repository evidence.

**Scope:** Documentation / governance / architecture review only. Does not assign an owner, invent org structure, authorize Auth/UI, or map roles to Business Owner.

---

## Evidence Reviewed

| Artifact | Path | Relevance |
| -------- | ---- | --------- |
| Spec catalog | `.specify/docs/spec-catalog.md` | Mirrors `BUSINESS_OWNER_STATUS: NOT_DEFINED`; next gate human clarification |
| Catalog decisions | `.specify/docs/catalog-decisions.md` | Governance Decision Authority Map (Design / IA / Batch only — no product-surface Business Owner class) |
| System flow | `.specify/docs/architecture/system-flow.md` | Workflow-centric flow; Request/Reporting/Lottery boundaries; no Business Owner org label |
| Architecture | `.specify/docs/architecture/dormsys-architecture.md` | RBAC role inventory (`department_manager`, `hr_manager`); Employee/Department module; Request states |
| Constitution | `.specify/memory/constitution.md` | Target Users roles; Product Owner as business-rule authority; §12 matrix |
| `qution.md` | — | **Not found** in repository (searched; no file by that name) |
| Domain / org discovery | `.specify/docs/discovery/domain-authority-and-organization-model-discovery.md` | Business Owner `NOT_DEFINED`; A7; A8 approver visibility gap |
| Entity/authority map | `.specify/docs/discovery/domain-entity-relationship-authority-map.md` | `BUSINESS_OWNER_STATUS: NOT_DEFINED`; self-ownership vs approver visibility |
| HR semantic clarification | `.specify/docs/discovery/hr-semantic-evidence-clarification.md` | HR = role + workflow stage; **not** organizational unit |
| Product auth gap triage | `.specify/docs/decisions/product-authorization-gap-triage.md` | Surface owner **BLOCKING**; `NO_NAMED_PRODUCT_SURFACE_AUTHORIZED` |
| Consolidation gate | `.specify/docs/decisions/domain-structure-evidence-consolidation-gate.md` | Formal business owner listed as unresolved input |
| Product auth example | `docs/product/product-authorization-next-ui-feature.md` | Authorized surface `audit-ui` only; Request UI **Excluded** |
| Spec03 department | `specs/003-employee-context/*` | `Department` aggregate + `employee_departments` (generic org structure) |
| Spec05 request | `specs/005-request-management/*` | Request `employee_id` ownership; approve actors; planned `request.approve.*` |
| Audit DTO example | `specs/010-audit-trail/contracts/audit-entry-dto.md` | Sample comment «تأیید منابع انسانی» (stage metadata illustration) |
| Request UI contracts / analysis | `docs/ui/contracts/requests/*`, `docs/ui/analysis/requests/*` | Employee-owned list/show; ownership enforcement; no approver inbox owner |

Repo-wide search for exact phrase «واحد اداری / منابع انسانی» and for «واحد اداری» returned **no matches**. Closest Persian strings found: architecture glosses «رئیس واحد» / «رئیس نیروی انسانی»; audit example «تأیید منابع انسانی».

---

## Exact Terminology Findings

### Proposed label: «واحد اداری / منابع انسانی»

| Question | Finding |
| -------- | ------- |
| Explicitly present as exact phrase? | **No** |
| Present as formal department name? | **No** |
| Present as role label? | **No** (exact string absent) |
| Present as stakeholder / authority owner? | **No** |
| Present as informal text? | **No** for this compound label |

**Classification of the proposed owner wording:** unsupported compound; **must not** be treated as a confirmed Business Owner, department entity, or governance authority.

### Closest evidence-backed concepts (not equivalent to the proposed owner)

| Concept | Form | Source evidence | Meaning | Not equal to Business Owner because |
| ------- | ---- | --------------- | ------- | ----------------------------------- |
| `Department` | Domain entity + table `employee_departments` | Spec03; Employee module | Generic organizational structure node (`code`, optional `manager_id` / `parent_id`) | Structure entity; no seed named «واحد اداری» or «منابع انسانی» as the Business Owner |
| `department_manager` / Department Manager | Role | Architecture RBAC; constitution Target Users / §12 | Stage 1 approver | Role / workflow actor — not product-surface governance owner |
| `hr_manager` / HR Manager | Role | Architecture RBAC; constitution; Spec05 US3 | Stage 2 approver | Role / workflow actor — HR clarification: **not** org unit |
| `ApprovalStage::HR` / `pending_hr` | Workflow stage | Request enums/states | Approval pipeline position | Stage label — not owner authority |
| «تأیید منابع انسانی» | Informal sample string | Audit entry DTO example metadata | Illustrative approval comment | Example text only |
| «رئیس واحد» / «رئیس نیروی انسانی» | Persian role glosses | `dormsys-architecture.md` roles tree | Localizations of `department_manager` / `hr_manager` | Role titles — not Business Owner |
| Constitution **Product Owner** | Process / governance role | Constitution § approval / escalation | Final authority on **business rules** and feature prioritization | Named process authority; **not** mapped in artifacts to «واحد اداری / منابع انسانی» or to employee-request-self-service surface ownership |
| Catalog Authority Map owners | Governance Review | `catalog-decisions.md` | Design Approval / Implementation Authorization / Batch Execution Permission | Does **not** define product-surface Business Owner for Auth/UI successor intake |
| Prior discovery status | Status flag | Domain discovery; entity map; spec-catalog | `Business Owner: NOT_DEFINED` / `BUSINESS_OWNER_STATUS: NOT_DEFINED` | Explicit absence of formalized owner |

### Distinctions already present in artifacts

| Concept | How artifacts treat it | Formalized as Business Owner? |
| ------- | ---------------------- | ----------------------------- |
| Department (org structure) | Employee BC entity | **No** |
| Manager / HR as role | Constitution + architecture role inventory + approval stages | **No** |
| Business Owner (product-surface authorization) | Repeatedly **NOT_DEFINED** / triage **BLOCKING** | **No** — gap, not a filled field |
| Request owner (submitting employee) | `requests.employee_id` + principal ownership checks | Ownership of **request records**, not product-surface Business Owner |
| Approval owner (CD-010) | Request BC owns `RequestApproval` state/history | Module ownership, not org Business Owner |

**Hard-stop compliance:** Related roles (`hr_manager`, `department_manager`) are **not** converted to Business Owner — no artifact maps them that way.

---

## Architecture and Authority Boundary Extract

Evidence-backed boundaries relevant to **employee-request-self-service** (for governance-safe wording in later authorization prompts):

| Boundary | Status in evidence | Anchor |
| -------- | ------------------ | ------ |
| Employee self-service only (list/show own requests) | **Clear for implemented read path** | `listByEmployee*` + `RequestPrincipalEmployeeResolver::assertOwnsSummary`; UI contracts: employee-owned list/show |
| Own-request ownership boundary | **Clear** | `employee_id` = submitting employee; non-owned show denied |
| Manager / HR approval | **Separate** (mutate/approve actors & stages) | Constitution chain; Spec05 US3; planned `request.approve.*`; mutation stage gate — **not** a closed “view others” rule |
| Approver visibility (who sees whose requests) | **Not defined** | Domain discovery A8; prior visibility evidence check |
| Workflow module orchestration | **Separate / deferred** | CD-010; system-flow: Workflow architecture approved, module deferred |
| Reporting | **Separate BC** | CD-017; system-flow read-model ownership; product triage: reporting ACL not Spec04 Auth residual |
| Lottery | **Separate / deferred for wave** | CD-011; core wave disposition DEFERRED |
| Dormitory admin / structure Auth | **Separate residual** | Spec02 structure keys; product triage; Request UI excluded from `audit-ui` grant |
| Full RBAC role→permission grants for this surface | **Deferred / incomplete** | Spec02 dormitory packet: keys without grants; Unit Staff seed parity historically unmatched; no closed grants for request view-of-others |
| Named product surface for Auth/UI successor | **Not authorized** | `NO_NAMED_PRODUCT_SURFACE_AUTHORIZED` |
| Business / product owner for next surface | **Not defined** | Triage row BLOCKING; discovery A7 |

**Implication:** Architecture supports describing the **surface intent** as employee-request-self-service with own-request ownership. It does **not** support naming «واحد اداری / منابع انسانی» (or `hr_manager` / `department_manager`) as the formal Business Owner of that authorization step.

---

## Business Owner Formalization Assessment

**Assessment (exactly one):** `CONFLICTING_TERM`

**Rationale:**

1. The exact proposed owner string is **absent** from repository artifacts.
2. Closest tokens are **roles**, a **workflow stage**, **generic Department** structure, or **informal** Persian gloss/example text — none formalized as product-surface Business Owner.
3. Authoritative discovery already records Business Owner as **`NOT_DEFINED`**; product triage records surface owner as **BLOCKING**.
4. Using «واحد اداری / منابع انسانی» as owner wording therefore **conflicts** with evidence: it elevates unsupported org/role shorthand to governance authority status.

This is **not** `CONFIRMED_DEFINED_OWNER`.  
This is **not** merely `IMPLIED_BUT_NOT_FORMALIZED` for that Persian compound (the compound is not implied as owner in artifacts).  
`NOT_DEFINED_REQUIRES_HUMAN_DECISION` remains true of the **owner field generally**; the **proposed label** specifically is classified as `CONFLICTING_TERM`.

---

## Risk of Keeping Current Owner Wording

If «واحد اداری / منابع انسانی» is retained as Business Owner in the next authorization decision:

1. **False authority** — Treats an unevidenced org label as if it were a formal department or governance owner.
2. **Role/org conflation** — Blurs `Department` entity, `department_manager` / `hr_manager` roles, and Stage 2 `HR` with product-surface authorization authority.
3. **Downstream Auth/UI drift** — Surface audience, grants, and visibility may be scoped to the wrong actor set (e.g. assuming HR “owns” self-service Auth).
4. **Contradiction of recorded status** — Overrides `BUSINESS_OWNER_STATUS: NOT_DEFINED` and triage BLOCKING without a human authority decision.
5. **Hard-stop violation** — Converts related roles into Business Owner without explicit mapping evidence.

---

## Recommended Owner Handling

**Recommendation (exactly one):** `BLOCK_PENDING_HUMAN_AUTHORITY`

Do **not** `KEEP_AS_IS`.  
Do **not** `REPLACE_WITH_FORMAL_TERM` unless/until a human authority names an evidenced owner (no formal replacement term for this surface exists in-repo today).  
`MARK_OWNER_UNRESOLVED` is compatible as the **field value** posture; the **authorization gate** remains blocked pending human authority (`BLOCK_PENDING_HUMAN_AUTHORITY`).

---

## Evidence-Backed Replacement Options

**None supported** as a drop-in Business Owner for employee-request-self-service without human decision.

Closest **non-owner** references (for clarification questions only — **not** auto-fill):

| Option | Evidence | Allowed use now |
| ------ | -------- | --------------- |
| Leave field `UNRESOLVED` | Discovery / triage | **Required** until human decides |
| Constitution “Product Owner” | Constitution escalation / approval language | May be **asked** whether Product Owner is the authorizing human for this surface — **not** assumed equal to «واحد اداری / منابع انسانی» |
| Governance Review (catalog map) | Design / IA / Batch owners | Does **not** replace product-surface Business Owner class (map has no such row) |
| `hr_manager` / `department_manager` | Role inventory | **Forbidden** as automatic Business Owner substitution |

Do **not** invent departments named واحد اداری or منابع انسانی.

---

## Next Authorization Prompt Owner Field

Use this exact wording in the next authorization decision prompt:

```text
Business Owner: UNRESOLVED
```

**Forbidden in that field until human authority confirms otherwise:**

- `واحد اداری / منابع انسانی`
- `hr_manager` / `HR Manager` as Business Owner
- `department_manager` / `Department Manager` as Business Owner
- Any unevidenced department name presented as owner

Optional companion clarification (outside the owner field) may ask humans to name the authorizing party without suggesting the conflicting Persian compound as default.

---

## Required Final Lines

```text
BUSINESS_OWNER_FORMALIZATION_STATUS: CONFLICTING_TERM

OWNER_HANDLING_RECOMMENDATION: BLOCK_PENDING_HUMAN_AUTHORITY

NEXT_PROMPT_OWNER_FIELD: UNRESOLVED

APPLICATION_FILES_MODIFIED: NO
```

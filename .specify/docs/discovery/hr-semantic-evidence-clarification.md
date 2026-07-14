---
artifact: hr_semantic_evidence_clarification
status: HR_SEMANTIC_CLARIFICATION_COMPLETED
mutation_permission: none
execution_authority: none
hr_semantic_status: CONFIRMED
hr_semantic_classification: COMBINATION
recommended_next_gate: HUMAN_DOMAIN_AUTHORITY_CLARIFICATION
date: 2026-07-13
---

# HR Semantic Evidence Clarification

**Artifact type:** Governance discovery clarification (non-authorizing)  
**Question:** What is HR in current DormSys evidence?  
**Mode:** Evidence-only — no HR entity/role/permission/workflow/implementation design

---

## 1. Evidence Found

| # | Source | Location | Meaning | Confidence |
| - | ------ | -------- | ------- | ---------- |
| 1 | Request Domain enum | `app/Modules/Request/Domain/Enums/ApprovalStage.php` — `case HR = 'hr'` | HR is an **approval stage** value on the four-stage chain | **Strong** |
| 2 | Request Domain state | `app/Modules/Request/Domain/States/PendingHRState.php` — `$name = 'pending_hr'` | HR appears as a **request lifecycle status** between Dept and Dorm stages | **Strong** |
| 3 | Request state machine | `RequestState.php` transitions PendingDepartmentManager → PendingHR → PendingDormitoryManager | HR is a **workflow-stage position** in the approval path | **Strong** |
| 4 | Spec05 data-model / research | `stage` / `ApprovalStage` includes `hr`; auto key `request.approval.auto.hr`; planned permission string `request.approve.hr` | HR is stage identifier + planned **approval capability** key | **Strong** |
| 5 | Spec05 spec.md | US3 / FR-008: DeptMgr → HR → DormMgr → DormUnit; actors include “HR manager” | HR named as **human approver role** at stage 2 | **Strong** |
| 6 | Constitution | Actors table: “HR Manager — Approves requests at Stage 2”; §12 matrix Stage 2 | HR Manager is a **job role / responsibility** in the permission matrix | **Strong** |
| 7 | Discovery Document | Approval chain “HR Manager”; stage enum `HR` | Same dual: chain actor + stage label | **Strong** |
| 8 | Architecture doc | `dormsys-architecture.md` lists `hr_manager` among roles | Role name used in architecture inventory | **Medium** (architecture inventory; not Identity seed proof) |
| 9 | Employee module | No HR entity/department/code matches under `app/Modules/Employee` | HR is **not** an Employee-BC organizational aggregate | **Strong (absence)** |
| 10 | Domain concept map | Prior consolidation: no HR org node; HR = role + workflow-stage label | Consistent with code/spec | **Strong** |

**Not found:** HR as Department/Organization entity; HR as location; migration/table for HR unit; closed rule binding which Employee/User is “the HR” for a given request.

---

## 2. Classification

```text
HR_SEMANTIC_CLASSIFICATION: COMBINATION
```

**Combination of:** `JOB_ROLE` (HR Manager / responsibility at Stage 2) **and** `WORKFLOW_STAGE_LABEL` (`ApprovalStage::HR` / `pending_hr`).

**Not:** `ORGANIZATIONAL_UNIT` / Department (no entity evidence).

---

## 3. Evidence Boundary

### Confirmed

- HR is a **workflow-stage label** in Request approval (enum + state + transitions).
- HR Manager is a **job role / responsibility** label in constitution actors and §12 Stage 2 approval.
- Spec/product language treats “HR manager” as the Stage 2 human actor type.

### NOT confirmed

- HR is **not** confirmed as an **organizational unit / Department** entity.
- HR is **not** confirmed as a formal Identity-seeded role with grants (architecture lists `hr_manager`; seed parity / Auth residual grants not evidenced as closed).
- Instance binding (which principal acts as HR for a request) and **approver visibility** are **not** confirmed.

---

## 4. Impact

| Gate / concern | Blocked by HR semantic ambiguity? |
| -------------- | --------------------------------- |
| Product Surface Authorization | **Not blocked by HR-as-entity ambiguity** — HR is sufficiently classifiable as role+stage. Surface auth still blocked by **business owner / named surface** (separate gap). |
| Auth Packet Preparation | **Partially blocked** — not by “is HR a department?”, but by **who holds HR responsibility** and visibility/grants for Stage 2. |
| Role Mapping | **Blocked for HR Stage 2** until product names surface + which Identity role maps to Stage 2 (semantic type is clear enough to map *as role*, not as org unit). |
| Workflow decisions | **Not blocked** for keeping HR as stage-2 label; Workflow module remains deferred (CD-010). Do not invent HR org structure for Workflow activation. |

---

## 5. Recommendation

```text
NO_HUMAN_DECISION_REQUIRED
```

for the narrow question “what semantic type is HR?” — evidence supports **COMBINATION** of job role + workflow-stage label, and rejects organizational-unit entity.

**Still required under broader gate:** `HUMAN_DOMAIN_AUTHORITY_CLARIFICATION` for actor **binding**, visibility, and product-surface ownership (not for re-asking “is HR a department?”).

```text
RECOMMENDED_NEXT_GATE: HUMAN_DOMAIN_AUTHORITY_CLARIFICATION
```

---

## Final Lines

```text
HR_SEMANTIC_STATUS: CONFIRMED
HR_SEMANTIC_CLASSIFICATION: COMBINATION
RECOMMENDED_NEXT_GATE: HUMAN_DOMAIN_AUTHORITY_CLARIFICATION
No application, auth, UI, workflow, schema, role-mapping, policy, middleware, permission, route, controller, Livewire, Blade, seeder, migration, test, or implementation files were modified.
```

---

## Document Control

- Version: 1.0.0  
- Last Updated: 2026-07-13  
- Checkpoint: `hr-semantic-evidence-clarification`

---
artifact: domain_authority_and_organization_model_discovery
status: DOMAIN_MODEL_DISCOVERY_COMPLETED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
phase: CORE_COMPLETION_WAVE
business_owner_status: NOT_DEFINED
request_ownership_status: PARTIAL
org_model_status: REQUIRES_HUMAN_CLARIFICATION
recommended_next_gate: HUMAN_DOMAIN_AUTHORITY_CLARIFICATION
upstream_triage: .specify/docs/decisions/product-authorization-gap-triage.md
upstream_origin: .specify/docs/decisions/deferred-origin-reconciliation.md
date: 2026-07-13
---

# Domain Authority and Organization Model Discovery

**Artifact type:** Governance / domain discovery (non-authorizing)  
**Status:** `DOMAIN_MODEL_DISCOVERY_COMPLETED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Purpose:** Reduce ambiguity before product-surface authorization and Auth packet preparation by extracting **only** evidenced organization, authority, and ownership facts from repository / specs / governance artifacts.

**Does not:** design a new domain model; invent entities, relations, owners, or authority; authorize Auth / UI / Workflow / Lottery / RBAC; reopen closed specs; implement anything.

**Evidence classes used throughout:** `Found` | `Implied` | `Ambiguous` | `Not Found`  
**Concept classes:** `Entity` | `Role` | `Assignment` | `Workflow actor label`

---

## 1. Discovery Baseline

| Topic | Posture |
| ----- | ------- |
| Phase | Core Completion Wave — ACTIVE |
| Prior product triage | `PRODUCT_AUTHORIZATION_REQUIRES_HUMAN_DECISION`; `NO_NAMED_PRODUCT_SURFACE_AUTHORIZED` |
| Prior recommended gate (triage) | `PRODUCT_SURFACE_AUTHORIZATION_DECISION` |
| Spec03 | `SPEC03_CLOSED` — Employee, Department, Dependent delivered (EmployeeRead deferred) |
| Spec04 Backend | `SPEC04_BACKEND_CLOSED` — physical hierarchy delivered; Auth residual OPEN |
| Spec05 | Implementation Authorized — Request + approval state |
| Workflow | `WORKFLOW_REMAINS_DEFERRED` (CD-010) |
| Authority Map | Design Approval / IA / Batch Execution owned; **product UI surface authorization owner not mapped**; next-spec/batch selection ownership undefined |

**Primary evidence sources (non-exhaustive):**

| Source | Role |
| ------ | ---- |
| `.specify/docs/context-map.md` | Context ownership inventory + CD-linked relationships |
| `.specify/docs/catalog-decisions.md` | CD-009–CD-017; Authority Map |
| `.specify/memory/constitution.md` | Actors, permission matrix §12, module tables |
| `.specify/docs/discovery/DormSys Discovery Document.md` | Historical discovery prose + conceptual schemas |
| `specs/003-employee-context/*` | Employee / Department / Dependent data model |
| `specs/004-accommodation-resource/*` + Spec04 handoffs | Dormitory hierarchy (incl. Floor Aggregate evolution) |
| `specs/005-request-management/*` | Request / RequestApproval / employee_id ownership |
| UI / Feature Analysis artifacts | Request self-access via principal ownership |
| Product discovery / Auth residual / deferred origin | Business-owner / surface-auth gaps |

---

## 2. Entity Evidence Map

| Concept | Class | Evidence status | What is evidenced | Primary sources |
| ------- | ----- | --------------- | ----------------- | --------------- |
| **Employee** | Entity | **Found** | Aggregate root in Employee BC; `identity_id` immutable UUID ref to Identity (CD-012); `department_id` nullable; submitting/request owner reference | Context map; CD-012; `specs/003-employee-context/data-model.md`; constitution §11 |
| **Department** | Entity | **Found** | Aggregate root in Employee BC; code, name, optional `manager_id` → Employee, optional `parent_id` tree, status | Spec03 data-model / US2; constitution `employee_departments` |
| **Organization** | Entity | **Not Found** (as BC aggregate) | Prose “the organization operates…”; Discovery **`ExternalPerson.organization: string (nullable)`** only — not an Organization aggregate or module | Discovery Document; no CD / context-map Organization row |
| **Dependent** | Entity | **Found** | Owned by Employee (CD-009); Request holds snapshots only | CD-009; Spec03; constitution |
| **Dormitory / DormitorySite** | Entity | **Found** | Dormitory BC owns physical accommodation sites; internal vs external types | Context map; Spec04; CD-014 |
| **Building** | Entity | **Found** | Under internal Dormitory | Spec04 / Spec04 backend handoffs |
| **Floor** | Entity | **Ambiguous → current disposition Found as aggregate** | OA-04-01 originally: floor as **Room attribute**. Spec.md governance note: OA-04-01 **superseded** by accepted **Floor Aggregate** (Dormitory → Building → Floor → Room → Bed) under Spec04 backend closure | Spec04 `spec.md` OA-04-01 + governance note; Spec04 IA / domain review handoffs |
| **Room** | Entity | **Found** | Physical room under Floor (current disposition) / historically under Building+floor label | Spec04 |
| **Bed** | Entity | **Found** | Physical bed; operability + occupancy markers; **no person FK** on Bed (CD-014) | Spec04; CD-014 |
| **Dormitory Unit** | Entity | **Not Found** | No Department-like or site-like **Dormitory Unit** aggregate in Employee or Dormitory specs. Name appears as **role / stage label** only (see §4) | Constitution §12; Discovery approval chain; Spec02 permission discovery |
| **Request** | Entity | **Found** | Request BC; `employee_id`, `dormitory_id` UUID refs (no cross-module FK); types; approval-phase states | Spec05 data-model; CD-010 |
| **RequestApproval** | Entity | **Found** | Append-only approval history; stages named after actor labels | Spec05; CD-010 |
| **RequestMember** | Entity | **Found** | Mission members; `member_employee_id` UUID | Spec05 data-model |
| **Allocation** | Entity | **Found** | Owns **assignment** authority (CD-014); consumes Request / Lottery inputs | Context map; CD-014 |
| **Person** (base) | Entity | **Ambiguous / historical** | Discovery models Person → Employee / ExternalPerson; active Spec03 models Employee directly without a Person BC | Discovery Document vs Spec03 data-model |
| **Identity User / Role / Permission** | Entity (Identity BC) | **Found** | Users, roles, permissions; separate from Employee | Context map; Spec02; CD-012 |
| **Workflow engine** | Capability (deferred) | **Found as deferred** | Not an active module; owns transition **rules** when activated (CD-010) | Catalog Deferred Components; CD-010 |

---

## 3. Relationship Evidence Map

| From → To | Relation nature | Evidence status | Notes |
| --------- | --------------- | --------------- | ----- |
| Identity → Employee | UUID attachment (`identity_id`) | **Found** | CD-012; immutable; no FK |
| Employee → Department | Assignment (`department_id`) | **Found** | Nullable until assigned (Spec03) |
| Department → Employee | Optional manager (`manager_id`) | **Found** | Same-module FK; **not** proven as sole Stage-1 approver binding |
| Department → Department | Parent tree (`parent_id`) | **Found** | Optional org tree |
| Employee → Dependent | Aggregate ownership | **Found** | CD-009 |
| Request → Employee | `employee_id` reference | **Found** | Submitting / request owner; no FK |
| Request → Dormitory | `dormitory_id` reference | **Found** | Target dormitory; no FK |
| Request → RequestApproval | History ownership | **Found** | CD-010; Request owns state + history |
| Request → Dependent | Snapshot / reference only | **Found** | CD-009; not lifecycle ownership |
| Request ↔ Workflow | Split: state vs transition rules | **Found** (boundary); orchestration **deferred** | CD-010 |
| Employee → Request | Eligibility computation → enforcement | **Found** | CD-013 |
| Allocation → Dormitory | Assignment vs physical state | **Found** | CD-014 |
| Dormitory hierarchy | Site → Building → Floor → Room → Bed | **Found** (current Spec04 disposition) | External: no children |
| Organization → * | Structural ownership of departments/dorms | **Not Found** | No Organization entity linking graph |
| Dormitory Unit → Dormitory / Department | Org or physical scoping | **Not Found** | Label exists; entity/link absent |
| Dept Manager → Department | Role bound via `manager_id` | **Implied** | Spec03 US2 purpose mentions approval routing; Workflow deferred; no closed CD that Stage 1 **must** use `manager_id` |
| Dorm Manager → Dormitory | Role bound via site `manager_id` | **Ambiguous** | Discovery schema had `Dormitory.manager_id`; Spec04 normative site model / Auth residual do **not** establish role→site grant rules as closed product law |
| person_id on Allocation | Occupant assignment | **Found** (assignment semantics) | CD-014 / program docs; Allocation owns assignment — not Dormitory Bed person FK |

---

## 4. Authority / Approval Actor Evidence

| Actor label | Concept class | Evidence status | What is evidenced |
| ----------- | ------------- | --------------- | ----------------- |
| **Employee** | Role (+ Entity) | **Found** | Constitution actors + §12: Submit Request only (matrix) |
| **Department Manager** / Dept. Manager | Role + Workflow actor label | **Found** | Stage 1 approve; Discovery chain position 1; Spec05 stage `PendingDepartmentManager` / auto key `department_manager` |
| **HR Manager** | Role + Workflow actor label | **Found** | Stage 2; Spec05 `PendingHR` / auto `hr` |
| **Dormitory Manager** | Role + Workflow actor label | **Found** | Stage 3; §12 Manage Rooms / Allocate / Lottery / Config ✅ |
| **Dormitory Unit Manager** | Workflow actor label | **Found** (label) | Discovery chain Stage 4 name |
| **Dormitory Unit Staff** | Role | **Found** | Constitution §12 Stage 4 + Allocate + Manage Rooms; Spec02 permission discovery notes matrix role with **no matching seed role name** found historically |
| **Lottery Operator** | Role | **Found** | §12 Execute Lottery |
| **Operator** | Role | **Found** | §12 Check-In/Out only |
| **Admin** | Role | **Found** | §12 broad ops |
| Approval **chain** Dept → HR → Dorm → Dorm Unit | Workflow actor sequence | **Found** as product/process intent | Constitution; Discovery; Spec05 transition matrix; **orchestration engine deferred** (CD-010) |
| Auto-approval flags per stage | Assignment (settings) | **Found** | Spec05 research R-09 settings keys |
| Who **is** the Stage 1–4 human for a given request | Assignment rule | **Ambiguous** | Stages named; binding to Department.manager_id / dormitory site / unit **not** closed as map-backed product authority for Auth packet |
| Product / business owner for UI surface authorization | Governance authority | **Not Found** | Authority Map lacks product-surface / next-UI decision class; product triage: surface owner not identified |

**Explicit non-claims:** Constitution §12 is a **permission matrix of roles**, not proof that Identity seeds currently grant those roles, and not a Dormitory Unit **entity** definition.

---

## 5. Ownership Evidence Map

| Concern | Evidence status | Evidenced rule | Gaps |
| ------- | --------------- | -------------- | ---- |
| **Request aggregate ownership (module)** | **Found** | Request BC owns Request, RequestApproval, RequestMember, RequestType; owns approval **state** + history (CD-010) | Transition **routing** owned by Workflow when activated |
| **Request “owner” / submitting employee** | **Found** | `requests.employee_id` = submitting employee; BR-01 / eligibility for submitting employee (OA-05-05 / Spec05) | Manager / HR visibility sets not fully specified as closed Auth law |
| **Employee self-access (list/show)** | **Found** (implemented path evidenced in governance analysis) | List via `listByEmployeePaginated`; show via `RequestPrincipalEmployeeResolver::assertOwnsSummary` — non-owned denied | Does not define approver inbox visibility |
| **Dependent ownership** | **Found** | Dependent ∈ Employee; Request snapshots only (CD-009) | Live Dependent path deferred |
| **Dormitory physical ownership** | **Found** | Dormitory owns Room/Bed physical state; no person FK on Bed (CD-014) | — |
| **Assignment / allocation ownership** | **Found** | Allocation owns assignment execution (CD-014) | — |
| **Check-in/out ownership** | **Found** | CheckIn/CheckOut operational transitions (CD-015); Spec04 Check-in residual retired | — |
| **Eligibility ownership** | **Found** | Employee computes; Request enforces at submit (CD-013) | — |
| **Dormitory structure Auth (Application PEP)** | **Found** (bounded) | Spec02 packet: `dormitory.structure.view` / `manage` on covered actions; **no role grants** | Role mapping deferred; Presentation/HTTP blocked |
| **Who may see/manage which dormitory’s rooms** | **Ambiguous** | §12 Manage Rooms for Dorm Manager / Unit Staff / Admin; no evidenced org-unit or site-scoped grant model closed for Auth residual | Blocks safe Auth packet scoping |
| **Business owner of product surfaces** | **Not Found** | — | Blocks finalized product-surface authorization |

---

## 6. Ambiguities and Missing Definitions

| ID | Topic | Status | Why it matters |
| -- | ----- | ------ | -------------- |
| A1 | **Organization** as entity | **Not Found** | Cannot scope “org-wide” vs multi-tenant org without inventing |
| A2 | **Dormitory Unit** as entity vs role/stage label only | **Ambiguous** | Stage 4 / Manage Rooms audience unclear for surface + role mapping |
| A3 | Dept Manager ↔ `Department.manager_id` | **Implied** only | Stage 1 routing / visibility for Auth packet unsafe to invent |
| A4 | Dorm Manager / Unit Staff ↔ physical Dormitory/site | **Ambiguous** | Dormitory-admin-ui scoping undefined |
| A5 | Floor: attribute vs aggregate | **Ambiguous historically; Found as Floor Aggregate for Spec04 current disposition** | Consumers must follow Spec04 governance note, not invent OA-04-01 reopen |
| A6 | Discovery **Person** hierarchy vs Spec03 Employee | **Ambiguous / superseded in practice** | Do not invent Person BC for Auth |
| A7 | **Business / product owner** for next UI surface | **Not Found** | Product triage already BLOCKING |
| A8 | Approver **visibility** (who sees whose requests) | **Not Found** as closed Auth residual law | Self-service ownership Found; approval inbox not |
| A9 | Constitution role names vs Identity seed role names | **Ambiguous** (Spec02 discovery: Unit Staff matrix-only historically) | Role mapping cannot assume seed parity |
| A10 | Authority Map gap: product UI authorization decision class | **Found as gap** | Catalog explicitly leaves some selection ownership undefined |

---

## 7. Governance Impact

### 7.1 Product Surface Authorization

| Impact | Assessment |
| ------ | ---------- |
| Can a human still **name** a first surface? | Yes, but **audience/org-scope** for dormitory admin remains under-specified (A2, A4, A7) |
| Employee request **self-service** surface semantics | Better evidenced (Request `employee_id` + principal ownership) than dormitory admin |
| Blocking gap for **finalizing** surface auth | **Business owner NOT_DEFINED** (A7) + unclear whether first Auth/UI surface is employee residual vs `dormitory-admin-ui` vs other |
| Prior triage `NO_NAMED_PRODUCT_SURFACE_AUTHORIZED` | **Still holds** — this discovery does not invent a surface |

### 7.2 Spec04 Auth Residual

| Impact | Assessment |
| ------ | ---------- |
| Application PEP closed | Unchanged — structure keys without grants |
| Role mapping | Still cannot be scoped safely without named surface **and** clarified actor→org/site binding (A2–A4, A9) |
| Presentation / HTTP | Remain blocked on product surface + Anti-Leak |
| Dormitory Unit / Manage Rooms audience | Ambiguity is a **domain-authority** input to residual disposition, not resolved by PEP |

### 7.3 Future Auth Packet

| Packet input | Ready? |
| ------------ | ------ |
| Named product-authorized surface | **No** |
| Target roles / audience for first surface | **No** (A2, A4, A7) |
| Request self-ownership rule for employee surfaces | **Partial — usable if surface is employee self-service** |
| Site/unit-scoped dormitory admin rule | **No** — would require human clarification (do not invent) |
| Workflow activation | **Not required** for packet prep of Auth residual (Workflow remains deferred) |

---

## 8. Final Assessment

### What is solid enough to rely on (without invention)

- Employee, Department, Dependent, Request, RequestApproval as modules/entities with CD-backed ownership.
- Physical dormitory hierarchy under Spec04 current Floor Aggregate disposition.
- CD splits: eligibility (CD-013), approval state vs Workflow rules (CD-010), assignment vs physical vs check-in (CD-014/015).
- Four-stage **actor labels** and Spec05 approval-phase states.
- Employee **self** request ownership / principal enforcement for existing Request list/show path.

### What must not be treated as settled product law

- Organization aggregate.
- Dormitory Unit as organizational entity.
- Closed binding of Stage 1–4 actors to Department/site/unit records.
- Business owner for product-surface authorization.
- Role→`dormitory.structure.*` grants or Presentation auth.

### Decision

```text
DOMAIN_MODEL_DISCOVERY_COMPLETED
```

Discovery is complete as an evidence extract. Org/authority clarity is **not** sufficient to finalize product-surface authorization without human clarification of missing owner and Dormitory Unit / actor-binding ambiguities.

**Recommended next gate:** `HUMAN_DOMAIN_AUTHORITY_CLARIFICATION` — clarify at minimum: (1) who may authorize product surfaces, (2) whether Dormitory Unit is entity or role/stage label only, (3) intended first surface audience (employee self-service vs dormitory admin vs other). Then proceed to `PRODUCT_SURFACE_AUTHORIZATION_DECISION` (prior triage gate remains valid **downstream**).

---

## Explicit Boundary Preservation

This discovery does **not** authorize:

- Auth implementation; role mapping; UI execution  
- Workflow or Lottery activation; full RBAC  
- Schema invention; new business-owner assignment as fact  
- Closed-spec reopening  

---

## Required Final Decision Block

```text
DOMAIN_AUTHORITY_AND_ORGANIZATION_MODEL_DISCOVERY

Discovery:
DOMAIN_MODEL_DISCOVERY_COMPLETED

Business Owner:
NOT_DEFINED

Request Ownership:
PARTIAL

Org Model:
REQUIRES_HUMAN_CLARIFICATION

Recommended Next Gate:
HUMAN_DOMAIN_AUTHORITY_CLARIFICATION

Downstream (unchanged intent):
PRODUCT_SURFACE_AUTHORIZATION_DECISION after clarification

Execution Authority:
NONE
```

---

## No-Change Confirmation

`No application, auth, UI, workflow, schema, role-mapping, policy, middleware, permission, route, controller, Livewire, Blade, seeder, or implementation files were modified.`

Only this discovery artifact was created:

- `.specify/docs/discovery/domain-authority-and-organization-model-discovery.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`DOMAIN_MODEL_DISCOVERY_COMPLETED`**  
- Last Updated: 2026-07-13  
- Checkpoint: `domain-authority-and-organization-model-discovery`

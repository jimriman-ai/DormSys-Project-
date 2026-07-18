# Spec04 Implementation Readiness — Technical Proposal (IMP-Q-01…07)

| Field | Value |
|-------|-------|
| **Status** | **TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL** |
| **Binding** | Non-binding; does **not** authorize implementation |
| **Created** | 1405/04/27 \| 2026-07-18 |
| **Auth packet** | `docs/specs/spec04-auth-packet.md` — **0.5.0-GOVERNANCE-ACCEPTED** |
| **Feature contract draft** | `docs/ui/contracts/requests/employee-request-self-service.feature-contract.draft.yaml` |
| **Fixed governance** | DGAP-05 A; DGAP-06 V1+U2; OQ-AUTH-01 B; OQ-AUTH-02 B; OQ-AUTH-03 B; OQ-AUTH-05 A |

> All recommendations below are labeled **TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL**.  
> No code, migrations, routes, Livewire, L5/L6 authorization, or GAP status changes are made by this document.

---

## 1. Technical Proposal Matrix

### IMP-Q-01 — Route naming and entry points

**1. Current evidence**

- `routes/web.php`: `employee.login`; dormitory dashboards under `identity.role:dormitory-manager` / `dormitory-unit-manager`; home redirects to `/requests`.
- Feature contracts reference named routes `requests.index`, `requests.show` (`/requests/{requestId}`).
- No Spec04 `employee` / `dormitory-manager` route groups registered yet.
- OQ-AUTH-02 B requires dual stacks: `auth:identity` + `identity.role:employee` vs `identity.role:dormitory-manager`.

**2. Options**

| Option | Proposal |
|--------|----------|
| **A** | Prefix split: `/employee/requests/*` (self-service) + `/approvals/stage1/*` (dormitory-manager console); names `employee.requests.*` / `approvals.stage1.*` |
| **B** | Keep `/requests/*` for employee; add sibling `/manager/requests/*` for dormitory-manager |
| **C** | Single `/requests/*` tree; differentiate only by middleware role (same URLs, different gates) |

**3. Advantages / disadvantages**

| Option | Pros | Cons |
|--------|------|------|
| A | Clearest U2 separation; hardest to embed approve on employee surface | New URL vocabulary; more route churn vs existing `/requests` docs |
| B | Reuses familiar `/requests` for employees; console clearly separate | “manager” naming can collide with dormitory-manager roles |
| C | Minimal path inventiveness | Weak U2 signal; risk of accidental shared components/actions |

**4. Dependencies:** OQ-AUTH-01 roles; U2 boundary; existing contract route names (`requests.show`) if reused (IMP-Q-03).

**5. Risks:** Breaking notification deep links (`requests.show`); confusing dormitory-manager with `dormitory-manager`.

**6. Recommendation:** **Option A** (or B if Lead prioritizes preserving `/requests` employee URLs).  
**TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL**

---

### IMP-Q-02 — Snapshot storage requirement

**1. Current evidence**

- `requests` table: `employee_id`, status, dates — **no** assigned Stage-1 identity column (`database/migrations/modules/request/2026_06_26_000001_create_requests_table.php`).
- `request_approvals.approver_id` records decisions after act — not a submit-time assignment snapshot.
- OQ-AUTH-03 **B**: snapshot assigned Stage-1 identity at submit via org-chart chain.

**2. Options**

| Option | Proposal |
|--------|----------|
| **A** | Add nullable UUID column on `requests`, e.g. `assigned_stage1_approver_identity_id` (value-ref, no FK) |
| **B** | Side table `request_stage1_assignments` (request_id, identity_id, assigned_at) |
| **C** | Derive only from live `manager_id` at read time and “freeze” in application memory without persistence — **conflicts with OQ-AUTH-03 B** |

**3. Advantages / disadvantages**

| Option | Pros | Cons |
|--------|------|------|
| A | Simple V1 filter; matches “on the request” wording | Requires migration permit |
| B | Audit-friendly history of reassignment (if ever allowed) | More schema; overkill if assignment is immutable |
| C | No migrate | **Invalid** under DECIDED OQ-AUTH-03 B |

**4. Dependencies:** Separate migration authorization; Application write on submit; V1 middleware/read filters (OQ-AUTH-02 B).

**5. Risks:** Null snapshot if employee has no department/manager; must define fail-closed behavior (out of scope to invent here).

**6. Recommendation:** **Option A** — single request column; fail-closed rules deferred to impl auth packet.  
**TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL**

---

### IMP-Q-03 — Reuse vs successor vs new list/show path

**1. Current evidence**

- Livewire exists: `RequestListPage`, `RequestShowPage`, `RequestCreatePage`.
- Contracts: `request-list` approved; `request-show` approved (read-only, no approve); list→detail navigation lock references `requests.show`.
- Draft Spec04 contract: must not silently reopen frozen contracts.
- Spec04 surface adds identity-role gates + V1 + U2 not fully expressed in older list/show contracts.

**2. Options**

| Option | Proposal |
|--------|----------|
| **A** | **Reuse** existing List/Show/Create pages; amend contracts + add middleware/role/V1 |
| **B** | **Successor** features: keep frozen contracts; new Spec04 contracts that wrap/extend behavior without editing frozen files |
| **C** | **New** parallel pages/routes for Spec04 self-service; leave legacy pilot pages frozen as-is |

**3. Advantages / disadvantages**

| Option | Pros | Cons |
|--------|------|------|
| A | Least duplication | High reopen risk of frozen boundaries; auth model may not match pilot assumptions |
| B | Governance-safe evolution | More artifacts; careful dependency mapping |
| C | Clean Spec04 auth story | Duplicate UX; deep-link/contract fragmentation |

**4. Dependencies:** IMP-Q-01 routes; IMP-Q-05 inventory; existing implementation locks.

**5. Risks:** Accidental mutation of frozen request-show (approve still forbidden there — good for U2).

**6. Recommendation:** **Option B** for list/show (successor contracts); treat Create as in-scope successor if Create lacks an approved contract, else align under same Spec04 umbrella.  
**TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL**

---

### IMP-Q-04 — HTTP actions vs Livewire interaction model

**1. Current evidence**

- `RequestMutationController`: HTTP JSON approve/reject/submit/cancel; principal from session; spoofing fields prohibited.
- `RequestShowPage`: read-only Livewire (history only).
- Docs note Livewire approver UI deferred historically (FR-EX-010 pattern).
- OQ-AUTH-02 B: middleware V1 on request-scoped routes; U2 = separate console.

**2. Options**

| Option | Proposal |
|--------|----------|
| **A** | U2 console Livewire UI calls existing HTTP mutation endpoints (or shared Application actions) |
| **B** | U2 console Livewire invokes Application actions directly (no HTTP) |
| **C** | Pure HTTP/API console (no Livewire) |

**3. Advantages / disadvantages**

| Option | Pros | Cons |
|--------|------|------|
| A | Reuses hardened mutation auth; clear transport boundary | Need CSRF/session wiring from Livewire |
| B | Matches employee Create page style; fewer hops | Must not bypass FormRequest spoofing protections |
| C | Thin UI | Diverges from Livewire-first product UI |

**4. Dependencies:** U2 surface (IMP-Q-07); existing `ApproveRequestStageAction` / `RejectRequestAction`; V1 middleware.

**5. Risks:** Dual code paths for approve; principal mismatch if Livewire uses wrong guard.

**6. Recommendation:** **Option B** with mandatory reuse of Application gates + FormRequest rules equivalent (or extract shared assert); HTTP remains available for API clients.  
**TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL**

---

### IMP-Q-05 — v1 employee page inventory

**1. Current evidence**

- Pages present: Create, List, Show (Livewire).
- Mutations exist: submit, cancel (HTTP); Create uses Livewire → `CreatePersonalRequestAction`.
- Show contract: read-only; no approve/reject on employee surface (aligns with U2).

**2. Options**

| Option | v1 inventory |
|--------|----------------|
| **A** | **Minimal:** List + Show + Create (+ submit if separate step) |
| **B** | **Standard:** A + Cancel |
| **C** | **Expanded:** B + edit-draft / family flows / multi-type |

**3. Advantages / disadvantages**

| Option | Pros | Cons |
|--------|------|------|
| A | Smallest auth surface to harden under V1 | Incomplete employee journey if cancel expected |
| B | Matches existing backend cancel capability | More AC/tests |
| C | Product-complete | Scope creep; blocks impl auth |

**4. Dependencies:** IMP-Q-03 path; BO product scope (HR); auth packet in-scope “create/view own”.

**5. Risks:** Shipping Create without List/Show breaks journey; shipping Cancel without V1 on mutate routes.

**6. Recommendation:** **Option B** — List, Show, Create, Cancel; **no** approve/reject on employee surface.  
**TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL**

---

### IMP-Q-06 — Role seed / catalog handling

**1. Current evidence**

- `IdentityRoleSeeder`: identity-guard roles today include `dormitory-manager`, `dormitory-unit-manager`; legacy `HRMgr`/`DormMgr` on **web**.
- OQ-AUTH-01 B: identity roles **`employee`** + **`dormitory-manager`** — **not** seeded yet.
- Must not reuse dormitory-manager for Stage-1 (DGAP-05 A).

**2. Options**

| Option | Proposal |
|--------|----------|
| **A** | Extend `IdentityRoleSeeder`: `Role::findOrCreate('employee'|'dormitory-manager', 'identity')` only; no permission grants until catalog decided |
| **B** | New dedicated seeder `Spec04IdentityRoleSeeder` |
| **C** | Manual/ops role creation via existing Identity role-manage permission UI/API |

**3. Advantages / disadvantages**

| Option | Pros | Cons |
|--------|------|------|
| A | Matches dormitory identity role pattern | Mixes Spec04 into shared seeder |
| B | Clear Spec04 audit trail | Extra seeder wiring |
| C | No seeder change | Non-reproducible envs; weak CI |

**4. Dependencies:** Implementation + seeder execution authorization; Spatie cache clear; assignment of roles to users (separate ops).

**5. Risks:** Creating `dormitory-manager` on **web** by mistake (SEC-G-01); colliding with future kebab rename.

**6. Recommendation:** **Option A** (or B if Lead wants isolation); enforce `guard_name = identity` only.  
**TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL**

---

### IMP-Q-07 — Approver console product surface and contract boundary

**1. Current evidence**

- DGAP-06 U2: separate console; not embedded in employee self-service.
- Draft contract placeholder: `department-request-approver-console`.
- No approved Feature Contract or routes for dormitory-manager console today.
- Employee request-show explicitly excludes approve/reject.

**2. Options**

| Option | Proposal |
|--------|----------|
| **A** | **Separate** product surface + Feature Contract id (e.g. `department-request-approver-console`); linked to Spec04 auth packet |
| **B** | **Same** product surface `employee-request-self-service` with two UI modules inside one contract |
| **C** | Defer console to later phase; employee self-service only in first impl auth |

**3. Advantages / disadvantages**

| Option | Pros | Cons |
|--------|------|------|
| A | Strong U2; independent locks/permits | Two auth tracks to manage |
| B | One packet | Blurs U2; easier to leak approve into employee UI |
| C | Shrinks first slice | Stage-1 path incomplete; snapshot/V1 for approver unused until later |

**4. Dependencies:** IMP-Q-01, IMP-Q-04, IMP-Q-06; Stage-1 snapshot (IMP-Q-02).

**5. Risks:** “Defer console” while claiming Spec04 complete for Stage-1 ops.

**6. Recommendation:** **Option A** for contract boundary; Lead may still **sequence** impl as employee-first then console (phased permits) without merging surfaces.  
**TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL**

---

## 2. Recommended decision sequence

All steps: **TECHNICAL PROPOSAL — REQUIRES HUMAN APPROVAL**

| Step | Decide | Why first |
|------|--------|-----------|
| 1 | **IMP-Q-07** surface/contract split (A recommended) | Defines whether console is in first permit |
| 2 | **IMP-Q-05** v1 employee inventory (B recommended) | Bounds employee slice |
| 3 | **IMP-Q-03** reuse/successor/new (B recommended) | Avoids frozen-contract violations |
| 4 | **IMP-Q-01** routes (A or B) | Needs surface + inventory |
| 5 | **IMP-Q-02** snapshot column (A recommended) | Schema prerequisite for V1/U2 data |
| 6 | **IMP-Q-06** role seed approach (A or B) | Prerequisite for middleware role strings |
| 7 | **IMP-Q-04** HTTP vs Livewire (B recommended) | After console surface exists in plan |

Then: Feature Contract draft → reviewed/approved → **separate** Implementation Authorization (not OQ-AUTH-05).

---

## 3. Remaining blockers before Implementation Authorization

| Blocker | Status |
|---------|--------|
| Human approval of IMP-Q-01…07 (this proposal) | **Open** — AWAITING HUMAN DECISION |
| Feature Contract still `draft` / non-binding | Open |
| Explicit Lead **implementation authorization** permit | Not issued |
| L5 / L6 authorization | Not issued |
| Migration permit for snapshot column (if Option A/B) | Not issued |
| Seeder/catalog execution authorization for `employee` + `dormitory-manager` | Not issued |
| Implementation lock(s) for chosen surfaces | Not issued |
| DGAP-03 / SGAP-05 | Unrelated — **do not block** Spec04 per OQ-AUTH-05 A; **unchanged** |

---

## Document control

| Field | Value |
|-------|-------|
| Path | `docs/specs/spec04-imp-q-technical-proposal.md` |
| Authority | Analysis only |
| Implementation | **NOT AUTHORIZED** |
| Final status | **AWAITING HUMAN DECISION** |

# Spec04 IMP-Q — Human Decision Pack

| Field | Value |
|-------|-------|
| **Status** | **DECISIONS RECORDED - AWAITING IMPL PERMIT** |
| **Binding** | Lead decisions recorded — **does not** authorize implementation |
| **Created** | 1405/04/27 \| 2026-07-18 |
| **Decided-On** | 1405/04/27 \| 2026-07-18 |
| **Decision-Owner** | Lead |
| **Source proposal** | `docs/specs/spec04-imp-q-technical-proposal.md` |
| **Auth packet** | `docs/specs/spec04-auth-packet.md` — GOVERNANCE-ACCEPTED |
| **Feature contract** | `docs/ui/contracts/requests/employee-request-self-service.feature-contract.yaml` — **1.0.0-READY** |
| **Implementation** | **NOT AUTHORIZED** |
| **L5 / L6** | **NOT AUTHORIZED** |

> Options below remain as historical decision space. **Selected options are recorded in § Lead Decision Input.**  
> This pack does **not** authorize code, migrations, routes, seeders, L5, or L6.

---

## Validation result (consistency check)

| Check | Result | Notes |
|-------|--------|-------|
| IMP-Q-01 A vs DGAP-06 U2 / OQ-AUTH-02 dual stacks | **PASS** | Separate URL prefixes reinforce separate employee vs DeptMgr surfaces |
| IMP-Q-02 A vs OQ-AUTH-03 B / DGAP-06 V1 | **PASS** | Column on `requests` persists assigned Stage-1 identity for V1 |
| IMP-Q-03 B vs Spec04 FC draft (no silent reopen of frozen contracts) | **PASS** | Successor contracts keep frozen list/show untouched |
| IMP-Q-04 B vs OQ-AUTH-02 B / Application mutation pattern | **PASS** | Livewire → Application preserves Application gates; approve stays off employee UI (U2) |
| IMP-Q-05 B vs auth-packet create/view + U2 (no employee approve) | **PASS** | List/Show/Create/Cancel; approve/reject excluded from employee inventory |
| IMP-Q-06 A vs OQ-AUTH-01 B (`employee` + `DeptMgr`, identity guard) | **PASS** | Extending IdentityRoleSeeder on `identity` matches decided role names; must not use dormitory-manager for Stage-1 (DGAP-05 A) |
| IMP-Q-07 A vs DGAP-06 U2 / DGAP-05 A | **PASS** | Separate Approver Console surface for DeptMgr Stage-1 |
| Conflicts with DGAP-05 / DGAP-06 / OQ-AUTH-01/02/03 | **NONE OBSERVED** | No reopen of settled gates |
| Implementation / L5 / L6 authorized by these decisions? | **NO** | Remain **NOT AUTHORIZED** |

**Validation verdict:** Lead IMP-Q selections are **consistent** with Spec04 GOVERNANCE-ACCEPTED decisions. Ready for a **separate** implementation permit process only — not granted here.

---

## Recorded decisions (summary)

| ID | Selected | One-line rationale |
|----|----------|-------------------|
| IMP-Q-01 | **A** | Separate URL boundaries enforce U2 and dual role stacks |
| IMP-Q-02 | **A** | Request-column snapshot implements OQ-AUTH-03 B for V1 |
| IMP-Q-03 | **B** | Successor contracts preserve frozen list/show artifacts |
| IMP-Q-04 | **B** | Livewire → Application actions; no employee-surface approve |
| IMP-Q-05 | **B** | List, Show, Create, Cancel — no approve/reject on employee |
| IMP-Q-06 | **A** | Extend IdentityRoleSeeder for `employee` + `DeptMgr` on identity |
| IMP-Q-07 | **A** | Separate Approver Console product surface (U2) |

---

**Suggested prior decision order (historical):** IMP-Q-07 → 05 → 03 → 01 → 02 → 06 → 04.

---

## IMP-Q-01 — Route naming and entry points

**Decision question:**  
Which URL / named-route layout should Spec04 use to separate employee self-service from the Stage-1 DeptMgr approver console?

### Option A

**Meaning:** Prefix split — `/employee/requests/*` (self-service) + `/approvals/stage1/*` (DeptMgr console); names `employee.requests.*` / `approvals.stage1.*`.

**Benefits:** Clearest U2 separation; hardest to embed approve actions on the employee surface.

**Risks:** New URL vocabulary; more churn vs existing `/requests` docs and deep links.

**Impact:**
- **Governance:** Strong support for DGAP-06 U2 surface split; dual stacks align with OQ-AUTH-02 B.
- **Dependency:** Affects IMP-Q-03 if existing `requests.show` names are reused; couples to OQ-AUTH-01 role middleware groups.

### Option B

**Meaning:** Keep `/requests/*` for employees; add sibling `/manager/requests/*` for DeptMgr.

**Benefits:** Familiar employee URLs; console still on a separate prefix.

**Risks:** “manager” naming can collide mentally/ops-wise with `dormitory-manager` roles.

**Impact:**
- **Governance:** Compatible with U2 if approve stays only under `/manager/requests/*`.
- **Dependency:** Easier continuity with existing `requests.*` contract routes for employee side.

### Option C

**Meaning:** Single `/requests/*` tree; differentiate only by middleware role (same URLs, different gates).

**Benefits:** Minimal path inventiveness.

**Risks:** Weak U2 signal; risk of shared components/actions leaking approve onto employee UI.

**Impact:**
- **Governance:** Weakest U2 enforcement at the routing layer.
- **Dependency:** Lowest route churn; highest reliance on discipline in Livewire/components (IMP-Q-04/05).

---

## IMP-Q-02 — Snapshot storage requirement

**Decision question:**  
How should the Stage-1 assigned approver identity (OQ-AUTH-03 B) be persisted at request submit?

### Option A

**Meaning:** Add a nullable UUID column on `requests` (e.g. `assigned_stage1_approver_identity_id`) as a value-ref with no FK.

**Benefits:** Simple V1 filter; matches “assigned approver on the request” wording.

**Risks:** Requires migration permit; null snapshot if no department/manager (fail-closed rules still TBD).

**Impact:**
- **Governance:** Compatible with DECIDED OQ-AUTH-03 B and DGAP-06 V1.
- **Dependency:** Blocks V1 middleware/read filters until migrate + Application write on submit are authorized.

### Option B

**Meaning:** Side table `request_stage1_assignments` (request_id, identity_id, assigned_at).

**Benefits:** Audit-friendly if assignment history is ever needed.

**Risks:** More schema; overkill if assignment is immutable under OQ-AUTH-03 B.

**Impact:**
- **Governance:** Still can satisfy snapshot semantics; heavier than governance requires.
- **Dependency:** Extra repository/Application paths; still needs migration permit.

### Option C

**Meaning:** Derive from live `manager_id` at read time only; no persisted snapshot.

**Benefits:** No migration.

**Risks:** **Conflicts with DECIDED OQ-AUTH-03 B** (invalid under current governance).

**Impact:**
- **Governance:** Non-compliant with Spec04 Auth Packet selection.
- **Dependency:** Would reopen OQ-AUTH-03 — not allowed without new Lead decision.

---

## IMP-Q-03 — Request UI path / contract strategy

**Decision question:**  
How should Spec04 relate to existing Request List / Show (and Create) pages and their frozen/approved contracts?

### Option A

**Meaning:** **Reuse** existing List/Show/Create pages; amend existing contracts and add middleware/role/V1.

**Benefits:** Least duplication of UI code.

**Risks:** High reopen risk of frozen boundaries; older auth model may not match Spec04 identity/V1/U2.

**Impact:**
- **Governance:** Conflicts with draft Spec04 guidance “must not silently reopen frozen contracts.”
- **Dependency:** Touches existing implementation locks; couples tightly to current `requests.*` routes (IMP-Q-01).

### Option B

**Meaning:** **Successor** features — keep frozen contracts untouched; new Spec04 contracts wrap/extend behavior.

**Benefits:** Governance-safe evolution; preserves pilot freeze.

**Risks:** More artifacts; careful dependency mapping required.

**Impact:**
- **Governance:** Aligns with frozen-contract preservation and Spec04 auth additions (identity roles, V1, U2).
- **Dependency:** Needs IMP-Q-01 routes and IMP-Q-05 inventory defined; Create may need successor treatment if no approved Create contract.

### Option C

**Meaning:** **New** parallel pages/routes for Spec04 self-service; leave legacy pilot pages frozen as-is.

**Benefits:** Clean Spec04 auth story.

**Risks:** Duplicate UX; deep-link and contract fragmentation.

**Impact:**
- **Governance:** Strong isolation; may orphan or confuse existing notification deep links to `requests.show`.
- **Dependency:** Highest net-new UI surface; still needs IMP-Q-01/05.

---

## IMP-Q-04 — Livewire approve action (interaction model)

**Decision question:**  
How should the U2 Stage-1 approver console invoke approve/reject?

### Option A

**Meaning:** U2 Livewire UI calls existing HTTP mutation endpoints (or shared Application actions via HTTP).

**Benefits:** Reuses hardened mutation auth; clear transport boundary.

**Risks:** CSRF/session wiring from Livewire to HTTP; dual-stack complexity.

**Impact:**
- **Governance:** Compatible with U2 + existing spoofing prohibitions if HTTP FormRequests remain in path.
- **Dependency:** Needs IMP-Q-07 console surface; existing `RequestMutationController` approve/reject.

### Option B

**Meaning:** U2 Livewire invokes Application actions directly (no HTTP hop).

**Benefits:** Matches employee Create page style; fewer hops.

**Risks:** Must not bypass FormRequest spoofing protections; principal/guard mismatch risk.

**Impact:**
- **Governance:** Acceptable if Application gates + equivalent asserts are mandatory (per proposal caveats).
- **Dependency:** Same Application actions; IMP-Q-07; V1 middleware on console routes.

### Option C

**Meaning:** Pure HTTP/API console (no Livewire).

**Benefits:** Thin UI; single mutation transport.

**Risks:** Diverges from Livewire-first product UI pattern.

**Impact:**
- **Governance:** U2 still enforceable via routes/roles; UX consistency impact is product/tech, not DGAP.
- **Dependency:** API client/console UX design; less reuse of Livewire layout patterns.

---

## IMP-Q-05 — V1 employee page inventory

**Decision question:**  
Which employee-facing pages are in Spec04 v1 scope (excluding approve/reject on employee surface)?

### Option A

**Meaning:** **Minimal:** List + Show + Create (+ submit if a separate step).

**Benefits:** Smallest auth surface to harden under V1.

**Risks:** Incomplete journey if Cancel is expected by business.

**Impact:**
- **Governance:** Fits “create/view own” auth-packet framing; keeps U2 clean if no approve on employee.
- **Dependency:** Smaller test/AC set; may defer cancel capability already present in backend.

### Option B

**Meaning:** **Standard:** Option A + Cancel.

**Benefits:** Matches existing backend cancel capability; fuller employee journey.

**Risks:** More acceptance criteria and V1 coverage on mutate routes.

**Impact:**
- **Governance:** Still forbids approve/reject on employee (U2); expands mutation surface under V1.
- **Dependency:** Couples to IMP-Q-03 path and cancel Application/HTTP path.

### Option C

**Meaning:** **Expanded:** B + edit-draft / family flows / multi-type.

**Benefits:** Product-complete relative to broader Request domain.

**Risks:** Scope creep; blocks timely implementation authorization.

**Impact:**
- **Governance:** Exceeds Spec04 auth-packet documented in-scope framing; may pull unrelated product decisions.
- **Dependency:** Large BO/product inventory; delays IMP-Q closure.

---

## IMP-Q-06 — Role Seeder approach

**Decision question:**  
How should identity-guard roles `employee` and `DeptMgr` (OQ-AUTH-01 B) be introduced into environments?

### Option A

**Meaning:** Extend `IdentityRoleSeeder` with `findOrCreate` for both roles on `guard_name = identity` only; no permission grants until catalog decided.

**Benefits:** Matches existing dormitory identity-role seeding pattern.

**Risks:** Mixes Spec04 into shared seeder; accidental `web` guard creation (SEC-G-01).

**Impact:**
- **Governance:** Satisfies OQ-AUTH-01 B naming if `identity` guard enforced; must not use dormitory-manager for Stage-1.
- **Dependency:** Needs seeder execution authorization; Spatie cache clear; separate user↔role assignment ops.

### Option B

**Meaning:** New dedicated seeder `Spec04IdentityRoleSeeder`.

**Benefits:** Clear Spec04 audit trail / isolation.

**Risks:** Extra seeder wiring in DatabaseSeeder / deploy docs.

**Impact:**
- **Governance:** Same role semantics as A with clearer artifact boundary.
- **Dependency:** Must still be authorized to run; same guard constraints.

### Option C

**Meaning:** Manual/ops role creation via existing Identity role-manage UI/API.

**Benefits:** No seeder code change.

**Risks:** Non-reproducible environments; weak CI; drift across envs.

**Impact:**
- **Governance:** Roles can still match OQ-AUTH-01 B if ops follow identity guard; auditability weaker.
- **Dependency:** Relies on ops process and `identity.roles.manage` (or equivalent) availability.

---

## IMP-Q-07 — Approver Console surface

**Decision question:**  
Should the Stage-1 DeptMgr approver console be a separate product surface/contract, part of the employee self-service surface, or deferred?

### Option A

**Meaning:** **Separate** product surface + Feature Contract id (e.g. `department-request-approver-console`), linked to Spec04 auth packet.

**Benefits:** Strong U2; independent locks/permits.

**Risks:** Two auth/implementation tracks to manage.

**Impact:**
- **Governance:** Best match to DECIDED DGAP-06 U2.
- **Dependency:** Drives IMP-Q-01, IMP-Q-04, IMP-Q-06; snapshot (IMP-Q-02) still needed for V1 on console reads/mutates.

### Option B

**Meaning:** **Same** product surface `employee-request-self-service` with two UI modules inside one contract.

**Benefits:** One packet / one contract tree.

**Risks:** Blurs U2; easier to leak approve into employee UI.

**Impact:**
- **Governance:** Weaker structural enforcement of U2 despite DGAP-06.
- **Dependency:** Single impl lock may over-couple employee and approver delivery.

### Option C

**Meaning:** Defer console to a later phase; first impl auth covers employee self-service only.

**Benefits:** Shrinks first implementation slice.

**Risks:** Stage-1 operational path incomplete; snapshot/V1 for approver unused until later; risk of claiming Spec04 “complete” prematurely for Stage-1 ops.

**Impact:**
- **Governance:** U2 remains a decided boundary but unrealized; does not reverse DGAP-06.
- **Dependency:** Employee-first permit possible; console needs a later separate authorization.

---

## Lead Decision Input

| ID | Decision Required | Selected Option | Rationale |
|----|-------------------|-----------------|-----------|
| IMP-Q-01 | Route naming / entry-point layout for employee vs DeptMgr console | **A** | Separate URL boundaries (`/employee/requests/*` + `/approvals/stage1/*`) structurally enforce DGAP-06 U2 and OQ-AUTH-02 dual `identity.role` stacks. |
| IMP-Q-02 | Persist Stage-1 assigned approver identity snapshot how? | **A** | Column on `requests` implements OQ-AUTH-03 B snapshot; enables DGAP-06 V1 “assigned approver” filtering. (Migration still requires separate permit.) |
| IMP-Q-03 | Reuse vs successor vs new Request List/Show (Create) path | **B** | Successor contracts keep frozen list/show files untouched; aligns with Spec04 Feature Contract draft guard against silent reopen. |
| IMP-Q-04 | U2 approve/reject via HTTP, Livewire→Application, or HTTP-only console | **B** | Livewire → Application Actions reuses Application mutation gates; approve/reject remain on DeptMgr console only (U2 / OQ-AUTH-02). |
| IMP-Q-05 | v1 employee page inventory (minimal / standard+cancel / expanded) | **B** | List, Show, Create, Cancel matches create/view (+ cancel) journey; explicitly excludes approve/reject on employee surface (U2). |
| IMP-Q-06 | How to seed/catalog `employee` + `DeptMgr` on identity guard | **A** | Extend IdentityRoleSeeder for OQ-AUTH-01 B roles on `guard_name = identity`; Stage-1 uses DeptMgr not dormitory-manager (DGAP-05 A). (Seeder execution still requires separate permit.) |
| IMP-Q-07 | Separate console surface, combined surface, or defer console | **A** | Separate Approver Console product surface/contract implements DGAP-06 U2 and Stage-1 DeptMgr ownership (DGAP-05 A / OQ-AUTH-01). |

---

## Constraints (unchanged by these decisions)

- Implementation: **NOT AUTHORIZED**
- L5 / L6: **NOT AUTHORIZED**
- No code, migrations, routes, seeders, or Livewire created by this recording
- DGAP-05 / DGAP-06 / OQ-AUTH-01/02/03 remain as previously DECIDED (not reopened)
- Next gate: **separate Implementation Permit** (AWAITING IMPL PERMIT)

---

**FINAL STATUS: DECISIONS RECORDED - AWAITING IMPL PERMIT**  
**IMPLEMENTATION NOT AUTHORIZED**  
**L5 / L6 NOT AUTHORIZED**

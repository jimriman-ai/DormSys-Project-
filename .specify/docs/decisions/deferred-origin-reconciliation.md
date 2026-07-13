---
artifact: deferred_origin_reconciliation
status: DEFERRED_ORIGIN_RECONCILIATION_COMPLETED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
phase: CORE_COMPLETION_WAVE
triage_impact: TRIAGE_CONFIRMED
recommended_next_gate: PRODUCT_SURFACE_AUTHORIZATION_DECISION
upstream_triage: .specify/docs/decisions/product-authorization-gap-triage.md
upstream_portfolio: .specify/docs/planning/deferred-portfolio-review-and-disposition.md
date: 2026-07-13
---

# Deferred Origin Reconciliation

**Artifact type:** Governance decision-origin recovery (non-authorizing)  
**Status:** `DEFERRED_ORIGIN_RECONCILIATION_COMPLETED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Purpose:** Recover historical origin of Deferred / Blocked items before treating product-authorization triage as final. Does **not** activate execution, invent product authority, or authorize Auth / UI / Workflow / Lottery / RBAC work.

**Upstream:**  
- `.specify/docs/planning/deferred-portfolio-review-and-disposition.md`  
- `.specify/docs/decisions/product-authorization-gap-triage.md` (`PRODUCT_AUTHORIZATION_REQUIRES_HUMAN_DECISION`)

---

## 1. Deferred Origin Table

| Item | Current Status | Original Intent | Decision Authority | Evidence Found | Still Valid? | Needs New Human Decision? | Classification |
| ---- | -------------- | --------------- | ------------------ | -------------- | ------------ | ------------------------- | -------------- |
| Workflow | `WORKFLOW_REMAINS_DEFERRED` | Intentional module deferral until activation criteria; Request keeps approval **state** (CD-010); Core Wave membership out | Catalog + CD-010 (2026-06-25 ACCEPTED); Core Wave reaffirmation by GOVERNANCE_PLANNING (2026-07-13) | `catalog-decisions.md` CD-010; `spec-catalog.md` Deferred Components; `workflow-activate-vs-defer-decision.md` | **Yes** — criteria still unmet; no Core Wave path needs Workflow module | **No** for keep-deferred; **Yes** only when criteria/product orchestration need appear | `KEEP_DEFERRED` |
| Role mapping (`dormitory.structure.*`) | Deferred — keys registered, no grants | Intentional **scope cut** from bounded Spec02 structure-binding packet; Spec02 remains Frozen | Spec02 IA lock / closeout (packet-scoped); Spec02 freeze (Wave 1A) | `spec02-dormitory-authorization-binding-implementation-lock.md`; structure-binding closeout; Auth residual refresh R1 | **Yes** as packet exclusion / freeze | **Yes** — when/if product names a surface that needs grants (not to reopen closed packet) | `KEEP_DEFERRED` |
| Dormitory UI (`dormitory-admin-ui`) | BLOCKED / NOT_READY | Spec04 Phase H presentation deferred; never received successor product UI authorization after `employee-context-ui` close | Spec OA deferral ≠ auth; product discovery (2026-07-10); Auth residual / triage (2026-07-13) | `next-ui-feature-authorization-discovery.md`; `governance-next-candidate-triage.md`; Spec04 Auth residual product decision | **Yes** — still no named product surface | **Yes** — explicit product surface authorization (or refuse) | `REQUIRE_PRODUCT_DECISION` |
| Spec06 / Lottery | `SPEC06_REMAINS_DEFERRED` + `AUTHORITY_NOT_AVAILABLE`; wave OUT | Dual: (1) documented exception — impl ahead of map-backed IA; hold **new** Lottery work; (2) Core Wave membership intentionally deferred | Regularization GDR Option B / Decision 3; Core Wave inclusion decision (GOVERNANCE_PLANNING 2026-07-13) | `spec06-regularization-decision.md`; `spec-catalog.md`; `spec06-core-wave-inclusion-decision.md` | **Yes** — authority gap + no product Lottery-in-wave mandate evidenced | **Yes** before any **new** Lottery work (product priority + Domain Authority); **No** to force into Core Wave now | `BLOCKED_PENDING_AUTHORITY` |
| Spec04 Auth residual (aggregate) | OPEN — `REQUIRES_PRODUCT_AUTHORITY` | After Application PEP close, remainder (role map / Presentation / HTTP / product auth) left open by design of bounded packet | Spec02 structure closeout + Auth residual refresh + product decision (2026-07-13) | Auth residual refresh; `spec04-auth-residual-product-decision.md`; deferred portfolio | **Yes** — Application PEP ≠ full Auth residual | **Yes** — named surface + packet scope | `REQUIRE_PRODUCT_DECISION` |
| UI Presentation auth (Dormitory) | Blocked | No authorized Dormitory UI → no Presentation binding target; Anti-Leak | Auth residual refresh R2; product triage | Same as Dormitory UI + Anti-Leak contracts | **Yes** | **Yes** (after surface named) | `BLOCKED_PENDING_AUTHORITY` |
| HTTP / Policy auth | Deferred | No Spec04-authorized admin HTTP surface | Auth residual refresh R3; product triage | Same cluster | **Yes** | **Yes** only if product includes HTTP in packet | `BLOCKED_PENDING_AUTHORITY` |
| OA-02-01 / Identity Livewire admin | Deferred (Spec02 Wave 1A) | Intentional Wave 1A out-of-scope / freeze | Spec02 freeze (2026-06-26); catalog; structure closeout non-claims | `spec-catalog.md`; Spec02 closeout; UI discovery | **Yes** | **Yes** to reopen Spec02 UX — separate from Dormitory Auth path | `KEEP_DEFERRED` |
| Full RBAC / Full Spec02 authorization | Not complete; Frozen | Intentional: only bounded structure PEP closed; Spec02 not unfrozen | Spec02 freeze + structure-binding closeout | Catalog; Spec02 closeout; Auth residual refresh | **Yes** | **Yes** for successive bounded packets / unfreeze — not one “do everything” stream | `KEEP_DEFERRED` |
| Spec11 Reporting (new work) | `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` | Documented exception — claimed DA not recoverable; hold new Reporting | Spec11 authority-resolution decision; catalog | `spec-catalog.md` Spec11 notes; product triage Spec11 separation | **Yes** | **Yes** on separate Reporting authority track | `BLOCKED_PENDING_AUTHORITY` |
| Main UI Feature Execution / successor UI intake | Deferred / blocked | After `employee-context-ui` consumed, no successor product auth | Product discovery 2026-07-10; UI triage | `next-ui-feature-authorization-discovery.md`; `product-authorization-next-ui-feature.md` (stale AUTHORIZED for closed slug) | **Yes** | **Yes** — successor surface authorization | `REQUIRE_PRODUCT_DECISION` |
| Request Dependent live path | Deferred at Spec03 close | Explicitly not required for Spec03 close; IRG for live stub | Spec03 closure handoff; CD-009 | `spec03-closure-handoff.md`; catalog | **Yes** unless product asserts Family-live | **Yes** only if Family-live asserted | `KEEP_DEFERRED` |
| EmployeeRead (T049–T052) | Deferred at Spec03 close | Intentional Post-Spec03 deferral | Spec03 closure handoff | Same | **Yes** | **Yes** for Post-Spec03 selection | `KEEP_DEFERRED` |
| Request Create Entrypoint Discoverability | `DEFERRED_PENDING_EVIDENCE` | Deferred because Feature Analysis not allowed — gap not confirmed | Governance defer decision 2026-07-11 | `request-create-entrypoint-discoverability-deferred-decision.md` | **Yes** pending new evidence | **Yes** only with material new evidence | `KEEP_DEFERRED` |
| Request List Detail Navigation (re-selection) | Deferred after review | Core list→show already delivered/closeout; further FC/IA not warranted | Feature Analysis Review 2026-07-11 | review-decision + UI closeout | **Yes** for same closed scope | **No** for same scope; **Yes** only for separately selected residual polish | `RETIRE_AS_LEGACY` |
| Spec04 Check-in residual | RETIRED / CLOSED_NO_FURTHER_ACTION | Settled retirement from Spec04 tracking | Spec04 residual ownership / closeout reconciliation | Catalog; deferred portfolio | **Yes** — do not revisit as open deferred work | **No** | `RETIRE_AS_LEGACY` |
| Spec03 / Spec04 Assignability / Spec07 | Closed | Closed delivery — not deferred incompleteness | Closure handoffs / catalog | Catalog; portfolio KEEP_CLOSED | **Yes** | **No** (reopen would need explicit reopen authority) | `RETIRE_AS_LEGACY` |
| Notification mark-all / reactive badge | Blocked / unclear | v1 UI exclusions; no product auth; backend gaps for mark-all | UI triage / discovery | `governance-next-candidate-triage.md`; discovery inventory | **Yes** | **Yes** + backend for mark-all | `BLOCKED_PENDING_AUTHORITY` |
| Audit explorer / Reporting KPI UI | Blocked | Explicit Spec11 IA exclusion (E-03/E-04) | Spec11 IA | Discovery; triage | **Yes** | **Yes** on Reporting/UI product track | `BLOCKED_PENDING_AUTHORITY` |
| Lottery / Voucher / Allocation operator UIs | Blocked | Spec OA presentation deferrals; never product-authorized for intake | Spec OAs + discovery | Discovery inventory | **Yes** | **Yes** for any intake | `REQUIRE_PRODUCT_DECISION` |

### Per-item origin answers (summary)

| Item | Why deferred | Who decided | Recorded? |
| ---- | ------------ | ----------- | --------- |
| Workflow | Activation criteria + CD-010 split; Core Wave non-necessity | Catalog/CD-010; GOVERNANCE_PLANNING wave decision | **Yes** |
| Role mapping | Explicit exclusion from structure-binding IA | Spec02 lock/closeout authority | **Yes** |
| Dormitory UI | Never product-authorized for intake; Auth remainder unfinished | Product discovery + governance triage (absence recorded) | **Yes** (as absence / blocked) |
| Spec06 | Authority gap (Option B) + Core Wave membership deferral | Spec06 GDR; GOVERNANCE_PLANNING inclusion decision | **Yes** |
| Other blocked UI / Reporting | Product auth missing and/or IA exclusions / Spec02 freeze | Spec OA + IA + product discovery | **Yes** for major items |

---

## 2. Decision Origin Findings

1. **Intentional v1 / wave scope cuts (valid KEEP_DEFERRED):** Workflow (CD-010 + catalog criteria + 2026-07-13 wave decision); OA-02-01 / Full RBAC / Spec02 freeze; EmployeeRead; Request Dependent live; role-mapping **grants** as exclusion from the closed structure-binding packet.
2. **Missing product authorization (REQUIRE_PRODUCT_DECISION):** Dormitory UI / Main UI successor intake; Lottery/Voucher/Allocation operator UIs as presentation candidates; Spec04 Auth residual **packet promotion** (cannot name surface).
3. **Authority-dependent blockers (BLOCKED_PENDING_AUTHORITY):** Spec06 new Lottery work (`AUTHORITY_NOT_AVAILABLE`); Spec11 new Reporting work; Presentation/HTTP auth until surfaces exist; mark-all / explorer / KPI UI (backend and/or IA exclusion).
4. **Not “implementation debt” mislabeled as Deferred:** Workflow and Spec06 wave membership are **intentional non-inclusion**, not unfinished Core Wave coding. Spec06 **delivery** already exists; remaining openness is **governance debt**, not missing lottery engine for this wave.
5. **Retire rather than revisit:** Spec04 Check-in residual; closed Spec03 / Assignability / Spec07; Request List Detail Navigation **same-scope** re-progression; treating closed UI closeouts as open deferred defects.
6. **Stale label risk (not origin inversion):** `product-authorization-next-ui-feature.md` still reads `AUTHORIZED` for closed `employee-context-ui` — closeout wins; does **not** authorize a next surface. Recorded in discovery; not contradictory on successor absence.
7. **Uncertainty preserved:** Exact historical **human product owner identity** for “who may authorize the next UI surface” is **not named** in Authority Map for successor intake — gap is recorded as blocking in triage; this reconciliation does **not** invent an owner. SPEC06-C06 alternate-path possibility remains UNKNOWN per GDR (does not upgrade Spec06 authority).

---

## 3. Origin Drift

| Case | Drift assessment |
| ---- | ---------------- |
| Workflow deferral | **Still valid** — activation criteria unmet; CD-010 still applies; 2026-07-13 reaffirmation current |
| Spec06 Core Wave deferral | **Still valid** — no product Lottery-in-wave mandate evidenced; regularization already complete |
| Spec06 `AUTHORITY_NOT_AVAILABLE` | **Still valid** — not resolved by Core Wave deferral or Auth work |
| Role mapping packet exclusion | **Still valid** — closed packet must not be reopened; **drift** is only that practical admin access now **depends** on a future product-named surface (sequencing dependency clarified, not origin invalidated) |
| Dormitory UI blocked | **Still valid** — Application PEP completion did **not** create product UI auth (no false unblock) |
| Spec02 Frozen / OA-02-01 | **Still valid** — structure-binding closeout explicitly did not unfreeze |
| Employee UI grant `AUTHORIZED` | **Stale relative to closeout** — lifecycle consumed; **does not** create a deferred “employee UI debt” under the same slug |
| Request Create Entrypoint | **Still valid** as evidence-pending deferral — not proven delivered gap, not retired-as-satisfied |
| Auth residual OPEN after PEP | **Still valid** — not drift; intentional remainder after bounded close |

**No recovered case** where a formerly blocked dependency is now resolved such that Dormitory UI / Auth packet prep may proceed without a new product surface decision.

---

## 4. Impact On Current Product Authorization Triage

```text
TRIAGE_CONFIRMED
```

`PRODUCT_AUTHORIZATION_REQUIRES_HUMAN_DECISION` / `NO_NAMED_PRODUCT_SURFACE_AUTHORIZED` **stands**. Origin recovery shows:

- Intentional deferrals (Workflow, Spec06 wave membership, Spec02 freeze, EmployeeRead, Dependent) are **not** silent product-auth substitutes.
- Auth/UI blockers are **rooted** in missing successor product authorization (plus Spec06/Spec11 authority gaps on separate tracks).
- Portfolio/triage dispositions are directionally consistent with recorded origins; no contradiction requires changing the triage outcome label.

**Refinement note (non-outcome-changing):** Role mapping’s **origin** is intentional packet exclusion (`KEEP_DEFERRED`), while its **next activation** remains gated by product surface decision — triage already modeled this as `DEFERRED_UNTIL_PRODUCT_SURFACE_DEFINED`.

---

## 5. Recommended Next Governance Gate

```text
PRODUCT_SURFACE_AUTHORIZATION_DECISION
```

Missing human/product authority to **name and authorize** (or explicitly refuse) a product surface for the Auth/UI path is confirmed. `SPEC04_AUTH_RESIDUAL_AUTH_PACKET_PREPARATION` remains invalid without a named authorized surface. Evidence is strong enough that `DEFERRED_DECISION_RECOVERY_INCOMPLETE` is **not** selected.

---

## 6. Explicit Boundary Preservation

This reconciliation does **not** authorize:

- Auth implementation  
- role mapping implementation  
- UI execution  
- Workflow activation  
- Lottery activation  
- full RBAC  
- closed spec reopening  

---

## Required Final Decision Block

```text
DEFERRED_ORIGIN_RECONCILIATION

Decision:
DEFERRED_ORIGIN_RECONCILIATION_COMPLETED

Triage Impact:
TRIAGE_CONFIRMED

Recommended Next Gate:
PRODUCT_SURFACE_AUTHORIZATION_DECISION

Execution Authority:
NONE
```

---

## No-Change Confirmation

`No application, test, contract, UI, workflow, lottery, role-mapping, policy, middleware, Livewire, Blade, seeder, or authorization implementation files were modified.`

Only this governance artifact was created:

- `.specify/docs/decisions/deferred-origin-reconciliation.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`DEFERRED_ORIGIN_RECONCILIATION_COMPLETED`**  
- Triage impact: **`TRIAGE_CONFIRMED`**  
- Recommended next gate: **`PRODUCT_SURFACE_AUTHORIZATION_DECISION`**  
- Last Updated: 2026-07-13  
- Checkpoint: `deferred-origin-reconciliation`

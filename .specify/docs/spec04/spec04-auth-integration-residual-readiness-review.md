---
artifact: spec04_auth_integration_residual_readiness_review
status: REVIEW_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: NON_AUTHORIZING_DISCOVERY
decision: READINESS_CONFIRMED
date: 2026-07-12
---

# Spec04 Auth Integration Residual — Readiness Review

**Artifact type:** Residual readiness review (non-authorizing discovery)  
**Upstream selection:** `.specify/docs/planning/next-work-selection.md` (post–Check-in retirement)  
**Status:** `REVIEW_RECORDED`

This review does **not** authorize implementation, create contracts/IA, unfreeze Spec02, start Dormitory UI, or reopen closed Spec04 Assignability / Check-in residuals.

---

## A. Current Context

| Topic | Posture |
| ----- | ------- |
| Spec04 Allocation Assignability | **CLOSED** |
| Spec04 Check-in ↔ Dormitory | **RETIRED** / `CLOSED_NO_FURTHER_ACTION` |
| Spec04 Backend | **CLOSED** |
| Spec04 Product | **PENDING_RESIDUAL** — remaining open Spec04-tracked items include Auth and UI |
| Why Auth now | Assignability and Check-in Spec04 residuals are finished; selection named Auth residual readiness as the next discovery focus |

Auth readiness is the next valid Spec04 Product residual question after the Spec04 repair loop ended. The question is whether any Auth work remains as Spec04-executable scope, or whether it is a Spec02-owned / cross-boundary concern that must not expand Spec04’s Dormitory domain.

---

## B. Residual Identification

### Precise residual (evidence-supported)

**Name:** Authorization / policies / roles / guards for Dormitory surfaces  

**Canonical closeout wording** (`spec04-backend-closeout.md` §6; Spec04 residual table): Spec04 backend Phases 1–4 explicitly excluded authorization policies, roles, guards, and related presentation/HTTP surfaces from delivered Spec04 backend scope. The residual tracks that excluded product concern as deferred — **not cancelled**.

### Interpretations considered

| Interpretation | Support | Verdict |
| -------------- | ------- | ------- |
| **A. Dormitory-surface authorization binding** (policies/guards that protect Dormitory presentation/HTTP when those surfaces exist), consuming Spec02 Identity RBAC foundation | Closeout wording; Decision Gate §3.1; Ownership D3; no Dormitory `Policy` classes today | **Primary — evidence-supported** |
| B. Spec02 platform authentication (login/session/OA-02-01) as Spec04 work | Spec02 owns OA-02-01; Spec04 FR-EX-006 excludes Identity data ownership | **Rejected** as Spec04 residual meaning |
| C. Spec04 Domain inventing parallel roles/permissions inside Dormitory | Contradicts D3 and context-map R12 (Identity supplies cross-cutting auth) | **Rejected** — would be Spec04 domain extension / parallel auth authority |
| D. Spec04 backend still missing auth inside Domain/Application catalog APIs | Backend closeout accepted without policies; Mutation/Read delivered under IA exclusions | **Not evidenced** as open Spec04 backend gap |

**Most evidence-supported residual:** Interpretation **A** — Spec04-tracked deferral of **Dormitory-surface authorization** that must bind to **Spec02 Identity** roles/permissions foundation (Ownership D3), without Spec04 owning Identity or inventing a second auth authority.

---

## C. Evidence Review

| Artifact | Signal |
| -------- | ------ |
| `handoff/spec04-backend-closeout.md` §6 | Residual origin: Authorization / policies / roles / guards for Dormitory surfaces excluded from Spec04 backend close |
| `specs/004-accommodation-resource/spec.md` residual table | Auth row still `DEFERRED_TO_FUTURE_WAVE`; Assignability CLOSED; Check-in RETIRED |
| `decision/spec04-residual-ownership-decision.md` **D3** | Owner = `SPEC02_IDENTITY`; Dormitory-surface policy binding (if later scoped) must respect Spec02 foundation; **does not** authorize Spec02 unfreeze, OA-02-01, Livewire admin, or coding |
| `review/spec04-residual-ownership-decision-gate.md` §3.1 | Pre-decision split: Identity platform RBAC vs Dormitory-surface policy binding; Spec02 Frozen |
| `planning/spec04-residual-ownership-map.md` | Auth row still shows TBD / Pending Decision (**status noise** vs D3 Decision Record) |
| `spec-catalog.md` | Spec02 **Frozen — Wave 1A**; Spec04 open residuals Auth/UI; RBAC baseline delivered under Spec02 |
| `specs/002-identity-access/spec.md` | Roles/permissions delivered; OA-02-01 auth UX deferred; Livewire admin (T035–T037) deferred; reopen requires catalog decision |
| `handoff/spec04-implementation-authorization.md` / Phase 4 IA | Policies, gates, authorization code, UI, HTTP explicitly out of Spec04 authorized backend |
| `app/Modules/Dormitory/**` | **No** Policy/Gate authorization classes found — residual not partially implemented as Spec04 policies |
| UI triage `dormitory-admin-ui` | Blocked — no product authorization for Dormitory UI intake |
| Ownership D4 | Dormitory UI is independent presentation feature — not Auth owner |

**Ambiguities remaining (do not invent resolution here):**

- Exact permission catalog names for future Dormitory surfaces (not defined).  
- Whether surface policies ship with a future UI feature packet vs a Spec02 extension packet (D3 allows Spec02-owned selection; does not authorize either).  
- Ownership map Auth row still “Pending Decision” despite D3 — documentation mismatch only.

---

## D. Domain Boundary Verification

| Question | Assessment |
| -------- | ---------- |
| Fits fully inside accepted Spec04 operational domain (Dormitory physical catalog / assignability)? | **No** — Spec04 Domain/Application backend closure intentionally excluded auth; Spec04 must not own Identity roles/permissions |
| Touches another domain but containable as narrow integration? | **Yes** — Spec02 Identity foundation + optional later Dormitory-surface policy binding that **consumes** Spec02 (D3) |
| Requires extending/redefining Spec04 domain to own auth? | **No — and must not** — inventing Spec04-owned auth authority would violate D3 and FR-EX-006 |
| Blocked because issue belongs elsewhere? | Work **belongs under Spec02 ownership lines** for foundation; Spec04 only historically **tracked** the deferral. Spec02 Frozen blocks Spec02 unfreeze/coding without separate product/reopen authority |

**Classification:** **Cross-spec integration issue** (Spec02-owned Auth foundation ↔ future Dormitory surfaces), **not** an internal Spec04 domain-completion issue, **not** a Spec04 boundary-extension request.

**Boundary status (for decision block):** `CROSSES_SPEC_BOUNDARY_BUT_CONTAINABLE`

Containable means: proceed under Spec02 ownership / dependency clarification **without** changing Spec04’s accepted Dormitory domain boundary. Not containable as Spec04-only IA/coding.

---

## E. Readiness Assessment

**Ready for a next structured process step?** **Yes** — for **dependency clarification** under Spec02 ownership lines, not for Spec04 contract definition or Spec04 Implementation Authorization.

| Next-step type | Appropriate now? |
| -------------- | ---------------- |
| Spec04 feature analysis / Spec04 Auth contract | **No** — residual is not Spec04-domain-owned execution |
| Spec04 Implementation Authorization | **No** |
| Interface/contract discovery for Spec04 Auth ports | **No** until Spec02-scoped packet and surface targets exist |
| **Dependency clarification** (Spec02 ↔ Dormitory-surface auth sequencing; Spec02 freeze/product reopen implications; relationship to Dormitory UI residual) | **Yes** — tight non-authorizing next step |
| Spec02 unfreeze / OA-02-01 / Livewire admin | **Not authorized** by this review |

Spec02 Wave 1A freeze and missing Dormitory UI product auth mean **implementation is not justified**. Readiness here means the residual is **sufficiently identified and boundary-compatible** to leave Spec04-centric execution framing and enter Spec02-owned dependency clarification — without Spec04 domain extension.

---

## F. Outcome

`READINESS_CONFIRMED`

**Not** `READINESS_CONFIRMED_WITH_DOMAIN_EXTENSION` — Spec04 domain must not expand to own Auth.  
**Not** `READINESS_BLOCKED_BY_BOUNDARY_CHANGE` — progress does not require changing Spec04’s accepted domain boundary; it requires Spec02-owned follow-on process (and later product/IA gates), which is a containable cross-boundary path.

---

## G. Immediate Next Step

**Create a Spec02-owned Auth ↔ Dormitory-surface authorization dependency clarification artifact** (non-authorizing).

That clarification must:

- treat Spec02 Identity as foundation owner (D3)  
- not unfreeze Spec02 or authorize OA-02-01 / Livewire admin  
- not start Dormitory UI or Spec04 Auth IA  
- clarify sequencing vs Dormitory UI residual (D4) and whether Spec04 Auth residual tracking should later be retired or retained as status-only  

This review does **not** authorize that clarification’s outcomes beyond naming the next process step.

---

## Required Final Decision Block

```text
AUTH_INTEGRATION_RESIDUAL_READINESS

Decision:
READINESS_CONFIRMED

Residual:
Authorization / policies / roles / guards for Dormitory surfaces (Spec04 Auth integration residual)

Boundary Status:
CROSSES_SPEC_BOUNDARY_BUT_CONTAINABLE

Selection Basis:
Closeout excludes Spec04 policies; Ownership D3 assigns Auth to SPEC02_IDENTITY; Spec02 Frozen RBAC baseline exists; no Dormitory Policy classes; Spec04 must not invent parallel auth — next step is Spec02-owned dependency clarification without Spec04 domain extension.

Immediate Next Step:
Create Spec02-owned Auth ↔ Dormitory-surface authorization dependency clarification (non-authorizing)

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Scope Integrity Confirmation

| Check | Result |
| ----- | ------ |
| Assignability not reopened | **Confirmed** |
| Check-in residual not reopened | **Confirmed** |
| No code changes | **Confirmed** |
| No contract / IA created | **Confirmed** |
| No Spec04 domain expansion | **Confirmed** |
| Only this review artifact written | **Confirmed** |

---

## No-Change Confirmation

`No application, test, catalog, ownership-map, contract, authorization, or Spec04 closed/retired residual files were modified.`

Only this artifact was created:

- `.specify/docs/spec04/spec04-auth-integration-residual-readiness-review.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`REVIEW_RECORDED`** / **`READINESS_CONFIRMED`**  
- Residual: Authorization / policies / roles / guards for Dormitory surfaces  
- Last Updated: 2026-07-12

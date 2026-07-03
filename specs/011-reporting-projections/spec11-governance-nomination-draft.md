# spec11 Governance Nomination Draft

**Type**: Governance review draft — nomination preparation only  
**Recorded**: 2026-07-03  
**Authority**: **NONE** — this document authorizes nothing  
**Branch**: `011-reporting-projections`  
**Authoritative inputs**: `spec.md`, `plan.md`, `architecture-clarification.md`, `decision-log.md`, `tasks.md`, `checklists/requirements.md`  
**Predecessor (documented)**: spec10 — **CLOSED / FROZEN**

---

## 1. GOVERNANCE_NOMINATION_SUMMARY

### Planning status (documented facts)

| Field | Value | Source |
| ----- | ----- | ------ |
| **spec11 status** | Architecture Clarified — Planning-only (no Design Approval · no Implementation Authorization · no execution) | `spec.md` header |
| **plan status** | Architecture clarified — planning-only — not implementation plan | `plan.md` header |
| **tasks status** | PLANNING BACKLOG ONLY — NON-EXECUTABLE | `tasks.md` header |
| **lifecycle_state** | `ARCHITECTURE_CLARIFIED` | `tasks.md` metadata block |
| **execution_state** | `NONE` | `tasks.md` metadata block |
| **executable** | `false` | `tasks.md` metadata block |
| **authorization_required** | separate governance record (not implied) | `tasks.md` metadata block |
| **predecessor** | spec10 CLOSED / FROZEN | `spec.md`, `tasks.md` |
| **checklist** | PASS (16/16) | `checklists/requirements.md` |

### Completed planning milestones (documented facts)

| Phase | Items | Status | Source |
| ----- | ----- | ------ | ------ |
| **P0** | P-001–P-006 | Complete | `tasks.md` |
| **P1** | P-010, P-011, P-012, P-014 | Complete | `tasks.md` |
| **P1** | P-013 | **Incomplete** | `tasks.md` |
| **P0 exit criteria** | All items in `spec.md` §7 Initialization | Complete | `spec.md` §7 |
| **P1 exit criteria** | All items in `spec.md` §7 Architecture clarification | Complete per `spec.md` §7 | `spec.md` §7 |
| **DL-01–DL-03** | Resolved at architecture clarification (2026-07-02) | Resolved | `decision-log.md` |
| **Architecture clarification** | Seven sections delivered | Complete — planning-only | `architecture-clarification.md` |

### Planning maturity (documented facts)

| Area | State | Source |
| ---- | ----- | ------ |
| Charter, problem frame, evolution tracks E-01–E-08 | Documented as future possibilities | `spec.md` §2–§3 |
| Consumption-layer architecture | Clarified (read shapes, T0/T1/T2, consumption frames, analytics separation) | `architecture-clarification.md` |
| Resolved planning decisions | DL-01 Hybrid; DL-02 Layered B→A; DL-03 role-gated `includeArchived` | `decision-log.md` |
| Technical planning artifacts | Not present — `data-model.md`, `contracts/` not yet required | `spec.md` §6 |
| P2 tasks P-020–P-024 | Not started — not authorized | `tasks.md` |
| P3 governance tasks P-030–P-033 | Not started — not authorized | `tasks.md` |
| P4 implementation tracks | HALT — placeholders only | `tasks.md` |

### Remaining documented open items

See §2. At minimum: **UD-11-01**, **UD-11-02**, **P-013** (`decision-log.md`, `architecture-clarification.md` §7.4).

### Overall readiness level

**Not assigned by the planning baseline.** The authoritative inputs contain **conflicting readiness signals**:

| Signal | Statement | Source |
| ------ | --------- | ------ |
| A | Checklist notes: "Ready for P2 technical planning or governance nomination (neither authorized)" | `checklists/requirements.md` Notes |
| B | `ready_for_governance_review`: **no** — initialization only | `tasks.md` READINESS_OUTPUT |
| C | `lifecycle_stage`: **PLANNING_INITIALIZATION**; `next_step`: Optional `/speckit-clarify` or governance nomination when ready | `tasks.md` NEXT_STATE |

**Recommendation (derived from documented conflict):** Governance reviewers should reconcile signals A/B/C against `spec.md` §7 (P0 and P1 marked complete) and `tasks.md` header (`ARCHITECTURE_CLARIFIED`) before accepting or deferring nomination. This draft does not resolve that reconciliation.

---

## 2. DOCUMENTED_OPEN_ITEMS_REGISTER

*Only items explicitly documented in the authoritative inputs. No governance owners are assigned unless stated in those sources.*

### 2.1 Decision-log open items

| ID | Description | Status | Documented default / note | Planning impact | Implementation impact | Documented outcomes (where stated) |
| -- | ----------- | ------ | ------------------------- | --------------- | --------------------- | ---------------------------------- |
| **UD-11-01** | Separate `reporting.read` permission vs extend `audit.read` | **OPEN** | Default: extend `audit.read` | Affects permission propagation question in `plan.md` E-01; visibility taxonomy in `architecture-clarification.md` §1 | Future permission seeding and read-port authorization — not authorized in planning baseline | Not enumerated in authoritative inputs |
| **UD-11-02** | Introduce `SecurityAuditor` role | **OPEN** | "governance at authorization" | Referenced for security/audit reporting audience in `architecture-clarification.md` §4.3; DL-03 lists "future governance roles" | Role definition if introduced — not authorized in planning baseline | Not enumerated in authoritative inputs |
| **P-013** | Compliance KPI stakeholder interviews for E-04 | **OPEN** | Task unchecked in `tasks.md` | E-04 planning questions in `plan.md` remain unanswered | Compliance dashboard scope undefined until addressed | Not enumerated in authoritative inputs |

### 2.2 Deferred decision (documented in DL-03 resolution)

| ID | Description | Status | Source | Planning impact | Implementation impact |
| -- | ----------- | ------ | ------ | --------------- | --------------------- |
| **DL-03-C** | Option C — separate archive-tier reporting projection | **DEFERRED** — may be revisited at P2 if performance requires partition without policy change | `decision-log.md` DL-03 | Belongs in P2 `data-model.md` per decision-log | T1 projection design — not authorized |

### 2.3 Evolution-track planning questions (documented, unresolved)

*These are planning questions in `plan.md` — not decision-log entries. Listed because they are explicitly open in the baseline.*

| Track | Documented open questions | Resolved in baseline? |
| ----- | ------------------------- | --------------------- |
| **E-01** | Aggregations required; export sync vs job-based; `audit.read` propagation | Partially — DL-03 addresses archive default |
| **E-02** | Snapshot vs incremental refresh; co-location vs separate projection tables; archive-tier default | Partially — DL-01 hybrid; archive default in DL-03 |
| **E-03** | Explorer MVP: entity timeline vs global search; Jalali/RTL; OA-10-05 relationship | Partially — DL-02 layered consumption |
| **E-04** | Compliance KPI event categories; data minimization for snapshots | **Open** — linked to P-013 |
| **E-05** | Volume thresholds; read replica vs materialized view | **Open** |
| **E-06** | `AuditEventType` dimension extensibility; per-context coordination | **Open** — P-024 not started |
| **E-07** | Architecture test boundaries; forbidden vs allowed Audit imports | Partially — rules in `architecture-clarification.md` §3.6, §7.3 |

### 2.4 Architecture-clarification open items (§7.4)

Matches decision-log entries: **P-013**, **UD-11-01**, **UD-11-02** (`architecture-clarification.md` §7.4).

---

## 3. GOVERNANCE_READINESS_ASSESSMENT

| Question | Assessment | Rationale (authoritative inputs only) |
| -------- | ---------- | ------------------------------------- |
| Is P0 planning complete? | **Yes (documented)** | `tasks.md` P-001–P-006 complete; `spec.md` §7 Initialization criteria met |
| Is P1 architecture clarification complete? | **Yes (documented)** | `tasks.md` P-010, P-011, P-012, P-014 complete; `spec.md` §7 P1 criteria met; `plan.md` P1 marked Complete |
| Is P-013 complete? | **No (documented)** | `tasks.md` P-013 unchecked |
| Is architecture sufficiently clarified for planning baseline? | **Yes (documented)** | `architecture-clarification.md` status CLARIFIED; `spec.md` §7 P1 exit criteria checked |
| Are unresolved items bounded? | **Yes (documented)** | Open items listed in `decision-log.md` and `architecture-clarification.md` §7.4; E-track questions remain hypotheses per `spec.md` §3 |
| Is technical planning (P2) authorized? | **No (documented)** | `plan.md`: P2 "requires design approval"; `tasks.md` P-020–P-024 not authorized |
| Is implementation authorized? | **No (documented)** | `spec.md` §4; `tasks.md` executable false; P4 HALT |
| Is execution authorized? | **No (documented)** | `spec.md` governance line; `tasks.md` execution_state NONE |

**Baseline artifact inconsistency (documented, unresolved):** `tasks.md` header/metadata (`ARCHITECTURE_CLARIFIED`) and `READINESS_OUTPUT` / `NEXT_STATE` footer blocks are not aligned. This draft does not normalize those fields.

---

## 4. AUTHORIZATION_PRECONDITIONS

*Documented governance chain from `spec.md` §5 rule 2: nomination → design approval → implementation authorization. No step is authorized by this draft.*

### A. Prerequisites for P2 technical planning (`tasks.md` P-020–P-024)

| # | Precondition | Source |
| - | ------------ | ------ |
| 1 | P2 tasks are **not authorized** until separate governance path permits them | `tasks.md` Phase P2 header |
| 2 | `plan.md` states P2 **requires design approval** | `plan.md` E-08 planning phases table |
| 3 | DL-01–DL-03 resolved decisions guide P2 only — **do not authorize implementation** | `decision-log.md` Purpose |
| 4 | `data-model.md`, `contracts/`, `research.md` are future P2 deliverables | `tasks.md` P-020–P-022; `architecture-clarification.md` §7.2 |
| 5 | Resolved decisions DL-01–DL-03 remain binding unless changed via governance | `decision-log.md` |

**Recommendation (derived):** P-013 and open UD items should be dispositioned before or during Design Approval if they affect E-04 or permission design. The baseline does not mandate a single ordering.

### B. Prerequisites for implementation planning (`tasks.md` P-032)

| # | Precondition | Source |
| - | ------------ | ------ |
| 1 | P-032 scope proposal: **waves TBD — not defined** | `tasks.md` P-032 |
| 2 | P3 tasks P-030–P-033 not started | `tasks.md` |
| 3 | `spec.md` §6: implementation authorization not yet required | `spec.md` §6 |
| 4 | P2 technical artifacts should exist per `plan.md` next steps | `plan.md` Next Planning Steps |

### C. Prerequisites for execution authorization

| # | Precondition | Source |
| - | ------------ | ------ |
| 1 | Separate governance authorization required — initialization grants none | `spec.md` §5 rules 2–3 |
| 2 | `tasks.md` entries non-executable until future authorization record activates them | `spec.md` §5 rule 3; `tasks.md` WARNING |
| 3 | P4 tracks E-01–E-08: **HALT** | `tasks.md` Phase P4 |
| 4 | `architecture-clarification.md` §6: Implementation Authorization, Design Approval, and nomination records excluded from clarification scope | `architecture-clarification.md` §6 item 6 |
| 5 | spec10 preservation: no mutation; consumption via `AuditHistoryReadContract` | `spec.md` §5; `architecture-clarification.md` §7.5 checklist |
| 6 | E-08: each expansion requires its own wave authorization | `plan.md` E-08; `architecture-clarification.md` §5.1 |

---

## 5. BASELINE_GROUNDED_RISK_SUMMARY

*Risks and mitigations explicitly stated in authoritative inputs only.*

| ID | Risk | Documented in | Mitigation documented in baseline |
| -- | ---- | ------------- | --------------------------------- |
| **R-01** | Ad-hoc queries bypass governance and R11 boundaries | `plan.md` E-01 "Risk if skipped" | Reporting-owned query façade hypothesis; E-07 consumption decoupling |
| **R-02** | Unresolved permission model (UD-11-01) | `decision-log.md` | Default extend `audit.read` |
| **R-03** | Unresolved SecurityAuditor role (UD-11-02) | `decision-log.md`; `architecture-clarification.md` §4.3 | `Administrator` primary audience documented; role decision deferred to authorization |
| **R-04** | Missing compliance KPI validation (P-013) | `tasks.md`; `plan.md` E-04 questions | E-04 remains non-binding hypothesis per `spec.md` §3 |
| **R-05** | Correlation views require T1 — frozen spec10 query has no `correlationId` filter | `architecture-clarification.md` §2.2 | T1 projection indexes during refresh; no spec10 contract extension in planning phase |
| **R-06** | Incomplete audit coverage — M1 producers only; M4 deferred | `spec.md` §2 limitations; `plan.md` E-06 | Reporting must not assume full-domain coverage; analytics must tolerate producer gap (`architecture-clarification.md` §5.3) |
| **R-07** | Reporting must not mutate `audit_logs` | `spec.md` §5 invariants; `architecture-clarification.md` §3.6 | AP-06 / CD-017 constraints in `plan.md` Inherited Constraints |
| **R-08** | Domain modules querying `audit_logs` directly | `spec.md` edge cases | Rejected — must use Reporting read layer |
| **R-09** | Projection treated as authoritative | `architecture-clarification.md` §3.6 | T0 contract wins for disputes |
| **R-10** | spec10 accidental mutation | `spec.md` §4; `tasks.md` P-033 | P-033 checklist task; spec10 mutation FORBIDDEN in `tasks.md` NEXT_STATE |

---

## 6. GOVERNANCE_DECISION_SEQUENCE

### Documented governance chain (fact)

`spec.md` §5 rule 2: **nomination → design approval → implementation authorization**

### Documented planning phase order (fact)

From `plan.md` E-08 table: P0 (complete) → P1 (complete) → P2 (not authorized) → P3 (not authorized) → P4+ (future).

### Suggested sequence (recommendation — not a decision)

`architecture-clarification.md` §7.1 labels the following as **"Suggested authorization sequence (governance hypothesis)"** and §7 as **"Preparation notes only — not wave design, not execution schedule"**:

1. spec11 Design Approval (after clarification)
2. Wave A — Reporting core read ports + T0 façade (after Design Approval)
3. Wave B — T1 projection store + refresh (after Wave A)
4. Wave C — Export / compliance packages (after Wave B)
5. Wave D — Operator Explorer UI (Wave A minimum)
6. Wave E — Analytics dashboards (after Wave B; separate E-08 authorization)

**Recommendation (derived from open-item dependencies in baseline text only):**

- UD-11-01 is referenced before permission propagation can be finalized (`plan.md` E-01).
- UD-11-02 is documented as "governance at authorization" and references security reporting audience.
- P-013 is linked to E-04 compliance KPI questions.
- DL-03-C is deferred to P2.

The baseline does **not** prescribe a mandatory order among UD-11-01, UD-11-02, and P-013. Reviewers must set order; this draft does not.

---

## 7. SUCCESSOR_READINESS

*Classifications use only evidence from authoritative inputs. "READY" requires explicit baseline support; absence of explicit support yields NOT_READY or CONDITIONALLY_READY with cited gaps.*

| Successor stage | Classification | Justification |
| --------------- | -------------- | ------------- |
| **Governance nomination review** | **CONDITIONALLY_READY** | `checklists/requirements.md` notes readiness for governance nomination (neither authorized); conflicts with `tasks.md` READINESS_OUTPUT (`no`); P-030 not complete; P-013 open |
| **P2 technical planning** | **NOT_READY** | `plan.md`: P2 requires design approval; `tasks.md` P-020–P-024 unauthorized and not started; checklist: "neither authorized" |
| **Design Approval package** | **CONDITIONALLY_READY** | `architecture-clarification.md` complete; `spec.md` §7 P1 complete; P-031 not started; UD-11-01, UD-11-02, P-013 open |
| **Implementation planning (P-032)** | **NOT_READY** | P-032 not started; waves TBD; P2 incomplete |
| **Execution authorization** | **NOT_READY** | `tasks.md` executable false; P4 HALT; `spec.md` and `architecture-clarification.md` exclude implementation authority |

---

## 8. NON_SCOPE

This governance nomination draft:

| Assertion | Confirmed |
| --------- | --------- |
| Authorizes nothing | **Yes** |
| Changes the planning baseline | **No** — single new draft file only |
| Implements nothing | **Yes** |
| Creates technical artifacts (`data-model.md`, `contracts/`, code) | **No** |
| Adds architecture beyond authoritative inputs | **No** |
| Modifies spec10 | **No** |
| Modifies `spec.md`, `plan.md`, `tasks.md`, `decision-log.md`, `architecture-clarification.md` | **No** |
| Converts recommendations into decisions | **No** |
| Grants execution authority | **No** |

Planning baseline exclusions remain as documented in `spec.md` §4 and `architecture-clarification.md` §6.

---

## 9. BASELINE_COMPLIANCE_NOTE

Content **intentionally excluded** because it is not supported by the six authoritative inputs:

| Excluded content | Reason |
| ---------------- | ------ |
| Catalog status labels (e.g. "Architecture Clarified" in `spec-catalog.md`) | Outside allowed input list |
| Governance owner assignments (e.g. Product, Identity, Compliance teams) | Not stated in authoritative inputs |
| Constitution / execution-policy / authority-model direct citations | Not in allowed input list — only constraints as restated in `spec.md` and `plan.md` |
| spec10-final-closure.md checkpoint details beyond what `spec.md` / `tasks.md` state | Outside allowed input list |
| speckit-analyze metrics, finding IDs, coverage percentages | Prior analysis artifact — not in allowed inputs |
| Nomination acceptance outcome or reviewer verdict | Not decided in baseline |
| Mandatory ordering resolving UD-11-01 vs UD-11-02 vs P-013 | Not prescribed in baseline |
| Wave labels A–E as authorized programs | `architecture-clarification.md` §7.1 explicitly marks these as hypothesis only |
| PHPStan / Pint / test gate requirements | Not in allowed inputs (referenced only in `architecture-clarification.md` §6 as excluded) |
| Identity `audit.read` role list beyond `architecture-clarification.md` §1 | Roles `Administrator`, `DormMgr`, `HRMgr` included where documented in clarification |
| Resolution of `tasks.md` READINESS_OUTPUT vs header inconsistency | Conflicting signals documented in §1 and §3; not resolved here |

---

**End of governance nomination draft. Review preparation only. No authority granted.**

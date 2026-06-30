# Governance Stack Architecture Review

**Report ID:** GSR-2026-06-24-001  
**Type:** Read-only architecture validation  
**Status:** FINAL  
**Date:** 1405/04/03 | 2026/06/24  
**Reviewer:** Architecture validation (automated read-only audit)

**Scope reviewed:**

| Category | Documents |
| --- | --- |
| Core governance model | `.specify/governance/_meta/authority-model.md` (v3.0.0) |
| Execution and enforcement | `.specify/governance/execution-policy.md` (v1.4.0), `.specify/governance/governance-enforcer.md` (v1.3.0) |
| Authority catalog | `.specify/docs/catalog-decisions.md` (v2.7.0) |
| Consistency stack | `.specify/governance/tests/governance-consistency-test-spec.md` (v1.0.0) |
| CI and drift control | `.specify/governance/ci/governance-guard-layer-spec.md`, `governance-drift-triage-spec.md`, `governance-drift-remediation-contract.md`, `governance-drift-prevention-contract.md`, `governance-drift-control-model.md`, `governance-drift-calibration-contract.md`, `governance-unified-verification.md` (all v1.0.0) |

**Method:** Cross-document invariant tracing, layer responsibility matrix, data-flow DAG review, DRIFT-01–10 applicability scan against current core file text. No files modified.

---

## 1. Executive Summary

The **governance drift-control stack** (prevention → detection → guard → triage → remediation, plus control model, calibration, and unified verification) is **internally consistent** as a specification architecture. None of the newly created stack documents introduce a fourth operational authority type, treat Case C as operational authority, or treat Nomination Record as authorization. Layer responsibility separation is explicitly documented and mutually reinforcing.

However, the **combined system** (core governance files + drift stack) is **not fully consistent**. Pre-existing cross-file contradictions in the **core four files** — particularly around Nomination Record / Next Spec Transition Nomination map references — would trigger **DRIFT-10** and related tests if the guard engine were executed today. The drift stack correctly *detects* this class of problem but does not *cause* it.

**Overall maturity: PARTIALLY READY**

| Dimension | Assessment |
| --- | --- |
| Drift stack internal consistency | Strong |
| Layer responsibility separation | Strong |
| Authority safety in stack specs | Strong |
| Core four-file cross-consistency | Weak (known contradictions) |
| Automated enforcement (CI runner) | Not implemented (spec-only) |
| Meta-layer documentation alignment | Good (minor diagram gaps) |

The drift-control stack does **not** introduce governance drift into itself. It surfaces drift that already exists in tiered governance documents.

---

## 2. Findings

### Authority Integrity

| ID | Severity | Finding |
| --- | --- | --- |
| **F-01** | **Major** | **`authority-model.md` (v3.0.0) does not define Nomination Record, Next Spec Transition Nomination, or Case C**, yet `execution-policy.md` v1.4.0 § Nomination and Execution Policy states: *"Vocabulary and classification are defined in `authority-model.md`."* Tiered files have adopted nomination vocabulary without a corresponding authority-model major bump or §4 lifecycle listing. This is an ontology gap, not a fourth authority type, but it violates the intended anchor role of the authority model. |
| **F-02** | **Critical** (core files) / **Informational** (stack) | **Nomination map reference contradiction (DRIFT-10).** `execution-policy.md` § Governance Transition Follow-Up requires creating a Nomination Record *"per the canonical map"* and states *"Authority ownership for Next Spec Transition Nomination is defined only in `## Governance Decision Authority Map`."* `catalog-decisions.md` v2.7.0 explicitly states that selecting the next specification **is not** in the map *"at this time"* and that Nomination Record **must not** appear as a map row. The consistency test spec already encodes this as DRIFT-10. **The drift stack is correct to flag this; it did not create it.** |
| **F-03** | **Informational** | **Three operational authority types** are consistently affirmed across all stack documents and core files (except where F-01/F-02 concern non-operational nomination, not a fourth operational type). Design Approval, Implementation Authorization, and Batch Execution Permission remain the sole operational types in the catalog map (three rows). |
| **F-04** | **Informational** | **No drift-stack document introduces a fourth operational authority type** or operational Case C / Nomination Record. UV-INV-01–03, TRIAGE-SAFE-01, REM-SAFE-01, PREV-SAFE-01, CAL-SAFE-02, and INT-08 all reinforce this. |

### Case C Integrity

| ID | Severity | Finding |
| --- | --- | --- |
| **F-05** | **Informational** | **Case C is consistently non-operational** across catalog v2.7.0 boundary notes, execution-policy v1.4.0, enforcer v1.3.0, and all stack specs. INV-02, INV-04, DRIFT-01, and UV-INV-02 align. |
| **F-06** | **Informational** | **HALT precedence `Case C → Case A → Case B`** is consistent between `execution-policy.md` (§ HALT Classification, detection procedure step 6) and `governance-enforcer.md` (step 4, step 7, HARD RULE). Enforcer step 7 blockquote reads correctly (`Case C → Case A → Case B`). INV-06 / DRIFT-04 / DRIFT-08 would **pass** on current enforcer text. |
| **F-07** | **Minor** | **Dual Case C output messages** in enforcer (classification message + policy HALT message) are documented in enforcer Output Expectations and match execution-policy mandated literal. Not a precedence violation; may increase automated literal-matching complexity for guard engine. |

### Nomination Record Boundary

| ID | Severity | Finding |
| --- | --- | --- |
| **F-08** | **Informational** | **Nomination Record is consistently evidence-only and non-authorizing** in catalog, execution-policy, enforcer, and all stack layers. Explicit exclusions from steps 1–4, authorization checks, and review-gate authority are present. INV-03, INV-05, INV-09, INV-10, DRIFT-02 affirmed. |
| **F-09** | **Minor** | **`authority-model.md` §4 "Artifacts outside the authorization record lifecycle"** lists governance state snapshots, transition state records, checkpoint summaries, and audit documents — but **not** Nomination Record. Tiered files treat nomination as outside lifecycle; anchor document is incomplete (related to F-01). |

### Layer Responsibility

| ID | Severity | Finding |
| --- | --- | --- |
| **F-10** | **Informational** | **Prevention** (`governance-drift-prevention-contract.md`) — design/review constraints only; explicitly does not detect, classify, or enforce. Matches objective. |
| **F-11** | **Informational** | **Detection** (`governance-consistency-test-spec.md`) — rule catalog only; does not execute or classify root cause. Matches objective. |
| **F-12** | **Informational** | **Guard** (`governance-guard-layer-spec.md`) — CI pass/fail and findings emission; defers classification to triage per control model INT-02. Matches objective. |
| **F-13** | **Informational** | **Triage** (`governance-drift-triage-spec.md`) — annotates only; TRIAGE-SAFE-01–08 prohibit enforcement override. Matches objective. |
| **F-14** | **Informational** | **Remediation** (`governance-drift-remediation-contract.md`) — repair discipline only; REM-FORBID-09 forbids editing detection/guard/triage specs as workaround. Matches objective. |
| **F-15** | **Informational** | **Calibration** (`governance-drift-calibration-contract.md`) — advisory only; CAL-NO-01–06 prohibit CI override. Matches objective. |
| **F-16** | **Informational** | **Unified Verification** (`governance-unified-verification.md`) — aggregation only; UV-INV-06 and §9 non-goals prohibit upstream override. Matches objective. |
| **F-17** | **Minor** | **Control model** (`governance-drift-control-model.md`) documents **five execution planes** only. Calibration and unified verification are correctly described in those documents as **outside** the execution DAG, but the control model diagram does not depict them as optional meta inputs. Not a responsibility violation — documentation completeness gap. |

### Data Flow Integrity

| ID | Severity | Finding |
| --- | --- | --- |
| **F-18** | **Informational** | **No circular dependency** in the documented DAG: Prevention → core files → Detection → Guard → Triage → Remediation → core files → re-Guard. Control model §6.1 forbids forbidden cycles; remediation cannot patch detection rules (REM-FORBID-09, INT-04). |
| **F-19** | **Informational** | **Triage does not mutate guard findings** (TRIAGE-SAFE-06; INT-03). Join on `finding_id` is read-only. |
| **F-20** | **Informational** | **Calibration cannot override FAIL** (CAL-NO-01, CAL-NO-05; unified verification §3.2). |
| **F-21** | **Informational** | **Unified verification does not redefine upstream semantics**; copies `guard_status`, derives sub-status from findings; precedence UNKNOWN_CONFLICT → FAIL → REQUIRES_REVIEW → PASS is internal to aggregation only. |
| **F-22** | **Minor** | **Unified verification `PASS` allows guard `WARN`** (MINOR-only findings) while requiring zero MAJOR/CRITICAL. Aligns with guard spec when HARD_GUARD_MODE=false and ALLOW_MINOR=true; reviewers should note dual PASS semantics (guard WARN vs unified PASS). |

### Stack Self-Drift Resistance

| ID | Severity | Finding |
| --- | --- | --- |
| **F-23** | **Informational** | **Drift categories are closed sets** in triage (5 types), remediation (4 categories), prevention (4 rule categories). No stack doc adds INV-* or DRIFT-* rules beyond referencing existing detection spec. |
| **F-24** | **Informational** | **Failure isolation principle** is stated consistently in control model §5 and unified verification §5; detection independent from interpretation and remediation. |
| **F-25** | **Major** (operational) | **No executable guard engine** exists in repository — all CI/guard/triage/unified outputs are specification-only. Stack cannot enforce drift resistance in pipeline until implemented. Does not indicate spec inconsistency. |

---

## 3. Objective Checklist Results

### 1. Authority Integrity Check

| Check | Result |
| --- | --- |
| Exactly three operational authority types | **PASS** (catalog map + all stack docs) |
| No layer introduces fourth authority type | **PASS** |
| Case C not treated as authority | **PASS** (stack + core, except F-02 map *reference* confusion — not operational grant) |
| Nomination not treated as authorization | **PASS** |

### 2. Layer Responsibility Check

| Layer | Scoped correctly? |
| --- | --- |
| Prevention | **PASS** |
| Detection | **PASS** |
| Guard | **PASS** |
| Triage | **PASS** |
| Remediation | **PASS** |
| Calibration | **PASS** |
| Unified Verification | **PASS** |

No layer performs another layer's primary responsibility per documented non-overlap guarantees.

### 3. Data Flow Integrity

| Check | Result |
| --- | --- |
| No circular dependency | **PASS** |
| Remediation does not modify detection rules | **PASS** (contractual) |
| Triage does not change guard results | **PASS** (contractual) |
| Calibration does not override FAIL | **PASS** (contractual) |
| Unified verification does not redefine upstream | **PASS** |

### 4. Case C Integrity Check

| Check | Result |
| --- | --- |
| Case C non-operational | **PASS** |
| Not in operational map | **PASS** |
| Precedence Case C → A → B | **PASS** (policy + enforcer) |
| Cross-file contradictions on Case C semantics | **PASS** (none on classification/precedence) |

### 5. Nomination Record Boundary Check

| Check | Result |
| --- | --- |
| Evidence-only | **PASS** |
| Non-authorizing | **PASS** |
| Outside authorization lifecycle | **PASS** in tiered files; **PARTIAL** in authority-model (F-01, F-09) |
| Cannot satisfy Design Approval / Implementation Authorization / Batch Execution Permission | **PASS** |

---

## 4. Blocking Issues

| ID | Blocks | Issue |
| --- | --- | --- |
| **B-01** | **PRODUCTION READY** | Core file **DRIFT-10** contradiction: execution-policy Follow-Up references nomination authority/artifact path via canonical map; catalog denies map entry and owner. Must be resolved in core governance (governance change proposal path), not by drift-stack edits. |
| **B-02** | **PRODUCTION READY** | **Authority-model v3.0.0** lacks nomination/Case C vocabulary while tiered documents reference it as normative source. Requires formal ontology update or corrected pointers in execution-policy. |
| **B-03** | **PRODUCTION READY** | **No implemented `governance-guard` CI job** — stack is specification-complete but not operationally deployed. |

**Blocking for READY (documentation-only maturity):** B-01 and B-02 prevent declaring the **full system** READY because core files fail the stack's own consistency tests on paper.

**Not blocking for stack architecture validity:** The drift-control specifications are fit for purpose as contracts; they correctly describe how to detect and respond to the contradictions in B-01/B-02.

---

## 5. Readiness Assessment

### Maturity matrix

| Level | Criteria | Verdict |
| --- | --- | --- |
| **NOT READY** | Layer overlap, stack introduces authority drift, circular deps | **Not met** — stack clears these bars |
| **PARTIALLY READY** | Specs consistent; core gaps or no automation | **Current state** |
| **READY** | Core files pass consistency tests; specs aligned | **Not yet** — F-01, F-02 |
| **PRODUCTION READY** | READY + automated guard in CI + sustained pass | **Not yet** — B-03 |

### Component scores

| Component | Maturity |
| --- | --- |
| Drift prevention contract | READY (as spec) |
| Consistency test spec | READY (as spec) |
| Guard layer spec | READY (as spec) |
| Triage spec | READY (as spec) |
| Remediation contract | READY (as spec) |
| Control model | READY (as spec) |
| Calibration contract | READY (as spec) |
| Unified verification | READY (as spec) |
| Core four governance files | PARTIALLY READY |
| End-to-end operational pipeline | NOT READY |

### Authority safety verdict

**SAFE at specification level** — the drift stack does not corrupt the three-type model or elevate Case C / Nomination to operational authority.

**AT RISK at runtime** — until B-01/B-02 are resolved, enforcer and execution-policy may guide operators to "canonical map" paths that do not exist, creating enforcement ambiguity unrelated to the drift stack.

### Drift resistance verdict

The stack architecture is **self-consistent and drift-resistant by design**. Prevention + detection + guard + triage + remediation + calibration + unified verification form a coherent closed loop **on paper**. Resistance is **not yet proven in CI** because the guard engine is not implemented (F-25).

---

## 6. Recommended Next Actions

*(Process recommendations only — no patches proposed per review charter.)*

1. **Governance change proposal** — Resolve B-01/B-02 as a single coordinated core-file change: either add Next Spec Transition Nomination to the canonical map with owner (major governance decision), or revise execution-policy Follow-Up to stop referencing the map for nomination and align with catalog § Governance Transition. This must follow formal governance review, not remediation-contract TEXTUAL_ALIGNMENT alone if semantics change.

2. **Authority-model major bump** — If nomination vocabulary is adopted, update `authority-model.md` §2/§4 with explicit non-operational classification per catalog boundary notes; re-verify all tiered documents against I6.

3. **Execute paper audit** — Run manual A1–D3 and DRIFT-01–10 checklist from `governance-consistency-test-spec.md` against current core files to produce a baseline finding set before any edits.

4. **Implement guard engine** — Build `governance-guard` job per `governance-guard-layer-spec.md` v1.0.0; emit `governance-drift-report.json`; wire triage and optional unified verification as downstream artifacts.

5. **Update control model diagram (optional doc hygiene)** — Add dashed meta-layer box for calibration + unified verification above the five-plane DAG without adding execution planes.

6. **Do not modify drift-stack contracts** to waive F-01/F-02 — REM-FORBID-09 and detection spec integrity prohibit patching tests to match broken core text.

---

## 7. Conclusion

The governance drift-control stack is **architecturally sound and internally consistent**. It successfully separates prevention, detection, CI gating, classification, remediation, advisory calibration, and final aggregation without introducing new operational authority types or violating Case C / Nomination boundaries **within the stack itself**.

The **combined governance system** remains **PARTIALLY READY** because core tiered documents contain cross-file contradictions (F-02 / DRIFT-10) and an incomplete authority-model anchor (F-01) that the stack is designed to detect — not fix. **No evidence was found that the newly created layers introduce governance drift into their own specifications.**

---

## Document Control

- **Report version:** 1.0.0
- **Owner:** DormSys Architecture Team (review artifact only)
- **Related:** `.specify/governance/ci/governance-drift-control-model.md`
- **Next review trigger:** After resolution of B-01/B-02 or authority-model bump; after first `governance-guard` CI implementation

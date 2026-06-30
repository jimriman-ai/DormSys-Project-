# Governance Drift Calibration Contract

**Version:** 1.0.0  
**Status:** ACTIVE  
**Last Updated:** 1405/04/03 | 2026/06/24  
**Type:** Semantic boundary definition (advisory only — not enforcement, not pipeline)

**Position relative to drift control stack:**

```
                    ┌─────────────────────────────────────┐
                    │  Calibration (this document)        │
                    │  advisory semantic lens only        │
                    │  human / architectural review       │
                    └─────────────────┬───────────────────┘
                                      │ informs understanding;
                                      │ does NOT execute
                                      ▼
governance-drift-control-model.md  →  Prevention → Detection → CI Guard → Triage → Remediation
```

Calibration is **not** a sixth execution plane. It does not appear in the control model lifecycle DAG as a processing step. It defines **what counts as evolution vs drift** for reviewers interpreting guard findings — without blocking, classifying, or remediating anything.

---

## 1. Purpose

**Governance Drift Calibration** is a **semantic boundary definition layer** that helps humans and architects distinguish:

| Category | Meaning |
| --- | --- |
| **Legitimate governance evolution** | Intentional, versioned, ontology-preserving changes to governance documents |
| **Structural drift violations** | Unintended or unversioned changes that break invariants, separation rules, or cross-file alignment |

Calibration exists to **reduce over-sensitivity** in drift interpretation: not every governance document edit is drift; not every CI finding implies authority corruption. Reviewers use calibration to ask *"Is this expected evolution or true drift?"* before choosing escalation vs remediation paths.

Calibration:

| Does | Does not |
| --- | --- |
| Define semantic boundaries between evolution and drift | Enforce HALT or block merges |
| Provide three classification axes for review discussion | Emit CI artifacts or modify `governance-drift-report.json` |
| Clarify when guard findings may reflect editorial vs structural issues | Override triage `drift_type` or `recommended_action` |
| Guide architectural review and governance change proposals | Replace remediation categories or prevention rules |
| Inform whether a change warrants formal governance review vs drift fix | Introduce new authority types or HALT cases |

---

## 2. Calibration Dimensions (Classification Axes)

Calibration assessment uses **exactly three axes**. Each axis is scored **only** for human/architectural review — not for automated pipeline gating.

### 2.1 INTENT AXIS

**Question:** Was the change **intentional governance evolution** or **accidental drift**?

| Signal | Lean evolution | Lean drift |
| --- | --- | --- |
| Change log / PR description | Explicit governance intent; references approved proposal | Silent edit; "fix typo" masking semantic change |
| Version bump | Document Control version incremented with change summary | Normative text changed without version note |
| Review path | Governance review or architecture sign-off | Drive-by edit without prevention checklist |
| Scope | Aligned with approved governance change proposal | Scope creep in tiered file without model update |

**Calibration note:** High accidental-drift intent does **not** waive CI. It informs **how urgently** to remediate and whether to revert vs align.

---

### 2.2 ONTOLOGY AXIS

**Question:** Does the change **preserve authority model structure**?

| Preserved (evolution-eligible) | Violated (drift) |
| --- | --- |
| Exactly three operational authority types | Fourth type, implicit authority, or map row added |
| Case C remains non-operational precondition only | Case C grants or satisfies operational authority |
| Nomination Record remains evidence-only | Nomination satisfies authorization checks |
| Catalog map remains three rows | Nomination/Case C in authority map table |
| `authority-model.md` §2 unchanged or formally version-bumped | Tiered file redefines ontology without model change |

**Calibration note:** Ontology axis aligns with triage `ONTOLOGY_DRIFT` and `CATALOG_DRIFT` **interpretively** — calibration does not assign those labels; triage does.

---

### 2.3 COMPATIBILITY AXIS

**Question:** Does the change remain **backward compatible** with the CI and enforcement stack?

| Compatible (evolution-eligible) | Incompatible (drift) |
| --- | --- |
| HALT messages unchanged or restored to policy literal | HALT semantics altered without execution-policy version bump |
| Precedence Case C → Case A → Case B consistent in policy + enforcer | Enforcer evaluates A/B before C; precedence string missing |
| Detection invariants INV-01–10 still satisfiable | DRIFT-01–10 conditions introduced |
| Enforcement reads same policy semantics | Enforcer introduces behavior not in policy |
| Editorial cross-refs updated with version sync | Cross-file contradiction on same mandated concept |

**Calibration note:** A CI FAIL always remains FAIL regardless of compatibility axis score. Compatibility axis explains whether failure is **expected transient** (e.g., mid-proposal edit) vs **structural defect**.

---

### 2.4 Combined calibration profile (advisory)

Reviewers may summarize a change as a **calibration profile** (informal, not a machine enum):

```
INTENT:     intentional | accidental | unclear
ONTOLOGY:   preserved   | at-risk    | violated
COMPAT:     compatible  | partial    | incompatible
```

**Evolution-eligible profile (typical):** intentional + preserved + compatible  
**True drift profile (typical):** accidental or unclear + at-risk/violated + partial/incompatible  

Profiles **do not** replace guard PASS/FAIL or triage annotations.

---

## 3. Acceptable Evolution Rules (NOT Drift)

The following change classes are **not** structural drift when executed correctly. They are **legitimate governance evolution** — even if CI temporarily fails until cross-files are aligned in the same change set.

### 3.1 Explicit versioned governance model updates

| Condition | Example |
| --- | --- |
| `authority-model.md` major/minor bump with change log | Formal adoption of Nomination vocabulary in §2 after governance review |
| `execution-policy.md` version bump with Document Control change line | Case C introduction in v1.4.0 with full HALT section |
| `catalog-decisions.md` version bump with boundary subsections only | v2.7.0 non-operational notes; **no** new map rows |
| Coordinated multi-file PR with synchronized version references | Policy v1.4.0 + enforcer v1.3.0 + catalog v2.7.0 in one governance batch |

**Requirement:** Evolution MUST be **declared** (version, change log, PR intent). Undeclared normative edits are **not** covered by this rule.

---

### 3.2 Invariant-preserving changes

Changes that **preserve** all of the following are evolution, not drift:

1. **Three operational authority types** — Design Approval, Implementation Authorization, Batch Execution Permission
2. **Non-operational separation** — Case C (precondition classification); Nomination Record (evidence-only)
3. **HALT precedence** — Case C → Case A → Case B (when HALT sections are touched)
4. **Map row count** — exactly three operational rows in catalog authority map

---

### 3.3 Structural clarifications without semantic change

| Evolution (not drift) | Would be drift if… |
| --- | --- |
| EXPLICITNESS_INJECTION per remediation contract — restates existing rule | New rule not entailed elsewhere |
| TEXTUAL_ALIGNMENT — harmonize wording across files | Wording changes HALT meaning |
| MISSING_REFERENCE_PATCH — add pointer to existing section | Pointer invents authority not in target |
| Catalog boundary subsection mirroring policy | Subsection adds map row or owner |
| Document Control date/version sync only | Normative body changed silently |
| Encoding fix for mandated literal (e.g., `→` arrow) | Arrow order or case labels changed |

---

### 3.4 Formal governance change proposal path

Changes that **require** ontology or map amendment (new decision class, new map row, new operational type) are **evolution via governance review** — not drift — **only when**:

- executed through approved governance change proposal,
- `authority-model.md` and catalog map updated in same approved batch,
- detection spec updated only if invariants genuinely change (separate authorized process).

Until approval completes, partial edits that imply new authority are **calibrated as drift**, not evolution.

---

## 4. True Drift Conditions

**Structural drift** exists **only** when one or more of the following hold. This list mirrors detection semantics (INV-*, DRIFT-*) **interpretively** — calibration does not redefine detection rules.

### 4.1 Implicit authority appears

- Artifact, status, or process described as satisfying Design Approval, Implementation Authorization, or Batch Execution Permission without map entry
- Nomination Record or Case C treated as authorization
- Review-gate, CI PASS, or triage annotation implied as operational authority
- Parallel ownership map outside `catalog-decisions.md`

**Calibration:** INTENT often accidental; ONTOLOGY violated; COMPAT incompatible.

---

### 4.2 HALT semantics altered without versioning

- Case A, B, or C triggers, messages, or precedence changed in enforcer or catalog without `execution-policy.md` version bump
- Mandated message paraphrased across files
- Case C evaluated after Case A/B in procedural text

**Calibration:** INTENT accidental or unclear; ONTOLOGY at-risk or preserved; COMPAT incompatible.

---

### 4.3 Cross-file contradictions emerge

- Same concept (nomination, precedence, three-type model) normatively stated two ways
- Policy references map entry that does not exist (undefined dependency)
- Catalog says non-operational; tiered file implies operational

**Calibration:** INTENT varies; ONTOLOGY at-risk; COMPAT partial or incompatible. Often remediated via TEXTUAL_ALIGNMENT or BOUNDARY_RESTORATION — not evolution.

---

### 4.4 Enforcement interpretation diverges from policy

- `governance-enforcer.md` mandates behavior absent from `execution-policy.md`
- Output Expectations omit Case C messages present in policy
- Validation order contradicts policy detection procedure

**Calibration:** INTENT often accidental; ONTOLOGY preserved if policy unchanged; COMPAT incompatible until enforcer aligned.

---

### 4.5 Drift vs evolution decision table

| Observation | Evolution | Drift |
| --- | --- | --- |
| v2.7.0 catalog boundary notes for Case C | ✓ if no map row | ✗ if map row added |
| execution-policy v1.4.0 adds Case C section | ✓ with version bump | ✗ if enforcer not updated in same batch |
| Fix corrupted `→` in enforcer precedence line | ✓ TEXTUAL_ALIGNMENT | ✗ if order changed |
| Add Nomination to authority-model §2 | ✓ after formal review + bump | ✗ if tiered-only adoption |
| "Clarify" that nomination satisfies step 3 | ✗ never evolution | ✓ DRIFT-02 class |

---

## 5. Non-Enforcement Guarantee (Normative)

| ID | Guarantee |
| --- | --- |
| **CAL-NO-01** | Calibration **does NOT block CI**. Guard PASS/FAIL is unchanged by calibration assessment. |
| **CAL-NO-02** | Calibration **does NOT classify failures**. `drift_type`, `severity`, and `failure_type` remain guard + triage authority. |
| **CAL-NO-03** | Calibration **does NOT override triage or remediation**. `recommended_action` and remediation categories are not replaced by calibration profiles. |
| **CAL-NO-04** | Calibration **is advisory only**. No artifact, label, or score from calibration may be wired into `HARD_GUARD_MODE` without a separate future spec. |
| **CAL-NO-05** | Calibration **does NOT waive** CRITICAL findings because a change was "intentional" — intentional ontology violation still requires governance review or revert. |
| **CAL-NO-06** | Calibration **does NOT introduce** enforcement mechanisms, new invariants, or pipeline steps. |

**Reviewer discipline:** Use calibration to **interpret** guard output and **prioritize** human attention — not to **disable** detection.

---

## 6. Integration Boundary

### 6.1 Conceptual position (above stack, outside pipeline)

Calibration sits **conceptually above** all drift control planes defined in `governance-drift-control-model.md`:

```
         [ Calibration — semantic lens for reviewers ]
                            │
    ┌───────────────────────┼───────────────────────┐
    │                       │                       │
 Prevention            Detection              CI Guard
                            │                       │
                         Triage               Remediation
```

- **Not** in the Prevention → Detection → CI Guard → Triage → Remediation DAG
- **Not** in data flow (`governance-drift-report.json` / `governance-drift-triage.json`)
- **Not** part of execution pipeline (CI jobs, enforcer runtime, Speckit implement gates)

### 6.2 When calibration is used

| Context | Use calibration? |
| --- | --- |
| Governance PR review before merge | **Yes** — evolution vs drift framing |
| Architecture review of governance change proposal | **Yes** — ontology/compatibility pre-check |
| Interpreting ambiguous CI findings with LOW triage confidence | **Yes** — reduce over-reaction |
| Automated CI pass/fail decision | **No** |
| Triage engine classification | **No** |
| Remediation category selection | **No** — use remediation contract; calibration may inform human choice only |
| Runtime HALT by enforcer | **No** |

### 6.3 Relationship to existing contracts (read-only)

| Document | Relationship to calibration |
| --- | --- |
| `governance-drift-prevention-contract.md` | Prevention implements proactive rules; calibration explains *why* those rules exist |
| `governance-consistency-test-spec.md` | Detection defines drift truth conditions; calibration explains *non-drift evolution* exceptions |
| `governance-guard-layer-spec.md` | Guard remains authoritative for FAIL; calibration does not soften thresholds |
| `governance-drift-triage-spec.md` | Triage classifies findings; calibration may inform human review of LOW confidence |
| `governance-drift-remediation-contract.md` | Remediation fixes drift; calibration helps decide evolution path vs remediation |
| `governance-drift-control-model.md` | Control model maps planes; calibration is meta-documentation outside §2 lifecycle |

**No contract is modified by this document.**

### 6.4 Over-sensitivity mitigation (goal)

Calibration addresses these review anti-patterns:

| Anti-pattern | Calibration response |
| --- | --- |
| Treat every version bump as drift | Check INTENT + declared evolution (§3.1) |
| Treat every cross-file edit as authority corruption | Check ONTOLOGY axis — clarifications may be evolution (§3.3) |
| Treat CI FAIL as mandatory semantic rollback | Check COMPAT — may need coordinated fix in same PR, not revert |
| Treat intentional governance program as "drift by design" | Formal proposal path (§3.4) is evolution, not drift |
| Ignore CI FAIL because "we meant to change it" | CAL-NO-05 — intent does not waive FAIL |

---

## 7. Review Workflow (Advisory)

Optional human workflow — **not** pipeline-required:

1. **Load change** — diff on four core governance files
2. **Score axes** — INTENT, ONTOLOGY, COMPAT (§2)
3. **Apply evolution rules** — if §3 satisfied, frame as evolution (may still need CI alignment)
4. **Check true drift** — if §4 triggered, frame as drift → remediation contract
5. **Record** — one-line calibration note in PR (optional):

   ```markdown
   **Calibration:** INTENT=intentional, ONTOLOGY=preserved, COMPAT=partial
   (editorial cross-file sync; not authority drift — enforcer literal alignment pending)
   ```

6. **Never** use calibration note to skip `governance-guard` re-run

---

## 8. Safety Constraints

| ID | Rule |
| --- | --- |
| **CAL-SAFE-01** | Calibration MUST NOT add governance layers to the control model execution DAG. |
| **CAL-SAFE-02** | Calibration MUST NOT introduce authority types, HALT cases, or map rows. |
| **CAL-SAFE-03** | Calibration MUST NOT modify prevention, detection, guard, triage, remediation, or control model documents. |
| **CAL-SAFE-04** | Evolution eligibility under §3 does NOT auto-clear CI; alignment + guard PASS still required. |
| **CAL-SAFE-05** | True drift under §4 MUST NOT be reclassified as evolution without meeting §3 requirements. |

---

## 9. Explicit Non-Goals

This contract **does NOT**:

- introduce new governance rules, INV-*, DRIFT-*, or detection tests
- add a sixth processing plane to `governance-drift-control-model.md`
- change CI logic, HARD_GUARD_MODE, or guard severity thresholds
- change triage `drift_type` enums or `recommended_action` mapping
- change remediation categories or forbidden actions
- change prevention PREV-* rules or runtime enforcement semantics
- provide executable tooling or machine-readable pipeline artifacts
- assign authority ownership or resolve catalog map gaps

---

## 10. Document Control

- **Version:** 1.0.0
- **Status:** ACTIVE
- **Owner:** DormSys Architecture Team (document maintenance only)
- **Related (read-only):**
  - `.specify/governance/ci/governance-drift-control-model.md`
  - `.specify/governance/ci/governance-drift-prevention-contract.md`
  - `.specify/governance/tests/governance-consistency-test-spec.md`
  - `.specify/governance/ci/governance-guard-layer-spec.md`
  - `.specify/governance/ci/governance-drift-triage-spec.md`
  - `.specify/governance/ci/governance-drift-remediation-contract.md`

This document defines calibration semantics only. It does not modify system behavior, pipeline execution, or any existing contract.

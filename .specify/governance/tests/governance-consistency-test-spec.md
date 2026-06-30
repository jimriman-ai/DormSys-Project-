# Governance Consistency Test Spec (Cross-File Governance Integrity Validation)

**Version:** 1.0.1  
**Status:** ACTIVE  
**Last Updated:** 1405/04/03 | 2026/06/24  
**Type:** Static governance validation spec (documentation only — not executable enforcement)

**System of record (baseline versions):**

| File | Baseline version |
| --- | --- |
| `.specify/governance/_meta/authority-model.md` | 4.0.0 |
| `.specify/governance/execution-policy.md` | 1.4.1 |
| `.specify/governance/governance-enforcer.md` | 1.3.0 |
| `.specify/docs/catalog-decisions.md` | 2.7.0 |

---

## 1. Purpose

This document defines a **static consistency test specification** for the DormSys governance framework. It ensures **no ontology drift** and **no enforcement semantic drift** across the four core governance files listed above.

This spec is used for:

- manual governance review before merging governance document changes,
- agent-assisted cross-file audits (read-only),
- regression checks after version bumps to any core governance file.

This spec **does not** execute tests, modify enforcement behavior, or grant authority. Failures reported under this spec indicate **governance defects** requiring document correction — not precedence-based resolution.

### Scope of validation

Validate consistency between:

- `.specify/governance/_meta/authority-model.md` — vocabulary, authority types, lifecycle concepts
- `.specify/governance/execution-policy.md` — HALT classification, nomination policy, execution preconditions
- `.specify/governance/governance-enforcer.md` — procedural enforcement alignment
- `.specify/docs/catalog-decisions.md` — canonical authority ownership map and non-operational boundary notes

### Out of scope

- Runtime code, CI pipelines, and automated scripts (may be added later; not part of this spec)
- Feature specifications (`spec.md`, `plan.md`, `tasks.md`)
- Handoff instance records (except as examples in pass/fail narratives)
- Constitution and architectural boundary decisions (CD-*), except where they conflict with governance ontology

---

## 2. System Invariants (MUST ALWAYS HOLD)

The following invariants are **non-negotiable**. Any governance document change that violates an invariant is a **Critical** defect until corrected.

| ID | Invariant |
| --- | --- |
| **INV-01** | Exactly **three** operational authority types exist: **Design Approval**, **Implementation Authorization**, **Batch Execution Permission**. No fourth type, subtype, or alias. |
| **INV-02** | **Case C** is **non-operational** and **precondition-only**. It classifies governance-precondition failure; it does not grant operational permission. |
| **INV-03** | **Nomination Record** is **evidence-only** and **non-authorizing**. It records program-level spec selection; it does not satisfy authorization checks. |
| **INV-04** | **Case C** is **not** part of `## Governance Decision Authority Map` in `catalog-decisions.md` as an authority type, decision class row, or owner entry. |
| **INV-05** | **Nomination Record** is **not** part of the authorization record lifecycle defined in `authority-model.md` §4–§5. |
| **INV-06** | **HALT precedence** is **mandatory** and **identical** across `execution-policy.md` and `governance-enforcer.md`: **Case C → Case A → Case B**. No alternative ordering. |
| **INV-07** | `catalog-decisions.md` § `## Governance Decision Authority Map` table contains **exactly three** operational authority rows (Design Approval, Implementation Authorization, Batch Execution Permission). |
| **INV-08** | Authority ownership is resolved **only** from `catalog-decisions.md` § `## Governance Decision Authority Map`. Tiered documents pointer-reference; they do not redefine ownership. |
| **INV-09** | A Nomination Record **cannot** substitute for Design Approval, Implementation Authorization, or Batch Execution Permission in pre-execution or enforcer validation. |
| **INV-10** | Presence of a valid Nomination Record **does not** clear HALT caused by missing operational authority. |

---

## 3. Cross-File Consistency Tests

Each test includes: **ID**, **Assertion**, **Primary source**, **Cross-check files**, **Pass criteria**, **Fail codes**.

### Test Group A — Authority Model Integrity

#### A1 — No authority beyond three operational types

| Field | Value |
| --- | --- |
| **Assertion** | `authority-model.md` §2 must define exactly three operational authority types and must not introduce a fourth operational authority type, subtype, or alias. |
| **Primary source** | `authority-model.md` §2 — Operational Authority Types |
| **Cross-check** | `catalog-decisions.md` § Operational authority map scope (strict); `execution-policy.md` § Authority Ownership; `governance-enforcer.md` § HARD RULE Clarification |
| **Pass** | All four files affirm exactly three operational types; no file adds a fourth operational type. |
| **Fail** | `AUTHORITY_DRIFT`, `CATALOG_MAP_VIOLATION` |

#### A2 — Nomination Record not operational authority

| Field | Value |
| --- | --- |
| **Assertion** | Nomination Record must NOT be classified as an operational authority type or Authorization Record in `authority-model.md`. |
| **Primary source** | `authority-model.md` §2, §4 |
| **Cross-check** | `execution-policy.md` § Nomination and Execution Policy; `catalog-decisions.md` § Nomination Record boundary; `governance-enforcer.md` § Enforcement Constraints |
| **Pass** | Nomination Record is described as evidence-only / outside authorization lifecycle; not listed among operational authority types. |
| **Fail** | `NOMINATION_AUTHORITY_LEAKAGE`, `AUTHORITY_DRIFT` |

#### A3 — Case C not an authority type

| Field | Value |
| --- | --- |
| **Assertion** | Case C must NOT be defined as an operational authority type, Authorization Record, or map-backed authority class in `authority-model.md`. |
| **Primary source** | `authority-model.md` §2 |
| **Cross-check** | `catalog-decisions.md` § Case C — governance precondition classification; `execution-policy.md` § Case C |
| **Pass** | Case C appears only in tiered policy/enforcement/catalog boundary text as non-operational precondition classification. |
| **Fail** | `CASE_C_MISCLASSIFICATION`, `AUTHORITY_DRIFT` |

---

### Test Group B — Execution Policy Alignment

#### B1 — Case C as non-operational precondition only

| Field | Value |
| --- | --- |
| **Assertion** | `execution-policy.md` must define Case C solely as governance-precondition failure (transition nomination), not as authorization or transition authorization. |
| **Primary source** | `execution-policy.md` v1.4.1 § Case C — Governance precondition failure; § Nomination and Execution Policy |
| **Cross-check** | `catalog-decisions.md` § Case C; `governance-enforcer.md` step 4 |
| **Pass** | Case C HALT message is `Governance precondition failure: transition nomination record required.`; Case C explicitly does not grant Design Approval, Implementation Authorization, or Batch Execution Permission. |
| **Fail** | `CASE_C_MISCLASSIFICATION` |

#### B2 — Nomination Record as precondition only

| Field | Value |
| --- | --- |
| **Assertion** | Nomination Record may be required as a **governance precondition** before next-spec flows; it must NOT satisfy Pre-Execution Requirements steps 1–4 or substitute for operational authority. |
| **Primary source** | `execution-policy.md` § Nomination and Execution Policy — Governance precondition; § Pre-Execution Requirements |
| **Cross-check** | `governance-enforcer.md` step 4 — Nomination Record rule; `catalog-decisions.md` § Nomination Record boundary |
| **Pass** | Policy states nomination cannot satisfy steps 1–4; operational checks follow Case C evaluation. |
| **Fail** | `NOMINATION_AUTHORITY_LEAKAGE` |

#### B3 — HALT precedence explicit and ordered

| Field | Value |
| --- | --- |
| **Assertion** | `execution-policy.md` must state detection procedure precedence: **Case C → Case A → Case B**. |
| **Primary source** | `execution-policy.md` § HALT Classification — Detection procedure step 6 |
| **Cross-check** | `governance-enforcer.md` step 7; `catalog-decisions.md` § Case C (informational cross-reference) |
| **Pass** | Identical precedence string and semantics in execution-policy and enforcer. |
| **Fail** | `PRECEDENCE_MISMATCH` |

---

### Test Group C — Enforcer Compliance

#### C1 — Case C explicitly classified

| Field | Value |
| --- | --- |
| **Assertion** | `governance-enforcer.md` must explicitly require Case C evaluation before operational authority checks and define Case C HALT behavior. |
| **Primary source** | `governance-enforcer.md` v1.3.0 § Validation Order step 4; § HARD RULE |
| **Cross-check** | `execution-policy.md` § Case C |
| **Pass** | Enforcer names Case C, requires evaluate-first ordering, and mandates immediate HALT without evaluating Case A/B when Case C applies. |
| **Fail** | `CASE_C_MISCLASSIFICATION`, `PRECEDENCE_MISMATCH` |

#### C2 — Nomination Record excluded from authorization validation

| Field | Value |
| --- | --- |
| **Assertion** | Enforcer must NOT treat Nomination Record as satisfying Design Approval, Implementation Authorization, or Batch Execution Permission. |
| **Primary source** | `governance-enforcer.md` step 4 — Nomination Record rule; § HARD RULE; § Enforcement Constraints |
| **Cross-check** | `execution-policy.md` § Authority Ownership; `catalog-decisions.md` § Nomination Record boundary |
| **Pass** | Nomination Records listed among artifacts that cannot satisfy authorization checks; HARD RULE prohibits using Nomination Record in place of canonical authorization artifact. |
| **Fail** | `NOMINATION_AUTHORITY_LEAKAGE` |

#### C3 — Case C output classification required

| Field | Value |
| --- | --- |
| **Assertion** | When Case C applies, enforcer output must include governance-precondition failure (Case C) defect classification and required messages. |
| **Primary source** | `governance-enforcer.md` § Output Expectations — Case C reporting |
| **Cross-check** | `execution-policy.md` § Case C HALT message |
| **Pass** | Output Expectations require defect type `governance-precondition failure (Case C)`, classification message `Case C — Governance precondition failure: transition nomination record required.`, and HALT message `Governance precondition failure: transition nomination record required.` |
| **Fail** | `CASE_C_MISCLASSIFICATION` |

---

### Test Group D — Catalog Boundary Integrity

#### D1 — Exactly three operational authority map rows

| Field | Value |
| --- | --- |
| **Assertion** | `catalog-decisions.md` § Governance Decision Authority Map table must contain exactly three operational authority rows. |
| **Primary source** | `catalog-decisions.md` v2.7.0 — authority map table |
| **Cross-check** | `catalog-decisions.md` § Operational authority map scope (strict); `authority-model.md` §2 |
| **Pass** | Table rows: Design Approval, Implementation Authorization, Batch Execution Permission only. |
| **Fail** | `CATALOG_MAP_VIOLATION`, `AUTHORITY_DRIFT` |

#### D2 — Case C excluded from authority map

| Field | Value |
| --- | --- |
| **Assertion** | `catalog-decisions.md` must explicitly state Case C does not appear in the authority map and is not an operational governance decision class. |
| **Primary source** | `catalog-decisions.md` § Case C — governance precondition classification (non-operational) |
| **Cross-check** | `execution-policy.md` § Case C; `governance-enforcer.md` § HARD RULE Clarification |
| **Pass** | Case C documented as precondition classification only; no map row or owner for Case C. |
| **Fail** | `CASE_C_MISCLASSIFICATION`, `CATALOG_MAP_VIOLATION` |

#### D3 — Nomination Record not a map node

| Field | Value |
| --- | --- |
| **Assertion** | Nomination Record must not appear as a decision node, authority row, or owner entry in `## Governance Decision Authority Map`. |
| **Primary source** | `catalog-decisions.md` § Nomination Record boundary (non-operational dependency) |
| **Cross-check** | Authority map table; `execution-policy.md` § Nomination and Execution Policy |
| **Pass** | Nomination Record documented as non-operational dependency; may be referenced as prerequisite only; zero map rows for nomination. |
| **Fail** | `CATALOG_MAP_VIOLATION`, `NOMINATION_AUTHORITY_LEAKAGE` |

---

## 4. Drift Detection Rules

Drift is detected when **any** of the following conditions hold across the four core files (descriptive logic — not executable):

| Rule ID | Drift condition | Typical fail codes |
| --- | --- | --- |
| **DRIFT-01** | Any file implies or states that **Case C is operational authority** or grants Design Approval, Implementation Authorization, or Batch Execution Permission | `CASE_C_MISCLASSIFICATION`, `AUTHORITY_DRIFT` |
| **DRIFT-02** | Any file allows a **Nomination Record to satisfy authorization** or pre-execution operational checks (steps 1–4) | `NOMINATION_AUTHORITY_LEAKAGE` |
| **DRIFT-03** | Any file introduces a **fourth operational authority class** (explicit table row, implicit “authorization” wording for nomination/Case C, or new map owner for non-operational artifact) | `AUTHORITY_DRIFT`, `CATALOG_MAP_VIOLATION` |
| **DRIFT-04** | **HALT precedence differs** between `execution-policy.md` and `governance-enforcer.md` (order, case labels, or “evaluate first” semantics for Case C) | `PRECEDENCE_MISMATCH` |
| **DRIFT-05** | **Catalog map includes non-operational classes** as authority rows (Case C, Nomination Record, planning authorization without separate governance change, etc.) | `CATALOG_MAP_VIOLATION` |
| **DRIFT-06** | **Parallel ownership maps** appear outside `catalog-decisions.md` § `## Governance Decision Authority Map` | `AUTHORITY_DRIFT` |
| **DRIFT-07** | **Case C HALT message** differs between execution-policy and enforcer mandated output text | `PRECEDENCE_MISMATCH`, `CASE_C_MISCLASSIFICATION` |
| **DRIFT-08** | Enforcer evaluates **Case A or Case B before Case C** when next-spec nomination precondition is required and unmet | `PRECEDENCE_MISMATCH` |
| **DRIFT-09** | `authority-model.md` lists Nomination Record or Case C as **operational authority types** in §2 | `AUTHORITY_DRIFT`, `CASE_C_MISCLASSIFICATION` |
| **DRIFT-10** | Governance Transition follow-up or catalog text **contradicts** catalog v2.7.0 boundary (e.g., nomination as map authority row) without version bump and cross-file reconciliation | `CATALOG_MAP_VIOLATION`, `AUTHORITY_DRIFT` |

### Manual audit procedure (recommended)

1. Record baseline versions from Document Control sections of all four files.
2. Run Test Groups A–D (pass/fail per test).
3. Scan for Drift Rules DRIFT-01 through DRIFT-10 using keyword and structural review (map row count, “operational authority type”, “Case C”, “Nomination Record”, precedence strings).
4. Complete `review-checklist.md` § Authority Drift Prevention if any core file changed.
5. Answer final control question: *If `catalog-decisions.md` disappeared, could any other document determine who owns authority?* — must be **NO**.

---

## 5. Failure Classification

### AUTHORITY_DRIFT

| Field | Value |
| --- | --- |
| **Description** | Operational authority ontology or ownership map diverges from the three-type model or single canonical map in `catalog-decisions.md`. |
| **Affected files** | Any of: `authority-model.md`, `catalog-decisions.md`, `execution-policy.md`, `governance-enforcer.md` |
| **Severity** | **Critical** |

---

### CASE_C_MISCLASSIFICATION

| Field | Value |
| --- | --- |
| **Description** | Case C treated as operational authority, authorization defect conflation, missing enforcer classification, or incorrect HALT/output messaging for Case C. |
| **Affected files** | Typically `execution-policy.md`, `governance-enforcer.md`; may include `catalog-decisions.md`, `authority-model.md` |
| **Severity** | **Critical** if Case C grants authority or replaces map; **Major** if classification/output incomplete |

---

### NOMINATION_AUTHORITY_LEAKAGE

| Field | Value |
| --- | --- |
| **Description** | Nomination Record used to satisfy Design Approval, Implementation Authorization, Batch Execution Permission, or authorization-record lifecycle checks. |
| **Affected files** | Typically `execution-policy.md`, `governance-enforcer.md`; may include `catalog-decisions.md`, `authority-model.md` |
| **Severity** | **Critical** |

---

### PRECEDENCE_MISMATCH

| Field | Value |
| --- | --- |
| **Description** | HALT classification order or Case C evaluate-first semantics differ between `execution-policy.md` and `governance-enforcer.md`, or Case A/B evaluated when Case C should win. |
| **Affected files** | `execution-policy.md`, `governance-enforcer.md` |
| **Severity** | **Major** (Critical if precedence inversion allows execution without required nomination or operational authority) |

---

### CATALOG_MAP_VIOLATION

| Field | Value |
| --- | --- |
| **Description** | `## Governance Decision Authority Map` has wrong row count, includes non-operational entries as authority rows, or contradicts § Operational authority map scope (strict). |
| **Affected files** | `catalog-decisions.md`; cross-impact in files that pointer-reference the map |
| **Severity** | **Critical** |

---

## 6. Pass Criteria (Release Gate)

A governance change set **passes** this consistency spec when:

- All tests **A1–A3**, **B1–B3**, **C1–C3**, **D1–D3** pass.
- No **DRIFT-01** through **DRIFT-10** conditions are observed.
- No **Critical** severity failures remain open.
- `review-checklist.md` § Authority Drift Prevention is satisfied if any core governance file was modified.

---

## 7. Document Control

- **Version:** 1.0.1
- **Status:** ACTIVE
- **Last Updated:** 1405/04/03 | 2026/06/24
- **Owner:** DormSys Architecture Team (document maintenance only — does not grant operational authority)
- **Change:** Baseline version sync — authority-model 4.0.0, execution-policy 1.4.1, governance-enforcer 1.3.0; editorial only
- **Supersedes:** 1.0.0
- **Related:** `.specify/governance/review-checklist.md` § Authority Drift Prevention

This file is a **validation spec only**. It does not participate in governance precedence resolution and does not modify enforcement behavior.

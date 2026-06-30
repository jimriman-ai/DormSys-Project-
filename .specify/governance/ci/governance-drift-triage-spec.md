# Governance Drift Triage Spec (Pre-Enforcement Classification Layer)

**Version:** 1.0.0  
**Status:** ACTIVE  
**Last Updated:** 1405/04/03 | 2026/06/24  
**Type:** Classification architecture specification (not enforcement)

**Position in stack:**

```
governance-consistency-test-spec.md   (rule catalog: INV-*, A–D, DRIFT-*)
            ↓ raw drift signals
governance-drift-triage-spec.md       (this document — classification only)
            ↓ annotated drift
governance-guard-layer-spec.md        (CI pass/fail — unchanged semantics)
```

---

## 1. Triage Purpose

The **Governance Drift Triage Layer** is a lightweight classification plane that sits between drift **detection** and CI **enforcement**.

It exists to:

- reduce **false-positive pipeline blocks** caused by structural ambiguity in raw drift signals,
- prevent **ontology-level misclassification** (e.g., treating a wording duplicate as authority drift),
- annotate findings with **drift type**, **confidence**, and **recommended action** for human and CI consumption.

The triage layer:

| Does | Does not |
| --- | --- |
| Classify raw drift signals into exactly five drift categories | Decide CI pass or fail |
| Assign confidence (HIGH / MEDIUM / LOW) | Enforce governance rules |
| Suggest recommended_action for the guard layer | Modify authority semantics |
| Preserve original rule_id and failure_type from detection | Override HARD_GUARD_MODE |
| Emit structured triage annotations | Introduce new governance concepts |

**Normative separation:** Triage **interprets** signals; the **Governance Guard Layer** **decides** pipeline outcome per `governance-guard-layer-spec.md` §2.6 and §3.

---

## 2. Drift Categories (STRICT)

Every drift signal MUST be classified into **exactly one** of the following types. No additional categories, subtypes, or aliases.

### 2.1 ONTOLOGY_DRIFT

**Definition:** Authority model inconsistency; introduction of implicit or hidden authority types; violation of non-operational vs operational separation at the conceptual level.

**Typical sources:**

| rule_id / failure_type | Examples |
| --- | --- |
| INV-01, INV-05, DRIFT-03, DRIFT-09 | Fourth operational authority type; Nomination Record listed as operational in `authority-model.md` |
| `AUTHORITY_DRIFT` | `authority-model.md` §2 contradicts three-type model |
| A1, A2, A3 | Authority model integrity test failures |

**Primary file:** `.specify/governance/_meta/authority-model.md`  
**Cross-check:** All four core files for three-type affirmation

---

### 2.2 POLICY_DRIFT

**Definition:** `execution-policy.md` mismatch with authority model; HALT logic inconsistency (Case A / B / C ordering or semantics); governance-precondition misalignment.

**Typical sources:**

| rule_id / failure_type | Examples |
| --- | --- |
| INV-02, INV-06, INV-10, DRIFT-01, DRIFT-07 | Case C grants authority; missing Case C section; wrong HALT message |
| `CASE_C_MISCLASSIFICATION` | Case C defined as authorization in policy only |
| B1, B2, B3 | Execution policy alignment test failures |
| DRIFT-04 (policy-side) | Precedence missing in `execution-policy.md` only |

**Primary file:** `.specify/governance/execution-policy.md`  
**Cross-check:** `authority-model.md`, `catalog-decisions.md` boundary notes

---

### 2.3 ENFORCER_DRIFT

**Definition:** `governance-enforcer.md` does not reflect execution-policy semantics; missing Case C handling; precedence mismatch; validation-order inconsistencies.

**Typical sources:**

| rule_id / failure_type | Examples |
| --- | --- |
| DRIFT-04, DRIFT-08 | Enforcer evaluates Case A/B before Case C |
| `PRECEDENCE_MISMATCH` (enforcer-primary) | Step 7 precedence block missing or wrong |
| C1, C2, C3 | Enforcer compliance test failures |
| DRIFT-07 (enforcer-side) | Output Expectations missing Case C messages |

**Primary file:** `.specify/governance/governance-enforcer.md`  
**Cross-check:** `execution-policy.md` mandated literals

---

### 2.4 CATALOG_DRIFT

**Definition:** `catalog-decisions.md` map violations; nomination leakage into operational map; incorrect inclusion or exclusion of decision classes in the authority map.

**Typical sources:**

| rule_id / failure_type | Examples |
| --- | --- |
| INV-04, INV-07, INV-08, DRIFT-05, DRIFT-06, DRIFT-10 | Map row count ≠ 3; Case C or Nomination as map row |
| `CATALOG_MAP_VIOLATION` | Operational authority map scope contradicted |
| D1, D2, D3 | Catalog boundary integrity test failures |

**Primary file:** `.specify/docs/catalog-decisions.md`  
**Cross-check:** Authority map table structure only

---

### 2.5 CROSS_FILE_CONTRADICTION

**Definition:** Conflict between two or more governance documents; inconsistent definitions of the same concept across files where no single file is solely at fault.

**Typical sources:**

| rule_id / failure_type | Examples |
| --- | --- |
| DRIFT-04 | Precedence literal in policy but absent in enforcer (both files implicated) |
| DRIFT-07 | Case C HALT message differs between policy and enforcer |
| DRIFT-10 | Catalog boundary text contradicts execution-policy follow-up without version reconciliation |
| Multiple failure_types on same concept | e.g., `PRECEDENCE_MISMATCH` + `CASE_C_MISCLASSIFICATION` on same HALT chain |

**Primary file:** Two or more of the four core files  
**Assignment rule:** Use CROSS_FILE_CONTRADICTION when **≥2 files** contain conflicting normative statements for the same invariant; otherwise use the single-file category above.

---

### 2.6 Category assignment precedence (deterministic)

When a signal could match multiple categories, apply **first match**:

1. **CATALOG_DRIFT** — if primary violation is map table structure or catalog § boundary subsection
2. **ONTOLOGY_DRIFT** — if primary violation is `authority-model.md` §2 or three-type invariant
3. **POLICY_DRIFT** — if only `execution-policy.md` normative text is wrong
4. **ENFORCER_DRIFT** — if only `governance-enforcer.md` normative text is wrong
5. **CROSS_FILE_CONTRADICTION** — if ≥2 files conflict on same mandated literal or invariant

---

## 3. Mapping Reference (DRIFT-* → drift_type)

| DRIFT ID | Primary drift_type | Secondary (if split) |
| --- | --- | --- |
| DRIFT-01 | POLICY_DRIFT or ONTOLOGY_DRIFT | CROSS_FILE_CONTRADICTION if policy + model conflict |
| DRIFT-02 | POLICY_DRIFT or ENFORCER_DRIFT | CROSS_FILE_CONTRADICTION if both |
| DRIFT-03 | ONTOLOGY_DRIFT | CATALOG_DRIFT if map row added |
| DRIFT-04 | CROSS_FILE_CONTRADICTION | ENFORCER_DRIFT if enforcer-only gap |
| DRIFT-05 | CATALOG_DRIFT | — |
| DRIFT-06 | CATALOG_DRIFT | ONTOLOGY_DRIFT if parallel map in tiered file |
| DRIFT-07 | CROSS_FILE_CONTRADICTION | POLICY_DRIFT or ENFORCER_DRIFT if one-sided |
| DRIFT-08 | ENFORCER_DRIFT | — |
| DRIFT-09 | ONTOLOGY_DRIFT | — |
| DRIFT-10 | CATALOG_DRIFT | CROSS_FILE_CONTRADICTION if policy also contradicts |

---

## 4. Triage Output

### 4.1 Input

| Field | Source | Required |
| --- | --- | --- |
| Raw drift signals | Governance Drift Detection Engine per `governance-guard-layer-spec.md` §4 | Yes |
| `finding_id`, `rule_id`, `failure_type`, `severity`, `file`, `description` | `governance-drift-report.json` findings[] | Yes |
| Source file contents | Four governance files (read-only, for confidence scoring) | Optional for MEDIUM/LOW disambiguation |

### 4.2 Output: TriageAnnotation

Each raw finding produces **exactly one** triage annotation. Triage does not merge or drop findings.

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `finding_id` | string | Yes | Links to raw finding |
| `drift_type` | enum | Yes | One of §2 categories |
| `severity` | enum | Yes | `CRITICAL` \| `MAJOR` \| `MINOR` — inherited from detection unless downgraded per §4.4 |
| `confidence` | enum | Yes | `HIGH` \| `MEDIUM` \| `LOW` |
| `recommended_action` | enum | Yes | `BLOCK_PIPELINE` \| `REQUIRE_REVIEW` \| `LOG_ONLY` |
| `rationale` | string | Yes | One sentence; no new governance vocabulary |
| `related_rule_ids` | string[] | No | Additional INV-/DRIFT- IDs implicated |
| `ambiguous` | boolean | Yes | `true` if confidence is LOW |

### 4.3 Confidence assignment (deterministic)

| Confidence | Criteria |
| --- | --- |
| **HIGH** | Single-file structural check failed (e.g., map row count ≠ 3); exact mandated literal missing; unique `rule_id` maps to one drift_type per §2.6 |
| **MEDIUM** | Cross-file literal mismatch with clear primary offender per §2.6; `failure_type` maps unambiguously but wording variant detected (e.g., bold vs plain three-type phrase) |
| **LOW** | Pattern match ambiguous; duplicate headings; editorial version skew only; `EVAL_AMBIGUOUS` from guard engine; could be ONTOLOGY_DRIFT or CROSS_FILE_CONTRADICTION |

### 4.4 Severity inheritance and triage downgrade (annotation only)

- Triage **inherits** `severity` from the guard layer finding by default.
- Triage MAY **annotate** a suggested downgrade in `rationale` only when `confidence` is **LOW** — it does **not** change the finding's severity field in the raw report.
- Final severity for pipeline gating remains the guard layer's `findings[].severity` unless guard layer implements an explicit triage-aware policy (out of scope for this spec).

### 4.5 recommended_action mapping

Triage suggests action; **guard layer decides** final pipeline effect.

| drift_type | severity | confidence | recommended_action |
| --- | --- | --- | --- |
| ONTOLOGY_DRIFT | CRITICAL | HIGH | BLOCK_PIPELINE |
| ONTOLOGY_DRIFT | CRITICAL | MEDIUM | REQUIRE_REVIEW |
| ONTOLOGY_DRIFT | any | LOW | REQUIRE_REVIEW |
| CATALOG_DRIFT | CRITICAL | HIGH | BLOCK_PIPELINE |
| CATALOG_DRIFT | CRITICAL | MEDIUM | BLOCK_PIPELINE |
| CATALOG_DRIFT | MAJOR | LOW | REQUIRE_REVIEW |
| POLICY_DRIFT | CRITICAL | HIGH | BLOCK_PIPELINE |
| POLICY_DRIFT | MAJOR | HIGH | BLOCK_PIPELINE |
| POLICY_DRIFT | MAJOR | LOW | REQUIRE_REVIEW |
| ENFORCER_DRIFT | CRITICAL | HIGH | BLOCK_PIPELINE |
| ENFORCER_DRIFT | MAJOR | HIGH | BLOCK_PIPELINE |
| ENFORCER_DRIFT | MAJOR | MEDIUM | REQUIRE_REVIEW |
| CROSS_FILE_CONTRADICTION | CRITICAL | HIGH | BLOCK_PIPELINE |
| CROSS_FILE_CONTRADICTION | MAJOR | any | REQUIRE_REVIEW |
| any | MINOR | any | LOG_ONLY |
| any | any | LOW | REQUIRE_REVIEW (minimum; never LOG_ONLY for CRITICAL raw severity) |

**recommended_action semantics:**

| Action | Meaning for guard layer |
| --- | --- |
| `BLOCK_PIPELINE` | Finding should contribute to FAIL under default HARD_GUARD_MODE |
| `REQUIRE_REVIEW` | Finding surfaced prominently; human must confirm before override; guard MAY still FAIL on CRITICAL |
| `LOG_ONLY` | Annotation only; guard SHOULD NOT FAIL solely on this triage annotation |

### 4.6 Machine-readable artifact

**File:** `governance-drift-triage.json`  
**Emitted alongside:** `governance-drift-report.json`

```json
{
  "schema_version": "1.0.0",
  "generated_at": "<ISO-8601>",
  "commit_sha": "<sha>",
  "annotations": [
    {
      "finding_id": "f-0001",
      "drift_type": "ENFORCER_DRIFT",
      "severity": "MAJOR",
      "confidence": "HIGH",
      "recommended_action": "BLOCK_PIPELINE",
      "rationale": "Precedence literal missing in governance-enforcer.md step 7",
      "related_rule_ids": ["DRIFT-04", "B3", "C1"],
      "ambiguous": false
    }
  ]
}
```

### 4.7 Human-readable summary extension

Append to `governance-drift-summary.md`:

```markdown
## Triage annotations

| Finding | Drift type | Confidence | Recommended action |
| --- | --- | --- | --- |
| f-0001 | ENFORCER_DRIFT | HIGH | BLOCK_PIPELINE |
```

---

## 5. Integration Point

### 5.1 Pipeline position

```
┌─────────────────────────────────────┐
│  Governance Drift Detection Engine  │  ← governance-guard-layer-spec.md §4
│  Output: governance-drift-report.json│
└─────────────────┬───────────────────┘
                  │ raw findings[]
                  ▼
┌─────────────────────────────────────┐
│  Governance Drift Triage Layer      │  ← this document
│  Output: governance-drift-triage.json │
└─────────────────┬───────────────────┘
                  │ annotations[]
                  ▼
┌─────────────────────────────────────┐
│  CI Guard Decision Step             │  ← governance-guard-layer-spec.md §2.6, §3
│  Inputs: report + triage + HARD_   │
│          GUARD_MODE                 │
│  Output: PASS | FAIL | WARN         │
└─────────────────────────────────────┘
```

### 5.2 Guard layer consumption rules

The guard layer MUST:

1. Load raw `findings[]` from `governance-drift-report.json`
2. Load `annotations[]` from `governance-drift-triage.json`
3. Join on `finding_id`
4. Apply pass/fail per **existing** `governance-guard-layer-spec.md` severity rules

The guard layer MAY:

- Surface `drift_type` and `confidence` in PR annotations
- When `confidence` is **LOW** and `recommended_action` is **REQUIRE_REVIEW**, add a non-blocking label `triage:review-required` while still FAILing on CRITICAL per HARD_GUARD_MODE
- When **all** CRITICAL findings have `confidence: LOW` and `recommended_action: REQUIRE_REVIEW`, emit a single advisory: *"Critical findings low-confidence — manual governance review required before merge"* — **without** changing FAIL semantics unless guard policy explicitly extended in a future spec version

The guard layer MUST NOT:

- Change HARD_GUARD_MODE behavior based solely on triage
- Downgrade CRITICAL to PASS without human approval outside CI
- Treat triage `recommended_action: LOG_ONLY` as overriding raw CRITICAL severity

### 5.3 Triage does not modify enforcement semantics

| Guard spec section | Modified by triage? |
| --- | --- |
| §2 CI Execution Model | **No** |
| §3 Enforcement Semantics | **No** |
| §5 HARD_GUARD_MODE | **No** |
| §6 Output schemas (findings schema) | **No** — triage adds parallel artifact only |

---

## 6. Safety Constraints (Normative)

| ID | Rule |
| --- | --- |
| **TRIAGE-SAFE-01** | The triage layer MUST NOT introduce new governance concepts, authority types, decision classes, or HALT cases. |
| **TRIAGE-SAFE-02** | The triage layer MUST NOT modify, grant, or revoke operational authority semantics. |
| **TRIAGE-SAFE-03** | The triage layer MUST NOT override CI enforcement decisions; it only annotates drift signals. |
| **TRIAGE-SAFE-04** | The triage layer MUST use only the five drift types defined in §2 — no extensions. |
| **TRIAGE-SAFE-05** | `recommended_action` is advisory; final pass/fail remains governed by `governance-guard-layer-spec.md`. |
| **TRIAGE-SAFE-06** | Triage MUST NOT suppress, delete, or merge away raw findings from the detection engine. |
| **TRIAGE-SAFE-07** | Triage rationale MUST reference existing `rule_id` / `failure_type` vocabulary only. |
| **TRIAGE-SAFE-08** | LOW confidence MUST NOT automatically convert FAIL to PASS. |

---

## 7. False-Positive Mitigation Patterns

| Ambiguous signal | Triage handling |
| --- | --- |
| Version string mismatch in Document Control only | `drift_type`: nearest file category; `confidence`: LOW; `recommended_action`: LOG_ONLY |
| Mandated literal present but different markdown emphasis | `confidence`: MEDIUM; keep severity; `drift_type`: CROSS_FILE_CONTRADICTION or single-file per §2.6 |
| `failure_type` NOMINATION_AUTHORITY_LEAKAGE in comment block vs normative section | `confidence`: LOW; `recommended_action`: REQUIRE_REVIEW; flag `ambiguous: true` |
| Duplicate precedence string in enforcer (encoding artifact) | `confidence`: MEDIUM; `drift_type`: ENFORCER_DRIFT; human review before BLOCK |

---

## 8. Document Control

- **Version:** 1.0.0
- **Status:** ACTIVE
- **Owner:** DormSys Architecture Team (document maintenance only)
- **Related:**
  - `.specify/governance/tests/governance-consistency-test-spec.md`
  - `.specify/governance/ci/governance-guard-layer-spec.md`

This document defines triage classification only. It does not modify governance semantics, CI execution model, or enforcement semantics.

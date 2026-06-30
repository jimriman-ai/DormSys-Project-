# Governance Guard Layer Spec (CI-Ready Cross-File Drift Detection)

**Version:** 1.0.0  
**Status:** ACTIVE  
**Last Updated:** 1405/04/03 | 2026/06/24  
**Type:** CI/validation architecture specification (not runtime enforcement)

**Upstream test spec:** `.specify/governance/tests/governance-consistency-test-spec.md` v1.0.0  
**Normative governance sources (read-only inputs):**

| Path | Role |
| --- | --- |
| `.specify/governance/_meta/authority-model.md` | Authority ontology, lifecycle vocabulary |
| `.specify/governance/execution-policy.md` | HALT classification, nomination policy |
| `.specify/governance/governance-enforcer.md` | Procedural enforcement alignment |
| `.specify/docs/catalog-decisions.md` | Canonical authority ownership map |

---

## 1. Purpose

This document defines the **Governance Guard Layer** — an automatable CI validation plane that executes the invariants, cross-file tests, and drift rules from `governance-consistency-test-spec.md` against the four core governance files.

The guard layer:

- **detects** ontology and enforcement semantic drift,
- **blocks** merges or releases when configured severity thresholds are exceeded,
- **reports** structured, machine-readable findings for remediation.

The guard layer **does not**:

- modify governance semantics,
- grant or revoke operational authority,
- replace `governance-enforcer.md` or `execution-policy.md` at runtime,
- introduce new authority types or governance concepts.

---

## 2. CI Execution Model

### 2.1 Job identity

| Field | Value |
| --- | --- |
| **Job name** | `governance-guard` |
| **Executor role** | Governance Drift Detection Engine (abstract; see §4) |
| **Spec authority** | `governance-consistency-test-spec.md` + this document |

### 2.2 Trigger points

| Trigger | When | Required |
| --- | --- | --- |
| **PR / merge request** | Any change touches one or more of the four input files, or this guard spec / consistency test spec | **Yes** (default) |
| **Push to protected branch** | Same path filter as PR | **Yes** (recommended) |
| **Pre-commit (optional)** | Local hook invoking same engine | Optional |
| **Scheduled audit** | Weekly or on-demand; full scan even if no diff | Optional (recommended for drift surveillance) |

**Path filter (deterministic):**

```
.specify/governance/_meta/authority-model.md
.specify/governance/execution-policy.md
.specify/governance/governance-enforcer.md
.specify/docs/catalog-decisions.md
.specify/governance/tests/governance-consistency-test-spec.md
.specify/governance/ci/governance-guard-layer-spec.md
```

If **no** filtered file changed in a PR, the job MAY **skip** with status `SKIPPED` (not `PASS`).

### 2.3 Inputs

| Input | Required | Description |
| --- | --- | --- |
| Four governance files | Yes | Current workspace versions at commit SHA |
| Consistency test spec | Yes | Rule catalog (INV-*, A–D, DRIFT-*) |
| Guard layer spec | Yes | CI semantics, schemas, severity policy |
| `HARD_GUARD_MODE` | No | Boolean; default `true` on protected branches |

### 2.4 Deterministic evaluation rules

Evaluation MUST be **deterministic** — same file contents produce identical findings.

| Rule | Requirement |
| --- | --- |
| **D-01** | No LLM inference, semantic guessing, or undocumented heuristics in CI gate path |
| **D-02** | Each check maps to an explicit `rule_id` from INV-*, test A–D, or DRIFT-* |
| **D-03** | Checks use documented literal patterns, structural counts, or cross-file equality of mandated strings |
| **D-04** | Evaluation order is fixed: Phase 1 → Phase 5 (see §4) |
| **D-05** | Findings are emitted in stable sort order: `severity` (desc), `rule_id` (asc), `file` (asc), `line` (asc) |
| **D-06** | Ambiguous pattern match → **fail the check** (not pass); report `EVAL_AMBIGUOUS` as Major unless mapped to Critical drift |

**Mandated cross-file literals (must match exactly where required):**

| Literal | Files that must contain |
| --- | --- |
| `Case C → Case A → Case B` | `execution-policy.md`, `governance-enforcer.md` |
| `Governance precondition failure: transition nomination record required.` | `execution-policy.md`, `governance-enforcer.md` |
| `Exactly **three** operational authority types` (or equivalent fixed phrase per test A1) | All four files per consistency spec A1 |
| Authority map row labels: `Design Approval`, `Implementation Authorization`, `Batch Execution Permission` | `catalog-decisions.md` map table only (exactly three data rows) |

### 2.5 Output artifacts

| Artifact | Format | Required |
| --- | --- | --- |
| `governance-drift-report.json` | JSON (schema §6.1) | Yes |
| `governance-drift-summary.md` | Markdown (format §6.2) | Yes |
| CI job annotation | Platform-native (e.g. GitHub Check Run) | Recommended |

### 2.6 Pass / fail semantics

| Result | Condition |
| --- | --- |
| **PASS** | Zero findings with `severity: CRITICAL`; zero findings with `severity: MAJOR` when `HARD_GUARD_MODE=true`; all tests A1–D3 pass |
| **FAIL** | One or more CRITICAL findings; OR (`HARD_GUARD_MODE=true` AND one or more MAJOR findings); OR engine error |
| **WARN** | Only MINOR findings; allowed when `HARD_GUARD_MODE=false` and `ALLOW_MINOR=true` |
| **SKIPPED** | No relevant path changes |

Exit code mapping:

| Exit code | Meaning |
| --- | --- |
| `0` | PASS or SKIPPED |
| `1` | FAIL |
| `2` | WARN (non-blocking mode only) |

---

## 3. Enforcement Semantics (CI-Level)

### 3.1 DRIFT rule → CI finding mapping

Each DRIFT-* rule produces one or more findings with stable IDs:

| DRIFT ID | Primary `failure_type` | Default severity | Tests invoked |
| --- | --- | --- | --- |
| DRIFT-01 | `CASE_C_MISCLASSIFICATION` | CRITICAL | B1, A3, D2 |
| DRIFT-02 | `NOMINATION_AUTHORITY_LEAKAGE` | CRITICAL | B2, C2, A2 |
| DRIFT-03 | `AUTHORITY_DRIFT` | CRITICAL | A1, D1 |
| DRIFT-04 | `PRECEDENCE_MISMATCH` | MAJOR | B3, C1 |
| DRIFT-05 | `CATALOG_MAP_VIOLATION` | CRITICAL | D1, D3 |
| DRIFT-06 | `AUTHORITY_DRIFT` | CRITICAL | D1, INV-08 |
| DRIFT-07 | `PRECEDENCE_MISMATCH` | MAJOR | B3, C3 |
| DRIFT-08 | `PRECEDENCE_MISMATCH` | MAJOR | C1, B3 |
| DRIFT-09 | `AUTHORITY_DRIFT` | CRITICAL | A2, A3 |
| DRIFT-10 | `CATALOG_MAP_VIOLATION` | CRITICAL | D2, D3 |

### 3.2 Severity → pipeline behavior

| Severity | Default (`HARD_GUARD_MODE=true`) | `HARD_GUARD_MODE=false` |
| --- | --- | --- |
| **CRITICAL** | Pipeline **blocked** | Pipeline **blocked** |
| **MAJOR** | Pipeline **blocked** | Pipeline **blocked** unless `ALLOW_MAJOR=true` |
| **MINOR** | Warning annotation; pipeline **passes** | Warning; pipeline passes if `ALLOW_MINOR=true` |

**CRITICAL** is assigned when:

- Any invariant INV-01, INV-04, INV-07, INV-09 violated
- `failure_type` is `AUTHORITY_DRIFT`, `NOMINATION_AUTHORITY_LEAKAGE`, or `CATALOG_MAP_VIOLATION`
- `CASE_C_MISCLASSIFICATION` where Case C grants operational authority

**MAJOR** is assigned when:

- `PRECEDENCE_MISMATCH` without execution-bypass risk
- `CASE_C_MISCLASSIFICATION` where messaging/classification incomplete but non-authorizing
- Cross-file literal mismatch for enforcer output expectations

**MINOR** is assigned when:

- Document Control version skew across files (informational)
- Missing optional cross-reference (non-normative)
- Stylistic duplication without semantic conflict

### 3.3 Aggregation rules

| Mode | Behavior |
| --- | --- |
| **Composite report** | Single JSON document containing all findings; never suppress individual findings |
| **Pipeline gate** | `FAIL` if **any** finding meets block threshold; first CRITICAL in sort order surfaced in CI title |
| **Deduplication** | Same `rule_id` + same `file` + same `line` → one finding; multiple `failure_type` tags allowed |
| **No suppression** | `HARD_GUARD_MODE=true` forbids allowlists, `skip-rules`, or per-PR waivers |

---

## 4. Governance Drift Detection Engine (Abstract Spec)

### 4.1 Definition

The **Governance Drift Detection Engine** is a stateless validator:

- **Input:** UTF-8 text of the four governance files + rule catalog
- **Output:** `GovernanceDriftReport` (JSON schema §6.1)
- **Properties:** Deterministic, side-effect free, read-only

### 4.2 Processing phases (fixed order)

```
Phase 1: Authority model invariants
Phase 2: Execution policy alignment
Phase 3: Enforcer compliance
Phase 4: Catalog boundary integrity
Phase 5: Cross-file invariant consistency
```

#### Phase 1 — Parse authority model invariants

- Load `authority-model.md`
- Verify §2 declares exactly three operational authority types (count numbered list items under Operational Authority Types)
- Verify Nomination Record / Case C not listed as operational types
- Emit findings for INV-01, INV-05, DRIFT-09
- Run tests **A1, A2, A3**

#### Phase 2 — Validate execution-policy alignment

- Load `execution-policy.md`
- Verify § Case C exists with exact HALT message literal
- Verify § Nomination and Execution Policy contains non-authorizing MUST NOT statements
- Verify detection procedure step 6 contains precedence literal `Case C` before `Case A` before `Case B`
- Emit findings for INV-02, INV-03, INV-06, INV-10
- Run tests **B1, B2, B3**

#### Phase 3 — Validate enforcer compliance

- Load `governance-enforcer.md`
- Verify step 4: Case C evaluate-first, immediate HALT, Nomination exclusion
- Verify step 7: precedence block matches execution-policy
- Verify Output Expectations: Case C defect class and both message literals
- Emit findings for DRIFT-07, DRIFT-08
- Run tests **C1, C2, C3**

#### Phase 4 — Validate catalog boundary integrity

- Load `catalog-decisions.md`
- Parse `## Governance Decision Authority Map` table; count data rows = 3
- Verify row Decision column values ⊆ {Design Approval, Implementation Authorization, Batch Execution Permission}
- Verify § Operational authority map scope (strict) exists
- Verify § Case C and § Nomination Record boundary subsections exist
- Verify no map row contains `Case C`, `Nomination`, or `Transition Nomination` as Decision label
- Emit findings for INV-04, INV-07, INV-08
- Run tests **D1, D2, D3**

#### Phase 5 — Cross-check invariants consistency

- Cross-file equality checks for mandated literals (§2.4)
- Verify tiered files do not contain parallel ownership map tables (heuristic: `| Decision | Canonical Authority Source | Owner |` only in catalog)
- Map all triggered conditions to DRIFT-01 through DRIFT-10
- Compute final `status`: PASS | FAIL | WARN

### 4.3 Engine constraints

- Engine MUST NOT modify input files
- Engine MUST NOT interpret handoff instances, spec.md, or code
- Engine MUST NOT resolve authority ownership — only verify documented boundaries
- Engine failures (parse error, missing file) → single CRITICAL finding `ENGINE_ERROR`

---

## 5. HARD_GUARD_MODE

### 5.1 Definition

| Variable | Type | Default (protected branch) |
| --- | --- | --- |
| `HARD_GUARD_MODE` | boolean | `true` |

### 5.2 Behavior when `HARD_GUARD_MODE=true`

1. **Zero tolerance:** Any CRITICAL finding → immediate pipeline FAIL (exit code 1)
2. **Major block:** Any MAJOR finding → pipeline FAIL (no `ALLOW_MAJOR` override)
3. **No suppression:** No rule allowlists, commit-message skips, or manual waivers
4. **No aggregation delay:** First CRITICAL fails job; engine still emits full report
5. **Mandatory on:** `main`, `master`, release branches, and any PR targeting them

### 5.3 Behavior when `HARD_GUARD_MODE=false`

- CRITICAL still fails pipeline
- MAJOR fails unless `ALLOW_MAJOR=true`
- MINOR emits WARN (exit code 2) if `ALLOW_MINOR=true`, else PASS with annotations

### 5.4 Configuration surface (CI environment)

| Variable | Values | Effect |
| --- | --- | --- |
| `HARD_GUARD_MODE` | `true` / `false` | See §5.2–5.3 |
| `ALLOW_MAJOR` | `true` / `false` | Only when `HARD_GUARD_MODE=false` |
| `ALLOW_MINOR` | `true` / `false` | Non-blocking MINOR |
| `GOVERNANCE_GUARD_STRICT_LITERALS` | `true` (default) | Enforce exact string matches §2.4 |

---

## 6. CI Guard Outputs

### 6.1 Machine-readable: `governance-drift-report.json`

**Schema version:** `1.0.0`

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "title": "GovernanceDriftReport",
  "type": "object",
  "required": [
    "schema_version",
    "generated_at",
    "commit_sha",
    "status",
    "hard_guard_mode",
    "summary",
    "findings"
  ],
  "properties": {
    "schema_version": { "type": "string", "const": "1.0.0" },
    "generated_at": { "type": "string", "format": "date-time" },
    "commit_sha": { "type": "string" },
    "status": { "enum": ["PASS", "FAIL", "WARN", "SKIPPED"] },
    "hard_guard_mode": { "type": "boolean" },
    "summary": {
      "type": "object",
      "required": ["critical", "major", "minor", "tests_passed", "tests_failed"],
      "properties": {
        "critical": { "type": "integer", "minimum": 0 },
        "major": { "type": "integer", "minimum": 0 },
        "minor": { "type": "integer", "minimum": 0 },
        "tests_passed": { "type": "array", "items": { "type": "string" } },
        "tests_failed": { "type": "array", "items": { "type": "string" } }
      }
    },
    "findings": {
      "type": "array",
      "items": {
        "type": "object",
        "required": [
          "finding_id",
          "rule_id",
          "failure_type",
          "severity",
          "file",
          "description"
        ],
        "properties": {
          "finding_id": { "type": "string" },
          "rule_id": {
            "type": "string",
            "description": "INV-*, A1–D3, or DRIFT-*"
          },
          "failure_type": {
            "enum": [
              "AUTHORITY_DRIFT",
              "CASE_C_MISCLASSIFICATION",
              "NOMINATION_AUTHORITY_LEAKAGE",
              "PRECEDENCE_MISMATCH",
              "CATALOG_MAP_VIOLATION",
              "ENGINE_ERROR",
              "EVAL_AMBIGUOUS"
            ]
          },
          "severity": { "enum": ["CRITICAL", "MAJOR", "MINOR"] },
          "file": { "type": "string" },
          "affected_sections": {
            "type": "array",
            "items": { "type": "string" }
          },
          "line_start": { "type": "integer", "minimum": 1 },
          "line_end": { "type": "integer", "minimum": 1 },
          "description": { "type": "string" },
          "remediation_hint": { "type": "string" },
          "related_files": {
            "type": "array",
            "items": { "type": "string" }
          }
        }
      }
    }
  }
}
```

**Example finding object:**

```json
{
  "finding_id": "f-0001",
  "rule_id": "DRIFT-04",
  "failure_type": "PRECEDENCE_MISMATCH",
  "severity": "MAJOR",
  "file": ".specify/governance/governance-enforcer.md",
  "affected_sections": ["Validation Order step 7"],
  "line_start": 104,
  "line_end": 108,
  "description": "HALT precedence literal 'Case C → Case A → Case B' missing or mismatched vs execution-policy.md",
  "remediation_hint": "Align precedence block with execution-policy.md v1.4.0 detection procedure step 6",
  "related_files": [".specify/governance/execution-policy.md"]
}
```

### 6.2 Human-readable: `governance-drift-summary.md`

**Template (normative structure):**

```markdown
# Governance Guard Summary

| Field | Value |
| --- | --- |
| Status | PASS / FAIL / WARN / SKIPPED |
| Commit | `<sha>` |
| HARD_GUARD_MODE | true / false |
| Critical | N |
| Major | N |
| Minor | N |

## Failed rules

| Rule | Severity | File | Description |
| --- | --- | --- | --- |
| DRIFT-04 | MAJOR | governance-enforcer.md | ... |

## Tests

- Passed: A1, A2, ...
- Failed: (none)

## Remediation

1. ...
```

---

## 7. Backward Compatibility Guarantees

| Guarantee | Statement |
| --- | --- |
| **BC-01** | This CI layer does **not** replace `governance-enforcer.md` runtime enforcement behavior |
| **BC-02** | This CI layer does **not** replace `execution-policy.md` operational HALT logic during batch execution |
| **BC-03** | The guard validates **structural and documented semantic consistency** across the four files only |
| **BC-04** | The guard **cannot** grant, revoke, or restore operational authority |
| **BC-05** | Passing the guard does **not** imply implementation authorization exists for any spec |
| **BC-06** | Failing the guard does **not** auto-fix documents; human governance correction required |
| **BC-07** | `governance-consistency-test-spec.md` remains the rule catalog; this document adds CI binding only |
| **BC-08** | No new authority types, map rows, or governance concepts are introduced by the guard layer |

---

## 8. CI Integration Checklist (Implementer)

1. Add `governance-guard` job with path filter §2.2
2. Set `HARD_GUARD_MODE=true` on protected branches
3. Upload `governance-drift-report.json` as build artifact (retention ≥ 90 days)
4. Post `governance-drift-summary.md` to PR comment on FAIL
5. Block merge on FAIL when branch protection requires `governance-guard`
6. On FAIL, reference `failure_type` and `rule_id` in PR template — not free-text reinterpretation

---

## 9. Document Control

- **Version:** 1.0.0
- **Status:** ACTIVE
- **Owner:** DormSys Architecture Team (document maintenance only)
- **Supersedes:** —
- **Related:**
  - `.specify/governance/tests/governance-consistency-test-spec.md`
  - `.specify/governance/review-checklist.md` § Authority Drift Prevention

This document defines CI binding only. It does not modify governance semantics and does not grant operational authority.

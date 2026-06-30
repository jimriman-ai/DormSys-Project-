# Governance Unified Verification

**Version:** 1.0.0  
**Status:** ACTIVE  
**Last Updated:** 1405/04/03 | 2026/06/24  
**Type:** Final decision aggregation layer (read-only inputs; derived system state output)

**Position in governance stack:**

```
Calibration (advisory lens — optional input)
Prevention → Detection → CI Guard → Triage → Remediation
                              │         │         │
                              └─────────┴─────────┘
                                        │
                                        ▼ (read-only aggregation)
                        governance-unified-verification.md (this document)
                                        │
                                        ▼
                        governance-unified-verification.json
                        (single system-level consistency state)
```

This document **does not replace** any upstream contract. It **aggregates** their outputs into one deterministic system state for architectural review, release readiness assessment, and human decision support. **CI pipeline pass/fail remains authoritative in `governance-guard-layer-spec.md`** unless a future integration explicitly wires unified verification into branch protection — which is **out of scope** for v1.0.0.

---

## 1. Purpose

**Governance Unified Verification** is a **deterministic final aggregation layer** that:

| Does | Does not |
| --- | --- |
| Consume read-only outputs from guard, triage, remediation, and optional calibration | Detect drift |
| Derive a single `final_state` and integrity sub-status fields | Classify drift (`drift_type` remains triage authority) |
| Reconcile signals using fixed evaluation order | Remediate drift |
| Surface `REQUIRES_REVIEW` and `UNKNOWN_CONFLICT` when layers disagree | Prevent drift at authoring time |
| Report system-level consistency for governance review | Modify CI behavior, enforcement, or detection rules |

**Explicit non-responsibilities:**

- It does **NOT** detect drift — detection is `governance-consistency-test-spec.md` executed by CI Guard.
- It does **NOT** classify drift — classification is `governance-drift-triage-spec.md`.
- It does **NOT** remediate drift — repair discipline is `governance-drift-remediation-contract.md`.
- It does **NOT** prevent drift — authoring constraints are `governance-drift-prevention-contract.md`.

It **only aggregates** results already produced by upstream layers.

---

## 2. Inputs (Read-Only)

All inputs are **immutable** for the duration of one verification run. Unified verification MUST NOT write back to any input artifact.

### 2.1 Required inputs

| Input | Source layer | Path / format | Required |
| --- | --- | --- | --- |
| Guard report | CI Guard | `governance-drift-report.json` | **Yes** |
| Triage annotations | Triage | `governance-drift-triage.json` | **Yes** when triage step ran; **No** when triage explicitly skipped per run metadata |

### 2.2 Optional inputs

| Input | Source | Format | Binding |
| --- | --- | --- | --- |
| Remediation result summary | Remediation | See §2.4 | No — affects `primary_failure_source` and reconciliation only |
| Calibration interpretation | Calibration | See §2.5 | **No** — advisory only; cannot change `final_state` alone |

### 2.3 `governance-drift-report.json` (required fields used)

Derived from `governance-guard-layer-spec.md` §6.1:

| Field | Use in aggregation |
| --- | --- |
| `schema_version` | Validate compatibility (`1.0.0`) |
| `status` | `PASS` \| `FAIL` \| `WARN` \| `SKIPPED` |
| `findings[]` | Severity, rule_id, failure_type, file |
| `findings[].finding_id` | Join key to triage |
| `findings[].severity` | `CRITICAL` \| `MAJOR` \| `MINOR` |
| `findings[].failure_type` | Authority / Case C / catalog signals |
| `hard_guard_mode` | Context only; does not override aggregation rules |

### 2.4 Remediation result summary (optional)

When present, MUST conform to this minimal structure (PR body, handoff note, or `governance-remediation-summary.json`):

```json
{
  "schema_version": "1.0.0",
  "remediation_attempted": true,
  "remediation_complete_claimed": true,
  "guard_re_run_status": "PASS",
  "findings_addressed": ["f-0001"],
  "remediation_categories": { "f-0001": "BOUNDARY_RESTORATION" },
  "core_principle_preserved": true,
  "recorded_at": "<ISO-8601>"
}
```

If `guard_re_run_status` is absent after `remediation_complete_claimed: true`, aggregation treats remediation as **unverified**.

### 2.5 Calibration interpretation (optional, advisory only)

Free-text or structured note per `governance-drift-calibration-contract.md` §2.4 profile:

```json
{
  "intent": "intentional | accidental | unclear",
  "ontology": "preserved | at-risk | violated",
  "compat": "compatible | partial | incompatible",
  "note": "<optional string>"
}
```

Calibration **cannot** force `PASS` when guard report contains CRITICAL findings. It may contribute to `REQUIRES_REVIEW` when combined with triage ambiguity (§4.3).

### 2.6 Input validation failures

| Condition | Effect on aggregation |
| --- | --- |
| Guard report missing or invalid JSON | `final_state` → `UNKNOWN_CONFLICT`; `primary_failure_source` → `guard_report_invalid` |
| Triage required but missing / `finding_id` join failures | Proceed with guard-only derivation; flag in `drift_summary` |
| Remediation claims complete without guard re-run | Contributes to `REQUIRES_REVIEW` (§4.3) |
| Calibration absent | Ignored; no penalty |

---

## 3. Decision Model (CRITICAL)

Unified verification emits **exactly one** `final_state` per run. **No other terminal states.**

| State | Meaning |
| --- | --- |
| `PASS` | System-level governance document consistency satisfied per §3.1 |
| `FAIL` | Blocking governance integrity defect per §3.2 |
| `REQUIRES_REVIEW` | Non-deterministic reconciliation or human judgment required per §3.3 |
| `UNKNOWN_CONFLICT` | Input or cross-layer contradiction cannot be resolved per §3.4 |

States are **orthogonal** to guard `status` where rules differ: unified verification may emit `REQUIRES_REVIEW` while guard `status` is `FAIL` or `PASS`. Unified verification does **not** change guard exit codes.

### 3.1 PASS (all conditions required)

`final_state` = `PASS` **only if**:

1. Guard `status` is `PASS` or `WARN` (not `FAIL`, not `SKIPPED` unless run policy explicitly treats SKIPPED as non-governing).
2. Zero findings with `severity: CRITICAL`.
3. Zero findings with `severity: MAJOR`.
4. `authority_integrity_status` = `INTACT` (§5.2).
5. `case_c_status` ∈ { `INTACT`, `NOT_APPLICABLE` } — no `VIOLATION_DETECTED` (§5.3).
6. `nomination_integrity_status` = `INTACT` (§5.4).
7. No `UNKNOWN_CONFLICT` or `REQUIRES_REVIEW` trigger from §3.3–3.4 fired earlier in evaluation order.

**Note:** Guard `WARN` with MINOR-only findings may yield unified `PASS` if all conditions above hold.

### 3.2 FAIL (any condition sufficient)

`final_state` = `FAIL` if **any**:

1. Guard `status` is `FAIL`, **unless** superseded by `UNKNOWN_CONFLICT` (invalid report — §3.4).
2. Any finding has `severity: CRITICAL`.
3. Any finding has `severity: MAJOR` **and** triage `recommended_action` is not `LOG_ONLY` for that finding (when triage present); if triage absent, MAJOR alone suffices for FAIL.
4. `authority_integrity_status` = `VIOLATION_DETECTED`.
5. `case_c_status` = `VIOLATION_DETECTED` (Case C misclassification, operational leakage, or precedence violation in **document** consistency — not runtime execution HALT).
6. `nomination_integrity_status` = `LEAKAGE_DETECTED`.
7. Remediation `core_principle_preserved` = `false` when remediation summary present.

**Unresolved Case C (document drift):** Any finding with `failure_type` ∈ `CASE_C_MISCLASSIFICATION` or rule_id matching DRIFT-01 / DRIFT-07 / DRIFT-08 **and** remediation not complete with guard `PASS` on re-run → **FAIL**.

### 3.3 REQUIRES_REVIEW (any condition sufficient)

Evaluate **after** FAIL checks unless inputs invalid. `final_state` = `REQUIRES_REVIEW` if **any** and **not** already `FAIL` or `UNKNOWN_CONFLICT`:

1. **Triage–guard conflict:** Triage `recommended_action: REQUIRE_REVIEW` on any CRITICAL or MAJOR finding while guard `status` is `PASS`.
2. **Triage–guard conflict:** Triage `recommended_action: BLOCK_PIPELINE` on a finding with `severity: MINOR` only.
3. **Ambiguous classification:** Any joined annotation has `confidence: LOW` or `ambiguous: true` on a CRITICAL or MAJOR finding.
4. **Medium ambiguity on authority/catalog:** `confidence: MEDIUM` and `drift_type` ∈ { `ONTOLOGY_DRIFT`, `CATALOG_DRIFT`, `CROSS_FILE_CONTRADICTION` } on MAJOR finding.
5. **Remediation unverified:** `remediation_complete_claimed: true` and (`guard_re_run_status` absent or not `PASS`).
6. **Calibration advisory conflict:** Calibration `ontology: violated` or `compat: incompatible` while guard `status` is `PASS` and zero CRITICAL/MAJOR findings.
7. **Partial triage coverage:** &gt;0 CRITICAL or MAJOR findings lack matching `finding_id` in triage when triage file was required.

`REQUIRES_REVIEW` does **not** mean PASS. It means aggregation cannot assert clean consistency without human confirmation.

### 3.4 UNKNOWN_CONFLICT (any condition sufficient)

`final_state` = `UNKNOWN_CONFLICT` if **any**:

1. Guard report JSON invalid or `schema_version` unsupported.
2. Guard `status: PASS` but any finding has `severity: CRITICAL` or `MAJOR` (internal report contradiction).
3. Guard `status: FAIL` but `findings[]` empty and no `engine_error` field documented in report.
4. Duplicate triage annotations for same `finding_id` with different `drift_type`.
5. Remediation `guard_re_run_status: PASS` but guard report provided in same run has `status: FAIL` (stale or mismatched artifact pair).
6. Evaluation order cannot complete due to irreconcilable timestamps or commit_sha mismatch between guard and triage artifacts (when both declare `commit_sha` and values differ).

`UNKNOWN_CONFLICT` takes **precedence** over `FAIL`, `REQUIRES_REVIEW`, and `PASS` when triggered.

### 3.5 State precedence (deterministic)

Apply **first match** when multiple states could apply:

```
UNKNOWN_CONFLICT  →  FAIL  →  REQUIRES_REVIEW  →  PASS
```

Within `FAIL`, no sub-ordering — first sufficient FAIL condition in evaluation steps (§6) documents `primary_failure_source`.

---

## 4. Hard Invariants (Normative)

These invariants are **affirmed** by unified verification output fields. Unified verification **cannot** redefine or override upstream contracts.

| ID | Invariant |
| --- | --- |
| **UV-INV-01** | Exactly **three** operational authority types remain unchanged: Design Approval, Implementation Authorization, Batch Execution Permission. |
| **UV-INV-02** | **Case C** is **non-operational** governance-precondition classification only; it does **not** belong to the operational enforcement authority system and does **not** grant operational permission. |
| **UV-INV-03** | **Nomination Record** is **evidence-only**; it **cannot** satisfy any operational authority check (Design Approval, Implementation Authorization, Batch Execution Permission). |
| **UV-INV-04** | HALT document precedence **Case C → Case A → Case B** is a consistency expectation when policy/enforcer findings are evaluated; unified verification reports violations; it does **not** execute HALT. |
| **UV-INV-05** | Catalog authority map **three-row** expectation is reflected in `authority_integrity_status`; unified verification does **not** modify the map. |
| **UV-INV-06** | This document **cannot** redefine, override, or supersede: prevention, detection, guard, triage, remediation, control model, or calibration contracts. |

Violation of UV-INV-01 through UV-INV-05 in **document findings** drives `authority_integrity_status`, `case_c_status`, or `nomination_integrity_status` — not silent override.

---

## 5. Derived Integrity Sub-Statuses

All sub-status fields are **derived from inputs only** — never loosely inferred from free text.

### 5.1 `authority_integrity_status`

| Value | Derivation rule |
| --- | --- |
| `INTACT` | No finding with `failure_type` ∈ { `AUTHORITY_DRIFT`, `CATALOG_MAP_VIOLATION` } and no rule_id ∈ { INV-01, INV-05, INV-07, INV-08, DRIFT-03, DRIFT-05, DRIFT-06, DRIFT-09 }; guard has no CRITICAL/MAJOR on those rules |
| `VIOLATION_DETECTED` | Any finding matches authority/catalog integrity signals above at any severity CRITICAL or MAJOR; OR any CRITICAL finding with `drift_type` ∈ { `ONTOLOGY_DRIFT`, `CATALOG_DRIFT` } when triage present |
| `UNKNOWN` | Guard report invalid; or conflicting signals (e.g., INTACT-eligible empty findings but calibration `ontology: violated` and triage missing) |

### 5.2 `case_c_status`

| Value | Derivation rule |
| --- | --- |
| `INTACT` | No Case C–related document drift signals; precedence literals consistent per findings |
| `VIOLATION_DETECTED` | Any finding with `failure_type` ∈ { `CASE_C_MISCLASSIFICATION`, `PRECEDENCE_MISMATCH` } tied to Case C / DRIFT-01 / DRIFT-04 / DRIFT-07 / DRIFT-08; OR triage `drift_type` ∈ { `POLICY_DRIFT`, `ENFORCER_DRIFT`, `CROSS_FILE_CONTRADICTION` } with rule_id referencing Case C at CRITICAL or MAJOR |
| `NOT_APPLICABLE` | Guard `status: SKIPPED` or zero findings and no Case C–related rules in scope |
| `UNRESOLVED` | Remediation attempted on Case C–related finding but §3.3 remediation unverified triggered |

Map `UNRESOLVED` → contributes to **FAIL** per §3.2 item 7 (unresolved Case C document drift).

### 5.3 `nomination_integrity_status`

| Value | Derivation rule |
| --- | --- |
| `INTACT` | No finding with `failure_type: NOMINATION_AUTHORITY_LEAKAGE` or rule_id DRIFT-02; no triage signal for nomination leakage at CRITICAL/MAJOR |
| `LEAKAGE_DETECTED` | Any DRIFT-02 / `NOMINATION_AUTHORITY_LEAKAGE` / INV-03 / INV-09 nomination-satisfaction finding at CRITICAL or MAJOR |
| `UNKNOWN` | Triage missing for nomination-related MAJOR+ finding |

### 5.4 `drift_summary`

Structured string array — one entry per aggregated signal, max 20 entries, stable sort by `finding_id`:

```
"<finding_id>: <severity> <failure_type> (<drift_type>|<unclassified>)"
```

Include remediation line when applicable:

```
"remediation: <complete|unverified|not_attempted>"
```

---

## 6. Evaluation Order (Deterministic)

Execute steps sequentially. **Stop early** only when `UNKNOWN_CONFLICT` is set (step 1 failure). Otherwise accumulate signals through step 5.

### Step 1 — Validate guard report

- Parse `governance-drift-report.json`.
- Verify `schema_version`.
- Check internal consistency (`status` vs `findings[].severity`).
- On failure → `UNKNOWN_CONFLICT`; `primary_failure_source` = `guard_report_invalid` or `guard_report_internal_conflict`.

### Step 2 — Validate triage classification

- If triage required: parse `governance-drift-triage.json`.
- Join each `findings[].finding_id` to `annotations[]`.
- Detect duplicate `finding_id`, unsupported `drift_type`, missing join.
- Do **not** reclassify — validate structure only.

### Step 3 — Validate remediation outcome (if present)

- If remediation summary absent → `remediation: not_attempted` in `drift_summary`.
- If present: verify `core_principle_preserved`, `guard_re_run_status`, `findings_addressed` ⊆ guard finding IDs.
- Flag unverified completion for step 5.

### Step 4 — Apply calibration interpretation (non-binding)

- If absent → skip.
- If present: record in output `calibration_advisory` sub-object (optional field).
- May set **review flags** for step 5; **cannot** set `PASS` if §3.2 FAIL conditions already met.

### Step 5 — Compute final system state

1. Compute `authority_integrity_status`, `case_c_status`, `nomination_integrity_status` (§5).
2. Build `drift_summary` (§5.4).
3. Apply state precedence (§3.5): `UNKNOWN_CONFLICT` → `FAIL` → `REQUIRES_REVIEW` → `PASS`.
4. Set `primary_failure_source` to first triggering rule (§7.2).

---

## 7. Output Schema

### 7.1 Artifact

**File:** `governance-unified-verification.json`  
**Emitted:** After guard (+ optional triage/remediation/calibration) in same verification run.  
**Commit binding:** SHOULD match `commit_sha` in guard report when present.

### 7.2 Required object

| Field | Type | Derivation |
| --- | --- | --- |
| `schema_version` | string | `"1.0.0"` |
| `generated_at` | ISO-8601 | Emission timestamp |
| `commit_sha` | string | From guard report or run context |
| `final_state` | enum | `PASS` \| `FAIL` \| `REQUIRES_REVIEW` \| `UNKNOWN_CONFLICT` |
| `primary_failure_source` | string | First sufficient cause (§7.3) |
| `drift_summary` | string[] | §5.4 |
| `authority_integrity_status` | enum | `INTACT` \| `VIOLATION_DETECTED` \| `UNKNOWN` |
| `case_c_status` | enum | `INTACT` \| `VIOLATION_DETECTED` \| `NOT_APPLICABLE` \| `UNRESOLVED` |
| `nomination_integrity_status` | enum | `INTACT` \| `LEAKAGE_DETECTED` \| `UNKNOWN` |
| `guard_status` | enum | Copied from report — not reinterpreted |
| `finding_counts` | object | `{ "critical": n, "major": n, "minor": n }` — counted from guard only |
| `inputs_present` | object | `{ "guard": true, "triage": bool, "remediation": bool, "calibration": bool }` |

### 7.3 `primary_failure_source` (enumerated)

| Value | When |
| --- | --- |
| `none` | `final_state: PASS` |
| `guard_report_invalid` | Step 1 parse failure |
| `guard_report_internal_conflict` | §3.4 condition 2 or 3 |
| `guard_status_fail` | Guard FAIL without higher precedence |
| `critical_finding` | First CRITICAL in sort order |
| `major_finding` | First MAJOR when no CRITICAL |
| `authority_integrity_violation` | §5.1 VIOLATION_DETECTED |
| `case_c_document_drift` | §5.2 VIOLATION_DETECTED or UNRESOLVED |
| `nomination_leakage` | §5.3 LEAKAGE_DETECTED |
| `remediation_principle_violation` | `core_principle_preserved: false` |
| `triage_guard_conflict` | §3.3 items 1–2 |
| `triage_ambiguity` | §3.3 items 3–4 |
| `remediation_unverified` | §3.3 item 5 |
| `calibration_advisory_conflict` | §3.3 item 6 |
| `triage_incomplete` | §3.3 item 7 |
| `artifact_commit_mismatch` | §3.4 item 6 |
| `duplicate_triage_annotation` | §3.4 item 4 |
| `remediation_guard_mismatch` | §3.4 item 5 |

### 7.4 JSON Schema (normative shape)

```json
{
  "schema_version": "1.0.0",
  "generated_at": "2026-06-24T12:00:00Z",
  "commit_sha": "abc123",
  "final_state": "FAIL",
  "primary_failure_source": "case_c_document_drift",
  "drift_summary": [
    "f-0003: MAJOR PRECEDENCE_MISMATCH (ENFORCER_DRIFT)",
    "remediation: unverified"
  ],
  "authority_integrity_status": "INTACT",
  "case_c_status": "VIOLATION_DETECTED",
  "nomination_integrity_status": "INTACT",
  "guard_status": "FAIL",
  "finding_counts": { "critical": 0, "major": 1, "minor": 2 },
  "inputs_present": {
    "guard": true,
    "triage": true,
    "remediation": true,
    "calibration": false
  },
  "calibration_advisory": null
}
```

Optional `calibration_advisory` object when calibration input provided — **informational only**.

### 7.5 Human-readable summary

**File:** `governance-unified-verification-summary.md` (optional companion)

```markdown
# Governance Unified Verification

| Field | Value |
| --- | --- |
| Final state | FAIL |
| Guard status | FAIL |
| Authority integrity | INTACT |
| Case C status | VIOLATION_DETECTED |
| Nomination integrity | INTACT |
| Primary source | case_c_document_drift |
```

---

## 8. Relationship to Upstream Contracts (Read-Only)

| Contract | Role | Modified by unified verification? |
| --- | --- | --- |
| `governance-consistency-test-spec.md` | Detection rules | **No** |
| `governance-guard-layer-spec.md` | CI gate + report | **No** |
| `governance-drift-triage-spec.md` | Classification | **No** |
| `governance-drift-remediation-contract.md` | Fix discipline | **No** |
| `governance-drift-prevention-contract.md` | Authoring constraints | **No** |
| `governance-drift-control-model.md` | Lifecycle map | **No** |
| `governance-drift-calibration-contract.md` | Evolution vs drift lens | **No** |

Unified verification is a **sixth documentation artifact** for aggregation semantics only. It is **not** added to the control model execution DAG as a processing plane that mutates governance files or detection rules.

---

## 9. Non-Goals

This document **does NOT**:

| Non-goal | Clarification |
| --- | --- |
| Introduce new governance logic | No new INV-*, DRIFT-*, PREV-*, REM-*, or HALT rules |
| Change CI behavior | Guard job name, HARD_GUARD_MODE, exit codes unchanged |
| Change enforcement | `execution-policy.md` / `governance-enforcer.md` runtime unchanged |
| Change authority model | Three operational types; Case C non-operational; Nomination evidence-only |
| Add drift categories | Uses triage five-type enum when present; does not extend |
| Replace upstream contracts | Aggregation only |
| Auto-remediate or auto-merge | Emits state; humans or separate tooling act |
| Waive CRITICAL findings | `PASS` impossible with CRITICAL present |

---

## 10. Document Control

- **Version:** 1.0.0
- **Status:** ACTIVE
- **Owner:** DormSys Architecture Team (document maintenance only)
- **Aggregates (read-only):**
  - `.specify/governance/tests/governance-consistency-test-spec.md` v1.0.0
  - `.specify/governance/ci/governance-guard-layer-spec.md` v1.0.0
  - `.specify/governance/ci/governance-drift-triage-spec.md` v1.0.0
  - `.specify/governance/ci/governance-drift-remediation-contract.md` v1.0.0
  - `.specify/governance/ci/governance-drift-prevention-contract.md` v1.0.0
  - `.specify/governance/ci/governance-drift-control-model.md` v1.0.0
  - `.specify/governance/ci/governance-drift-calibration-contract.md` v1.0.0

This document defines final aggregation semantics only. It does not modify system behavior in any upstream layer.

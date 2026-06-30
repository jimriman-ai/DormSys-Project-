# Governance Drift Remediation Contract

**Version:** 1.0.0  
**Status:** ACTIVE  
**Last Updated:** 1405/04/03 | 2026/06/24  
**Type:** Post-detection remediation contract (not enforcement, not CI decisioning)

**Position in stack:**

```
governance-consistency-test-spec.md      (rule catalog — detection rules)
            ↓
governance-guard-layer-spec.md           (CI pass/fail — unchanged)
            ↓
governance-drift-triage-spec.md          (classification — unchanged)
            ↓
governance-drift-remediation-contract.md (this document — fix guidance only)
            ↓
Human or agent-guided document edits → re-run consistency tests
```

---

## 1. Purpose

The **Governance Drift Remediation Contract** is a post-detection governance rule framework that governs **how** detected drift may be corrected.

It applies:

- **after** the Governance Guard Layer reports findings (`governance-drift-report.json`),
- **after** optional triage annotations (`governance-drift-triage.json`) classify drift type and confidence,
- **before** any governance document edit is merged.

It is **not**:

| Not part of | Reason |
| --- | --- |
| CI decisioning | Pass/fail remains solely in `governance-guard-layer-spec.md` |
| Triage classification | Drift type assignment remains in `governance-drift-triage-spec.md` |
| Runtime enforcement | Operational HALT behavior remains in `execution-policy.md` and `governance-enforcer.md` |
| Authority model | Ontology definitions remain in `authority-model.md` |

This contract provides **remediation discipline** only: permitted fix patterns, forbidden actions, and a deterministic workflow so corrections do not introduce secondary drift, authority corruption, or ontology violations.

---

## 2. Core Principle (Strict Invariant)

> **No drift remediation is allowed to modify governance semantics unless it explicitly preserves all three operational authority types and non-operational separation rules.**

### 2.1 What must be preserved

Every remediation change MUST affirm, without alteration:

1. **Exactly three operational authority types:** Design Approval, Implementation Authorization, Batch Execution Permission.
2. **Non-operational separation:** Case C (governance-precondition classification), Nomination Record (evidence-only), and other artifacts outside the authorization record lifecycle **do not** grant operational authority.
3. **HALT precedence:** Case C → Case A → Case B (as normative in `execution-policy.md` and `governance-enforcer.md`).
4. **Catalog map scope:** `catalog-decisions.md` § `## Governance Decision Authority Map` contains **exactly three** operational rows; no fourth map row for non-operational classes.
5. **Mandated literals:** Exact HALT messages and precedence strings defined in execution policy and enforcer output expectations.

### 2.2 Semantic change vs alignment

| Permitted | Forbidden |
| --- | --- |
| Restore wording to match an already-normative definition in another core file | Introduce a new definition, decision class, or authority type |
| Add a cross-reference where one file already normatively states the rule | Change what the rule *means* |
| Remove text that contradicts the authority model | "Fix" drift by evolving design (new concepts, new map rows, new HALT cases) |

If a fix would require changing semantics beyond alignment to existing normative text in the four core files, remediation **MUST HALT** and escalate to a **governance change proposal** — outside this contract's scope.

---

## 3. Allowed Remediation Actions (STRICT)

All drift fixes MUST use **exactly one** primary remediation category per finding. No additional categories.

### 3.1 TEXTUAL_ALIGNMENT

**Purpose:** Clarify inconsistent wording across documents where the underlying semantics are already agreed.

**Permitted:**

- Harmonize terminology (e.g., "implementation execution target" vs "nominated specification" where policy already distinguishes them).
- Fix typos, encoding corruption (e.g., corrupted arrow characters in precedence lines).
- Align markdown emphasis without changing normative meaning.
- Synchronize version cross-references in Document Control blocks when content is already aligned.

**Forbidden within this category:**

- Any change to HALT case definitions, precedence order, or mandated messages.
- Any new normative MUST/SHALL statement not already implied by another core file.

**Typical drift_types:** `CROSS_FILE_CONTRADICTION` (wording-only), `ENFORCER_DRIFT` (literal mismatch), `POLICY_DRIFT` (editorial inconsistency).

---

### 3.2 BOUNDARY_RESTORATION

**Purpose:** Restore correct separation between the four governance layers.

| Layer | File | Boundary concern |
| --- | --- | --- |
| Ontology | `authority-model.md` | Three operational types; non-operational artifact classes |
| Policy | `execution-policy.md` | HALT classification, nomination precondition, pre-execution steps |
| Enforcement | `governance-enforcer.md` | Validation order, Case C-first precedence, output expectations |
| Mapping | `catalog-decisions.md` | Authority map rows; non-operational boundary subsections |

**Permitted:**

- Remove or rewrite text that incorrectly places Nomination Record or Case C in the operational map.
- Restore enforcer step 4 / step 7 ordering to match execution policy.
- Re-add catalog boundary subsections that state non-operational status without adding map rows.
- Remove parallel ownership maps outside `catalog-decisions.md`.

**Forbidden within this category:**

- Adding a new map row or authority owner.
- Redefining Case A, B, or C behavior.
- Moving normative HALT logic from policy into catalog or enforcer as *source of truth* (enforcer reflects policy; it does not replace it).

**Typical drift_types:** `CATALOG_DRIFT`, `ONTOLOGY_DRIFT`, `ENFORCER_DRIFT`, `POLICY_DRIFT`.

---

### 3.3 MISSING_REFERENCE_PATCH

**Purpose:** Add missing cross-document references where the referenced rule already exists normatively elsewhere.

**Permitted:**

- Add "per `execution-policy.md` vX.Y.Z" style pointers in enforcer or catalog.
- Add informational cross-references in catalog boundary notes to policy/enforcer sections.
- Link Document Control version fields consistently across files after content alignment.

**Forbidden within this category:**

- Using a reference patch to *introduce* a rule that does not exist in the target document's authoritative source.
- Referencing `authority-model.md` for Nomination/Case C vocabulary if that vocabulary is not yet in authority-model (escalate governance change instead of patching around absence).

**Typical drift_types:** `CROSS_FILE_CONTRADICTION`, `ENFORCER_DRIFT`, `CATALOG_DRIFT`.

---

### 3.4 EXPLICITNESS_INJECTION

**Purpose:** Make implicit rules explicit when the rule is already entailed by existing normative text.

**Permitted:**

- Add a clarifying sentence that restates an existing invariant (e.g., "Nomination Records cannot satisfy authorization checks" where policy already says so).
- Add a descriptive subsection mirroring content already in another file (catalog non-operational notes reflecting execution-policy § Nomination and Execution Policy).
- Document precedence in enforcer when execution-policy already mandates Case C → Case A → Case B.

**Forbidden within this category:**

- Injecting a rule that is **not** already entailed by the four core files.
- Treating explicitness injection as a vehicle for "design evolution" or new governance concepts.
- Adding new DRIFT rules, invariants, or enforcement steps.

**Typical drift_types:** `POLICY_DRIFT`, `ENFORCER_DRIFT`, `ONTOLOGY_DRIFT` (when authority-model is silent but other files over-specify — prefer escalation if authority-model update is required).

---

### 3.5 Category selection precedence

When multiple categories could apply, use **first match**:

1. **BOUNDARY_RESTORATION** — if map structure, layer separation, or precedence order is wrong
2. **TEXTUAL_ALIGNMENT** — if only wording/literals differ but semantics match
3. **MISSING_REFERENCE_PATCH** — if content exists but cross-file pointer is absent
4. **EXPLICITNESS_INJECTION** — if rule is implicit but already entailed elsewhere

---

## 4. Drift Type → Remediation Category Map

| triage `drift_type` | Primary category | Secondary (if applicable) |
| --- | --- | --- |
| `ONTOLOGY_DRIFT` | BOUNDARY_RESTORATION | EXPLICITNESS_INJECTION only if authority-model already states the rule |
| `POLICY_DRIFT` | BOUNDARY_RESTORATION or TEXTUAL_ALIGNMENT | MISSING_REFERENCE_PATCH for version pointers |
| `ENFORCER_DRIFT` | BOUNDARY_RESTORATION | TEXTUAL_ALIGNMENT for mandated literals |
| `CATALOG_DRIFT` | BOUNDARY_RESTORATION | EXPLICITNESS_INJECTION for non-operational boundary notes |
| `CROSS_FILE_CONTRADICTION` | TEXTUAL_ALIGNMENT | BOUNDARY_RESTORATION if separation violation is root cause |

If no allowed category can remediate without semantic change → **escalate** (§6.4); do not improvise.

---

## 5. Forbidden Actions (CRITICAL)

The following actions are **explicitly forbidden** during drift remediation. Violation invalidates the remediation and may introduce secondary drift.

| ID | Forbidden action |
| --- | --- |
| **REM-FORBID-01** | Adding a new operational authority type or map row |
| **REM-FORBID-02** | Modifying HALT semantics — Case A, Case B, or Case C definitions, triggers, or messages |
| **REM-FORBID-03** | Changing enforcement precedence (Case C → Case A → Case B order) |
| **REM-FORBID-04** | Modifying `catalog-decisions.md` authority map table structure (row count, owners, artifact paths) except to **remove** an erroneous row |
| **REM-FORBID-05** | Introducing new governance concepts (decision classes, artifact types, HALT cases, DRIFT rules) |
| **REM-FORBID-06** | Resolving drift by "design evolution" — treating CI failure as authorization to redesign governance |
| **REM-FORBID-07** | Granting Nomination Record or Case C operational authority in any file |
| **REM-FORBID-08** | Weakening or bypassing triage/guard findings without re-running consistency tests |
| **REM-FORBID-09** | Editing `governance-consistency-test-spec.md`, `governance-guard-layer-spec.md`, or `governance-drift-triage-spec.md` as a workaround for document drift |
| **REM-FORBID-10** | Splitting a semantic change across multiple "alignment" edits to evade review |

**Escalation trigger:** Any fix that would violate REM-FORBID-01 through REM-FORBID-07 requires a formal governance change proposal and map amendment process — not remediation under this contract.

---

## 6. Remediation Workflow (Deterministic)

### Step 1 — Identify drift type

Load from triage annotation (`governance-drift-triage.json`) or, if triage unavailable, infer from raw finding `failure_type` and `rule_id` per `governance-drift-triage-spec.md` §2.

Record:

- `finding_id`
- `drift_type`
- `rule_id` / `failure_type`
- affected file(s)

### Step 2 — Map to allowed remediation category

Apply §3.5 precedence and §4 mapping table. Document chosen category and one-sentence justification in remediation notes (PR description or handoff record).

If **no** allowed category applies → proceed to §6.4 Escalation HALT.

### Step 3 — Apply minimal change principle

- Edit **only** files cited in the finding (plus at most one cross-file alignment target if `CROSS_FILE_CONTRADICTION`).
- Prefer the **smallest** diff that restores invariant satisfaction.
- One finding → one primary remediation commit scope where possible.
- Do not refactor unrelated sections.

### Step 4 — Verify no ontology changes introduced

Before merge, confirm:

| Check | Pass criterion |
| --- | --- |
| Three-type model | No new operational authority wording in any core file |
| Map row count | `catalog-decisions.md` map still exactly three rows |
| HALT literals | Mandated messages unchanged unless restoring exact policy text |
| Precedence | Case C → Case A → Case B present and consistent in policy + enforcer |
| Non-operational boundary | Nomination / Case C remain non-operational in catalog notes |
| Core principle (§2) | Explicit preservation statement in PR/remediation notes |

Manual spot-check or diff review against §2.1 checklist is required. Automated re-run follows in Step 5.

### Step 5 — Re-run governance consistency tests

Execute the rule catalog per `governance-consistency-test-spec.md` (via guard layer job or local equivalent):

- All tests A1–D3 applicable to changed files
- No DRIFT-01 through DRIFT-10 conditions
- CI `governance-guard` job PASS (or WARN only if pre-existing policy allows)

Remediation is **complete** only when Step 5 passes. Triage and guard layer specs are **not** modified to achieve pass.

### Step 6 — Record remediation (recommended)

Document in PR or `.specify/docs/handoff/` remediation note:

```markdown
## Governance drift remediation

| Finding | Drift type | Category | Files changed | Invariant preserved |
| --- | --- | --- | --- | --- |
| f-0001 | ENFORCER_DRIFT | BOUNDARY_RESTORATION | governance-enforcer.md | §2 core principle |
```

---

### 6.4 Escalation HALT

When remediation under this contract cannot proceed:

1. **Stop** document edits framed as "drift fixes."
2. Open a **governance change proposal** (authority-model bump, map amendment, new decision class).
3. Do **not** merge partial fixes that leave cross-file contradiction unresolved.
4. Re-run guard only after governance change is approved through normal governance review — not through remediation bypass.

---

## 7. Integration Boundary

### 7.1 No CI interaction

This contract:

- does **not** define pass/fail thresholds,
- does **not** modify `HARD_GUARD_MODE`,
- does **not** consume or emit CI artifacts that override `governance-drift-report.json`,
- does **not** instruct the guard layer to ignore findings.

CI outcome is determined **only** by `governance-guard-layer-spec.md` after Step 5 re-run.

### 7.2 Activation conditions

Remediation under this contract is used **only when**:

| Trigger | Action |
| --- | --- |
| CI `governance-guard` job **FAIL** | Full workflow §6 |
| Triage `recommended_action: REQUIRE_REVIEW` with human/agent decision to fix | Full workflow §6 |
| Triage `recommended_action: BLOCK_PIPELINE` | Full workflow §6; no merge until PASS |
| Triage `recommended_action: LOG_ONLY` | Contract optional; if fixing anyway, still follow §2–5 |

### 7.3 Actor model

Remediation is **manual or agent-guided**:

- Humans follow this contract in PR review and governance maintenance.
- Agents (e.g., Speckit implement, architecture assistants) MUST cite remediation category and §2 preservation checklist in change descriptions.
- Neither actor may use this contract to bypass enforcement or redefine authority.

### 7.4 Upstream/downstream references (read-only)

| Document | Relationship |
| --- | --- |
| `governance-consistency-test-spec.md` | Verification target (Step 5); not modified |
| `governance-guard-layer-spec.md` | CI decision authority; not modified |
| `governance-drift-triage-spec.md` | Drift type input; not modified |
| Four core governance files | **Only** editable targets under this contract |

---

## 8. Safety Constraints (Normative)

| ID | Rule |
| --- | --- |
| **REM-SAFE-01** | Remediation MUST preserve the §2 core principle invariant. |
| **REM-SAFE-02** | Remediation MUST use only the four categories in §3. |
| **REM-SAFE-03** | Remediation MUST NOT violate any item in §5. |
| **REM-SAFE-04** | Remediation MUST re-run consistency tests before merge completion. |
| **REM-SAFE-05** | Remediation MUST NOT modify CI, triage, or test specs as a fix strategy. |
| **REM-SAFE-06** | Semantic governance changes require escalation — not remediation. |
| **REM-SAFE-07** | Secondary drift introduced by a fix invalidates the remediation; revert and re-apply per §6. |

---

## 9. Examples (Illustrative, Non-Normative)

| Finding | Allowed fix | Forbidden fix |
| --- | --- | --- |
| Corrupted `Case C  Case A` arrow in enforcer | TEXTUAL_ALIGNMENT: restore `Case C → Case A → Case B` | Redefine precedence order |
| Catalog says nomination not in map; policy follow-up says "per canonical map" | MISSING_REFERENCE_PATCH or TEXTUAL_ALIGNMENT to catalog boundary note | Add Nomination row to map |
| Enforcer missing Case C evaluate-first block | BOUNDARY_RESTORATION: copy procedural alignment from policy | Invent new Case D |
| DRIFT-07 message mismatch policy vs enforcer | TEXTUAL_ALIGNMENT: enforcer output to exact policy literal | Change policy message to match enforcer typo |
| authority-model silent on Nomination | Escalate governance change | EXPLICITNESS_INJECTION in authority-model without formal bump |

---

## 10. Document Control

- **Version:** 1.0.0
- **Status:** ACTIVE
- **Owner:** DormSys Architecture Team (document maintenance only)
- **Related:**
  - `.specify/governance/tests/governance-consistency-test-spec.md`
  - `.specify/governance/ci/governance-guard-layer-spec.md`
  - `.specify/governance/ci/governance-drift-triage-spec.md`

This document defines remediation discipline only. It does not modify governance semantics, CI execution, triage classification, or runtime enforcement.

# Governance Drift Prevention Contract

**Version:** 1.0.0  
**Status:** ACTIVE  
**Last Updated:** 1405/04/03 | 2026/06/24  
**Type:** Design-time prevention contract (not detection, not enforcement, not CI)

**Position in stack:**

```
governance-drift-prevention-contract.md   (this document — design/review time only)
            ↓ authors edit governance documents
governance-consistency-test-spec.md       (detection rules)
            ↓
governance-guard-layer-spec.md            (CI pass/fail)
            ↓
governance-drift-triage-spec.md           (classification)
            ↓
governance-drift-remediation-contract.md  (post-detection fix guidance)
```

Prevention operates **upstream** of all detection and enforcement layers. Its goal is to stop drift from being authored, not to detect or fix it after the fact.

---

## 1. Purpose

The **Governance Drift Prevention Contract** is a **pre-runtime governance constraint system** that enforces **design-time invariants** on edits to the four core governance files.

It exists to:

- prevent governance drift **at the source** — before it enters detection, triage, or CI,
- constrain authors, reviewers, and agents during governance document design and review,
- reduce false positives and ontology-level misclassification downstream by eliminating ambiguous authoring patterns.

This contract:

| Is | Is not |
| --- | --- |
| A design-time and review-time constraint system | A CI job or automated gate (unless separately wired by future tooling) |
| A proactive invariant checklist for governance authors | Runtime enforcement (`execution-policy.md`, `governance-enforcer.md`) |
| A source-of-truth for *how to write* governance consistently | A detection rule catalog (`governance-consistency-test-spec.md`) |
| A complement to remediation (prevents; remediation repairs) | Authority model ontology (`authority-model.md`) |

**Normative separation:** Prevention **constrains authoring**; detection **finds violations**; CI **blocks merges**; triage **classifies**; remediation **guides fixes**. No layer substitutes for another.

---

## 2. Prevention Principles (Strict Invariants)

All governance document edits MUST satisfy these principles before merge. Violation at authoring time is a **prevention failure** — even if CI has not yet run.

### PRINCIPLE-01 — No ambiguity in authority definitions

Every operational authority type, artifact class, and owner reference MUST be:

- explicitly named,
- uniquely identifiable,
- resolvable to a single canonical definition in `authority-model.md` or `catalog-decisions.md` § `## Governance Decision Authority Map`.

Partial definitions ("similar to Design Approval", "acts like authorization") are forbidden.

### PRINCIPLE-02 — No dual-interpretation rules across files

If a concept appears in more than one core file, it MUST have **one meaning** everywhere:

- same operational vs non-operational classification,
- same satisfaction rules (what can / cannot satisfy pre-execution checks),
- same owner resolution path.

A reader MUST NOT be able to derive conflicting behavior from two files that both claim normative status for the same concept.

### PRINCIPLE-03 — No implicit HALT semantics

HALT classification (Case A, Case B, Case C), precedence (Case C → Case A → Case B), and mandated messages MUST be:

- explicitly stated in `execution-policy.md`,
- procedurally reflected in `governance-enforcer.md`,
- never inferred from examples, change logs, or descriptive notes alone.

Silent assumptions about which case applies, or in what order, are forbidden.

### PRINCIPLE-04 — No cross-file undefined dependencies

Every normative cross-reference MUST:

- name the target file and section (or invariant ID where applicable),
- point to content that **already exists** in the target file at edit time,
- not rely on "future governance change" or "TBD map entry" as the sole authority source.

Undefined dependencies ("per the canonical map" when the map does not contain the referenced item) are forbidden unless accompanied by an approved governance change proposal outside this contract.

### PRINCIPLE-05 — Three operational types preserved

All edits MUST preserve exactly **three** operational authority types: Design Approval, Implementation Authorization, Batch Execution Permission. Non-operational artifacts (Case C, Nomination Record, governance state snapshots) MUST remain explicitly non-operational in every file that mentions them.

---

## 3. Prevention Rules (Categories)

All design-time compliance is expressed through **exactly four** prevention rule categories. No additional categories.

### 3.1 DEFINITION_LOCKING

**Intent:** Authority and governance concepts cannot be ambiguous or partial at authoring time.

**Requirements:**

| ID | Rule |
| --- | --- |
| **PREV-DEF-01** | Operational authority types MUST be named using canonical labels from `authority-model.md` §2 only. |
| **PREV-DEF-02** | Non-operational classes (Case C, Nomination Record, snapshot artifacts) MUST be labeled **non-operational** or **evidence-only** on first substantive mention in any file. |
| **PREV-DEF-03** | New normative MUST/SHALL statements about authority MUST trace to an existing invariant (INV-01–10) or an approved governance change — not invented in tiered files alone. |
| **PREV-DEF-04** | Artifact paths in catalog map rows MUST match the canonical path pattern; no placeholder or "see elsewhere" as the sole path cell. |
| **PREV-DEF-05** | HALT messages MUST be quoted exactly when introduced; no paraphrase variants across files. |

**Applies at:** Authoring and PR review of `authority-model.md`, `catalog-decisions.md`, `execution-policy.md`, `governance-enforcer.md`.

---

### 3.2 CROSS_FILE_CONSISTENCY_REQUIREMENT

**Intent:** Identical concepts MUST have identical meaning across all four core files.

**Requirements:**

| ID | Rule |
| --- | --- |
| **PREV-XFILE-01** | Before merging a governance edit, verify the same concept in all files that mention it — especially Case C, Nomination Record, HALT precedence, three-type model. |
| **PREV-XFILE-02** | Version cross-references in Document Control blocks MUST be updated in the same change set when normative content in a referenced file changes. |
| **PREV-XFILE-03** | Enforcer procedural text MUST mirror execution-policy semantics; enforcer does not introduce policy not present in policy. |
| **PREV-XFILE-04** | Catalog boundary subsections MUST not contradict execution-policy nomination or HALT sections. |
| **PREV-XFILE-05** | Tiered files pointer-reference catalog for ownership; they do not assign new owners. |

**Applies at:** Multi-file governance PRs; governance change proposals; agent-authored batch edits.

---

### 3.3 NO_IMPLICIT_AUTHORITY_RULE

**Intent:** Anything affecting execution, HALT, or authorization checks MUST be explicitly defined — never implied.

**Requirements:**

| ID | Rule |
| --- | --- |
| **PREV-EXPL-01** | No artifact may be described as satisfying authorization unless explicitly listed as operational in the authority map. |
| **PREV-EXPL-02** | No handoff filename, directory placement, or status header may imply Implementation Authorization. |
| **PREV-EXPL-03** | Review-gate approval, triage annotations, and CI PASS MUST NOT be described as granting operational authority. |
| **PREV-EXPL-04** | Pre-execution validation order MUST be explicit in enforcer (Case C before operational checks when policy requires). |
| **PREV-EXPL-05** | "May", "should", or descriptive examples MUST NOT be the sole source of normative HALT or authority behavior — normative sections required. |

**Applies at:** All four core files; handoff template references in catalog.

---

### 3.4 ONTOLOGY_STABILITY_RULE

**Intent:** The authority model ontology is stable; changes require explicit versioning and governance review — not drift-by-editing.

**Requirements:**

| ID | Rule |
| --- | --- |
| **PREV-ONT-01** | `authority-model.md` MUST NOT gain new operational types, map-equivalent rows, or authorization lifecycle classes without a **major version bump** and governance review. |
| **PREV-ONT-02** | Tiered files MUST NOT redefine §2 operational authority types; they affirm or apply the model only. |
| **PREV-ONT-03** | Introducing Nomination, Case C, or new artifact classes in tiered files BEFORE authority-model adoption requires **escalation** — not silent explicitness injection. |
| **PREV-ONT-04** | Catalog map row count MUST remain three unless a formal map amendment process completes. |
| **PREV-ONT-05** | Document Control version increments MUST reflect semantic vs editorial change per each file's existing convention. |

**Applies at:** `authority-model.md` edits; any edit that touches operational vs non-operational boundary.

---

### 3.5 Category application order (review checklist)

When reviewing a proposed governance edit, evaluate in order:

1. **ONTOLOGY_STABILITY_RULE** — would this change the authority model or map structure?
2. **DEFINITION_LOCKING** — are all new terms fully and unambiguously defined?
3. **NO_IMPLICIT_AUTHORITY_RULE** — is every execution-affecting rule explicit?
4. **CROSS_FILE_CONSISTENCY_REQUIREMENT** — are all touched files aligned?

If any category fails → **do not merge**; revise at source or escalate to governance change proposal.

---

## 4. Forbidden Design Patterns (CRITICAL)

The following patterns MUST NOT appear in new or modified governance content. They are primary drift **generators** detected downstream as DRIFT-01–10 and triage categories.

| ID | Forbidden pattern | Why it causes drift |
| --- | --- | --- |
| **PREV-FORBID-01** | **Implicit governance logic** — behavior described only in examples, change logs, or "see above" without normative section | Detection cannot anchor rules; dual interpretation |
| **PREV-FORBID-02** | **Undocumented cross-file dependencies** — "per canonical map" when map lacks entry; references to non-existent sections | Case C / nomination contradictions; CROSS_FILE_CONTRADICTION |
| **PREV-FORBID-03** | **Silent precedence assumptions** — Case A/B evaluated before Case C without explicit ordering text | PRECEDENCE_MISMATCH; ENFORCER_DRIFT |
| **PREV-FORBID-04** | **Hidden authority inference paths** — nomination, snapshots, or review outcomes implied to satisfy authorization | NOMINATION_AUTHORITY_LEAKAGE; AUTHORITY_DRIFT |
| **PREV-FORBID-05** | **Parallel ownership maps** — authority owners defined outside catalog map table | DRIFT-06; AUTHORITY_DRIFT |
| **PREV-FORBID-06** | **Operational wording for non-operational artifacts** — "Nomination authorizes", "Case C grants permission" | CASE_C_MISCLASSIFICATION; ONTOLOGY_DRIFT |
| **PREV-FORBID-07** | **Fourth map row or authority type** — even as "informational" table row | CATALOG_MAP_VIOLATION |
| **PREV-FORBID-08** | **Design evolution in a drift-fix PR** — new concepts smuggled as "clarification" | Violates remediation contract; secondary drift |
| **PREV-FORBID-09** | **Encoding or formatting-only corruption of mandated literals** — broken arrows, wrong quotes in HALT messages | False CRITICAL signal; TEXTUAL_ALIGNMENT debt |
| **PREV-FORBID-10** | **Splitting one semantic change across multiple PRs** to evade ontology review | ONTOLOGY_STABILITY violation |

---

## 5. Design-Time Review Workflow

### 5.1 When this contract applies

| Event | Prevention review required? |
| --- | --- |
| PR touching any of the four core governance files | **Yes** |
| Governance change proposal draft | **Yes** |
| Agent-authored governance batch edit | **Yes** |
| Handoff artifact template change referenced in catalog | **Yes** |
| Application code, specs, or module README only | No (unless they redefine governance) |

### 5.2 Review steps (deterministic)

1. **Scope** — List all files and sections changed.
2. **Principles** — Confirm PRINCIPLE-01 through PRINCIPLE-05 (§2).
3. **Categories** — Run PREV-DEF, PREV-XFILE, PREV-EXPL, PREV-ONT checks (§3).
4. **Patterns** — Scan for PREV-FORBID-01 through PREV-FORBID-10 (§4).
5. **Cross-file diff** — For each concept touched, read occurrences in all four core files.
6. **Sign-off** — Author or reviewer records: *"Prevention contract satisfied"* in PR description, or lists blocking items.

Prevention review does **not** replace CI. It reduces the likelihood CI fails.

### 5.3 Author checklist (minimal)

Before opening a governance PR:

- [ ] Three operational types unchanged; no new map rows
- [ ] Case C and Nomination Record labeled non-operational everywhere introduced
- [ ] HALT precedence Case C → Case A → Case B explicit in policy and enforcer if either touched
- [ ] Mandated HALT messages match execution-policy exactly in enforcer if enforcer touched
- [ ] All cross-references resolve to existing sections
- [ ] No PREV-FORBID patterns introduced
- [ ] Document Control versions updated if normative content changed

---

## 6. Integration Boundary

### 6.1 Design and review time only

This contract is evaluated at **design time** and **review time** only:

- during governance authoring,
- in PR review checklists,
- in governance change proposal review,
- in agent instructions before writing to core governance paths.

It is **not** executed as part of the `governance-guard` CI job unless a future tooling layer explicitly adopts it as an optional lint — which would be a separate spec change **outside** this document.

### 6.2 Prevents generation; does not detect

| Layer | Role |
| --- | --- |
| **Prevention (this contract)** | Stop bad edits before merge |
| **Consistency test spec** | Define what drift looks like |
| **Guard layer** | Automate detection; PASS/FAIL |
| **Triage** | Classify findings |
| **Remediation** | Guide fixes after failure |

Prevention failure discovered **after** merge is handled by detection and remediation — not by retroactive application of this contract to waive CI.

### 6.3 Does not override downstream layers

This contract:

- does **not** modify `governance-guard-layer-spec.md`,
- does **not** modify `governance-drift-triage-spec.md`,
- does **not** modify `governance-drift-remediation-contract.md`,
- does **not** modify `governance-consistency-test-spec.md`,
- does **not** modify `execution-policy.md` or `governance-enforcer.md` enforcement semantics,
- does **not** introduce new governance concepts, authority types, HALT cases, or map rows.

Compliance with prevention is **necessary for quality**; non-compliance does **not** auto-pass CI. CI remains authoritative for merge blocking.

### 6.4 Relationship to remediation

| Phase | Contract |
| --- | --- |
| Before drift exists | **Prevention** (this document) |
| After drift detected | **Remediation** (`governance-drift-remediation-contract.md`) |

Authors should prefer prevention compliance to avoid remediation cycles. Remediation categories (TEXTUAL_ALIGNMENT, BOUNDARY_RESTORATION, etc.) are **reactive**; prevention rules are **proactive** mirrors of the same invariants.

### 6.5 Core file scope

Prevention applies to edits under:

| Path | Layer |
| --- | --- |
| `.specify/governance/_meta/authority-model.md` | Ontology |
| `.specify/governance/execution-policy.md` | Policy |
| `.specify/governance/governance-enforcer.md` | Enforcement |
| `.specify/docs/catalog-decisions.md` | Mapping |

---

## 7. Mapping to Downstream Detection (Informational)

Prevention rules align with downstream detection to show **what to avoid at source**. This table is informational only; it does not extend detection rules.

| Prevention rule | Typical downstream signal if violated |
| --- | --- |
| PREV-DEF-01, PREV-ONT-02 | INV-01, DRIFT-03, AUTHORITY_DRIFT |
| PREV-EXPL-01, PREV-FORBID-04 | INV-09, DRIFT-02, NOMINATION_AUTHORITY_LEAKAGE |
| PREV-FORBID-03, PREV-XFILE-03 | INV-06, DRIFT-04, DRIFT-08, PRECEDENCE_MISMATCH |
| PREV-FORBID-06 | INV-02, DRIFT-01, CASE_C_MISCLASSIFICATION |
| PREV-ONT-04, PREV-FORBID-07 | INV-07, DRIFT-05, CATALOG_MAP_VIOLATION |
| PREV-FORBID-02, PREV-XFILE-04 | DRIFT-10, CROSS_FILE_CONTRADICTION |
| PREV-FORBID-05 | DRIFT-06 |

Satisfying prevention does **not** guarantee CI PASS — it reduces probability of failure.

---

## 8. Safety Constraints (Normative)

| ID | Rule |
| --- | --- |
| **PREV-SAFE-01** | Prevention MUST NOT introduce new governance concepts or authority types. |
| **PREV-SAFE-02** | Prevention MUST NOT override CI, triage, remediation, or runtime enforcement. |
| **PREV-SAFE-03** | Prevention MUST use only the four rule categories in §3. |
| **PREV-SAFE-04** | Prevention review failure MUST block merge by process discipline — not by bypassing CI when CI runs. |
| **PREV-SAFE-05** | Agents and authors MUST cite prevention checklist completion when editing core governance files. |

---

## 9. Document Control

- **Version:** 1.0.0
- **Status:** ACTIVE
- **Owner:** DormSys Architecture Team (document maintenance only)
- **Related:**
  - `.specify/governance/tests/governance-consistency-test-spec.md`
  - `.specify/governance/ci/governance-guard-layer-spec.md`
  - `.specify/governance/ci/governance-drift-triage-spec.md`
  - `.specify/governance/ci/governance-drift-remediation-contract.md`

This document defines design-time prevention discipline only. It does not modify detection, CI decisioning, triage, remediation, or enforcement semantics.

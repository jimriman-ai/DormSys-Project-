---
artifact_type: governance_alignment_plan
target_spec: spec04
decision_ref: spec04-governance-decision.md
authority_level: alignment_planning
execution_authority: none
mutation_permission: none
status: PLAN_READY_FOR_APPROVAL
timestamp: 2026-07-12
---

# Spec04 Governance Alignment Plan

## 1. Artifact Metadata

| Field | Value |
| ----- | ----- |
| Artifact path | `.specify/docs/plans/spec04-alignment-plan.md` |
| Artifact type | `governance_alignment_plan` |
| Target Spec | `spec04` (`004-accommodation-resource`) |
| Decision reference | `.specify/docs/decision/spec04-governance-decision.md` (`DECISION_FINALIZED`) |
| Authority level | `alignment_planning` |
| Execution authority | `none` — **plan only; not an authorization to mutate** |
| Mutation permission | `none` (this plan does not execute changes) |
| Recorded | 2026-07-12 |

**Purpose:** Blueprint for a future Alignment Phase that synchronizes Spec04-related **documentary** artifacts with the finalized GDR. Sequence: **Plan → Human Approval → Controlled Execution** (separate implementation prompt).

---

## 2. Scope of Alignment

### In scope (documentary mutation targets — when separately authorized)

| File | Alignment intent |
| ---- | ---------------- |
| `specs/004-accommodation-resource/spec.md` | Header/metadata alignment (Spec04-local layered labels); append Governance & Evolution Notes (OA-04-01 supersession note; residual manifest); update Governance Traceability footer pointers |
| `.specify/docs/spec-catalog.md` | Spec04 Status + Open Questions / Notes cells to reflect composite state |
| `specs/004-accommodation-resource/tasks.md` | **Optional but recommended:** Status header only — recognize backend closeout vs checklist drift (no mass checkbox rewrite required in this plan) |

### Explicitly out of scope

| Item | Rule |
| ---- | ---- |
| Backend / application / domain / infrastructure / presentation **code** | **NO change to backend code** |
| Original OA-04-01 decision body, FR-003/FR-004 requirement text, US1 acceptance scenario wording | **NO change to original requirement text (OA-04-01 body)** |
| `plan.md`, contracts (unless a later addendum plan) | Not required for this alignment pass |
| Residual **implementation** (Auth, UI, Allocation integration, etc.) | Deferred; not authorized by alignment |
| Global metadata schema (`governance_phase`, `product_status` for all specs) | **Forbidden** — Spec04-local labels only (GDR Decision 2) |
| Historical validation / review / GDR files | Do not rewrite |

---

## 3. Step-by-Step Execution Sequence

*Do not execute these steps under this plan alone. Execution requires a separate implementation prompt after human approval.*

### Step A — Spec04 Metadata Alignment (`spec.md` header)

**Target:** `specs/004-accommodation-resource/spec.md` header block (lines currently stating Planning / implementation not authorized).

**Strict rule:** Spec04-local layered labels only. Do **not** introduce global YAML schema fields for all specs. Prefer a visible markdown Status block (same style as today’s `**Status**:` line), not a new repo-wide frontmatter convention.

**Planned replacement Status representation (exact draft for execution):**

```markdown
**Status**: Spec04-local composite (see GDR `spec04-governance-decision.md`)

| Layer (Spec04-local) | Value |
| -------------------- | ----- |
| Planning | Complete |
| Backend | CLOSED (`SPEC04_BACKEND_CLOSED` — Phases 1–4) |
| Product | PENDING_RESIDUAL |
| Documentation / mirrors | ALIGNMENT_IN_PROGRESS → set to `ALIGNED` when Steps A–D of this plan complete |

**Catalog**: spec04 — see `spec-catalog.md` (composite Status; not “Planning Authorized” alone)

**Governance decision**: [`.specify/docs/decision/spec04-governance-decision.md`](../../.specify/docs/decision/spec04-governance-decision.md)
```

**Also update (header adjacency):**

- Replace or qualify the line that says implementation not authorized so it does not contradict Backend CLOSED; residual work remains unauthorized separately under Product PENDING_RESIDUAL.
- Keep Feature Branch / Created / Depends on / Input / Normative boundary lines unless they conflict; do not invent unrelated fields.

**Post-Step-A Documentation label:** After Steps A–D succeed, Documentation / mirrors value is recognized as `ALIGNED` (GDR Decision 2’s `DRIFT_PRESENT` was the pre-alignment observation).

---

### Step B — Documentation Amendment (Supersede Note)

**Target:** Append a new section near the end of `spec.md` (after Governance Traceability or before Document Control), e.g. `## Governance & Evolution Notes`.

**Strict rule:** Note-based only. **Do not rewrite** the OA-04-01 body, FR-003/FR-004, entity list, or US1 acceptance text.

**Draft wording for execution (exact text to append):**

```markdown
## Governance & Evolution Notes

**Authority:** Recorded in [`.specify/docs/decision/spec04-governance-decision.md`](../../.specify/docs/decision/spec04-governance-decision.md) (Decision 3). This note does not rewrite the historical OA-04-01 decision body below.

### Floor hierarchy — OA-04-01 and accepted backend evolution

OA-04-01 is identified as superseded by the accepted backend domain evolution (Floor Aggregate: Dormitory → Building → Floor → Room → Bed), as recorded in Spec04 Implementation Authorization / backend design and `SPEC04_BACKEND_CLOSED`.

The original OA-04-01 decision text (Floor as a Room attribute; no separate Floor aggregate) is retained for decision-chain history. Normative understanding for Spec04 alignment purposes follows the accepted Floor Aggregate evolution. Consumers and future amendments should treat the Floor Aggregate hierarchy as the current domain disposition pending any later Design Approval that reopens this topic.

**Evidence pointers:** `.specify/docs/handoff/spec04-implementation-authorization.md`; `.specify/docs/handoff/spec04-backend-closeout.md`.
```

**Governance Traceability table (footer amendment):** Add rows pointing to:

- `spec04-governance-decision.md` (DECISION_FINALIZED)
- `spec04-backend-closeout.md` (`SPEC04_BACKEND_CLOSED`)
- `spec04-implementation-authorization.md` (`IMPLEMENTATION_AUTHORIZED` — historical)
- This alignment plan (after execution, optional “alignment applied” note)

Do **not** remove the historical planning-authorization row; qualify it as historical planning scope.

---

### Step C — Residual Scope Mapping

**Target:** Same `## Governance & Evolution Notes` section in `spec.md` (preferred single manifest), subsection for residuals per GDR Decision 4.

**Draft wording for execution:**

```markdown
### Residual scope (deferred — not cancelled)

Per GDR Decision 4 and `spec04-backend-closeout.md` §6, the following are identified as deferred scope. Ownership is transferred to future waves/specs; items are **not** considered cancelled.

| Residual item | Disposition |
| ------------- | ----------- |
| Authorization / policies / roles / guards for Dormitory surfaces | `DEFERRED_TO_FUTURE_WAVE` |
| Livewire / Blade / UI | `DEFERRED_TO_FUTURE_WAVE` |
| HTTP / API / controllers / FormRequests | `DEFERRED_TO_FUTURE_WAVE` |
| Allocation ↔ Dormitory integration (`bedExists` / `isBedAssignable`, related Application Read extensions) | `DEFERRED_TO_FUTURE_WAVE` |
| CheckIn/CheckOut ↔ Dormitory occupancy request wiring | `DEFERRED_TO_FUTURE_WAVE` |
| Workflow ownership / orchestration inside Dormitory | `DEFERRED_TO_FUTURE_WAVE` |
| Events / listeners / jobs (unless separately approved) | `DEFERRED_TO_FUTURE_WAVE` |
| External system adapters | `DEFERRED_TO_FUTURE_WAVE` |
| Voucher / billing / payment / notification behavior | `DEFERRED_TO_FUTURE_WAVE` |
| Broad Request Feature-suite dormitory fixture remediation | `DEFERRED_TO_FUTURE_WAVE` |

Successor Spec / Wave ids are **not** assigned by this alignment; nomination remains a separate governance act.
```

**Optional companion:** If reviewers prefer catalog-only residual visibility, mirror a one-line pointer in catalog Open Questions: “Residuals: see `spec.md` Governance & Evolution Notes — `DEFERRED_TO_FUTURE_WAVE`.”

---

### Step D — Catalog Synchronization

**Target:** `.specify/docs/spec-catalog.md`

**Locations:**

1. Hard Freeze / status-after-freeze summary row for `spec04` (currently Planning Authorized).
2. Spec Inventory row for `spec04` — **Status** and **Open Questions** columns.

**Planned Status representation (composite; Spec04-local meaning):**

```text
Backend CLOSED / Product PENDING_RESIDUAL
```

**Planned Open Questions / Notes cell (draft):**

```text
**Composite (GDR):** Planning Complete; Backend CLOSED (`SPEC04_BACKEND_CLOSED`); Product PENDING_RESIDUAL; Documentation aligned per alignment plan. **Domain:** OA-04-01 identified as superseded by Floor Aggregate — see `specs/004-accommodation-resource/spec.md` Governance & Evolution Notes (body retained). **Residuals:** `DEFERRED_TO_FUTURE_WAVE` (Auth, UI, Allocation integration, CheckIn wiring, etc.) — not cancelled. **Evidence:** `docs/decision/spec04-governance-decision.md`; `handoff/spec04-backend-closeout.md`. **Hold:** residual implementation until separate authorization. Catalog hierarchy “Open (planning)” note retired as stale.
```

**Changelog:** Append a dated catalog version note (e.g. next patch under Document Control / changelog) stating Spec04 Status synchronized to GDR composite labels — informational only; does not grant IA.

**Do not** change other specs’ Status cells in this pass.

---

### Step E (Optional) — `tasks.md` header alignment

**Target:** `specs/004-accommodation-resource/tasks.md` Status line only.

**Draft:**

```markdown
**Status**: Backend Phases 1–4 closed (`SPEC04_BACKEND_CLOSED`). Checklist below may lag phase-acceptance handoffs; residual product work remains `PENDING_RESIDUAL` / `DEFERRED_TO_FUTURE_WAVE`. See GDR `spec04-governance-decision.md`. Implementation of residuals **not** authorized by documentary alignment.
```

**Out of scope for this plan:** Bulk-checking `[x]` all historical tasks (optional later hygiene; not required to close Documentation drift if Status + GDR pointers are clear).

---

## 4. Guardrails & Constraints

| Guardrail | Statement |
| --------- | --------- |
| Code | **NO change to backend code.** |
| Requirements body | **NO change to original requirement text (OA-04-01 body).** Also no rewrite of FR-003/FR-004 or US1 acceptance scenario text in this alignment. |
| Schema | No global `governance_phase` / `product_status` fields for all specs. |
| Authority | This plan has `execution_authority: none`. |
| Execution | **Execution of this plan requires a separate implementation prompt** after human approval. |
| Residuals | Alignment lists and labels residuals; it does not implement them or cancel them. |
| Phases 1–4 | Do not reopen accepted backend phases. |
| GDR / validation / review | Do not alter historical governance records except optional forward-pointer from Traceability. |

---

## 5. Approval & Execution Gate

| Stage | Actor | Outcome |
| ----- | ----- | ------- |
| 1. Plan | This artifact | `PLAN_READY_FOR_APPROVAL` |
| 2. Human Approval | Governance Review | Approve / request changes to this plan only |
| 3. Controlled Execution | Separate implementation prompt | Mutate only in-scope files per Steps A–D (and optional E) |
| 4. Closeout (optional follow-on) | Separate note | Record that Documentation layer is `ALIGNED`; update GDR execution posture only if a later gate requires it |

**Until Stage 3 is explicitly authorized:** repository Spec04 package and catalog remain in pre-alignment (DRIFT_PRESENT) state relative to the GDR.

---

## Document Control

| Field | Value |
| ----- | ----- |
| Version | 1.0.0 |
| Status | `PLAN_READY_FOR_APPROVAL` |
| Upstream | `.specify/docs/decision/spec04-governance-decision.md` |
| Last updated | 2026-07-12 |

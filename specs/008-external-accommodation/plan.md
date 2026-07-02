# Planning Document: External Accommodation (spec08)

**Branch**: `008-external-accommodation` | **Date**: 2026-07-01 | **Spec**: [spec.md](./spec.md)

**Input**: Voucher bounded context — eligibility evaluation and issuance lifecycle for external dormitory accommodation outcomes; **CD-016** ownership; **R8** upstream trigger direction (Lottery / Allocation → Voucher).

**Governance**: Specification stabilized; planning translation only. **Not** Design Approval. **Not** Implementation Authorization. **No** `tasks.md`. spec07 remains **CLOSED**.

---

## Summary

This plan decomposes [spec.md](./spec.md) into logical planning clusters and conceptual domain flows. It introduces **no new capabilities**, **no boundary changes**, and **no resolution** of open items **UD-03** or **UD-08**.

**Voucher** is the sole owner of:

- eligibility evaluation (policy and outcomes)
- issuance lifecycle (codes, validity, archival)
- read inquiry for employees, operators, and downstream consumers

**Upstream (facts only):** Lottery (external draw outcomes), Allocation (unfulfilled-accommodation triggers).

**Downstream (consumers only):** Audit, Notification, Reporting — no lifecycle mutation authority.

---

## Frozen Boundaries (unchanged)

### CD-016 — Voucher Eligibility Ownership

| Rule | Planning implication |
| ---- | -------------------- |
| Voucher owns eligibility evaluation | Eligibility cluster is wholly within Voucher planning scope |
| Voucher owns issuance lifecycle | Issuance and lifecycle clusters are wholly within Voucher planning scope |
| Lottery supplies facts | Lottery winner path is a trigger source, not a policy owner |
| Allocation supplies facts | Unfulfilled-accommodation path is a trigger source, not a policy owner |

### R8 — Lottery / Allocation → Voucher

| Rule | Planning implication |
| ---- | -------------------- |
| Direction | Upstream → Voucher only; no reverse lifecycle ownership |
| Integration character | Trigger facts cross the boundary; Voucher retains decision authority |
| Open | **UD-03** — trigger fact bundle shape not defined in this plan |

---

## Requirement Grouping (Planning Clusters)

Clusters map **one-to-one** to spec capabilities. No new requirements are introduced.

| Cluster ID | Name | Spec source | Functional requirements |
| ---------- | ---- | ----------- | ---------------------- |
| **PC-01** | Trigger intake | US1, US3, US4; R8; OA-08-03, OA-08-04, OA-08-07 | FR-006, FR-011 |
| **PC-02** | Eligibility evaluation | US1; edge cases | FR-002, FR-005, FR-007, FR-016 |
| **PC-03** | Issuance lifecycle | US2 | FR-001, FR-003, FR-004, FR-008, FR-009, FR-015 |
| **PC-04** | External lottery winner path | US3; BR-09; OA-06-05 (spec06 deferral) | FR-004, FR-005, FR-006 (lottery trigger subset) |
| **PC-05** | Reserve promotion | US4; BR-10 | FR-010 |
| **PC-06** | Voucher inquiry (read) | US5; CD-017 | FR-014 |
| **PC-07** | Transition recording | US2, US4; AP-06 | FR-012, FR-013 |
| **PC-08** | Overlap and conflict rules | Edge cases; OA-08-01 | FR-011 (shared with PC-01); BR-02 interpretation |

### User story → cluster mapping

| User story | Priority | Primary clusters |
| ---------- | -------- | ---------------- |
| US1 — Eligibility evaluation | P1 | PC-01, PC-02, PC-08 |
| US2 — Issuance lifecycle | P1 | PC-02, PC-03, PC-07 |
| US3 — External lottery winner path | P1 | PC-01, PC-02, PC-03, PC-04 |
| US4 — Reserve promotion | P2 | PC-01, PC-02, PC-03, PC-05, PC-07 |
| US5 — Voucher read access | P2 | PC-06 |

### Success criteria → cluster mapping

| Success criterion | Cluster(s) |
| ----------------- | ---------- |
| SC-001 | PC-04, PC-03 |
| SC-002 | PC-01, PC-08 |
| SC-003 | PC-03 |
| SC-004 | PC-06 |
| SC-005 | PC-01 |
| SC-006 | PC-05, PC-03 |

---

## Conceptual Domain Modules

Conceptual groupings for planning only — **not** implementation modules, services, or code structure.

| Module (conceptual) | Responsibility | Owns (per spec) | Does not own |
| ------------------- | -------------- | --------------- | ------------ |
| **Trigger intake** | Accept and correlate upstream trigger facts | Correlation identifiers; idempotency gate | Upstream operational records; eligibility policy |
| **Eligibility evaluation** | Evaluate external-accommodation eligibility | Outcomes (`Eligible`, `Ineligible`, `Deferred`); reason codes | Lottery draw rules; allocation assignment |
| **Issuance** | Generate unique voucher codes; establish validity | Code uniqueness; `Issued` state entry | Physical bed assignment |
| **Lifecycle management** | Govern post-issuance states | `Expired`, `Cancelled`, `Superseded`; archival | Check-in/out; internal occupancy |
| **Reserve promotion handling** | Process promotion triggers for external lottery | Supersession of prior voucher; reserve evaluation | Reserve list ownership (Lottery) |
| **Voucher inquiry** | Read-only access to voucher status | Inquiry responses without mutation | Reporting projections; notification delivery |
| **Transition recording** | Capture audit-relevant lifecycle changes | Material transition records for downstream | Audit storage (spec10) |

### Key entities (from spec — unchanged)

| Entity | Role in plan |
| ------ | ------------ |
| **Voucher** | Central credential record; carries lifecycle state |
| **VoucherEligibilityOutcome** | Evaluation result attached to trigger processing |
| **VoucherIssuanceTrigger** | Inbound fact bundle with correlation identifier |
| **VoucherValidityPeriod** | Effective stay window for issued credential |

---

## Lifecycle Transition Logic (abstract)

Planning vocabulary only. Exact transition rules remain unspecified (per spec.md).

### Evaluation outcomes (pre-issuance)

```
Trigger received
    → evaluate
        → Eligible      (may proceed to issuance)
        → Ineligible    (does not proceed to issuance)
        → Deferred      (may retry when additional facts available)
```

### Lifecycle states (voucher record)

```
PendingEvaluation
    → (Eligible outcome) → Issued
    → Issued → Expired        (validity window ended)
    → Issued → Cancelled      (voided: decline, administrative cancel)
    → Issued → Superseded     (replaced: reserve promotion, corrected re-issuance)
```

### Abstract transition constraints (from spec acceptance scenarios)

| Constraint | Source |
| ---------- | ------ |
| Re-issuance from terminal state requires new `Eligible` evaluation | US2 |
| Reserve promotion may `Cancel` or `Supersede` active winner voucher before reserve issuance | US4 |
| Duplicate correlation identifier must not produce duplicate issuance | US1, BR-14 |
| Internal dormitory triggers rejected or ignored | US3, FR-005 |
| Expiration applies per validity policy (**UD-08** open) | US2, FR-009 |

---

## Domain Flow Map (conceptual)

### Primary flow — external lottery winner

```
Lottery (spec06)
    │  external-winner trigger facts (after draw completion)
    ▼
[PC-01 Trigger intake]
    ▼
[PC-02 Eligibility evaluation] ──Ineligible──► stop (reason recorded)
    │ Eligible
    ▼
[PC-03 Issuance] ──► Issued voucher
    │
    ├──► [PC-07 Transition recording] ──► Audit / Notification (downstream)
    └──► [PC-06 Voucher inquiry] ◄── employee / operator / Reporting (read-only)
```

### Secondary flow — unfulfilled accommodation (allocation-related trigger)

```
Allocation (spec07, closed — facts only)
    │  unfulfilled-accommodation trigger facts
    ▼
[PC-01 Trigger intake]
    ▼
[PC-02 Eligibility evaluation]  ← Voucher policy applies; facts do not bypass
    │ Eligible
    ▼
[PC-03 Issuance]
```

### Reserve promotion flow (external lottery)

```
Winner decline / ineligibility
    │  reserve promotion trigger facts
    ▼
[PC-05 Reserve promotion handling]
    │  supersede/cancel winner voucher if still active
    ▼
[PC-02 Eligibility evaluation] (reserve)
    │ Eligible
    ▼
[PC-03 Issuance] (reserve voucher)
    │
    └──► no remaining reserves → explicit no-issuance outcome + audit record
```

### External classification check (cross-cutting)

```
Trigger facts include dormitory reference
    ▼
[PC-02 Eligibility evaluation]
    │  confirm external classification (OA-08-02; accommodation catalog when available)
    └── non-external or missing reference → Ineligible
```

### Boundary enforcement (no reverse flow)

```
Voucher ──X──► Lottery lifecycle ownership
Voucher ──X──► Allocation assignment ownership
Voucher ──X──► Request approval ownership
Voucher ──X──► Dormitory physical inventory ownership
Reporting / Notification / Audit ──X──► Voucher lifecycle mutation
```

---

## Open Questions Registry

Items carried from spec.md. **Not resolved in this plan.**

| ID | Question | Spec reference | Planning status |
| -- | -------- | -------------- | --------------- |
| **UD-03** | Upstream trigger fact bundle shape (lottery winner facts; allocation-related triggers) | spec.md § Open Planning Items; R8 | **Open** — PC-01 blocked on shape definition at design phase |
| **UD-08** | Voucher expiration and renewal policy detail | spec.md § Open Planning Items; FR-009 | **Open** — PC-03 lifecycle rules partially unspecified |

### Recorded assumptions (unchanged — not open questions)

| ID | Assumption | Affects clusters |
| -- | ---------- | ---------------- |
| OA-08-01 | One active voucher per overlapping stay (BR-02 external interpretation) | PC-08 |
| OA-08-02 | External dormitory classification from accommodation catalog (spec04) when available | PC-02 |
| OA-08-03 | Lottery facts after draw completion; no voucher policy in Lottery | PC-04 |
| OA-08-04 | Allocation supplies facts only | PC-01 |
| OA-08-05 | Employee/operator presentation deferred | Out of scope |
| OA-08-06 | Validity defaults to stay dates in trigger facts when present | PC-03 |
| OA-08-07 | Unfulfilled approved requests may supply triggers per organizational policy | PC-01 |

---

## Dependency Map (non-technical)

Relationships at bounded-context level only. No implementation coupling implied.

| Upstream / peer | Relationship to Voucher | What Voucher receives | What Voucher does not receive |
| --------------- | ----------------------- | --------------------- | ----------------------------- |
| **spec01** Foundation | Platform conventions | — | — |
| **spec05** Request | Context in trigger facts | Approved-request references | Request lifecycle authority |
| **spec06** Lottery | Trigger supplier (R8) | External draw outcome facts | Draw rules; reserve list ownership |
| **spec04** Accommodation Resource | Classification reference | External dormitory classification (when available) | Physical inventory |
| **spec07** Allocation (**closed**) | Trigger supplier (R8) | Unfulfilled-accommodation facts | Assignment authority; check-in/out |
| **spec09** Notification | Downstream consumer | — (receives transition records) | — |
| **spec10** Audit | Downstream consumer | — (receives transition records) | — |
| **spec11** Reporting | Downstream consumer | — (read-only inquiry) | Write authority (CD-017) |

### Dependency direction summary

```
spec05, spec06, spec07 ──trigger facts──► Voucher (spec08)
spec04 ──classification read (optional)──► Voucher (spec08)
Voucher (spec08) ──transition records──► spec09, spec10
Voucher (spec08) ──read inquiry──► spec11, employees, operators
```

---

## Out of Scope (reaffirmed from spec)

Planning does not cover:

- Internal assignment, check-in/out, physical dormitory modeling
- Lottery execution, request lifecycle, notification delivery, audit storage, reporting projections
- Employee/operator presentation interfaces
- Third-party operator integrations beyond voucher issuance
- Payment or billing
- Reopening spec07

---

## Planning Artifacts Status

| Artifact | Status |
| -------- | ------ |
| spec.md | ✅ Stabilized |
| plan.md | ✅ This document |
| tasks.md | ⬜ Not created (forbidden at this step) |
| data-model.md | ⬜ Not created (forbidden at this step) |
| contracts/ | ⬜ Not created (forbidden at this step) |
| Design Approval | ⬜ Not granted |
| Implementation Authorization | ⬜ Not granted |

---

**End of plan.**

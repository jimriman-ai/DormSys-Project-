# Decision Freeze Log — DormSys

> Purpose: Record frozen decisions and open questions so they are not forgotten.
> This is NOT a final ADR; it is a working record.

## General Status

- **Last updated:** 2026-06-26
- **Source of Truth:** `.specify/memory/constitution.md` v1.3.0 + `catalog-decisions.md` v2.2.0
- **Governing principle:** Catalog decisions (CD-*) supersede informal freeze notes when they conflict.

---

## Closed Items (Resolved — Do Not Reopen Without Catalog Amendment)

### RequestApproval ownership — **CLOSED (CD-010)**

- **Decision:** Request owns `RequestApproval` entity, approval state, and history.
- **Workflow (deferred):** When activated, owns approval chain definition, routing, and transition rules only.
- **Reference:** `catalog-decisions.md` CD-010; `context-map.md` OQ-03 CLOSED; `spec-catalog.md` Deferred Components.

> Previously listed as open below — **removed 2026-06-26** after Hard Freeze v1.0.0.

---

## Frozen Items (Do Not Touch Without Unfreeze Rationale)

1. **Workflow scaffold**
   - Workflow module scaffold may exist; **orchestration is deferred** per spec-catalog.
   - Do not implement active approval engine until Workflow activation criteria are met (CD-010).
2. **Constitution business rules (BR-01–BR-14)**
   - Non-negotiable; changes require Constitution version bump.
3. **CD-012 Identity ↔ Employee boundary**
   - `identity_id` immutable UUID, no FK; Identity supplier read contract only.

---

## Open Questions (Require User / Catalog Decision)

- [ ] Resolve internal contradiction in `سند معماری و استک فنی.md` (RequestApproval line references) — **superseded at catalog level by CD-010**; source doc alignment optional.
- [ ] OQ-06 — CheckIn/CheckOut module promotion (`spec07` planning).
- [ ] OQ-07 — Voucher eligibility boundary (`spec08`).
- [ ] OQ-08 — Reporting projection scope (`spec11`).

---

## Identified Drift (Documented, Non-Blocking)

| Topic | Status |
|-------|--------|
| Laravel version | Aligned to 13.x per ADR-005 |
| Table naming `tbl_` vs `{module}_*` | **Resolved** — ADR-006 |
| Kernel path `app/Shared` vs `app/Support` | **Resolved** — `app/Support/` is canonical (spec01/spec02) |
| Dependent ∈ Employee vs Request table | **Resolved** — CD-009; Constitution §11 updated |

---

## Important Note for Any Model/Assistant

> For boundary ownership questions, consult **`catalog-decisions.md`** first, then **`spec-catalog.md`**, then Constitution. This freeze log is a convenience index — not supreme authority.

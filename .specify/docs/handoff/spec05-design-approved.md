# spec05 Design Approval Record

**Recorded:** 2026-06-23  
**Authority:** Product / Tech governance  

**Checkpoint:** `spec05-design-review` = **PASS**  
**Tag:** `spec05-design-approved`

---

## Decision

| Level | Status |
| ----- | ------ |
| **Design** | **Approved** |
| **Tasks** (`tasks.md` / `/speckit-tasks`) | **Authorized** (after this tag) |
| **Implementation** | **Not authorized** |
| **Code** (migrations, module, tests) | **Not authorized** |

**spec05 Request Management — Phase 1 design is approved.**

Implementation requires separate **`spec05-implementation-authorization.md`** after `tasks.md` review.

---

## Design artifacts (approved)

| Artifact | Path |
| -------- | ---- |
| Specification | `specs/005-request-management/spec.md` |
| Plan | `specs/005-request-management/plan.md` |
| Research | `specs/005-request-management/research.md` |
| Data model | `specs/005-request-management/data-model.md` |
| Contracts | `specs/005-request-management/contracts/` |
| Quickstart | `specs/005-request-management/quickstart.md` |
| Planning auth | `.specify/docs/handoff/spec05-planning-authorization.md` |

---

## Design review gates

| Gate | Result |
| ---- | ------ |
| Architecture boundary | ✅ PASS |
| Data ownership | ✅ PASS |
| Contracts (read-only hardening) | ✅ PASS |
| Lifecycle model (approval phase only) | ✅ PASS |
| Dependency direction | ✅ PASS |

**Contract hardening applied:**

- `RequestReadContract` — read-only projections; no lifecycle mutation via supplier API
- `PendingRequestReadPort` — query-only port; must never become a command boundary (OA-05-09)

---

## Boundary decisions (frozen at design)

| Decision | Design resolution |
| -------- | ----------------- |
| CD-009 | `request_dependent_snapshots` — immutable; no Dependent FK |
| CD-010 | `request_approvals` append-only; inline routing; Workflow deferred |
| CD-013 | Enforce at submit via `EmployeeEligibilityContract` |
| OA-05-01 | Terminal states: `approved`, `rejected`, `cancelled` — no spec07 states in spec05 |
| OA-05-09 | `PendingRequestReadAdapter` — `hasPendingRequest()` only |
| R-04 | Pending includes `Draft` (DR-05-01 **adopted**) |
| R-07 | Full approval-phase transition matrix in `research.md` |

---

## Design review items — disposition

| ID | Question | Disposition for implementation planning |
| -- | -------- | --------------------------------------- |
| DR-05-01 | Draft counts as pending? | **Adopted:** yes (R-04) |
| DR-05-02 | Persist `Submitted` state? | **Adopted:** yes; auto-advance in same transaction |
| DR-05-03 | Snapshot `relationship` enum | **Provisional:** `spouse`, `child`, `parent`, `other` — align with spec03 US3 when live |
| DR-05-04 | System actor for auto-approval | **Open** — resolve at implementation (Shared `SystemActorId` recommended) |
| DR-05-05 | Validate Mission member employees | **Adopted:** yes when `EmployeeReadContract` available |
| DR-05-06 | LotteryRegistration requires program? | **Rejected for spec05** — spec06 owns linkage |

---

## Protected status (unchanged)

| Scope | State |
| ----- | ----- |
| spec04 | **Frozen** — tag `spec04-design-approved`; implementation hold |
| spec03 US3 / US4 | **Hold** — Wave 1B (FamilyDirect) gated |
| spec06–spec11 | **Not authorized** |
| Workflow | **Deferred** |

---

## Implementation waves (from plan — not authorized yet)

| Wave | Scope |
| ---- | ----- |
| Wave 1A | US1–US3 — Personal, lifecycle, four-stage approval |
| Wave 1B | US4 — FamilyDirect (spec03 US3 or fixtures) |
| Wave 1C | US5–US6 — Mission, LotteryRegistration |

---

## References

- [`spec05-planning-authorization.md`](./spec05-planning-authorization.md)
- `spec-catalog.md` v1.0.6 — spec05 Planning Authorized
- [`specs/005-request-management/research.md`](../../specs/005-request-management/research.md)
- [`specs/005-request-management/data-model.md`](../../specs/005-request-management/data-model.md)

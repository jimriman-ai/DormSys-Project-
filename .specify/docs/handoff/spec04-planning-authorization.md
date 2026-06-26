# spec04 Planning Authorization Record

**Recorded:** 2026-06-23  
**Baseline:** `spec02-baseline` @ `ef73c11` (tag: `spec03-wave1b-complete`)  
**Authority:** Product / Tech governance  

---

## Decision

**spec04 Accommodation Resource — planning is authorized.**

**Implementation is not authorized** until `spec.md`, `plan.md`, and `tasks.md` are approved under a separate implementation authorization.

---

## Rationale

- Catalog ordering guidance places spec04 after spec03 Wave 1A + Wave 1B (`spec-catalog.md` §Ordering).
- spec03 supplier baseline (US1 + US2) is complete; Employee BC does not block spec04 planning.
- Dormitory BC (`context-map.md`) is on the critical path for spec07 per **CD-014**.
- Physical accommodation modeling should be specified before Request/Allocation implementation consumes it.

---

## Authorization scope

| Allowed | Not allowed |
| ------- | ----------- |
| `/speckit-specify` for spec04 | Dormitory module implementation |
| `/speckit-plan` for spec04 | spec05 implementation |
| `/speckit-tasks` for spec04 | Workflow module activation |
| Resolve spec04 open questions (including building/floor hierarchy) in spec or carry documented in spec | Reopening spec03 US2 (T027–T034) |
| CD-014 refinement at specification level only (no boundary change without `catalog-decisions.md`) | US3 / US4 (spec03 Wave 1C / 1D) implementation |

---

## Dependencies (satisfied for planning)

| Dependency | Status |
| ---------- | ------ |
| spec01 Foundation | Approved |
| spec03 Employee — Wave 1A (US1) | Complete |
| spec03 Employee — Wave 1B (US2) | Complete (`spec03-wave1b-complete`) |

---

## Protected status (unchanged)

| Scope | State |
| ----- | ----- |
| spec03 Wave 1B (US2) | **Closed — Complete** |
| spec03 US3 / US4 (T035+) | **Hold — Unauthorized** |
| spec05 | **Not authorized** |
| Workflow | **Deferred** |

---

## Open questions for planning

| Topic | Source | Planning action |
| ----- | ------ | --------------- |
| Building/floor hierarchy | `spec-catalog.md` spec04 row | Resolve in spec04 `spec.md` or carry as documented assumption |
| CD-014 Dormitory vs Allocation split | `context-map.md` R7 | Refine in spec04 plan; boundary already decided — no CD change unless unfreeze |
| OQ-06 CheckIn/CheckOut | `spec07` scope | **Out of scope** for spec04 planning; document deferral only |

---

## References

- `spec-catalog.md` v1.0.5 — spec04 Planning Authorized
- `handoff/spec03-post-mvp-authorization.md` — Wave 1B complete; US3+ hold
- `context-map.md` — Dormitory bounded context (spec04)
- `catalog-decisions.md` CD-014

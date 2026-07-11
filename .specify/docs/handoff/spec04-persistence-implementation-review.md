# Spec04 Persistence Implementation Review: Accommodation Resource / Dormitory

## 1. Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `PERSISTENCE_IMPLEMENTATION_ACCEPTED` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Current gate** | Spec04 Persistence Implementation Review |
| **Reviewed phase** | Spec04 Backend Implementation Phase 2 — Persistence Layer Implementation |
| **Previous gate** | Spec04 Domain Layer Implementation Review (`DOMAIN_IMPLEMENTATION_ACCEPTED`) |
| **Review result** | `ACCEPTED` |
| **Next allowed gate** | Spec04 Backend Implementation Phase 3 — Application Layer Implementation |
| **Decision date** | 2026-07-10 |

This artifact accepts **only** the completed Persistence Layer implementation. It does **not** authorize Application, Integration, Authorization, or UI implementation by itself except as the next separately executed implementation phase.

---

## 2. Purpose

This artifact reviews whether Phase 2 Persistence Layer implementation matches the approved Spec04 persistence design and stayed inside the authorized persistence-only scope. It records implementation evidence, test evidence (including the corrected enum-constraint verification path), and approves progression to Application Layer Implementation.

---

## 3. Accepted Scope Summary

| Deliverable | Path / detail |
| ----------- | ------------- |
| Migrations | `database/migrations/modules/dormitory/` — `dormitories`, `dormitory_buildings`, `dormitory_floors`, `dormitory_rooms`, `dormitory_beds` |
| Persistence models | `app/Modules/Dormitory/Infrastructure/Persistence/Models/` — `DormitoryModel`, `BuildingModel`, `FloorModel`, `RoomModel`, `BedModel` |
| Constraints | PKs; FKs with `restrictOnDelete`; scoped uniqueness; status/occupancy CHECKs; `capacity_total >= 0`; hierarchy/availability indexes; soft deletes + audit columns |
| Repositories | None in this phase (deferred to Application phase) |
| Persistence tests | `tests/Feature/Modules/Dormitory/Persistence/DormitoryPersistenceTest.php` |

**Accepted scope remained Persistence-only.**

---

## 4. Validation Summary

| Check | Result |
| ----- | ------ |
| Hierarchy tables created | Pass |
| Dormitory persistable | Pass |
| Building/Floor/Room/Bed FK ownership | Pass |
| Scoped uniqueness | Pass |
| Non-negative room capacity | Pass |
| ResourceStatus / PhysicalOccupancyState storage | Pass |
| Invalid status/occupancy rejected at DB | Pass (via corrected raw-insert path) |
| Eloquent hierarchy relationships | Pass |
| Domain layer unchanged | Pass |

---

## 5. Test Evidence

| Command | Result |
| ------- | ------ |
| `php -d memory_limit=512M artisan test tests/Unit/Modules/Dormitory/Domain` | **31 Domain tests passed** |
| `php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Persistence` | **11 Persistence tests passed** |
| Combined | **42 tests passed total** |

### Enum-constraint verification note

The initial invalid-enum persistence test hit Eloquent enum casting and raised `ValueError` before the database CHECK constraint was reached. The test was then corrected to use raw inserts so invalid values reached the database layer directly. Final acceptance is based on the corrected test path that validates real database constraint enforcement rather than framework-level enum casting behavior.

---

## 6. Scope Protection Confirmation

Confirmed for Phase 2:

- No Application commands, queries, services, or DTOs were added.
- No Integration adapters were added.
- No Authorization policies, gates, or permission records were added.
- No UI, Livewire, Blade, or feature-contract artifacts were added.
- No Allocation, CheckIn/CheckOut, Workflow, or Voucher implementation was added.
- Repositories were intentionally deferred; models and schema cover Phase 2.

---

## 7. Domain Protection Confirmation

- Accepted Domain Layer remains locked and unchanged.
- Domain entities were not modified.
- Domain tests continue to pass (31 passed).
- Persistence models live under Infrastructure and do not replace Domain entities.
- Domain entities do not extend Eloquent.

---

## 8. Governance Decision

**DECISION:** `PERSISTENCE_IMPLEMENTATION_ACCEPTED`

**Acceptance rationale:**

- Approved persistence hierarchy is implemented.
- Foreign keys, uniqueness, indexes, and CHECK constraints are in place.
- Persistence models and relationships align with project conventions.
- Domain behavior was not changed.
- Scope remained Persistence-only.
- Corrected persistence tests validate database constraint enforcement.
- Domain + Persistence tests pass (42 total).

---

## 9. Next Gate

**NEXT GATE:** Spec04 Backend Implementation Phase 3 — Application Layer Implementation

The next phase may implement approved Application Layer read/mutation capabilities, contracts, and related tests within Spec04 backend foundation scope. Integration, Authorization (beyond what Application requires), and UI remain out of scope until their own phases.

---

## 10. Stop Boundary

- This artifact accepts Phase 2 Persistence Layer implementation only.
- It does not implement Application, Integration, Authorization, or UI.
- It does not resume Dormitory UI or Workflow UI governance.
- It does not authorize feature-contracts.
- It does not claim Spec04 backend closure.

---

## References

- [`spec04-persistence-design.md`](spec04-persistence-design.md)
- [`spec04-domain-layer-implementation-review.md`](spec04-domain-layer-implementation-review.md)
- [`spec04-implementation-authorization.md`](spec04-implementation-authorization.md)
- [`../catalog-decisions.md`](../catalog-decisions.md) — CD-014, CD-015

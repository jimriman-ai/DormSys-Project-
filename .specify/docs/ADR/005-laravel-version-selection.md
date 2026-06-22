# ADR-005: Laravel Version Selection

**Status:** Accepted  
**Date:** 2026-06-22  
**Context:** Spec01 - Technical Foundation  
**Constitutional Reference:** AP-01 Technology Stack


---

## Context

DormSys initially specified Laravel 12.x as the backend framework version.

During Phase 1 implementation, the Laravel skeleton installed was Laravel 13.x.

Since the project is a new greenfield application with no production dependencies, this version alignment decision must be documented.


---

## Decision

Adopt Laravel 13.x as the official backend framework version for DormSys.

The technology baseline is updated to:

- Laravel 13.x
- PHP 8.4+
- Livewire 3
- Alpine.js
- Tailwind CSS
- PostgreSQL 17
- Redis 7
- Laravel Sail


---

## Rationale

Laravel 13.x was selected because:

- DormSys is a new project without legacy constraints.
- The newer framework version provides the latest supported ecosystem.
- Existing Phase 1 work was already created on Laravel 13.x.
- Migration cost at this stage is minimal.
- Maintaining a single source of truth is more important than preserving the initial version.


---

## Alternatives Considered

### Option 1: Downgrade to Laravel 12.x

Pros:
- Matches original Constitution.

Cons:
- Requires rebuilding foundation.
- Creates unnecessary churn.

Reason for rejection:
The project has no legacy dependency requiring Laravel 12.


### Option 2: Keep Laravel 13.x without documentation

Pros:
- No additional work.

Cons:
- Creates architecture drift.

Reason for rejection:
Technology decisions must be traceable.


---

## Consequences

### Positive

- Official stack matches implementation.
- Future AI agents have clear guidance.
- No version ambiguity.

### Negative

- Existing documentation required updates.
- Future upgrades must consider Laravel 13 compatibility.

### Neutral

- Development workflow remains unchanged.


---

## Implementation Notes

All future tasks must target Laravel 13.x.

Examples:

- Laravel packages must support Laravel 13.
- Testing strategy must use Laravel 13 conventions.
- New ADRs must reference Laravel 13 baseline.


---

## References

- Constitution v1.3.0
- Spec01 Technical Foundation
- TASK-F01-005
- Phase 1 Completion Report
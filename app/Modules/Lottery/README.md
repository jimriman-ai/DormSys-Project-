# Lottery Module (spec06)

Bounded context for **Lottery Selection** per **CD-011**: programs, registrations, deterministic scoring, draw results.

## Scope

- **LotteryProgram**, **LotteryRegistration**, **LotteryResult** aggregates
- Program lifecycle state machine (AP-05)
- Migrations under `database/migrations/modules/lottery/`

## Integration boundaries

| Direction | Contract | Notes |
| --------- | -------- | ----- |
| Inbound (R4) | `RequestReadContract` (spec05) | Approved lottery registration requests — read-only |
| Inbound stub | `EmployeeLotteryScorePort` | Base score until Employee supplier extends |
| Outbound (R5) | `LotteryResultReadContract` | Allocation consumer stub in foundation phase |

## Rules

- No cross-module Eloquent queries
- `request_id`, `employee_id`, `dormitory_id` stored as UUID references without FK
- Scoring formula loaded from `settings` at lock (never hardcoded)

## Foundation status

Phases 1–2 (T001–T018): module wiring, schema, domain models, state machine, repositories.

Phase 3 (T019–T026): program lifecycle actions — create, open/close registration, cancel.

Phase 4 (T027–T032): registration enrollment via `LotteryRequestReadPort` / `RequestReadAdapter`.

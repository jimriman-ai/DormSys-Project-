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
| Outbound (R5) | `LotteryResultReadContract` | Public shape: `program_id`, `winners`, `reserves`, `ranks` — see `specs/006-lottery-selection/contracts/lottery-result-read-service.md` |

## Rules

- No cross-module Eloquent queries
- `request_id`, `employee_id`, `dormitory_id` stored as UUID references without FK
- Scoring formula loaded from `settings` at lock (never hardcoded)

## Foundation status

Phases 1–2 (T001–T018): module wiring, schema, domain models, state machine, repositories.

Phase 3 (T019–T026): program lifecycle actions — create, open/close registration, cancel.

Phase 4 (T027–T032): registration enrollment via `LotteryRequestReadPort` / `RequestReadAdapter`.

Phase 5 (T033–T038): scoring engine, lock snapshot, deterministic scores at lock.

Phase 6 (T039–T044): draw execution, result persistence, allocation stub port.

Phase 7 (T045–T047): auto-lock and draw background jobs.

Phase 8 (T048–T051): lottery result read supplier contract.

Phase 9 (T052–T055): architecture gate, scoring reproducibility, PHPStan, Pint.

Phase 10: integration boundary verification (`LotteryIntegrationBoundaryTest`).

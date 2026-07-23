# Contract: Proposed Allocation Port (spec06 → spec07)

**Direction:** Outbound (R5)  
**Consumer:** Allocation module (spec07) — not implemented in spec06  
**Producer:** `ExecuteDrawAction` after successful draw

## Port

`App\Modules\Lottery\Application\Contracts\ProposedAllocationPort`

## Stub adapter

`App\Modules\Lottery\Infrastructure\Adapters\NullProposedAllocationAdapter`

No-op in MVP; spec07 will replace with a real consumer.

## Payload shape

Emitted once per draw for **winners only**:

```json
[
  {
    "program_id": "uuid",
    "lottery_result_id": "uuid",
    "registration_id": "uuid",
    "employee_id": "uuid",
    "dormitory_id": "uuid",
    "rank": 1
  }
]
```

`lottery_result_id` is `lottery_results.id` (A2 CLOSED Option A — `.dormSys/open-decisions.md`).

## Rules

- Called only after results are persisted and program reaches `Completed`
- Idempotent draw does not re-emit (results already exist)
- No Allocation module logic in spec06

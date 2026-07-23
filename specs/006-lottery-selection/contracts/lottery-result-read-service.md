# Contract: Lottery Result Read Service (spec06 → spec07)

**Direction:** Outbound supplier (R5)  
**Consumer:** Allocation (spec07)  
**Interface:** `App\Modules\Lottery\Application\Contracts\LotteryResultReadContract`  
**Implementation:** `App\Modules\Lottery\Application\Services\LotteryResultReadService`

## Method

| Method | Input | Output |
| ------ | ----- | ------ |
| `resultsForProgram(LotteryProgramId)` | Program identifier | Public contract payload (below) |

## Public contract output shape

The supplier returns a single associative array with **exactly** these top-level keys:

```json
{
  "program_id": "uuid",
  "winners": [
    { "lottery_result_id": "uuid", "registration_id": "uuid", "rank": 1 }
  ],
  "reserves": [
    { "lottery_result_id": "uuid", "registration_id": "uuid", "rank": 2 }
  ],
  "ranks": [
    { "rank": 1, "lottery_result_id": "uuid", "registration_id": "uuid", "outcome": "winner" },
    { "rank": 2, "lottery_result_id": "uuid", "registration_id": "uuid", "outcome": "reserve" }
  ]
}
```

### Field definitions

| Field | Type | Description |
| ----- | ---- | ----------- |
| `program_id` | string (UUID) | Lottery program identifier |
| `winners` | list | Winner rows only |
| `reserves` | list | Reserve rows only |
| `ranks` | list | Full ordered outcome list |

### Winner / reserve row

| Field | Type |
| ----- | ---- |
| `lottery_result_id` | string (UUID) — `lottery_results.id` (A2 CLOSED Option A) |
| `registration_id` | string (UUID) |
| `rank` | int |

### Rank row

| Field | Type |
| ----- | ---- |
| `rank` | int |
| `lottery_result_id` | string (UUID) — `lottery_results.id` (A2 CLOSED Option A) |
| `registration_id` | string (UUID) |
| `outcome` | string (`winner` \| `reserve`) |

## Structural rules

- Read-only contract — no mutations
- No persistence model fields (table names, Eloquent attributes, internal IDs)
- No business logic or transformation rules in this document — shape only
- When no results exist, `winners`, `reserves`, and `ranks` are empty lists; `program_id` is still present

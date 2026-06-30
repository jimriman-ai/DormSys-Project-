# Contract: Lottery Result Read Service (spec06 → spec07)

**Direction:** Outbound supplier (R5)  
**Consumer:** Allocation (spec07)  
**Foundation:** `NullLotteryResultReadAdapter`

## Interface

`App\Modules\Lottery\Application\Contracts\LotteryResultReadContract`

| Method | Returns | Purpose |
| ------ | ------- | ------- |
| `resultsForProgram(LotteryProgramId)` | `list<array>` | Winner/reserve rows for a completed draw |

## Payload shape

```php
[
    'registration_id' => string, // UUID
    'program_id' => string,      // UUID
    'rank' => int,
    'outcome' => string,         // winner | reserve
]
```

## Rules

- No Allocation module code in spec06
- Full read service implementation follows draw completion (US4)

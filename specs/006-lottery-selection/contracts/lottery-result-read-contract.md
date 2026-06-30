# Contract: Lottery Result Read Service (spec06 → spec07)

**Canonical contract:** [lottery-result-read-service.md](./lottery-result-read-service.md)

**Direction:** Outbound supplier (R5)  
**Consumer:** Allocation (spec07)

## Interface

`App\Modules\Lottery\Application\Contracts\LotteryResultReadContract`

## Rules

- Read-only supplier contract for draw outcomes
- No Allocation module code in spec06
- Public output shape is defined in `lottery-result-read-service.md`

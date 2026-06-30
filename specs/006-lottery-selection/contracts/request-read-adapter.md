# Contract: Request Read Consumption (spec06)

**Direction:** Inbound (R4)  
**Supplier:** spec05 `RequestReadContract`  
**Consumer:** Lottery enrollment (US2)

## Port

`App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort`

## Adapter

`App\Modules\Lottery\Application\Adapters\RequestReadAdapter`

Delegates to `RequestReadContract::listApprovedByType('lottery_registration')` and maps to `ApprovedLotteryRequestDTO`.

## Rules

- Read-only — no Request lifecycle mutations
- Approved `lottery_registration` type only
- Dormitory on request must match program at enrollment time

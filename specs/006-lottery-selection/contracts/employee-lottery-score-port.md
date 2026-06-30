# Contract: Employee Lottery Score Port (spec06)

**Direction:** Inbound stub  
**Consumer:** Lottery scoring (US3)  
**Supplier:** Employee module (future)  
**Foundation:** `NullEmployeeLotteryScoreAdapter`

## Interface

`App\Modules\Lottery\Application\Contracts\EmployeeLotteryScorePort`

| Method | Returns | Purpose |
| ------ | ------- | ------- |
| `baseScoreFor(EmployeeReferenceId)` | `float` | Normalized base lottery score |
| `departmentPriorityFor(EmployeeReferenceId)` | `int` | Department priority weight input |

## Rules

- Lottery MUST NOT query Employee Eloquent models
- Stub returns neutral values until Employee supplier is live (OA-06-02)
